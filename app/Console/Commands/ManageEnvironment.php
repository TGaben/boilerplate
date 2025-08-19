<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\EnvironmentValidator;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ManageEnvironment extends Command
{
    protected $signature = 'boilerplate:env
                            {action : Action to perform (validate|copy|list|show|check)}
                            {template? : Template name (development|testing|production|ci)}
                            {--force : Force overwrite existing .env file}
                            {--backup : Create backup of existing .env file}
                            {--target-env= : Target environment for validation}';

    protected $description = 'Manage environment templates and validate configuration';

    public function __construct(
        protected EnvironmentValidator $validator,
        protected Filesystem $files,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $action = (string) $this->argument('action');
        $template = $this->argument('template');
        $templateString = is_string($template) ? $template : null;

        return match ($action) {
            'validate' => $this->validateEnvironment(),
            'copy' => $this->copyTemplate($templateString),
            'list' => $this->listTemplates(),
            'show' => $this->showTemplate($templateString),
            'check' => $this->checkEnvironment(),
            default => $this->showHelp(),
        };
    }

    /**
     * Validate current environment configuration
     */
    protected function validateEnvironment(): int
    {
        $targetEnv = (string) $this->option('target-env');

        $this->info('🔍 Környezeti változók validálása...');
        $this->newLine();

        $validation = $this->validator->validate($targetEnv ?: null);

        $this->displayValidationResults($validation);

        return $validation['valid'] ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Copy template to .env file
     */
    protected function copyTemplate(?string $template): int
    {
        if (!$template) {
            $this->error('❌ Template név megadása kötelező!');
            $this->line('Elérhető template-ek: ' . implode(', ', array_keys($this->validator->getAvailableTemplates())));

            return self::FAILURE;
        }

        if (!$this->validator->templateExists($template)) {
            $this->error("❌ Template '{$template}' nem található!");
            $this->listTemplates();

            return self::FAILURE;
        }

        $envPath = base_path('.env');
        $force = (bool) $this->option('force');
        $backup = (bool) $this->option('backup');

        // Check if .env exists and handle backup/confirmation
        if ($this->files->exists($envPath)) {
            if ($backup) {
                $this->createBackup($envPath);
            }

            if (!$force && !$this->confirmOverwrite()) {
                $this->warn('⚠️ Művelet megszakítva.');

                return self::FAILURE;
            }
        }

        // Copy template
        $templateContent = $this->validator->getTemplateContent($template);

        if (!$templateContent) {
            $this->error('❌ Template tartalom beolvasása sikertelen!');

            return self::FAILURE;
        }

        $this->files->put($envPath, $templateContent);

        $this->info("✅ Template '{$template}' sikeresen másolva .env fájlba!");
        $this->newLine();

        $this->displayNextSteps($template);

        return self::SUCCESS;
    }

    /**
     * List available templates
     */
    protected function listTemplates(): int
    {
        $templates = $this->validator->getAvailableTemplates();

        $this->info('📋 Elérhető környezeti template-ek:');
        $this->newLine();

        foreach ($templates as $key => $config) {
            $exists = $this->validator->templateExists($key) ? '✅' : '❌';
            $this->line("  {$exists} <info>{$key}</info> - {$config['name']}");
            $this->line("     {$config['description']}");
            $this->newLine();
        }

        $this->info('💡 Használat: boilerplate:env copy <template-név>');

        return self::SUCCESS;
    }

    /**
     * Show template content
     */
    protected function showTemplate(?string $template): int
    {
        if (!$template) {
            $this->error('❌ Template név megadása kötelező!');

            return self::FAILURE;
        }

        if (!$this->validator->templateExists($template)) {
            $this->error("❌ Template '{$template}' nem található!");

            return self::FAILURE;
        }

        $content = $this->validator->getTemplateContent($template);
        $templates = $this->validator->getAvailableTemplates();
        $config = $templates[$template] ?? [];

        $templateName = $config['name'] ?? $template;
        $templateDescription = $config['description'] ?? 'Nincs leírás';

        $this->info("📄 Template: {$templateName}");
        $this->line("📝 Leírás: {$templateDescription}");
        $this->newLine();

        $this->line('--- Template tartalom ---');
        $this->line($content ?? 'Template content not available');
        $this->line('--- Vége ---');

        return self::SUCCESS;
    }

    /**
     * Check environment health
     */
    protected function checkEnvironment(): int
    {
        $this->info('🔍 Környezeti konfigurációs ellenőrzés...');
        $this->newLine();

        // Check if .env file exists
        $envPath = base_path('.env');
        if (!$this->files->exists($envPath)) {
            $this->error('❌ .env fájl nem található!');
            $this->newLine();
            $this->info('💡 Használd: boilerplate:env copy <template> a .env fájl létrehozásához');

            return self::FAILURE;
        }

        // Basic environment checks
        $this->checkBasicEnvironment();
        $this->newLine();

        // Validate configuration
        $validation = $this->validator->validate();
        $this->displayValidationResults($validation);

        return $validation['valid'] ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Show help information
     */
    protected function showHelp(): int
    {
        $this->error('❌ Ismeretlen művelet!');
        $this->newLine();

        $this->info('📋 Elérhető műveletek:');
        $this->line('  <info>validate</info>  - Jelenlegi környezeti konfigurációs validálása');
        $this->line('  <info>copy</info>      - Template másolása .env fájlba');
        $this->line('  <info>list</info>      - Elérhető template-ek listázása');
        $this->line('  <info>show</info>      - Template tartalom megjelenítése');
        $this->line('  <info>check</info>     - Környezeti konfiguráció ellenőrzése');
        $this->newLine();

        $this->info('💡 Példák:');
        $this->line('  boilerplate:env list');
        $this->line('  boilerplate:env copy development');
        $this->line('  boilerplate:env validate --target-env=production');
        $this->line('  boilerplate:env copy production --backup --force');

        return self::FAILURE;
    }

    /**
     * Display validation results
     */
    protected function displayValidationResults(array $validation): void
    {
        $env = $validation['environment'];

        if ($validation['valid']) {
            $this->info("✅ Környezeti konfiguráció érvényes ({$env})!");
        } else {
            $this->error("❌ Környezeti konfiguráció hibás ({$env})!");
        }

        $this->newLine();

        // Display errors
        if (!empty($validation['errors'])) {
            $this->error('🚨 Hibák:');
            foreach ($validation['errors'] as $key => $errors) {
                foreach ($errors as $error) {
                    $errorMessage = is_string($error) ? $error : 'Unknown error';
                    $this->line("  • {$key}: {$errorMessage}");
                }
            }
            $this->newLine();
        }

        // Display warnings
        if (!empty($validation['warnings'])) {
            $this->warn('⚠️ Figyelmeztetések:');
            foreach ($validation['warnings'] as $key => $warnings) {
                foreach ($warnings as $warning) {
                    $this->line("  • {$key}: {$warning}");
                }
            }
            $this->newLine();
        }

        // Display summary
        $summary = $this->validator->getSummary();
        $this->info('📊 Összesítés:');
        $this->line("  Hibák: {$summary['error_count']}");
        $this->line("  Figyelmeztetések: {$summary['warning_count']}");
        $this->line("  Ellenőrzött változók: {$summary['total_checked']}");
    }

    /**
     * Check basic environment setup
     */
    protected function checkBasicEnvironment(): void
    {
        $this->info('🔧 Alapvető környezeti ellenőrzések:');

        // APP_KEY check
        if (env('APP_KEY')) {
            $this->line('  ✅ APP_KEY beállítva');
        } else {
            $this->line('  ❌ APP_KEY hiányzik - futtasd: php artisan key:generate');
        }

        // Database connection
        try {
            DB::connection()->getPdo();
            $this->line('  ✅ Adatbázis kapcsolat működik');
        } catch (\Exception $e) {
            $this->line('  ❌ Adatbázis kapcsolat sikertelen');
        }

        // Cache connection
        try {
            Cache::put('test_key', 'test_value', 1);
            Cache::forget('test_key');
            $this->line('  ✅ Cache kapcsolat működik');
        } catch (\Exception $e) {
            $this->line('  ❌ Cache kapcsolat sikertelen');
        }

        // Environment
        $env = App::environment();
        $this->line("  📍 Környezet: {$env}");
    }

    /**
     * Create backup of existing .env file
     */
    protected function createBackup(string $envPath): void
    {
        $backupPath = $envPath . '.backup.' . date('Y-m-d_H-i-s');
        $this->files->copy($envPath, $backupPath);
        $this->info("📦 Backup létrehozva: {$backupPath}");
    }

    /**
     * Confirm overwrite of existing .env file
     */
    protected function confirmOverwrite(): bool
    {
        return $this->confirm(
            '⚠️ .env fájl már létezik. Felülírjam?',
            false,
        );
    }

    /**
     * Display next steps after copying template
     */
    protected function displayNextSteps(string $template): void
    {
        $this->info('🚀 Következő lépések:');

        switch ($template) {
            case 'development':
                $this->line('  1. Futtasd: php artisan key:generate');
                $this->line('  2. Állítsd be az adatbázis kapcsolatot');
                $this->line('  3. Futtasd: php artisan migrate --seed');
                $this->line('  4. Teszteld: sail up -d');
                break;

            case 'testing':
                $this->line('  1. Állítsd be a test APP_KEY-t');
                $this->line('  2. Futtasd: php artisan test');
                break;

            case 'production':
                $this->line('  1. ⚠️ Cseréld le az összes REPLACE_WITH_* értéket!');
                $this->line('  2. Generálj új APP_KEY-t: php artisan key:generate');
                $this->line('  3. Állítsd be az adatbázis és Redis kapcsolatokat');
                $this->line('  4. Konfiguráld a mail beállításokat');
                $this->line('  5. Ellenőrizd: boilerplate:env validate --target-env=production');
                break;

            case 'ci':
                $this->line('  1. Másold ezt .env.testing néven a CI környezetbe');
                $this->line('  2. Állítsd be a CI változókat a pipeline-ban');
                break;
        }

        $this->newLine();
        $this->info('💡 Ellenőrizd a konfigurációt: boilerplate:env validate');
    }
}
