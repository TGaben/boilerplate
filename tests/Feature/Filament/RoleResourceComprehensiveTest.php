<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\RoleResource;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleResourceComprehensiveTest extends TestCase
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
        /** @var User $adminUser */
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
        ]);
        $this->adminUser = $adminUser;
        $this->adminUser->assignRole($this->adminRole);

        // Create regular user
        /** @var User $regularUser */
        $regularUser = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@test.com',
        ]);
        $this->regularUser = $regularUser;
        $this->regularUser->assignRole($this->userRole);

        // Force refresh permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function test_admin_can_access_role_resource_index(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/roles');

        $response->assertOk();
    }

    public function test_regular_user_cannot_access_role_resource(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->get('/admin/roles');

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_role_resource(): void
    {
        $response = $this->get('/admin/roles');

        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_create_role_page(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/roles/create');

        $response->assertOk();
    }

    public function test_admin_can_access_edit_role_page(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get("/admin/roles/{$this->adminRole->id}/edit");

        $response->assertOk();
    }

    public function test_role_resource_policy_works(): void
    {
        $this->actingAs($this->adminUser);
        $this->assertTrue(RoleResource::canViewAny());
        $this->assertTrue(RoleResource::canCreate());

        $this->actingAs($this->regularUser);
        $this->assertFalse(RoleResource::canViewAny());
        $this->assertFalse(RoleResource::canCreate());
    }

    public function test_role_creation_works(): void
    {
        $role = Role::create([
            'name' => 'test_role',
            'guard_name' => 'web',
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'test_role',
            'guard_name' => 'web',
        ]);
    }

    public function test_role_can_be_assigned_permissions(): void
    {
        $role = Role::create(['name' => 'test_role', 'guard_name' => 'web']);
        $permission = Permission::where('name', 'view_any_user')->first();

        $role->givePermissionTo($permission);

        $this->assertTrue($role->hasPermissionTo($permission));
    }

    public function test_role_can_be_assigned_to_users(): void
    {
        $role = Role::create(['name' => 'test_role', 'guard_name' => 'web']);
        /** @var User $user */
        $user = User::factory()->create();

        $user->assignRole($role);

        $this->assertTrue($user->hasRole($role));
    }

    public function test_role_permissions_sync(): void
    {
        $role = Role::create(['name' => 'test_role', 'guard_name' => 'web']);
        $permission1 = Permission::where('name', 'view_any_user')->first();
        $permission2 = Permission::where('name', 'create_user')->first();

        // Assign permissions
        $role->givePermissionTo([$permission1, $permission2]);

        $this->assertTrue($role->hasPermissionTo($permission1));
        $this->assertTrue($role->hasPermissionTo($permission2));

        // Remove one permission
        $role->revokePermissionTo($permission1);

        $this->assertFalse($role->hasPermissionTo($permission1));
        $this->assertTrue($role->hasPermissionTo($permission2));
    }

    public function test_system_roles_exist(): void
    {
        $this->assertNotNull(Role::where('name', 'admin')->first());
        $this->assertNotNull(Role::where('name', 'user')->first());
    }

    public function test_admin_role_has_all_permissions(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $allPermissions = Permission::all();

        foreach ($allPermissions as $permission) {
            $this->assertTrue(
                $adminRole->hasPermissionTo($permission),
                "Admin role should have permission: {$permission->name}",
            );
        }
    }

    public function test_user_role_has_limited_permissions(): void
    {
        $userRole = Role::where('name', 'user')->first();

        // User role should not have admin permissions
        $this->assertFalse($userRole->hasPermissionTo('view_any_user'));
        $this->assertFalse($userRole->hasPermissionTo('delete_user'));
    }

    public function test_role_guard_name_consistency(): void
    {
        $roles = Role::all();

        foreach ($roles as $role) {
            $this->assertEquals('web', $role->guard_name);
        }
    }

    public function test_role_users_relationship(): void
    {
        $this->assertTrue($this->adminRole->users()->count() > 0);
        $this->assertTrue($this->adminRole->users->contains($this->adminUser));
    }

    public function test_role_permissions_relationship(): void
    {
        $this->assertTrue($this->adminRole->permissions()->count() > 0);
    }

    public function test_role_can_be_deleted(): void
    {
        $role = Role::create(['name' => 'deletable_role', 'guard_name' => 'web']);
        $roleId = $role->id;

        $role->delete();

        $this->assertDatabaseMissing('roles', ['id' => $roleId]);
    }

    public function test_role_name_must_be_unique(): void
    {
        Role::create(['name' => 'unique_role', 'guard_name' => 'web']);

        $this->expectException(\Spatie\Permission\Exceptions\RoleAlreadyExists::class);
        Role::create(['name' => 'unique_role', 'guard_name' => 'web']);
    }

    public function test_role_resource_uses_correct_model(): void
    {
        $this->assertEquals(Role::class, RoleResource::getModel());
    }

    public function test_multiple_users_can_have_same_role(): void
    {
        $role = Role::create(['name' => 'shared_role', 'guard_name' => 'web']);
        /** @var User $user1 */
        $user1 = User::factory()->create();
        /** @var User $user2 */
        $user2 = User::factory()->create();

        $user1->assignRole($role);
        $user2->assignRole($role);

        $this->assertTrue($user1->hasRole($role));
        $this->assertTrue($user2->hasRole($role));
        $this->assertEquals(2, $role->users()->count());
    }

    public function test_role_timestamps_exist(): void
    {
        $role = Role::create(['name' => 'timestamp_test_role', 'guard_name' => 'web']);

        $this->assertNotNull($role->created_at);
        $this->assertNotNull($role->updated_at);
    }

    public function test_role_permission_counts(): void
    {
        // Admin role should have all permissions
        $adminRole = Role::where('name', 'admin')->first();
        $totalPermissions = Permission::count();

        $this->assertEquals($totalPermissions, $adminRole->permissions()->count());

        // User role should have no permissions by default
        $userRole = Role::where('name', 'user')->first();
        $this->assertEquals(0, $userRole->permissions()->count());
    }

    public function test_seeded_roles_count(): void
    {
        // Should have exactly 2 roles: admin and user
        $this->assertEquals(2, Role::count());
    }
}
