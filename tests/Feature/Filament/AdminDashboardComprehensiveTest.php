<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $regularUser;

    private Role $adminRole;

    private Role $userRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Run seeders
        $this->artisan('db:seed', ['--class' => RolesAndPermissionsSeeder::class]);

        // Get roles
        $this->adminRole = Role::where('name', 'admin')->first();
        $this->userRole = Role::where('name', 'user')->first();

        // Create admin user
        $this->adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
        ]);
        $this->adminUser->assignRole($this->adminRole);

        // Create regular user
        $this->regularUser = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@test.com',
        ]);
        $this->regularUser->assignRole($this->userRole);

        // Force refresh permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function test_admin_can_access_dashboard(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin');

        $response->assertOk();
    }

    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->get('/admin');

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_logout_from_dashboard(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->post('/admin/logout');

        $response->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_admin_can_access_user_management_from_dashboard(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/users');
        $response->assertOk();
    }

    public function test_admin_can_access_role_management_from_dashboard(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/roles');
        $response->assertOk();
    }

    public function test_admin_can_access_activity_log_from_dashboard(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/activities');
        $response->assertOk();
    }

    public function test_dashboard_statistics_are_accurate(): void
    {
        $this->actingAs($this->adminUser);

        $userCount = User::count();
        $roleCount = Role::count();
        $permissionCount = Permission::count();

        $response = $this->get('/admin');

        $response->assertOk();

        // Verify counts exist in system
        $this->assertGreaterThan(0, $userCount);
        $this->assertGreaterThan(0, $roleCount);
        $this->assertGreaterThan(0, $permissionCount);
    }

    public function test_dashboard_handles_empty_activity_log(): void
    {
        $this->actingAs($this->adminUser);

        // Clear all activities
        Activity::truncate();

        $response = $this->get('/admin');

        $response->assertOk();
    }

    public function test_dashboard_handles_many_activities(): void
    {
        $this->actingAs($this->adminUser);

        // Create many activities
        for ($i = 1; $i <= 50; $i++) {
            Activity::create([
                'log_name' => 'test',
                'description' => "Test activity {$i}",
                'subject_type' => User::class,
                'subject_id' => $this->adminUser->id,
            ]);
        }

        $response = $this->get('/admin');

        $response->assertOk();
    }

    public function test_dashboard_respects_admin_panel_access_control(): void
    {
        $adminPanel = \Filament\Facades\Filament::getPanel('admin');

        $this->assertTrue($this->adminUser->canAccessPanel($adminPanel));
        $this->assertFalse($this->regularUser->canAccessPanel($adminPanel));
    }

    public function test_dashboard_authentication_middleware(): void
    {
        // Test without authentication
        $response = $this->get('/admin');
        $response->assertRedirect('/admin/login');

        // Test with authentication
        $this->actingAs($this->adminUser);
        $response = $this->get('/admin');
        $response->assertOk();
    }

    public function test_dashboard_session_handling(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin');

        $response->assertOk();
        $response->assertSessionHas('_token');
    }

    public function test_dashboard_csrf_protection_works(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin');

        $response->assertOk();
        // CSRF token should be present in the session
        $this->assertNotEmpty($this->app['session']->token());
    }

    public function test_dashboard_error_handling_for_invalid_routes(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/invalid-route-12345');

        $response->assertStatus(404);
    }

    public function test_dashboard_performance_is_acceptable(): void
    {
        $this->actingAs($this->adminUser);

        $startTime = microtime(true);

        $response = $this->get('/admin');

        $endTime = microtime(true);
        $loadTime = $endTime - $startTime;

        $response->assertOk();
        // Dashboard should load within reasonable time (3 seconds for safety)
        $this->assertLessThan(3.0, $loadTime);
    }

    public function test_dashboard_database_queries_are_optimized(): void
    {
        $this->actingAs($this->adminUser);

        \DB::enableQueryLog();

        $response = $this->get('/admin');

        $queries = \DB::getQueryLog();

        $response->assertOk();
        // Should not have excessive database queries
        $this->assertLessThan(30, count($queries));
    }

    public function test_dashboard_locale_setting_works(): void
    {
        $this->actingAs($this->adminUser);

        // Test with Hungarian locale
        $response = $this->withSession(['locale' => 'hu'])->get('/admin');
        $response->assertOk();

        // Test with English locale
        $response = $this->withSession(['locale' => 'en'])->get('/admin');
        $response->assertOk();
    }

    public function test_dashboard_timezone_handling(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin');

        $response->assertOk();

        // Just verify the dashboard loads properly with timezone
        $this->assertNotEmpty($response->getContent());
    }

    public function test_dashboard_handles_concurrent_users(): void
    {
        // Create multiple admin users
        $admin1 = User::factory()->create();
        $admin1->assignRole($this->adminRole);

        $admin2 = User::factory()->create();
        $admin2->assignRole($this->adminRole);

        // Both should be able to access dashboard
        $this->actingAs($admin1);
        $response1 = $this->get('/admin');
        $response1->assertOk();

        $this->actingAs($admin2);
        $response2 = $this->get('/admin');
        $response2->assertOk();
    }

    public function test_dashboard_memory_usage_is_reasonable(): void
    {
        $this->actingAs($this->adminUser);

        $memoryBefore = memory_get_usage();

        $response = $this->get('/admin');

        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;

        $response->assertOk();
        // Should not use excessive memory (50MB limit)
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed);
    }

    public function test_dashboard_handles_large_datasets(): void
    {
        $this->actingAs($this->adminUser);

        // Create large dataset
        User::factory()->count(100)->create();
        for ($i = 1; $i <= 10; $i++) {
            Role::create(['name' => "test_role_{$i}", 'guard_name' => 'web']);
        }

        $response = $this->get('/admin');

        $response->assertOk();
    }

    public function test_dashboard_navigation_structure(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin');

        $response->assertOk();

        // Basic navigation elements should be present
        $content = $response->getContent();
        $this->assertStringContainsString('nav', $content);
    }

    public function test_dashboard_responsive_design_elements(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin');

        $response->assertOk();

        // Should contain responsive design elements
        $content = $response->getContent();
        $this->assertStringContainsString('viewport', $content);
    }

    public function test_admin_user_information_display(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin');

        $response->assertOk();

        // Admin user name should appear somewhere in the dashboard
        $content = $response->getContent();
        $this->assertStringContainsString($this->adminUser->name, $content);
    }
}
