<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions (idempotent)
        $permissions = [
            'access admin panel',
            'manage users',
            'view reports',
            // Filament Shield style permissions for UserResource
            'view_any_user',
            'view_user',
            'create_user',
            'update_user',
            'delete_user',
            'restore_user',
            'force_delete_user',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles (idempotent)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Assign permissions to roles (idempotent)
        $adminRole->syncPermissions($permissions);
        $userRole->syncPermissions([]);

        // Create admin user (idempotent)
        $adminUser = \App\Models\User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'email_verified_at' => now(),
                'profile_photo_path' => null,
                'last_login_at' => now(),
            ],
        );

        // Assign admin role to admin user (idempotent)
        if (!$adminUser->hasRole('admin')) {
            $adminUser->assignRole('admin');
        }
    }
}
