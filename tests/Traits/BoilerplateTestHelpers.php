<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Boilerplate Test Helper Trait
 *
 * Provides common helper methods for testing boilerplate functionality.
 * Includes user creation, role management, and permission handling.
 */
trait BoilerplateTestHelpers
{
    /**
     * Clear the permission cache to ensure fresh permissions in tests
     */
    protected function clearPermissionCache(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Run the roles and permissions seeder
     */
    protected function seedRolesAndPermissions(): void
    {
        $this->artisan('db:seed', ['--class' => RolesAndPermissionsSeeder::class]);
    }

    /**
     * Get or create the admin role
     */
    protected function getAdminRole(): Role
    {
        return Role::where('name', 'admin')->first()
            ?? Role::create(['name' => 'admin', 'guard_name' => 'web']);
    }

    /**
     * Get or create the user role
     */
    protected function getUserRole(): Role
    {
        return Role::where('name', 'user')->first()
            ?? Role::create(['name' => 'user', 'guard_name' => 'web']);
    }

    /**
     * Create an admin user with admin role assigned
     *
     * @param array $attributes Additional user attributes
     *
     * @return User
     */
    protected function createAdminUser(array $attributes = []): User
    {
        $defaultAttributes = [
            'name' => 'Admin User',
            'email' => 'admin@test.com',
        ];

        /** @var User $user */
        $user = User::factory()->create(array_merge($defaultAttributes, $attributes));
        $user->assignRole($this->getAdminRole());

        return $user;
    }

    /**
     * Create a regular user with user role assigned
     *
     * @param array $attributes Additional user attributes
     *
     * @return User
     */
    protected function createRegularUser(array $attributes = []): User
    {
        $defaultAttributes = [
            'name' => 'Regular User',
            'email' => 'user@test.com',
        ];

        /** @var User $user */
        $user = User::factory()->create(array_merge($defaultAttributes, $attributes));
        $user->assignRole($this->getUserRole());

        return $user;
    }

    /**
     * Create a user with a specific role
     *
     * @param string $roleName Role name to assign
     * @param array $attributes Additional user attributes
     *
     * @return User
     */
    protected function createUserWithRole(string $roleName, array $attributes = []): User
    {
        $defaultAttributes = [
            'name' => "User with {$roleName} role",
            'email' => strtolower($roleName) . '@test.com',
        ];

        /** @var User $user */
        $user = User::factory()->create(array_merge($defaultAttributes, $attributes));

        // Get or create the role
        $role = Role::where('name', $roleName)->first()
            ?? Role::create(['name' => $roleName, 'guard_name' => 'web']);

        $user->assignRole($role);

        return $user;
    }

    /**
     * Create a user with specific permissions (directly assigned, not through roles)
     *
     * @param array $permissions Array of permission names
     * @param array $attributes Additional user attributes
     *
     * @return User
     */
    protected function createUserWithPermissions(array $permissions, array $attributes = []): User
    {
        $defaultAttributes = [
            'name' => 'User with custom permissions',
            'email' => 'user.permissions@test.com',
        ];

        /** @var User $user */
        $user = User::factory()->create(array_merge($defaultAttributes, $attributes));

        // Ensure permissions exist and assign them
        foreach ($permissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first()
                ?? Permission::create(['name' => $permissionName, 'guard_name' => 'web']);

            $user->givePermissionTo($permission);
        }

        return $user;
    }

    /**
     * Create multiple users with the same role
     *
     * @param string $roleName Role name to assign to all users
     * @param int $count Number of users to create
     * @param array $baseAttributes Base attributes for all users
     *
     * @return array Array of created users
     */
    protected function createUsersWithRole(string $roleName, int $count = 2, array $baseAttributes = []): array
    {
        $users = [];

        for ($i = 1; $i <= $count; $i++) {
            $attributes = array_merge($baseAttributes, [
                'name' => ($baseAttributes['name'] ?? 'Test User') . " {$i}",
                'email' => 'user' . $i . '.' . strtolower($roleName) . '@test.com',
            ]);

            $users[] = $this->createUserWithRole($roleName, $attributes);
        }

        return $users;
    }

    /**
     * Setup standard test environment with admin and regular users
     * Call this in your test's setUp() method
     *
     * @return array Returns ['adminUser' => User, 'regularUser' => User, 'adminRole' => Role, 'userRole' => Role]
     */
    protected function setupStandardTestUsers(): array
    {
        $this->clearPermissionCache();
        $this->seedRolesAndPermissions();

        $adminRole = $this->getAdminRole();
        $userRole = $this->getUserRole();

        $adminUser = $this->createAdminUser();
        $regularUser = $this->createRegularUser();

        $this->clearPermissionCache(); // Clear cache after setup

        return [
            'adminUser' => $adminUser,
            'regularUser' => $regularUser,
            'adminRole' => $adminRole,
            'userRole' => $userRole,
        ];
    }

    /**
     * Assert that a user has a specific role
     *
     * @param User $user
     * @param string $roleName
     * @param string $message
     */
    protected function assertUserHasRole(User $user, string $roleName, string $message = ''): void
    {
        $this->assertTrue(
            $user->hasRole($roleName),
            $message ?: "User {$user->name} should have role '{$roleName}'",
        );
    }

    /**
     * Assert that a user does not have a specific role
     *
     * @param User $user
     * @param string $roleName
     * @param string $message
     */
    protected function assertUserDoesNotHaveRole(User $user, string $roleName, string $message = ''): void
    {
        $this->assertFalse(
            $user->hasRole($roleName),
            $message ?: "User {$user->name} should not have role '{$roleName}'",
        );
    }

    /**
     * Assert that a user has a specific permission
     *
     * @param User $user
     * @param string $permissionName
     * @param string $message
     */
    protected function assertUserHasPermission(User $user, string $permissionName, string $message = ''): void
    {
        $this->assertTrue(
            $user->hasPermissionTo($permissionName),
            $message ?: "User {$user->name} should have permission '{$permissionName}'",
        );
    }

    /**
     * Assert that a user does not have a specific permission
     *
     * @param User $user
     * @param string $permissionName
     * @param string $message
     */
    protected function assertUserDoesNotHavePermission(User $user, string $permissionName, string $message = ''): void
    {
        $this->assertFalse(
            $user->hasPermissionTo($permissionName),
            $message ?: "User {$user->name} should not have permission '{$permissionName}'",
        );
    }

    /**
     * Assert that specific users exist in the database
     *
     * @param array $users Array of users to check
     */
    protected function assertUsersExistInDatabase(array $users): void
    {
        foreach ($users as $user) {
            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'email' => $user->email,
            ]);
        }
    }

    /**
     * Assert that a role exists and has specific permissions
     *
     * @param string $roleName
     * @param array $expectedPermissions
     */
    protected function assertRoleHasPermissions(string $roleName, array $expectedPermissions): void
    {
        $role = Role::where('name', $roleName)->first();

        $this->assertNotNull($role, "Role '{$roleName}' should exist");

        foreach ($expectedPermissions as $permissionName) {
            $this->assertTrue(
                $role->hasPermissionTo($permissionName),
                "Role '{$roleName}' should have permission '{$permissionName}'",
            );
        }
    }

    /**
     * Get all users with a specific role
     *
     * @param string $roleName
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getUsersWithRole(string $roleName): \Illuminate\Database\Eloquent\Collection
    {
        return User::role($roleName)->get();
    }

    /**
     * Get all users with a specific permission
     *
     * @param string $permissionName
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getUsersWithPermission(string $permissionName): \Illuminate\Database\Eloquent\Collection
    {
        /** @var \Illuminate\Database\Eloquent\Collection */
        return User::query()->whereHas('permissions', function ($query) use ($permissionName) {
            $query->where('name', $permissionName);
        })->orWhereHas('roles.permissions', function ($query) use ($permissionName) {
            $query->where('name', $permissionName);
        })->get();
    }
}
