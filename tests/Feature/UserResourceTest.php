<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    #[Test]
    public function guest_cannot_access_user_resource(): void
    {
        $response = $this->get('/admin/users');

        $response->assertRedirect('/admin/login');
    }

    #[Test]
    public function user_without_admin_role_cannot_access_user_resource(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user'); // User role has no admin permissions

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertStatus(403); // Forbidden
    }

    #[Test]
    public function admin_user_can_access_user_resource(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Test UserResource permissions directly
        $this->actingAs($admin);
        $this->assertTrue(\App\Filament\Resources\UserResource::canViewAny());
        $this->assertTrue(\App\Filament\Resources\UserResource::canCreate());
    }

    #[Test]
    public function admin_user_from_seeder_can_access_user_resource(): void
    {
        $adminUser = User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($adminUser);
        $this->assertTrue($adminUser->hasRole('admin'));

        // Test UserResource permissions directly
        $this->actingAs($adminUser);
        $this->assertTrue(\App\Filament\Resources\UserResource::canViewAny());
        $this->assertTrue(\App\Filament\Resources\UserResource::canCreate());
    }

    #[Test]
    public function admin_cannot_delete_themselves_via_policy(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Test the policy directly
        $this->assertFalse($admin->can('delete', $admin));
    }

    #[Test]
    public function admin_can_delete_other_users_via_policy(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $otherUser = User::factory()->create();
        $otherUser->assignRole('user');

        // Test the policy directly
        $this->assertTrue($admin->can('delete', $otherUser));
    }

    #[Test]
    public function non_admin_cannot_delete_any_users_via_policy(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $otherUser = User::factory()->create();

        // Test the policy directly
        $this->assertFalse($user->can('delete', $otherUser));
        $this->assertFalse($user->can('delete', $user)); // Can't even delete self
    }

    #[Test]
    public function admin_can_view_any_users_via_policy(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->assertTrue($admin->can('viewAny', User::class));
    }

    #[Test]
    public function non_admin_cannot_view_any_users_via_policy(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->assertFalse($user->can('viewAny', User::class));
    }

    #[Test]
    public function admin_can_create_users_via_policy(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->assertTrue($admin->can('create', User::class));
    }

    #[Test]
    public function non_admin_cannot_create_users_via_policy(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->assertFalse($user->can('create', User::class));
    }

    #[Test]
    public function admin_can_update_users_via_policy(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $otherUser = User::factory()->create();

        $this->assertTrue($admin->can('update', $otherUser));
        $this->assertTrue($admin->can('update', $admin)); // Can update self
    }

    #[Test]
    public function non_admin_cannot_update_users_via_policy(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $otherUser = User::factory()->create();

        $this->assertFalse($user->can('update', $otherUser));
        $this->assertFalse($user->can('update', $user)); // Can't even update self
    }

    #[Test]
    public function admin_can_view_specific_users_via_policy(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $otherUser = User::factory()->create();

        $this->assertTrue($admin->can('view', $otherUser));
        $this->assertTrue($admin->can('view', $admin));
    }

    #[Test]
    public function non_admin_cannot_view_specific_users_via_policy(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $otherUser = User::factory()->create();

        $this->assertFalse($user->can('view', $otherUser));
        $this->assertFalse($user->can('view', $user));
    }

    #[Test]
    public function policy_prevents_force_delete_of_self(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->assertFalse($admin->can('forceDelete', $admin));
    }

    #[Test]
    public function policy_allows_force_delete_of_others_for_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $otherUser = User::factory()->create();

        $this->assertTrue($admin->can('forceDelete', $otherUser));
    }

    #[Test]
    public function policy_prevents_restore_for_non_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $otherUser = User::factory()->create();

        $this->assertFalse($user->can('restore', $otherUser));
    }

    #[Test]
    public function policy_allows_restore_for_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $otherUser = User::factory()->create();

        $this->assertTrue($admin->can('restore', $otherUser));
    }
}
