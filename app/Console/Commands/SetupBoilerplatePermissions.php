<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SetupBoilerplatePermissions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'boilerplate:setup-permissions 
                           {--reset : Minden meglévő jogosultság törlése és újra létrehozása}
                           {--sync-admin : Admin szerepkör szinkronizálása minden jogosultsággal}
                           {--dry-run : Csak a módosítások megjelenítése, végrehajtás nélkül}';

    /**
     * The console command description.
     */
    protected $description = 'Jogosultság struktúra újragenerálása és szinkronizálása';

    private Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $reset = (bool) $this->option('reset');
        $syncAdmin = (bool) $this->option('sync-admin');
        $dryRun = (bool) $this->option('dry-run');

        $this->info('🔑 Boilerplate Jogosultság Struktúra Beállítása');
        $this->newLine();

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MÓD - Változtatások nem lesznek alkalmazva');
            $this->newLine();
        }

        // Meglévő jogosultságok eltávolítása (ha kérték)
        if ($reset && ! $dryRun) {
            $this->resetPermissions();
        }

        // Alap jogosultságok létrehozása
        $this->createCorePermissions($dryRun);

        // Model-alapú jogosultságok létrehozása
        $this->createModelPermissions($dryRun);

        // Admin szerepkör szinkronizálása
        if ($syncAdmin || $reset) {
            $this->syncAdminRole($dryRun);
        }

        // Statisztikák megjelenítése
        $this->displayStatistics();

        if (! $dryRun) {
            $this->newLine();
            $this->info('✅ Jogosultság struktúra sikeresen beállítva!');
        }

        return self::SUCCESS;
    }

    private function resetPermissions(): void
    {
        $this->info('📋 Meglévő jogosultságok törlése...');
        Permission::query()->delete();
        $this->line('   ✅ Jogosultságok törölve!');
    }

    private function createCorePermissions(bool $dryRun): void
    {
        $corePermissions = [
            // Admin panel hozzáférés
            'access_admin_panel' => 'Admin panel elérése',
            'view_admin_dashboard' => 'Admin dashboard megtekintése',

            // Általános jogosultságok
            'export_data' => 'Adatok exportálása',
            'import_data' => 'Adatok importálása',
            'backup_database' => 'Adatbázis biztonsági mentés',
            'restore_database' => 'Adatbázis visszaállítás',

            // Rendszer jogosultságok
            'view_system_logs' => 'Rendszer naplók megtekintése',
            'clear_cache' => 'Cache törlése',
            'run_maintenance' => 'Karbantartási műveletek futtatása',

            // Felhasználó kezelés speciális jogosultságok
            'impersonate_users' => 'Felhasználók megszemélyesítése',
            'force_password_reset' => 'Jelszó reset kényszerítése',
        ];

        $this->info('📋 Alap jogosultságok létrehozása...');

        foreach ($corePermissions as $name => $description) {
            if ($dryRun) {
                $this->line("  • {$name} - {$description}");
                continue;
            }

            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['description' => $description],
            );
        }

        if (! $dryRun) {
            $this->info('✅ ' . count($corePermissions) . ' alap jogosultság létrehozva');
        }
    }

    private function createModelPermissions(bool $dryRun): void
    {
        $models = $this->discoverModels();
        $actions = [
            'view_any' => 'Összes megtekintése',
            'view' => 'Megtekintés',
            'create' => 'Létrehozás',
            'update' => 'Módosítás',
            'delete' => 'Törlés',
            'delete_any' => 'Tömeges törlés',
            'force_delete' => 'Végleges törlés',
            'force_delete_any' => 'Tömeges végleges törlés',
            'restore' => 'Helyreállítás',
            'restore_any' => 'Tömeges helyreállítás',
            'replicate' => 'Duplikálás',
            'reorder' => 'Átrendezés',
        ];

        $this->info('🏗️  Model-alapú jogosultságok létrehozása...');

        foreach ($models as $model) {
            $modelKey = Str::snake(class_basename($model));

            if ($dryRun) {
                $this->line("  📁 {$modelKey}:");
            }

            foreach ($actions as $action => $description) {
                $permissionName = "{$action}_{$modelKey}";

                if ($dryRun) {
                    $this->line("    • {$permissionName}");
                    continue;
                }

                Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => 'web'],
                    ['description' => "{$description} - {$modelKey}"],
                );
            }
        }

        if (! $dryRun) {
            $totalPermissions = count($models) * count($actions);
            $this->info("✅ {$totalPermissions} model jogosultság létrehozva " . count($models) . ' modellhez');
        }
    }

    private function discoverModels(): array
    {
        $modelPath = app_path('Models');
        $models = [];

        if (! $this->files->isDirectory($modelPath)) {
            return $models;
        }

        $files = $this->files->allFiles($modelPath);

        foreach ($files as $file) {
            $className = $file->getFilenameWithoutExtension();
            $fullClassName = "App\\Models\\{$className}";

            if (class_exists($fullClassName)) {
                $models[] = $fullClassName;
            }
        }

        // Alapértelmezett modellek hozzáadása, ha még nincsenek
        $defaultModels = [
            'App\Models\User',
            'Spatie\Permission\Models\Role',
            'Spatie\Permission\Models\Permission',
            'Spatie\Activitylog\Models\Activity',
        ];

        foreach ($defaultModels as $model) {
            if (class_exists($model) && ! in_array($model, $models)) {
                $models[] = $model;
            }
        }

        return $models;
    }

    private function syncAdminRole(bool $dryRun): void
    {
        if ($dryRun) {
            $this->info('🎭 Admin szerepkör szinkronizálása minden jogosultsággal...');

            return;
        }

        $this->info('📋 Admin szerepkör szinkronizálása...');
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
        );

        $allPermissions = Permission::where('guard_name', 'web')->get();
        $adminRole->syncPermissions($allPermissions);
        $this->line('   ✅ Admin szerepkör szinkronizálva!');
    }

    private function displayStatistics(): void
    {
        $totalPermissions = Permission::count();
        $totalRoles = Role::count();

        $this->newLine();
        $this->comment('📊 Jogosultság Statisztikák:');
        $this->table(
            ['Metrika', 'Érték'],
            [
                ['Összes jogosultság', $totalPermissions],
                ['Összes szerepkör', $totalRoles],
                ['Modellek száma', count($this->discoverModels())],
                ['Guard típus', 'web'],
            ],
        );

        // Legújabb jogosultságok megjelenítése
        $recentPermissions = Permission::orderBy('created_at', 'desc')->take(10)->get();
        if ($recentPermissions->isNotEmpty()) {
            $this->newLine();
            $this->comment('🆕 Legutóbbi jogosultságok:');
            foreach ($recentPermissions as $permission) {
                $this->line("  • {$permission->name}");
            }
        }

        // Admin szerepkör információ
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $this->newLine();
            $this->comment('👑 Admin szerepkör:');
            $this->line("  • Jogosultságok száma: {$adminRole->permissions->count()}");
            $this->line("  • Felhasználók száma: {$adminRole->users->count()}");
        }

        $this->newLine();
        $this->comment('📝 Hasznos parancsok:');
        $this->line('  • Új szerepkör: <info>sail artisan make:boilerplate-role editor</info>');
        $this->line('  • Jogosultság cache törlése: <info>sail artisan permission:cache-reset</info>');
        $this->line('  • Admin panel: <info>http://localhost/admin/roles</info>');
    }
}
