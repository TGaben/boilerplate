<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Testing\TestResponse;

/**
 * Filament Test Helper Trait
 *
 * Provides helper methods for testing Filament admin panel functionality.
 * Includes admin panel access testing, resource testing, and navigation testing.
 */
trait FilamentTestHelpers
{
    /**
     * Get the admin panel instance
     */
    protected function getAdminPanel(): Panel
    {
        return Filament::getPanel('admin');
    }

    /**
     * Assert that a user can access the admin panel
     *
     * @param User $user
     * @param string $message
     */
    protected function assertUserCanAccessAdminPanel(User $user, string $message = ''): void
    {
        $this->assertTrue(
            $user->canAccessPanel($this->getAdminPanel()),
            $message ?: "User {$user->name} should be able to access the admin panel",
        );
    }

    /**
     * Assert that a user cannot access the admin panel
     *
     * @param User $user
     * @param string $message
     */
    protected function assertUserCannotAccessAdminPanel(User $user, string $message = ''): void
    {
        $this->assertFalse(
            $user->canAccessPanel($this->getAdminPanel()),
            $message ?: "User {$user->name} should not be able to access the admin panel",
        );
    }

    /**
     * Assert that a user has a specific Filament permission/ability
     * This is different from Spatie permissions - this checks Filament resource policies
     *
     * @param User $user
     * @param string $permission Permission/ability name (e.g., 'viewAny', 'create', 'update')
     * @param string $resourceClass Filament resource class name
     * @param string $message
     */
    protected function assertUserHasFilamentPermission(User $user, string $permission, string $resourceClass, string $message = ''): void
    {
        $this->actingAs($user);

        $methodName = match($permission) {
            'viewAny' => 'canViewAny',
            'create' => 'canCreate',
            'update' => 'canEdit',
            'delete' => 'canDelete',
            'deleteAny' => 'canDeleteAny',
            'view' => 'canView',
            default => 'can' . ucfirst($permission)
        };

        if (method_exists($resourceClass, $methodName)) {
            $this->assertTrue(
                $resourceClass::{$methodName}(),
                $message ?: "User {$user->name} should have '{$permission}' permission for {$resourceClass}",
            );
        } else {
            $this->fail("Method '{$methodName}' does not exist on {$resourceClass}");
        }
    }

    /**
     * Assert that a user does not have a specific Filament permission/ability
     *
     * @param User $user
     * @param string $permission Permission/ability name
     * @param string $resourceClass Filament resource class name
     * @param string $message
     */
    protected function assertUserDoesNotHaveFilamentPermission(User $user, string $permission, string $resourceClass, string $message = ''): void
    {
        $this->actingAs($user);

        $methodName = match($permission) {
            'viewAny' => 'canViewAny',
            'create' => 'canCreate',
            'update' => 'canEdit',
            'delete' => 'canDelete',
            'deleteAny' => 'canDeleteAny',
            'view' => 'canView',
            default => 'can' . ucfirst($permission)
        };

        if (method_exists($resourceClass, $methodName)) {
            $this->assertFalse(
                $resourceClass::{$methodName}(),
                $message ?: "User {$user->name} should not have '{$permission}' permission for {$resourceClass}",
            );
        } else {
            $this->fail("Method '{$methodName}' does not exist on {$resourceClass}");
        }
    }

    /**
     * Test admin panel route access with various scenarios
     *
     * @param string $route Admin panel route (e.g., '/admin', '/admin/users')
     * @param User|null $adminUser Admin user for positive test
     * @param User|null $regularUser Regular user for negative test
     *
     * @return array Test results
     */
    protected function testAdminRouteAccess(string $route, ?User $adminUser = null, ?User $regularUser = null): array
    {
        $results = [];

        // Test guest access - should redirect to login
        $response = $this->get($route);
        $results['guest'] = [
            'status' => $response->getStatusCode(),
            'redirectsToLogin' => $response->isRedirect() && str_contains((string) $response->headers->get('Location', ''), '/admin/login'),
        ];

        // Test admin access - should be successful
        if ($adminUser) {
            $this->actingAs($adminUser);
            $response = $this->get($route);
            $results['admin'] = [
                'status' => $response->getStatusCode(),
                'successful' => $response->isOk(),
            ];
        }

        // Test regular user access - should be forbidden
        if ($regularUser) {
            $this->actingAs($regularUser);
            $response = $this->get($route);
            $results['regular_user'] = [
                'status' => $response->getStatusCode(),
                'forbidden' => $response->getStatusCode() === 403,
            ];
        }

        return $results;
    }

    /**
     * Assert that admin route access works correctly for different user types
     *
     * @param string $route
     * @param User|null $adminUser
     * @param User|null $regularUser
     */
    protected function assertAdminRouteAccessible(string $route, ?User $adminUser = null, ?User $regularUser = null): void
    {
        // Guest should be redirected to login, forbidden, or not found (depending on route)
        $guestResponse = $this->get($route);
        $this->assertTrue(
            $guestResponse->isRedirect() || in_array($guestResponse->getStatusCode(), [403, 404]),
            "Guest should be redirected, forbidden, or not found, got status: " . $guestResponse->getStatusCode()
        );

        // Admin should be able to access
        if ($adminUser) {
            $this->actingAs($adminUser)
                ->get($route)
                ->assertOk();
        }

        // Regular user should be forbidden
        if ($regularUser) {
            $this->actingAs($regularUser)
                ->get($route)
                ->assertStatus(403);
        }
    }

    /**
     * Test Filament resource CRUD routes
     *
     * @param string $resourcePath Resource path (e.g., 'users', 'roles')
     * @param User $adminUser
     * @param User|null $regularUser
     * @param int|null $recordId Record ID for edit/view tests
     */
    protected function testFilamentResourceAccess(string $resourcePath, User $adminUser, ?User $regularUser = null, ?int $recordId = null): void
    {
        $routes = [
            'index' => "/admin/{$resourcePath}",
            'create' => "/admin/{$resourcePath}/create",
        ];

        if ($recordId) {
            $routes['edit'] = "/admin/{$resourcePath}/{$recordId}/edit";
            $routes['view'] = "/admin/{$resourcePath}/{$recordId}";
        }

        foreach ($routes as $action => $route) {
            $this->assertAdminRouteAccessible($route, $adminUser, $regularUser);
        }
    }

    /**
     * Assert that admin dashboard loads correctly
     *
     * @param User $adminUser
     */
    protected function assertAdminDashboardLoads(User $adminUser): void
    {
        $this->actingAs($adminUser)
            ->get('/admin')
            ->assertOk()
            ->assertSee($adminUser->name); // Should see user's name in dashboard
    }

    /**
     * Assert that admin navigation contains expected elements
     *
     * @param User $adminUser
     * @param array $expectedNavigationItems
     */
    protected function assertAdminNavigationContains(User $adminUser, array $expectedNavigationItems): void
    {
        $response = $this->actingAs($adminUser)->get('/admin');

        $content = $response->getContent();

        foreach ($expectedNavigationItems as $item) {
            $this->assertStringContainsString($item, $content, "Navigation should contain '{$item}'");
        }
    }

    /**
     * Test admin logout functionality
     *
     * @param User $adminUser
     */
    protected function testAdminLogout(User $adminUser): TestResponse
    {
        $this->actingAs($adminUser);

        $response = $this->post('/admin/logout');

        $response->assertRedirect('/admin/login');
        $this->assertGuest();

        return $response;
    }

    /**
     * Assert that session handling works correctly in admin panel
     *
     * @param User $adminUser
     */
    protected function assertAdminSessionHandling(User $adminUser): void
    {
        $response = $this->actingAs($adminUser)->get('/admin');

        $response->assertOk();
        $response->assertSessionHas('_token');
        $this->assertNotEmpty($this->app['session']->token());
    }

    /**
     * Test admin panel performance (load time)
     *
     * @param User $adminUser
     * @param float $maxLoadTime Maximum acceptable load time in seconds
     */
    protected function assertAdminPerformance(User $adminUser, float $maxLoadTime = 3.0): void
    {
        $this->actingAs($adminUser);

        $startTime = microtime(true);
        $response = $this->get('/admin');
        $endTime = microtime(true);

        $loadTime = $endTime - $startTime;

        $response->assertOk();
        $this->assertLessThan(
            $maxLoadTime,
            $loadTime,
            "Admin dashboard should load within {$maxLoadTime} seconds, took {$loadTime} seconds",
        );
    }

    /**
     * Assert that admin panel handles database queries efficiently
     *
     * @param User $adminUser
     * @param int $maxQueries Maximum acceptable number of database queries
     */
    protected function assertAdminQueryEfficiency(User $adminUser, int $maxQueries = 30): void
    {
        $this->actingAs($adminUser);

        \Illuminate\Support\Facades\DB::enableQueryLog();

        $response = $this->get('/admin');

        $queries = \Illuminate\Support\Facades\DB::getQueryLog();

        $response->assertOk();
        $this->assertLessThan(
            $maxQueries,
            count($queries),
            "Admin dashboard should execute fewer than {$maxQueries} queries, executed " . count($queries),
        );
    }

    /**
     * Test admin panel with different locales
     *
     * @param User $adminUser
     * @param array $locales Array of locale codes to test
     */
    protected function testAdminLocales(User $adminUser, array $locales = ['en', 'hu']): void
    {
        foreach ($locales as $locale) {
            $response = $this->actingAs($adminUser)
                ->withSession(['locale' => $locale])
                ->get('/admin');

            $response->assertOk();
        }
    }

    /**
     * Assert that admin panel is responsive (contains viewport meta tag)
     *
     * @param User $adminUser
     */
    protected function assertAdminResponsive(User $adminUser): void
    {
        $response = $this->actingAs($adminUser)->get('/admin');

        $content = $response->getContent();
        $this->assertStringContainsString('viewport', $content, 'Admin panel should contain viewport meta tag for responsive design');
    }

    /**
     * Test admin panel error handling
     *
     * @param User $adminUser
     */
    protected function testAdminErrorHandling(User $adminUser): void
    {
        $this->actingAs($adminUser);

        // Test invalid route
        $this->get('/admin/invalid-route-12345')
            ->assertStatus(404);
    }

    /**
     * Create a test activity log entry for testing purposes
     *
     * @param User $user
     * @param string $description
     * @param string $logName
     *
     * @return \Spatie\Activitylog\Models\Activity
     */
    protected function createTestActivity(User $user, string $description = 'Test activity', string $logName = 'test'): \Spatie\Activitylog\Models\Activity
    {
        return \Spatie\Activitylog\Models\Activity::create([
            'log_name' => $logName,
            'description' => $description,
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'causer_type' => User::class,
            'causer_id' => $user->id,
        ]);
    }
}
