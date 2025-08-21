<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\RecipeManager;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ManageBoilerplateRecipes extends Command
{
    protected $signature = 'boilerplate:recipes 
                            {action? : Action to perform (list|browse|install|remove|info|stats)}
                            {recipe? : Recipe ID for install/remove/info actions}
                            {--category= : Filter by category}
                            {--difficulty= : Filter by difficulty (easy|medium|advanced)}
                            {--installed : Show only installed recipes}
                            {--available : Show only available (not installed) recipes}
                            {--force : Force install/remove without confirmation}';

    protected $description = 'Kezeli a boilerplate recipes-eket: böngészés, telepítés, eltávolítás';

    protected RecipeManager $recipeManager;

    public function __construct(RecipeManager $recipeManager)
    {
        parent::__construct();
        $this->recipeManager = $recipeManager;
    }

    public function handle(): int
    {
        $action = (string) $this->argument('action') ?: 'browse';

        return match ($action) {
            'list' => $this->listRecipes(),
            'browse' => $this->browseRecipes(),
            'install' => $this->installRecipe(),
            'remove' => $this->removeRecipe(),
            'info' => $this->showRecipeInfo(),
            'stats' => $this->showStatistics(),
            default => $this->showHelp(),
        };
    }

    /**
     * List recipes in a simple format.
     */
    protected function listRecipes(): int
    {
        $recipes = $this->getFilteredRecipes();

        if ($recipes->isEmpty()) {
            $this->warn('Nincsenek recipes a megadott szűrési feltételekkel.');

            return 0;
        }

        $this->info('📋 Elérhető Recipes:');
        $this->newLine();

        foreach ($recipes->groupBy('category') as $category => $categoryRecipes) {
            $categoryInfo = $this->recipeManager->getCategories()[$category] ?? ['name' => $category];
            $icon = $categoryInfo['icon'] ?? '📦';
            $this->line("<fg=cyan>● {$icon} {$categoryInfo['name']}</>");

            foreach ($categoryRecipes as $recipe) {
                $status = $recipe['is_installed'] ?? false ? '✅' : '⬜';
                $difficulty = $this->getDifficultyColor($recipe['difficulty']);

                $this->line("  {$status} <fg={$difficulty}>{$recipe['id']}</> - {$recipe['name']}");
            }
            $this->newLine();
        }

        return 0;
    }

    /**
     * Interactive recipe browser.
     */
    protected function browseRecipes(): int
    {
        $this->displayHeader();

        while (true) {
            $this->displayMenu();

            $choice = $this->ask('Válassz egy opciót (1-6), vagy nyomj Enter-t a kilépéshez');

            if (empty($choice)) {
                $this->info('Viszlát! 👋');

                return 0;
            }

            match ($choice) {
                '1' => $this->browseByCategory(),
                '2' => $this->browseByDifficulty(),
                '3' => $this->showInstalledRecipes(),
                '4' => $this->searchRecipes(),
                '5' => $this->showStatistics(),
                '6' => $this->installRecipeInteractive(),
                default => $this->error('Érvénytelen választás. Próbáld újra!')
            };

            $this->newLine();
            if (! $this->confirm('Folytassuk?', true)) {
                break;
            }
        }

        return 0;
    }

    /**
     * Install a recipe.
     */
    protected function installRecipe(): int
    {
        $recipeId = (string) $this->argument('recipe');

        if (empty($recipeId)) {
            $this->error('Recipe ID megadása kötelező.');

            return 1;
        }

        if (! $this->recipeManager->recipeExists($recipeId)) {
            $this->error("Recipe '{$recipeId}' nem található.");

            return 1;
        }

        $recipe = $this->recipeManager->getRecipe($recipeId);

        if (! $recipe) {
            $this->error("Recipe '{$recipeId}' nem található.");

            return 1;
        }

        if ($this->recipeManager->isRecipeInstalled($recipeId)) {
            $this->warn("Recipe '{$recipeId}' már telepítve van.");

            return 0;
        }

        $this->displayRecipeInfo($recipe);

        if (! $this->option('force') && ! $this->confirm("Telepíted a '{$recipe['name']}' recipe-t?")) {
            $this->info('Telepítés megszakítva.');

            return 0;
        }

        $this->info('Recipe telepítése folyamatban...');

        try {
            $result = $this->recipeManager->installRecipe($recipeId);
            $this->displayInstallationResult($result);

            return 0;
        } catch (\Exception $e) {
            $this->error("Hiba történt a telepítés során: {$e->getMessage()}");

            return 1;
        }
    }

    /**
     * Remove a recipe.
     */
    protected function removeRecipe(): int
    {
        $recipeId = (string) $this->argument('recipe');

        if (empty($recipeId)) {
            $this->error('Recipe ID megadása kötelező.');

            return 1;
        }

        if (! $this->recipeManager->isRecipeInstalled($recipeId)) {
            $this->warn("Recipe '{$recipeId}' nincs telepítve.");

            return 0;
        }

        if (! $this->option('force') && ! $this->confirm("Biztosan eltávolítod a '{$recipeId}' recipe-t?")) {
            $this->info('Eltávolítás megszakítva.');

            return 0;
        }

        try {
            $result = $this->recipeManager->removeRecipe($recipeId);
            $this->info("✅ Recipe '{$recipeId}' sikeresen eltávolítva.");

            return 0;
        } catch (\Exception $e) {
            $this->error("Hiba történt az eltávolítás során: {$e->getMessage()}");

            return 1;
        }
    }

    /**
     * Show detailed recipe information.
     */
    protected function showRecipeInfo(): int
    {
        $recipeId = (string) $this->argument('recipe');

        if (empty($recipeId)) {
            $this->error('Recipe ID megadása kötelező.');

            return 1;
        }

        $recipe = $this->recipeManager->getRecipe($recipeId);

        if (! $recipe) {
            $this->error("Recipe '{$recipeId}' nem található.");

            return 1;
        }

        $this->displayRecipeInfo($recipe);

        return 0;
    }

    /**
     * Show recipe statistics.
     */
    protected function showStatistics(): int
    {
        $stats = $this->recipeManager->getInstallationStats();

        $this->info('📊 Recipe Statisztikák:');
        $this->newLine();

        $this->line("📦 Összes elérhető: <fg=cyan>{$stats['total_available']}</>");
        $this->line("✅ Telepített: <fg=green>{$stats['total_installed']}</>");
        $this->line("📈 Telepítési arány: <fg=yellow>{$stats['installation_rate']}%</>");
        $this->newLine();

        $this->line('<fg=cyan>Kategóriák szerint:</>');
        foreach ($stats['by_category'] as $category => $count) {
            $categoryInfo = $this->recipeManager->getCategories()[$category] ?? ['name' => $category, 'icon' => '📦'];
            $this->line("  {$categoryInfo['icon']} {$categoryInfo['name']}: {$count}");
        }

        $this->newLine();
        $this->line('<fg=cyan>Nehézség szerint:</>');
        foreach ($stats['by_difficulty'] as $difficulty => $count) {
            $color = $this->getDifficultyColor($difficulty);
            $this->line("  <fg={$color}>● {$difficulty}:</> {$count}");
        }

        return 0;
    }

    /**
     * Get filtered recipes based on command options.
     */
    protected function getFilteredRecipes()
    {
        $recipes = $this->recipeManager->getAvailableRecipes();

        $category = $this->option('category');
        if ($category && is_string($category)) {
            $recipes = $this->recipeManager->getRecipesByCategory($category);
        }

        $difficulty = $this->option('difficulty');
        if ($difficulty && is_string($difficulty)) {
            $recipes = $recipes->filter(function ($recipe) use ($difficulty) {
                return is_array($recipe) && isset($recipe['difficulty']) && $recipe['difficulty'] === $difficulty;
            });
        }

        if ($this->option('installed')) {
            $recipes = $recipes->filter(function ($recipe) {
                return is_array($recipe) && isset($recipe['id']) && $this->recipeManager->isRecipeInstalled((string) $recipe['id']);
            });
        }

        if ($this->option('available')) {
            $recipes = $recipes->filter(function ($recipe) {
                return is_array($recipe) && isset($recipe['id']) && ! $this->recipeManager->isRecipeInstalled((string) $recipe['id']);
            });
        }

        // Add installation status to each recipe
        return $recipes->map(function ($recipe) {
            if (is_array($recipe) && isset($recipe['id'])) {
                $recipe['is_installed'] = $this->recipeManager->isRecipeInstalled((string) $recipe['id']);
            }

            return $recipe;
        });
    }

    /**
     * Display recipe browser header.
     */
    protected function displayHeader(): void
    {
        $this->info('🍳 Laravel Boilerplate Recipe Manager');
        $this->info('====================================');
        $this->newLine();
    }

    /**
     * Display main menu.
     */
    protected function displayMenu(): void
    {
        $this->line('<fg=cyan>Mit szeretnél csinálni?</>');
        $this->line('1️⃣  Böngészés kategória szerint');
        $this->line('2️⃣  Böngészés nehézség szerint');
        $this->line('3️⃣  Telepített recipes megtekintése');
        $this->line('4️⃣  Recipe keresése');
        $this->line('5️⃣  Statisztikák megtekintése');
        $this->line('6️⃣  Recipe telepítése');
        $this->newLine();
    }

    /**
     * Browse recipes by category.
     */
    protected function browseByCategory(): void
    {
        $categories = $this->recipeManager->getCategories();

        $this->info('📂 Kategóriák:');
        foreach ($categories as $id => $category) {
            $count = $this->recipeManager->getRecipesByCategory((string) $id)->count();
            $this->line("  {$category['icon']} {$category['name']} ({$count})");
        }

        $category = $this->ask('Melyik kategóriát szeretnéd megnézni?');

        if ($category && isset($categories[$category])) {
            $recipes = $this->recipeManager->getRecipesByCategory((string) $category);
            $this->displayRecipeList($recipes);
        }
    }

    /**
     * Browse recipes by difficulty.
     */
    protected function browseByDifficulty(): void
    {
        $difficulties = ['easy' => 'Könnyű', 'medium' => 'Közepes', 'advanced' => 'Haladó'];

        $this->info('🎯 Nehézségi szintek:');
        foreach ($difficulties as $id => $name) {
            $count = $this->recipeManager->getRecipesByDifficulty($id)->count();
            $color = $this->getDifficultyColor($id);
            $this->line("  <fg={$color}>● {$name}</> ({$count})");
        }

        $difficulty = $this->ask('Melyik nehézségi szintet szeretnéd megnézni?');

        if ($difficulty && isset($difficulties[$difficulty])) {
            $recipes = $this->recipeManager->getRecipesByDifficulty($difficulty);
            $this->displayRecipeList($recipes);
        }
    }

    /**
     * Show installed recipes.
     */
    protected function showInstalledRecipes(): void
    {
        $installed = $this->recipeManager->getInstalledRecipes();

        if (empty($installed)) {
            $this->warn('Nincsenek telepített recipes.');

            return;
        }

        $this->info('✅ Telepített Recipes:');
        foreach ($installed as $id => $recipe) {
            $this->line("  📦 {$recipe['name']} (v{$recipe['version']})");
            $this->line("     Telepítve: {$recipe['installed_at']}");
        }
    }

    /**
     * Search recipes.
     */
    protected function searchRecipes(): void
    {
        $query = $this->ask('Mit keresel?');

        if (empty($query) || ! is_string($query)) {
            return;
        }

        $results = $this->recipeManager->getAvailableRecipes()->filter(function ($recipe) use ($query) {
            if (! is_array($recipe)) {
                return false;
            }

            $recipeName = $recipe['name'] ?? '';
            $recipeDescription = $recipe['description'] ?? '';
            $recipeTags = $recipe['tags'] ?? [];

            return Str::contains(strtolower((string) $recipeName), strtolower($query)) ||
                   Str::contains(strtolower((string) $recipeDescription), strtolower($query)) ||
                   collect($recipeTags)->contains(function ($tag) use ($query) {
                       return Str::contains(strtolower((string) $tag), strtolower($query));
                   });
        });

        if ($results->isEmpty()) {
            $this->warn("Nincs találat a '{$query}' keresésre.");

            return;
        }

        $this->info("🔍 Keresési eredmények '{$query}'-ra:");
        $this->displayRecipeList($results);
    }

    /**
     * Install recipe interactively.
     */
    protected function installRecipeInteractive(): void
    {
        $available = $this->recipeManager->getAvailableRecipes()->filter(function ($recipe) {
            return is_array($recipe) && isset($recipe['id']) && ! $this->recipeManager->isRecipeInstalled((string) $recipe['id']);
        });

        if ($available->isEmpty()) {
            $this->warn('Nincsenek elérhető recipes a telepítéshez.');

            return;
        }

        $this->info('📦 Elérhető recipes:');
        $choices = [];
        foreach ($available as $recipe) {
            if (! is_array($recipe)) {
                continue;
            }

            $recipeId = (string) ($recipe['id'] ?? '');
            $recipeDifficulty = (string) ($recipe['difficulty'] ?? '');
            $recipeName = (string) ($recipe['name'] ?? '');

            $choices[] = $recipeId;
            $difficulty = $this->getDifficultyColor($recipeDifficulty);
            $this->line("  <fg={$difficulty}>{$recipeId}</> - {$recipeName}");
        }

        $choice = $this->ask('Melyik recipe-t szeretnéd telepíteni?');

        if ($choice && in_array($choice, $choices)) {
            $this->call('boilerplate:recipes', ['action' => 'install', 'recipe' => $choice]);
        }
    }

    /**
     * Display a list of recipes.
     */
    protected function displayRecipeList($recipes): void
    {
        foreach ($recipes as $recipe) {
            if (! is_array($recipe)) {
                continue;
            }

            $recipeId = (string) ($recipe['id'] ?? '');
            $recipeDifficulty = (string) ($recipe['difficulty'] ?? '');
            $recipeName = (string) ($recipe['name'] ?? '');
            $recipeDescription = (string) ($recipe['description'] ?? '');
            $recipeEstimatedTime = (string) ($recipe['estimated_time'] ?? '');
            $recipeCategory = (string) ($recipe['category'] ?? '');

            $status = $this->recipeManager->isRecipeInstalled($recipeId) ? '✅' : '⬜';
            $difficulty = $this->getDifficultyColor($recipeDifficulty);

            $this->line("{$status} <fg={$difficulty}>{$recipeId}</> - {$recipeName}");
            $this->line("   {$recipeDescription}");
            $this->line("   🕒 {$recipeEstimatedTime} | 📋 {$recipeCategory}");
        }
    }

    /**
     * Display detailed recipe information.
     */
    protected function displayRecipeInfo(array $recipe): void
    {
        $status = $recipe['is_installed'] ? '✅ Telepítve' : '⬜ Nem telepítve';
        $difficulty = $this->getDifficultyColor($recipe['difficulty']);

        $this->info("📦 {$recipe['name']}");
        $this->line("ID: {$recipe['id']}");
        $this->line("Állapot: {$status}");
        $this->line("Leírás: {$recipe['description']}");
        $this->line("Nehézség: <fg={$difficulty}>{$recipe['difficulty']}</>");
        $this->line("Becsült idő: {$recipe['estimated_time']}");
        $this->line("Kategória: {$recipe['category']}");
        $this->line("Verzió: {$recipe['version']}");

        if (! empty($recipe['tags'])) {
            $this->line('Címkék: ' . implode(', ', $recipe['tags']));
        }

        if (! empty($recipe['packages'])) {
            $this->line('Csomagok: ' . implode(', ', $recipe['packages']));
        }

        if (! empty($recipe['dependencies'])) {
            $this->line('Függőségek: ' . implode(', ', $recipe['dependencies']));
        }
    }

    /**
     * Display installation result.
     */
    protected function displayInstallationResult(array $result): void
    {
        $this->info("✅ Recipe '{$result['recipe']['name']}' sikeresen telepítve!");

        if (isset($result['packages'])) {
            $this->line('📦 Telepített csomagok:');
            foreach ($result['packages'] as $package => $packageResult) {
                $status = $packageResult['success'] ? '✅' : '❌';
                $this->line("  {$status} {$package}");
            }
        }

        if (isset($result['migrations']) && $result['migrations']['success']) {
            $this->line('✅ Adatbázis migrálva');
        }

        if (isset($result['cache']) && $result['cache']['success']) {
            $this->line('✅ Cache törölve');
        }
    }

    /**
     * Get color for difficulty level.
     */
    protected function getDifficultyColor(string $difficulty): string
    {
        return match ($difficulty) {
            'easy' => 'green',
            'medium' => 'yellow',
            'advanced' => 'red',
            default => 'white',
        };
    }

    /**
     * Show help information.
     */
    protected function showHelp(): int
    {
        $this->info('🍳 Laravel Boilerplate Recipe Manager');
        $this->newLine();
        $this->line('Elérhető akciók:');
        $this->line('  list        - Recipe-k listázása');
        $this->line('  browse      - Interaktív böngészés');
        $this->line('  install     - Recipe telepítése');
        $this->line('  remove      - Recipe eltávolítása');
        $this->line('  info        - Recipe részletek');
        $this->line('  stats       - Statisztikák');
        $this->newLine();
        $this->line('Példák:');
        $this->line('  php artisan boilerplate:recipes browse');
        $this->line('  php artisan boilerplate:recipes list --category=backend');
        $this->line('  php artisan boilerplate:recipes install api-development');
        $this->line('  php artisan boilerplate:recipes info multi-tenancy');

        return 0;
    }
}
