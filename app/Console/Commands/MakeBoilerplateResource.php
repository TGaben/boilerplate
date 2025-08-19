<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeBoilerplateResource extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:boilerplate-resource {name : A modell neve (pl. Product, Category)} 
                           {--force : Meglévő fájlok felülírása}
                           {--skip-tests : Tesztek kihagyása}
                           {--skip-seeder : Seeder kihagyása}';

    /**
     * The console command description.
     */
    protected $description = 'Teljes CRUD scaffold létrehozása (Model, Filament Resource, Policy, Factory, Seeder, Tests)';

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
        $name = (string) $this->argument('name');
        $force = (bool) $this->option('force');
        $skipTests = (bool) $this->option('skip-tests');
        $skipSeeder = (bool) $this->option('skip-seeder');

        // Validálás
        if (! preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name)) {
            $this->error('A modell neve nagybetűvel kell kezdődjön és csak betűket/számokat tartalmazhat!');

            return self::FAILURE;
        }

        $this->info("🚀 Boilerplate Resource létrehozása: {$name}");
        $this->newLine();

        $steps = [
            'Model létrehozása' => fn () => $this->createModel($name, $force),
            'Migration létrehozása' => fn () => $this->createMigration($name),
            'Factory létrehozása' => fn () => $this->createFactory($name, $force),
            'Policy létrehozása' => fn () => $this->createPolicy($name, $force),
            'Filament Resource létrehozása' => fn () => $this->createFilamentResource($name, $force),
        ];

        if (! $skipSeeder) {
            $steps['Seeder létrehozása'] = fn () => $this->createSeeder($name, $force);
        }

        if (! $skipTests) {
            $steps['Tesztek létrehozása'] = fn () => $this->createTests($name, $force);
        }

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

        $this->newLine();
        $this->info('✅ Boilerplate Resource sikeresen létrehozva!');
        $this->newLine();

        // Következő lépések
        $this->comment('📝 Következő lépések:');
        $this->line('1. Futtasd le a migrációt: <info>sail artisan migrate</info>');
        $this->line("2. Töltsd fel alapadatokkal: <info>sail artisan db:seed --class={$name}Seeder</info>");
        $this->line('3. Ellenőrizd az admin panelt: <info>http://localhost/admin</info>');
        $this->line('4. Futtasd a teszteket: <info>sail artisan test</info>');

        return self::SUCCESS;
    }

    private function createModel(string $name, bool $force): bool
    {
        $arguments = [
            'name' => $name,
            '--migration' => true,
        ];

        if ($force) {
            $arguments['--force'] = true;
        }

        $this->call('make:model', $arguments);

        // Model fájl frissítése boilerplate specifikus tartalmakkal
        $modelPath = app_path("Models/{$name}.php");
        if ($this->files->exists($modelPath)) {
            $this->enhanceModel($modelPath, $name);
        }

        return true;
    }

    private function enhanceModel(string $modelPath, string $name): void
    {
        $stub = $this->getModelStub();
        $content = str_replace(
            ['{{modelName}}', '{{tableName}}'],
            [$name, Str::snake(Str::plural($name))],
            $stub,
        );

        $this->files->put($modelPath, $content);
    }

    private function getModelStub(): string
    {
        return <<<'STUB'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class {{modelName}} extends Model
{
    use HasFactory;
    use LogsActivity;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
STUB;
    }

    private function createMigration(string $name): bool
    {
        $tableName = Str::snake(Str::plural($name));

        // A make:model már létrehozta a migrációt, csak frissítjük
        $migrationFiles = glob(database_path("migrations/*_create_{$tableName}_table.php"));

        if (! empty($migrationFiles)) {
            $migrationPath = $migrationFiles[0];
            $this->enhanceMigration($migrationPath, $tableName);
        }

        return true;
    }

    private function enhanceMigration(string $migrationPath, string $tableName): void
    {
        $content = $this->files->get($migrationPath);

        // Alapértelmezett mezők hozzáadása
        $columnsToAdd = <<<'COLUMNS'
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
COLUMNS;

        // Lecseréljük a $table->timestamps(); sort
        $pattern = '/\$table->timestamps\(\);/';
        $replacement = $columnsToAdd;

        $newContent = preg_replace($pattern, $replacement, $content);
        $this->files->put($migrationPath, $newContent);
    }

    private function createFactory(string $name, bool $force): bool
    {
        $arguments = [
            'name' => "{$name}Factory",
        ];

        if ($force) {
            $arguments['--force'] = true;
        }

        $this->call('make:factory', $arguments);

        // Factory frissítése
        $factoryPath = database_path("factories/{$name}Factory.php");
        if ($this->files->exists($factoryPath)) {
            $this->enhanceFactory($factoryPath, $name);
        }

        return true;
    }

    private function enhanceFactory(string $factoryPath, string $name): void
    {
        $stub = $this->getFactoryStub();
        $content = str_replace(['{{modelName}}'], [$name], $stub);
        $this->files->put($factoryPath, $content);
    }

    private function getFactoryStub(): string
    {
        return <<<'STUB'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\{{modelName}};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\{{modelName}}>
 */
class {{modelName}}Factory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = {{modelName}}::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->paragraph(),
            'is_active' => $this->faker->boolean(80), // 80% eséllyel aktív
        ];
    }

    /**
     * Indicate that the model should be active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the model should be inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
STUB;
    }

    private function createPolicy(string $name, bool $force): bool
    {
        $arguments = [
            'name' => "{$name}Policy",
            '--model' => $name,
        ];

        if ($force) {
            $arguments['--force'] = true;
        }

        $this->call('make:policy', $arguments);

        // Policy frissítése boilerplate specifikus logikával
        $policyPath = app_path("Policies/{$name}Policy.php");
        if ($this->files->exists($policyPath)) {
            $this->enhancePolicy($policyPath, $name);
        }

        return true;
    }

    private function enhancePolicy(string $policyPath, string $name): void
    {
        $stub = $this->getPolicyStub();
        $content = str_replace(['{{modelName}}', '{{resourceName}}'], [$name, Str::snake($name)], $stub);
        $this->files->put($policyPath, $content);
    }

    private function getPolicyStub(): string
    {
        return <<<'STUB'
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\{{modelName}};
use Illuminate\Auth\Access\HandlesAuthorization;

class {{modelName}}Policy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('view_any_{{resourceName}}');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, {{modelName}} ${{resourceName}}): bool
    {
        return $user->hasRole('admin') || $user->can('view_{{resourceName}}');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('create_{{resourceName}}');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, {{modelName}} ${{resourceName}}): bool
    {
        return $user->hasRole('admin') || $user->can('update_{{resourceName}}');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, {{modelName}} ${{resourceName}}): bool
    {
        return $user->hasRole('admin') || $user->can('delete_{{resourceName}}');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('delete_any_{{resourceName}}');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, {{modelName}} ${{resourceName}}): bool
    {
        return $user->hasRole('admin') || $user->can('force_delete_{{resourceName}}');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('force_delete_any_{{resourceName}}');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, {{modelName}} ${{resourceName}}): bool
    {
        return $user->hasRole('admin') || $user->can('restore_{{resourceName}}');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('restore_any_{{resourceName}}');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, {{modelName}} ${{resourceName}}): bool
    {
        return $user->hasRole('admin') || $user->can('replicate_{{resourceName}}');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('reorder_{{resourceName}}');
    }
}
STUB;
    }

    private function createFilamentResource(string $name, bool $force): bool
    {
        $arguments = [
            'name' => $name,
            '--generate' => true,
        ];

        if ($force) {
            $arguments['--force'] = true;
        }

        $this->call('make:filament-resource', $arguments);

        // Filament Resource frissítése boilerplate specifikus konfigurációval
        $resourcePath = app_path("Filament/Resources/{$name}Resource.php");
        if ($this->files->exists($resourcePath)) {
            $this->enhanceFilamentResource($resourcePath, $name);
        }

        return true;
    }

    private function enhanceFilamentResource(string $resourcePath, string $name): void
    {
        $content = $this->files->get($resourcePath);

        // Policy hozzáadása a resource-hoz
        $policyClass = "\\App\\Policies\\{$name}Policy::class";

        // Navigation group beállítása
        $pattern = '/protected static \?string \$navigationGroup = .*?;/s';
        $replacement = "protected static ?string \$navigationGroup = 'Tartalomkezelés';";

        $content = preg_replace($pattern, $replacement, $content);

        // Egyéb boilerplate specifikus beállítások...
        $this->files->put($resourcePath, $content);
    }

    private function createSeeder(string $name, bool $force): bool
    {
        $arguments = [
            'name' => "{$name}Seeder",
        ];

        if ($force) {
            $arguments['--force'] = true;
        }

        $this->call('make:seeder', $arguments);

        // Seeder frissítése
        $seederPath = database_path("seeders/{$name}Seeder.php");
        if ($this->files->exists($seederPath)) {
            $this->enhanceSeeder($seederPath, $name);
        }

        return true;
    }

    private function enhanceSeeder(string $seederPath, string $name): void
    {
        $stub = $this->getSeederStub();
        $content = str_replace(['{{modelName}}'], [$name], $stub);
        $this->files->put($seederPath, $content);
    }

    private function getSeederStub(): string
    {
        return <<<'STUB'
<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\{{modelName}};
use Illuminate\Database\Seeder;

class {{modelName}}Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ha már vannak adatok, ne duplikáljunk
        if ({{modelName}}::count() > 0) {
            $this->command->info('{{modelName}} adatok már léteznek, kihagyva...');
            return;
        }

        $this->command->info('{{modelName}} teszt adatok létrehozása...');

        // Aktív elemek létrehozása
        {{modelName}}::factory()
            ->count(10)
            ->active()
            ->create();

        // Néhány inaktív elem
        {{modelName}}::factory()
            ->count(3)
            ->inactive()
            ->create();

        $this->command->info('{{modelName}} adatok sikeresen létrehozva!');
    }
}
STUB;
    }

    private function createTests(string $name, bool $force): bool
    {
        // Feature tesztek létrehozása
        $testPath = base_path("tests/Feature/{$name}Test.php");

        if (! $this->files->exists($testPath) || $force) {
            $this->files->put($testPath, $this->getTestStub($name));
        }

        // Filament Resource tesztek
        $filamentTestPath = base_path("tests/Feature/Filament/{$name}ResourceTest.php");
        $this->files->ensureDirectoryExists(dirname($filamentTestPath));

        if (! $this->files->exists($filamentTestPath) || $force) {
            $this->files->put($filamentTestPath, $this->getFilamentTestStub($name));
        }

        return true;
    }

    private function getTestStub(string $name): string
    {
        $modelName = $name;
        $variableName = Str::camel($name);

        return <<<STUB
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\{$modelName};
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class {$modelName}Test extends TestCase
{
    use RefreshDatabase;

    private User \$adminUser;
    private User \$regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        [\$this->adminUser, \$this->regularUser] = \$this->setupStandardTestUsers();
    }

    /** @test */
    public function {$variableName}_can_be_created(): void
    {
        \${$variableName} = {$modelName}::factory()->create([
            'name' => 'Teszt {$modelName}',
            'description' => 'Teszt leírás',
            'is_active' => true,
        ]);

        \$this->assertDatabaseHas('{$modelName}', [
            'name' => 'Teszt {$modelName}',
            'description' => 'Teszt leírás',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function {$variableName}_factory_works(): void
    {
        \${$variableName} = {$modelName}::factory()->create();

        \$this->assertInstanceOf({$modelName}::class, \${$variableName});
        \$this->assertNotEmpty(\${$variableName}->name);
    }

    /** @test */
    public function {$variableName}_can_be_active_or_inactive(): void
    {
        \$active{$modelName} = {$modelName}::factory()->active()->create();
        \$inactive{$modelName} = {$modelName}::factory()->inactive()->create();

        \$this->assertTrue(\$active{$modelName}->is_active);
        \$this->assertFalse(\$inactive{$modelName}->is_active);
    }

    /** @test */
    public function {$variableName}_logs_activity(): void
    {
        \${$variableName} = {$modelName}::factory()->create();
        \${$variableName}->update(['name' => 'Frissített név']);

        \$this->assertDatabaseHas('activity_log', [
            'subject_type' => {$modelName}::class,
            'subject_id' => \${$variableName}->id,
            'description' => 'updated',
        ]);
    }

    /** @test */
    public function {$variableName}_mass_assignment_works(): void
    {
        \$data = [
            'name' => 'Tesztnév',
            'description' => 'Teszt leírás',
            'is_active' => false,
        ];

        \${$variableName} = {$modelName}::create(\$data);

        \$this->assertEquals('Tesztnév', \${$variableName}->name);
        \$this->assertEquals('Teszt leírás', \${$variableName}->description);
        \$this->assertFalse(\${$variableName}->is_active);
    }
}
STUB;
    }

    private function getFilamentTestStub(string $name): string
    {
        $modelName = $name;
        $resourceName = Str::snake($name);

        return <<<STUB
<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\\{$modelName}Resource;
use App\Models\\{$modelName};
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class {$modelName}ResourceTest extends TestCase
{
    use RefreshDatabase;

    private User \$adminUser;
    private User \$regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        [\$this->adminUser, \$this->regularUser] = \$this->setupStandardTestUsers();
    }

    /** @test */
    public function admin_can_access_{$resourceName}_resource(): void
    {
        \$this->assertUserCanAccessAdminPanel(\$this->adminUser);
        
        \$response = \$this->actingAs(\$this->adminUser)
            ->get('/admin/{$resourceName}s');
            
        \$response->assertOk();
    }

    /** @test */
    public function regular_user_cannot_access_{$resourceName}_resource(): void
    {
        \$response = \$this->actingAs(\$this->regularUser)
            ->get('/admin/{$resourceName}s');
            
        \$response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_{$resourceName}_resource(): void
    {
        \$response = \$this->get('/admin/{$resourceName}s');
        \$response->assertRedirect('/admin/login');
    }

    /** @test */
    public function {$resourceName}_resource_uses_correct_model(): void
    {
        \$resource = new {$modelName}Resource();
        \$this->assertEquals({$modelName}::class, \$resource->getModel());
    }

    /** @test */
    public function admin_can_view_{$resourceName}_via_policy(): void
    {
        \${$resourceName} = {$modelName}::factory()->create();
        
        \$this->assertTrue(\$this->adminUser->can('viewAny', {$modelName}::class));
        \$this->assertTrue(\$this->adminUser->can('view', \${$resourceName}));
    }

    /** @test */
    public function regular_user_cannot_view_{$resourceName}_via_policy(): void
    {
        \${$resourceName} = {$modelName}::factory()->create();
        
        \$this->assertFalse(\$this->regularUser->can('viewAny', {$modelName}::class));
        \$this->assertFalse(\$this->regularUser->can('view', \${$resourceName}));
    }

    /** @test */
    public function admin_can_create_{$resourceName}_via_policy(): void
    {
        \$this->assertTrue(\$this->adminUser->can('create', {$modelName}::class));
    }

    /** @test */
    public function admin_can_update_{$resourceName}_via_policy(): void
    {
        \${$resourceName} = {$modelName}::factory()->create();
        \$this->assertTrue(\$this->adminUser->can('update', \${$resourceName}));
    }

    /** @test */
    public function admin_can_delete_{$resourceName}_via_policy(): void
    {
        \${$resourceName} = {$modelName}::factory()->create();
        \$this->assertTrue(\$this->adminUser->can('delete', \${$resourceName}));
    }
}
STUB;
    }
}
