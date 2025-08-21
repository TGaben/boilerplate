<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use InvalidArgumentException;
use RuntimeException;

class RecipeManager
{
    protected Filesystem $files;

    /** @var array<string, mixed> */
    protected array $config;

    protected string $installedRecipesFile;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
        $this->config = (array) config('recipes');
        $this->installedRecipesFile = (string) ($this->config['installed_recipes_file'] ?? '');

        // Ensure the .boilerplate directory exists
        $boilerplateDir = dirname($this->installedRecipesFile);
        if (! $this->files->exists($boilerplateDir)) {
            $this->files->makeDirectory($boilerplateDir, 0755, true);
        }
    }

    /**
     * Get all available recipes from the catalog.
     *
     * @return Collection<string, array<string, mixed>>
     */
    public function getAvailableRecipes(): Collection
    {
        return collect($this->config['catalog'] ?? [])->map(function ($recipe, $key) {
            return array_merge((array) $recipe, ['id' => $key]);
        });
    }

    /**
     * Get recipes filtered by category.
     *
     * @return Collection<string, array<string, mixed>>
     */
    public function getRecipesByCategory(string $category): Collection
    {
        return $this->getAvailableRecipes()->filter(function ($recipe) use ($category) {
            return is_array($recipe) && isset($recipe['category']) && $recipe['category'] === $category;
        });
    }

    /**
     * Get recipes filtered by difficulty level.
     *
     * @return Collection<string, array<string, mixed>>
     */
    public function getRecipesByDifficulty(string $difficulty): Collection
    {
        return $this->getAvailableRecipes()->filter(function ($recipe) use ($difficulty) {
            return is_array($recipe) && isset($recipe['difficulty']) && $recipe['difficulty'] === $difficulty;
        });
    }

    /**
     * Get all recipe categories.
     *
     * @return array<string, mixed>
     */
    public function getCategories(): array
    {
        return $this->config['categories'];
    }

    /**
     * Get a specific recipe by ID.
     */
    public function getRecipe(string $recipeId): ?array
    {
        $recipe = $this->config['catalog'][$recipeId] ?? null;

        if ($recipe) {
            $recipe['id'] = $recipeId;
            $recipe['is_installed'] = $this->isRecipeInstalled($recipeId);
            $recipe['documentation_path'] = $this->getRecipeDocumentationPath($recipeId);
        }

        return $recipe;
    }

    /**
     * Check if a recipe exists.
     */
    public function recipeExists(string $recipeId): bool
    {
        return isset($this->config['catalog'][$recipeId]);
    }

    /**
     * Check if a recipe is installed.
     */
    public function isRecipeInstalled(string $recipeId): bool
    {
        $installedRecipes = $this->getInstalledRecipes();

        return isset($installedRecipes[$recipeId]);
    }

    /**
     * Get all installed recipes.
     */
    public function getInstalledRecipes(): array
    {
        if (! $this->files->exists($this->installedRecipesFile)) {
            return [];
        }

        $content = $this->files->get($this->installedRecipesFile);
        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Install a recipe.
     */
    public function installRecipe(string $recipeId): array
    {
        if (! $this->recipeExists($recipeId)) {
            throw new InvalidArgumentException("Recipe '{$recipeId}' not found in catalog.");
        }

        if ($this->isRecipeInstalled($recipeId)) {
            throw new RuntimeException("Recipe '{$recipeId}' is already installed.");
        }

        $recipe = $this->getRecipe($recipeId);

        if (! $recipe) {
            throw new InvalidArgumentException("Recipe '{$recipeId}' data not found.");
        }

        $results = [];

        // Check dependencies
        $this->validateDependencies($recipe);

        // Install Composer packages if specified
        if (! empty($recipe['packages'])) {
            $results['packages'] = $this->installComposerPackages($recipe['packages']);
        }

        // Mark as installed
        $this->markRecipeAsInstalled($recipeId, $recipe);

        // Run post-installation tasks
        if ($this->config['installation']['run_migrations_after_install']) {
            $results['migrations'] = $this->runMigrations();
        }

        if ($this->config['installation']['clear_cache_after_install']) {
            $results['cache'] = $this->clearCache();
        }

        $results['recipe'] = $recipe;

        return $results;
    }

    /**
     * Remove a recipe.
     */
    public function removeRecipe(string $recipeId): array
    {
        if (! $this->isRecipeInstalled($recipeId)) {
            throw new RuntimeException("Recipe '{$recipeId}' is not installed.");
        }

        $installedRecipes = $this->getInstalledRecipes();
        $recipe = $installedRecipes[$recipeId];

        // Remove from installed recipes
        unset($installedRecipes[$recipeId]);
        $this->saveInstalledRecipes($installedRecipes);

        return [
            'recipe' => $recipe,
            'removed_at' => now()->toISOString(),
        ];
    }

    /**
     * Get recipe documentation path.
     */
    public function getRecipeDocumentationPath(string $recipeId): ?string
    {
        $recipe = $this->config['catalog'][$recipeId] ?? null;

        if (! $recipe || ! isset($recipe['documentation'])) {
            return null;
        }

        $path = $this->config['path'] . '/' . $recipe['documentation'];

        return $this->files->exists($path) ? $path : null;
    }

    /**
     * Get recipe documentation content.
     */
    public function getRecipeDocumentation(string $recipeId): ?string
    {
        $path = $this->getRecipeDocumentationPath($recipeId);

        return $path ? $this->files->get($path) : null;
    }

    /**
     * Validate recipe dependencies.
     */
    protected function validateDependencies(array $recipe): void
    {
        if (empty($recipe['dependencies'])) {
            return;
        }

        $installedRecipes = $this->getInstalledRecipes();

        foreach ($recipe['dependencies'] as $dependency) {
            if (! isset($installedRecipes[$dependency])) {
                throw new RuntimeException(
                    "Recipe '{$recipe['id']}' requires '{$dependency}' to be installed first.",
                );
            }
        }
    }

    /**
     * Install Composer packages.
     */
    protected function installComposerPackages(array $packages): array
    {
        $results = [];

        foreach ($packages as $package) {
            $command = "composer require {$package}";
            $result = Process::run($command);

            $results[$package] = [
                'success' => $result->successful(),
                'output' => $result->output(),
                'error' => $result->errorOutput(),
            ];
        }

        return $results;
    }

    /**
     * Mark recipe as installed.
     */
    protected function markRecipeAsInstalled(string $recipeId, array $recipe): void
    {
        $installedRecipes = $this->getInstalledRecipes();

        $installedRecipes[$recipeId] = [
            'name' => $recipe['name'],
            'version' => $recipe['version'],
            'installed_at' => now()->toISOString(),
            'packages' => $recipe['packages'] ?? [],
        ];

        $this->saveInstalledRecipes($installedRecipes);
    }

    /**
     * Save installed recipes to file.
     */
    protected function saveInstalledRecipes(array $recipes): void
    {
        $json = json_encode($recipes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            throw new RuntimeException('Failed to encode recipes as JSON.');
        }

        $this->files->put($this->installedRecipesFile, $json);
    }

    /**
     * Run database migrations.
     */
    protected function runMigrations(): array
    {
        try {
            Artisan::call('migrate', ['--force' => true]);

            return [
                'success' => true,
                'output' => Artisan::output(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Clear application cache.
     */
    protected function clearCache(): array
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            return [
                'success' => true,
                'commands' => ['cache:clear', 'config:clear', 'route:clear', 'view:clear'],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get installation statistics.
     */
    public function getInstallationStats(): array
    {
        $available = $this->getAvailableRecipes();
        $installed = $this->getInstalledRecipes();

        return [
            'total_available' => $available->count(),
            'total_installed' => count($installed),
            'by_category' => $available->groupBy('category')->map->count(),
            'by_difficulty' => $available->groupBy('difficulty')->map->count(),
            'installation_rate' => $available->count() > 0
                ? round((count($installed) / $available->count()) * 100, 2)
                : 0,
        ];
    }
}
