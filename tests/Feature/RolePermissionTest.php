<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run the roles and permissions seeder
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    #[Test]
    public function test_user_can_be_assigned_role(): void
    {
        $user = User::factory()->create();

        $user->assignRole('admin');

        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('user'));
    }

    #[Test]
    public function test_user_can_be_assigned_multiple_roles(): void
    {
        $user = User::factory()->create();

        $user->assignRole(['admin', 'user']);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('user'));
    }

    #[Test]
    public function test_user_with_admin_role_has_admin_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertTrue($user->can('access admin panel'));
        $this->assertTrue($user->can('manage users'));
        $this->assertTrue($user->can('view reports'));
    }

    #[Test]
    public function test_user_with_user_role_has_no_admin_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->assertFalse($user->can('access admin panel'));
        $this->assertFalse($user->can('manage users'));
        $this->assertFalse($user->can('view reports'));
    }

    #[Test]
    public function test_roles_and_permissions_are_created_by_seeder(): void
    {
        $this->assertDatabaseHas('roles', ['name' => 'admin']);
        $this->assertDatabaseHas('roles', ['name' => 'user']);

        $this->assertDatabaseHas('permissions', ['name' => 'access admin panel']);
        $this->assertDatabaseHas('permissions', ['name' => 'manage users']);
        $this->assertDatabaseHas('permissions', ['name' => 'view reports']);
    }

    #[Test]
    public function test_admin_role_has_all_permissions(): void
    {
        $adminRole = Role::findByName('admin');

        $this->assertTrue($adminRole->hasPermissionTo('access admin panel'));
        $this->assertTrue($adminRole->hasPermissionTo('manage users'));
        $this->assertTrue($adminRole->hasPermissionTo('view reports'));
    }

    #[Test]
    public function test_user_role_has_no_permissions(): void
    {
        $userRole = Role::findByName('user');

        $this->assertFalse($userRole->hasPermissionTo('access admin panel'));
        $this->assertFalse($userRole->hasPermissionTo('manage users'));
        $this->assertFalse($userRole->hasPermissionTo('view reports'));
    }

    #[Test]
    public function test_seeder_is_idempotent(): void
    {
        // Run seeder again
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Should still have only 2 roles and 24 permissions (original 3 + 21 Resource permissions)
        $this->assertEquals(2, Role::count());
        $this->assertEquals(24, Permission::count());
    }
}
