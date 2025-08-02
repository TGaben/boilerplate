<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    #[Test]
    public function guest_cannot_access_role_resource(): void
    {
        $response = $this->get('/admin/roles');

        $response->assertRedirect('/admin/login');
    }

    #[Test]
    public function user_without_admin_role_cannot_access_role_resource(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user'); // User role has no admin permissions

        $response = $this->actingAs($user)->get('/admin/roles');

        $response->assertStatus(403); // Forbidden
    }

    #[Test]
    public function admin_user_can_access_role_resource(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Test RoleResource permissions directly
        $this->actingAs($admin);
        $this->assertTrue(\App\Filament\Resources\RoleResource::canViewAny());
        $this->assertTrue(\App\Filament\Resources\RoleResource::canCreate());
    }

    #[Test]
    public function admin_user_from_seeder_can_access_role_resource(): void
    {
        $adminUser = User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($adminUser);
        $this->assertTrue($adminUser->hasRole('admin'));

        // Test RoleResource permissions directly
        $this->actingAs($adminUser);
        $this->assertTrue(\App\Filament\Resources\RoleResource::canViewAny());
        $this->assertTrue(\App\Filament\Resources\RoleResource::canCreate());
    }

    #[Test]
    public function admin_can_view_roles_via_policy(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $role = Role::where('name', 'user')->first();
        $this->assertNotNull($role);

        $this->assertTrue($admin->can('viewAny', Role::class));
        $this->assertTrue($admin->can('view', $role));
    }

    #[Test]
    public function non_admin_cannot_view_roles_via_policy(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $role = Role::where('name', 'admin')->first();
        $this->assertNotNull($role);

        $this->assertFalse($user->can('viewAny', Role::class));
        $this->assertFalse($user->can('view', $role));
    }

    #[Test]
    public function admin_can_create_roles_via_policy(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->assertTrue($admin->can('create', Role::class));
    }

    #[Test]
    public function non_admin_cannot_create_roles_via_policy(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->assertFalse($user->can('create', Role::class));
    }

    #[Test]
    public function admin_can_update_roles_via_policy(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $role = Role::where('name', 'user')->first();
        $this->assertNotNull($role);

        $this->assertTrue($admin->can('update', $role));
    }

    #[Test]
    public function non_admin_cannot_update_roles_via_policy(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $role = Role::where('name', 'admin')->first();
        $this->assertNotNull($role);

        $this->assertFalse($user->can('update', $role));
    }

    #[Test]
    public function admin_can_delete_roles_via_policy(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $role = Role::create(['name' => 'temporary_role', 'guard_name' => 'web']);

        $this->assertTrue($admin->can('delete', $role));
    }

    #[Test]
    public function non_admin_cannot_delete_roles_via_policy(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $role = Role::where('name', 'admin')->first();
        $this->assertNotNull($role);

        $this->assertFalse($user->can('delete', $role));
    }

    #[Test]
    public function roles_are_available_in_system(): void
    {
        $this->assertDatabaseHas('roles', ['name' => 'admin']);
        $this->assertDatabaseHas('roles', ['name' => 'user']);

        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'user')->first();

        $this->assertNotNull($adminRole);
        $this->assertNotNull($userRole);
    }

    #[Test]
    public function admin_role_has_permissions_assigned(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $this->assertNotNull($adminRole);

        // Admin role should have permissions assigned
        $this->assertGreaterThan(0, $adminRole->permissions()->count());

        // Check for specific permissions
        $this->assertTrue($adminRole->hasPermissionTo('access admin panel'));
        $this->assertTrue($adminRole->hasPermissionTo('manage users'));
    }

    #[Test]
    public function user_role_has_no_admin_permissions(): void
    {
        $userRole = Role::where('name', 'user')->first();
        $this->assertNotNull($userRole);

        // User role should have no permissions
        $this->assertEquals(0, $userRole->permissions()->count());
    }

    #[Test]
    public function permissions_exist_in_system(): void
    {
        $expectedPermissions = [
            'access admin panel',
            'manage users',
            'view reports',
            'view_any_user',
            'view_user',
            'create_user',
            'update_user',
            'delete_user',
        ];

        foreach ($expectedPermissions as $permissionName) {
            $this->assertDatabaseHas('permissions', ['name' => $permissionName]);
        }
    }

    #[Test]
    public function role_resource_uses_correct_model(): void
    {
        $this->assertEquals(Role::class, \App\Filament\Resources\RoleResource::getModel());
    }

    #[Test]
    public function permission_resource_uses_correct_model(): void
    {
        $this->assertEquals(Permission::class, \App\Filament\Resources\PermissionResource::getModel());
    }

    #[Test]
    public function admin_can_access_permission_resource(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Test PermissionResource permissions directly
        $this->actingAs($admin);
        $this->assertTrue(\App\Filament\Resources\PermissionResource::canViewAny());

        // Should not be able to create/edit permissions (security)
        $this->assertFalse(\App\Filament\Resources\PermissionResource::canCreate());
    }

    #[Test]
    public function non_admin_cannot_access_permission_resource(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        // Test PermissionResource permissions directly
        $this->actingAs($user);
        $this->assertFalse(\App\Filament\Resources\PermissionResource::canViewAny());
        $this->assertFalse(\App\Filament\Resources\PermissionResource::canCreate());
    }
}
