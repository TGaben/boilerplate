<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeBoilerplateRecipe extends Command
{
    protected $signature = 'make:boilerplate-recipe {name} 
                            {--category=backend : Recipe category}
                            {--difficulty=medium : Difficulty level (easy|medium|advanced)}
                            {--time=2-3 óra : Estimated implementation time}
                            {--force : Overwrite existing recipe}';

    protected $description = 'Új boilerplate recipe dokumentáció scaffolding';

    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle(): int
    {
        $name = (string) $this->argument('name');
        $category = (string) $this->option('category');
        $difficulty = (string) $this->option('difficulty');
        $time = (string) $this->option('time');
        $force = (bool) $this->option('force');

        // Validate inputs
        if (!$this->validateInputs($name, $category, $difficulty)) {
            return 1;
        }

        $recipeId = Str::kebab($name);
        $recipeName = Str::title($name);

        $this->info("📝 Recipe scaffolding generálása: {$recipeName}");
        $this->newLine();

        try {
            // Generate documentation
            $this->generateDocumentation($recipeId, $recipeName, $category, $difficulty, $time, $force);

            // Update recipe configuration
            $this->updateRecipeConfig($recipeId, $recipeName, $category, $difficulty, $time);

            // Generate placeholder files
            $this->generatePlaceholderFiles($recipeId, $recipeName);

            $this->newLine();
            $this->info("✅ Recipe '{$recipeName}' sikeresen generálva!");
            $this->displayNextSteps($recipeId);

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Hiba történt: {$e->getMessage()}");

            return 1;
        }
    }

    protected function validateInputs(string $name, string $category, string $difficulty): bool
    {
        if (empty($name)) {
            $this->error('Recipe név megadása kötelező.');

            return false;
        }

        $validCategories = ['backend', 'frontend', 'storage', 'architecture', 'communication', 'security', 'performance', 'analytics', 'realtime'];
        if (!in_array($category, $validCategories)) {
            $this->error('Érvénytelen kategória. Elérhető opciók: ' . implode(', ', $validCategories));

            return false;
        }

        $validDifficulties = ['easy', 'medium', 'advanced'];
        if (!in_array($difficulty, $validDifficulties)) {
            $this->error('Érvénytelen nehézségi szint. Elérhető opciók: ' . implode(', ', $validDifficulties));

            return false;
        }

        return true;
    }

    protected function generateDocumentation(string $recipeId, string $recipeName, string $category, string $difficulty, string $time, bool $force): void
    {
        $documentationPath = base_path("docs/recipes/{$recipeId}.md");

        if ($this->files->exists($documentationPath) && !$force) {
            throw new \Exception("Recipe dokumentáció már létezik: {$documentationPath}. Használd a --force opciót a felülíráshoz.");
        }

        $stubPath = base_path('stubs/recipes/recipe.md.stub');
        if (!$this->files->exists($stubPath)) {
            throw new \Exception("Recipe template nem található: {$stubPath}");
        }

        $template = $this->files->get($stubPath);

        // Generate placeholder values based on inputs
        $replacements = $this->generatePlaceholders($recipeId, $recipeName, $category, $difficulty, $time);

        $content = str_replace(array_keys($replacements), array_values($replacements), $template);

        $this->files->put($documentationPath, $content);
        $this->line("📄 Dokumentáció generálva: docs/recipes/{$recipeId}.md");
    }

    protected function generatePlaceholders(string $recipeId, string $recipeName, string $category, string $difficulty, string $time): array
    {
        $placeholders = [
            '{{ NAME }}' => $recipeName,
            '{{ ESTIMATED_TIME }}' => $time,
        ];

        // Category-specific placeholders
        switch ($category) {
            case 'backend':
                $placeholders = array_merge($placeholders, [
                    '{{ USE_CASE_1 }}' => 'Server-side API endpoints',
                    '{{ USE_CASE_2 }}' => 'Business logic implementation',
                    '{{ USE_CASE_3 }}' => 'Data processing workflows',
                    '{{ PREREQUISITE_1 }}' => 'Laravel backend tudás',
                    '{{ PREREQUISITE_2 }}' => 'MVC pattern ismerete',
                    '{{ ADVANCED_PREREQUISITE }}' => 'API design patterns ajánlottak',
                ]);
                break;

            case 'frontend':
                $placeholders = array_merge($placeholders, [
                    '{{ USE_CASE_1 }}' => 'User interface components',
                    '{{ USE_CASE_2 }}' => 'Interactive user experiences',
                    '{{ USE_CASE_3 }}' => 'Frontend state management',
                    '{{ PREREQUISITE_1 }}' => 'JavaScript/Vue.js tudás',
                    '{{ PREREQUISITE_2 }}' => 'TailwindCSS ismerete',
                    '{{ ADVANCED_PREREQUISITE }}' => 'Frontend build tools tapasztalat',
                ]);
                break;

            case 'storage':
                $placeholders = array_merge($placeholders, [
                    '{{ USE_CASE_1 }}' => 'File management systems',
                    '{{ USE_CASE_2 }}' => 'Media processing workflows',
                    '{{ USE_CASE_3 }}' => 'Cloud storage integration',
                    '{{ PREREQUISITE_1 }}' => 'File system knowledge',
                    '{{ PREREQUISITE_2 }}' => 'Laravel storage drivers',
                    '{{ ADVANCED_PREREQUISITE }}' => 'Cloud provider experience',
                ]);
                break;

            default:
                $placeholders = array_merge($placeholders, [
                    '{{ USE_CASE_1 }}' => 'Feature implementation',
                    '{{ USE_CASE_2 }}' => 'System integration',
                    '{{ USE_CASE_3 }}' => 'Performance optimization',
                    '{{ PREREQUISITE_1 }}' => 'Laravel framework knowledge',
                    '{{ PREREQUISITE_2 }}' => 'Development best practices',
                    '{{ ADVANCED_PREREQUISITE }}' => 'Advanced concepts recommended',
                ]);
        }

        // Technical placeholders
        $serviceName = Str::studly($recipeId) . 'Service';
        $controllerName = Str::studly($recipeId) . 'Controller';
        $modelName = Str::studly(Str::singular($recipeId));

        $placeholders = array_merge($placeholders, [
            '{{ INSTALL_COMMAND }}' => 'composer require vendor/package',
            '{{ CONFIG_COMMAND }}' => 'php artisan vendor:publish --provider="Provider"',
            '{{ MIGRATION_COMMAND }}' => 'php artisan migrate',
            '{{ ADDITIONAL_SETUP }}' => '# További konfigurációs lépések...',
            '{{ CONFIG_FILE }}' => $recipeId,
            '{{ FEATURE_ENV_KEY }}' => Str::upper($recipeId) . '_ENABLED',
            '{{ OPTION1_ENV_KEY }}' => Str::upper($recipeId) . '_OPTION1',
            '{{ OPTION2_ENV_KEY }}' => Str::upper($recipeId) . '_OPTION2',
            '{{ FEATURE_NAME }}' => $recipeName,
            '{{ SERVICE_NAME }}' => $serviceName,
            '{{ SERVICE_VARIABLE }}' => Str::camel($serviceName),
            '{{ CONTROLLER_NAME }}' => $controllerName,
            '{{ MODEL_NAME }}' => $modelName,
            '{{ RESOURCE_NAME }}' => $modelName . 'Resource',
            '{{ TEST_CLASS_NAME }}' => $serviceName,
            '{{ PRIMARY_METHOD }}' => 'process',
            '{{ SECONDARY_METHOD }}' => 'validate',
            '{{ PARAMETER_TYPE }}' => 'array',
            '{{ RETURN_TYPE }}' => 'bool',
            '{{ DEFAULT_RETURN }}' => 'true',
            '{{ VIEW_PREFIX }}' => $recipeId,
            '{{ ROUTES_FILE }}' => 'web',
            '{{ ROUTE_PREFIX }}' => $recipeId,
            '{{ PERMISSION_PREFIX }}' => $recipeId,
            '{{ ICON_NAME }}' => 'cog',
            '{{ NAVIGATION_GROUP }}' => Str::title($category),
            '{{ TEST_METHOD_NAME }}' => 'user_can_access_' . $recipeId,
            '{{ UNIT_TEST_METHOD }}' => $recipeId . '_processes_correctly',
            '{{ EXPECTED_RESULT }}' => 'true',
            '{{ CACHE_KEY }}' => $recipeId . '_data',
            '{{ NEXT_STEP_1 }}' => 'Advanced Configuration',
            '{{ NEXT_STEP_1_DESCRIPTION }}' => 'Fine-tune the configuration options',
            '{{ NEXT_STEP_2 }}' => 'Performance Optimization',
            '{{ NEXT_STEP_2_DESCRIPTION }}' => 'Implement caching and optimization',
            '{{ NEXT_STEP_3 }}' => 'Testing & Monitoring',
            '{{ NEXT_STEP_3_DESCRIPTION }}' => 'Add comprehensive tests and monitoring',
            '{{ DOCUMENTATION_LINK_1 }}' => 'Official Documentation',
            '{{ DOCUMENTATION_URL_1 }}' => 'https://laravel.com/docs',
            '{{ DOCUMENTATION_LINK_2 }}' => 'Package Documentation',
            '{{ DOCUMENTATION_URL_2 }}' => '#',
            '{{ DOCUMENTATION_LINK_3 }}' => 'Best Practices Guide',
            '{{ DOCUMENTATION_URL_3 }}' => '#',
            '{{ COMMON_PROBLEM_1 }}' => 'Configuration not working',
            '{{ SOLUTION_COMMAND_1 }}' => 'php artisan config:clear',
            '{{ SOLUTION_COMMAND_2 }}' => 'php artisan cache:clear',
            '{{ COMMON_PROBLEM_2 }}' => 'Permission denied errors',
            '{{ SOLUTION_EXPLANATION }}' => 'Check file permissions and ownership',
            '{{ COMMON_PROBLEM_3 }}' => 'Performance issues',
            '{{ ALTERNATIVE_SOLUTION }}' => 'Enable caching and optimize queries',
        ]);

        return $placeholders;
    }

    protected function updateRecipeConfig(string $recipeId, string $recipeName, string $category, string $difficulty, string $time): void
    {
        $configPath = config_path('recipes.php');

        if (!$this->files->exists($configPath)) {
            $this->warn("⚠️ Recipe konfiguráció nem található: {$configPath}");

            return;
        }

        $config = require $configPath;

        // Add new recipe to catalog
        $config['catalog'][$recipeId] = [
            'name' => $recipeName,
            'description' => "Generated {$recipeName} recipe",
            'difficulty' => $difficulty,
            'estimated_time' => $time,
            'category' => $category,
            'tags' => [$category, $difficulty],
            'dependencies' => [],
            'packages' => [],
            'documentation' => "{$recipeId}.md",
            'version' => '1.0.0',
            'author' => 'Laravel Boilerplate Team',
        ];

        // Write updated configuration
        $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($config, true) . ";\n";
        $this->files->put($configPath, $content);

        $this->line('⚙️ Recipe konfigurációba felvéve: config/recipes.php');
    }

    protected function generatePlaceholderFiles(string $recipeId, string $recipeName): void
    {
        $serviceName = Str::studly($recipeId) . 'Service';
        $controllerName = Str::studly($recipeId) . 'Controller';

        // Create placeholder service file
        $servicePath = app_path("Services/{$serviceName}.php");
        if (!$this->files->exists($servicePath)) {
            $serviceContent = $this->generateServiceStub($serviceName);
            $this->files->put($servicePath, $serviceContent);
            $this->line("🔧 Service osztály generálva: app/Services/{$serviceName}.php");
        }

        // Create placeholder controller file
        $controllerPath = app_path("Http/Controllers/{$controllerName}.php");
        if (!$this->files->exists($controllerPath)) {
            $controllerContent = $this->generateControllerStub($controllerName, $serviceName);
            $this->files->put($controllerPath, $controllerContent);
            $this->line("🎮 Controller generálva: app/Http/Controllers/{$controllerName}.php");
        }
    }

    protected function generateServiceStub(string $serviceName): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace App\Services;

class {$serviceName}
{
    public function __construct()
    {
        // Initialize service
    }
    
    public function process(): bool
    {
        // TODO: Implement service logic
        
        return true;
    }
}

PHP;
    }

    protected function generateControllerStub(string $controllerName, string $serviceName): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\\{$serviceName};
use Illuminate\Http\Request;

class {$controllerName} extends Controller
{
    protected {$serviceName} \$service;

    public function __construct({$serviceName} \$service)
    {
        \$this->service = \$service;
    }

    public function index()
    {
        // TODO: Implement index action
        
        return view('welcome');
    }
}

PHP;
    }

    protected function displayNextSteps(string $recipeId): void
    {
        $this->newLine();
        $this->info('🎯 Következő lépések:');
        $this->line("1. Szerkeszd a dokumentációt: docs/recipes/{$recipeId}.md");
        $this->line('2. Implementáld a service logikát: app/Services/');
        $this->line('3. Konfiguráld a route-okat és controller-eket');
        $this->line('4. Írj teszteket a funkcionalitáshoz');
        $this->line('5. Frissítsd a recipe konfigurációt: config/recipes.php');
        $this->newLine();
        $this->line('📋 Recipe kezelés:');
        $this->line('  php artisan boilerplate:recipes list');
        $this->line("  php artisan boilerplate:recipes info {$recipeId}");
    }
}
