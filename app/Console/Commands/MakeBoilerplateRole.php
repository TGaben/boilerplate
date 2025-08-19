<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MakeBoilerplateRole extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:boilerplate-role {name : A szerepkör neve} 
                           {--permissions=* : Jogosultságok (elhagyva alapértelmezett jogosultságokat kap)}
                           {--guard=web : Guard név}
                           {--force : Meglévő szerepkör felülírása}';

    /**
     * The console command description.
     */
    protected $description = 'Új szerepkör létrehozása alapértelmezett jogosultságokkal';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = (string) $this->argument('name');
        $permissions = (array) $this->option('permissions');
        $guard = (string) $this->option('guard');
        $force = (bool) $this->option('force');

        // Validálás
        if (! preg_match('/^[a-z][a-z_]*[a-z]$/', $name)) {
            $this->error('A szerepkör neve kisbetűvel kell kezdődjön és csak kisbetűket/alsóvonásokat tartalmazhat!');
            $this->line('Példák: editor, content_manager, moderator');

            return self::FAILURE;
        }

        $this->info("🎭 Boilerplate Szerepkör létrehozása: {$name}");
        $this->newLine();

        // Szerepkör létrehozása vagy frissítése
        $role = $this->createOrUpdateRole($name, $guard, $force);
        if (! $role) {
            return self::FAILURE;
        }

        // Jogosultságok beállítása
        $this->assignPermissions($role, $permissions);

        $this->newLine();
        $this->info("✅ Szerepkör '{$name}' sikeresen létrehozva!");
        $this->displayRoleInfo($role);

        return self::SUCCESS;
    }

    private function createOrUpdateRole(string $name, string $guard, bool $force): ?Role
    {
        $existingRole = Role::where('name', $name)->where('guard_name', $guard)->first();

        if ($existingRole && ! $force) {
            $this->error("A '{$name}' szerepkör már létezik! Használd a --force opciót a felülíráshoz.");

            return null;
        }

        if ($existingRole && $force) {
            $this->warn("Meglévő '{$name}' szerepkör felülírása...");
            $existingRole->syncPermissions([]); // Jogosultságok eltávolítása

            return $existingRole;
        }

        $this->info('📋 Szerepkör létrehozása...');
        Role::create([
            'name' => $name,
            'guard_name' => $guard,
        ]);
        $this->line('   ✅ Szerepkör létrehozva!');

        return Role::where('name', $name)->where('guard_name', $guard)->first();
    }

    private function assignPermissions(Role $role, array $permissions): void
    {
        if (empty($permissions)) {
            $permissions = $this->getDefaultPermissions($role->name);
            $this->info('Alapértelmezett jogosultságok használata...');
        }

        $this->info('📋 Jogosultságok hozzárendelése...');
        $validPermissions = [];

        foreach ($permissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();

            if (! $permission) {
                $this->warn("   Jogosultság '{$permissionName}' nem található, létrehozás...");
                $permission = Permission::create([
                    'name' => $permissionName,
                    'guard_name' => $role->guard_name,
                ]);
            }

            $validPermissions[] = $permission;
        }

        $role->syncPermissions($validPermissions);
        $this->line('   ✅ Jogosultságok hozzárendelve!');

        $this->line('📋 Hozzárendelt jogosultságok: ' . implode(', ', $permissions));
    }

    private function getDefaultPermissions(string $roleName): array
    {
        return match ($roleName) {
            'editor' => [
                'view_any_user',
                'view_user',
                'create_user',
                'update_user',
                'view_any_activity',
                'view_activity',
            ],
            'content_manager' => [
                'view_any_user',
                'view_user',
                'create_user',
                'update_user',
                'delete_user',
                'view_any_activity',
                'view_activity',
                'view_any_role',
                'view_role',
            ],
            'moderator' => [
                'view_any_user',
                'view_user',
                'update_user',
                'view_any_activity',
                'view_activity',
            ],
            'support' => [
                'view_any_user',
                'view_user',
                'view_any_activity',
                'view_activity',
            ],
            'analyst' => [
                'view_any_user',
                'view_user',
                'view_any_activity',
                'view_activity',
                'view_any_role',
                'view_role',
            ],
            'viewer' => [
                'view_any_user',
                'view_user',
                'view_any_activity',
                'view_activity',
            ],
            default => [
                'view_any_activity',
                'view_activity',
            ],
        };
    }

    private function displayRoleInfo(Role $role): void
    {
        $this->newLine();
        $this->comment('📊 Szerepkör információk:');
        $this->table(
            ['Tulajdonság', 'Érték'],
            [
                ['Név', $role->name],
                ['Guard', $role->guard_name],
                ['Jogosultságok száma', $role->permissions->count()],
                ['Létrehozva', $role->created_at?->format('Y-m-d H:i:s') ?? 'N/A'],
            ],
        );

        if ($role->permissions->isNotEmpty()) {
            $this->comment('🔑 Jogosultságok:');
            foreach ($role->permissions as $permission) {
                $this->line("  • {$permission->name}");
            }
        }

        $this->newLine();
        $this->comment('📝 Következő lépések:');
        $this->line('1. Szerepkör hozzárendelése felhasználóhoz:');
        $this->line("   <info>\$user->assignRole('{$role->name}');</info>");
        $this->newLine();
        $this->line('2. Szerepkör ellenőrzése:');
        $this->line("   <info>\$user->hasRole('{$role->name}')</info>");
        $this->newLine();
        $this->line('3. Admin panelben megtekinthető: <info>http://localhost/admin/roles</info>');
    }
}
