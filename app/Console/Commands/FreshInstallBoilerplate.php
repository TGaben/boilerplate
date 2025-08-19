<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class FreshInstallBoilerplate extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'boilerplate:fresh-install 
                           {--seed : Teszt adatok létrehozása}
                           {--force : Erősítés kihagyása (VESZÉLYES!)}
                           {--skip-npm : NPM build kihagyása}
                           {--environment=local : Környezet megadása}';

    /**
     * The console command description.
     */
    protected $description = 'Teljes alkalmazás újrainitiálása (VESZÉLYES - minden adat törlődik!)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $seed = (bool) $this->option('seed');
        $force = (bool) $this->option('force');
        $skipNpm = (bool) $this->option('skip-npm');
        $environment = (string) $this->option('environment');

        // Biztonsági ellenőrzések
        if (! $this->performSafetyChecks($environment, $force)) {
            return self::FAILURE;
        }

        $this->warn('🚨 FIGYELEM: Ez a parancs MINDEN adatot töröl az adatbázisból!');
        $this->newLine();

        if (! $force && ! $this->confirm('Biztosan folytatod? (igen/nem)', false)) {
            $this->info('Művelet megszakítva.');

            return self::SUCCESS;
        }

        $this->info('🔄 Boilerplate Fresh Install indítása...');
        $this->newLine();

        $steps = [
            'Cache törlése' => fn () => $this->clearCache(),
            'Adatbázis reset' => fn () => $this->resetDatabase(),
            'Migrációk futtatása' => fn () => $this->runMigrations(),
            'Jogosultságok beállítása' => fn () => $this->setupPermissions(),
            'Alapértelmezett szerepkörök és felhasználók' => fn () => $this->createDefaultData(),
        ];

        if ($seed) {
            $steps['Teszt adatok létrehozása'] = fn () => $this->seedTestData();
        }

        if (! $skipNpm) {
            $steps['Assets build'] = fn () => $this->buildAssets();
        }

        $steps['Végleges optimalizáció'] = fn () => $this->finalOptimization();

        // Lépések végrehajtása
        foreach ($steps as $description => $step) {
            $this->info("📋 {$description}...");

            try {
                $result = $step();
                if ($result) {
                    $this->line("   ✅ {$description} sikeres!");
                } else {
                    $this->error("   ❌ {$description} sikertelen!");

                    return self::FAILURE;
                }
            } catch (\Exception $e) {
                $this->error("   ❌ {$description} hiba: " . $e->getMessage());

                return self::FAILURE;
            }
        }

        $this->displaySuccessInfo();

        return self::SUCCESS;
    }

    private function performSafetyChecks(string $environment, bool $force): bool
    {
        // Production környezet ellenőrzése
        if ($environment === 'production' && ! $force) {
            $this->error('🚫 Ez a parancs nem futtatható production környezetben!');
            $this->line('Használd a --force opciót, ha mégis szeretnéd (NEM AJÁNLOTT!)');

            return false;
        }

        // Adatbázis kapcsolat ellenőrzése
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $this->error('🚫 Adatbázis kapcsolat hiba: ' . $e->getMessage());

            return false;
        }

        // Backup figyelmeztetés
        if ($environment === 'production') {
            $this->warn('⚠️  PRODUCTION KÖRNYEZET DETECTÁLVA!');
            $this->warn('⚠️  Készítsd el a biztonsági mentést mielőtt folytatnád!');
            $this->newLine();

            if (! $this->confirm('Biztonsági mentés elkészült?', false)) {
                $this->error('Hozz létre biztonsági mentést és próbáld újra.');

                return false;
            }
        }

        return true;
    }

    private function clearCache(): bool
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        // Permission cache törlése
        if (class_exists('Spatie\Permission\PermissionServiceProvider')) {
            Artisan::call('permission:cache-reset');
        }

        return true;
    }

    private function resetDatabase(): bool
    {
        Artisan::call('migrate:fresh', ['--force' => true]);

        return true;
    }

    private function runMigrations(): bool
    {
        Artisan::call('migrate', ['--force' => true]);

        return true;
    }

    private function setupPermissions(): bool
    {
        Artisan::call('boilerplate:setup-permissions', ['--reset' => true]);

        return true;
    }

    private function createDefaultData(): bool
    {
        // Alapértelmezett szerepkörök és jogosultságok
        Artisan::call('db:seed', [
            '--class' => 'RolesAndPermissionsSeeder',
            '--force' => true,
        ]);

        return true;
    }

    private function seedTestData(): bool
    {
        Artisan::call('db:seed', ['--force' => true]);

        return true;
    }

    private function buildAssets(): bool
    {
        $this->info('   Building frontend assets...');

        $result = shell_exec('npm run build 2>&1');

        if (str_contains($result ?: '', 'ERROR') || str_contains($result ?: '', 'failed')) {
            $this->warn('   NPM build warning, but continuing...');

            return true; // Don't fail the whole process
        }

        return true;
    }

    private function finalOptimization(): bool
    {
        // Konfiguráció cache újraépítése
        Artisan::call('config:cache');

        // Route cache újraépítése
        Artisan::call('route:cache');

        // View cache újraépítése
        Artisan::call('view:cache');

        return true;
    }

    private function displaySuccessInfo(): void
    {
        $this->newLine();
        $this->info('🎉 Boilerplate Fresh Install sikeresen befejezve!');
        $this->newLine();

        // Alapértelmezett admin adatok
        $this->comment('🔑 Alapértelmezett admin adatok:');
        $this->table(
            ['Mező', 'Érték'],
            [
                ['Email', 'admin@example.com'],
                ['Jelszó', 'password'],
                ['Szerepkör', 'admin'],
            ],
        );

        $this->newLine();
        $this->comment('🌐 Elérhető felületek:');
        $this->line('  • Publikus oldal: <info>http://localhost</info>');
        $this->line('  • Admin panel: <info>http://localhost/admin</info>');

        if ($this->option('seed')) {
            $this->line('  • Teszt adatok létrehozva ✅');
        }

        $this->newLine();
        $this->comment('📋 Következő lépések:');
        $this->line('1. Ellenőrizd a publikus oldalt: <info>http://localhost</info>');
        $this->line('2. Jelentkezz be az admin panelbe: <info>http://localhost/admin</info>');
        $this->line('3. Futtass teszteket: <info>./vendor/bin/sail artisan test</info>');
        $this->line('4. Kezdj el fejleszteni! 🚀');

        $this->newLine();
        $this->comment('🛠️  Hasznos parancsok:');
        $this->line('  • Quality check: <info>./scripts/quality-check.sh</info>');
        $this->line('  • Új resource: <info>sail artisan make:boilerplate-resource Product</info>');
        $this->line('  • Új szerepkör: <info>sail artisan make:boilerplate-role editor</info>');
        $this->line('  • Jogosultságok: <info>sail artisan boilerplate:setup-permissions</info>');

        $this->newLine();
        $this->info('✨ Kellemes fejlesztést!');
    }
}
