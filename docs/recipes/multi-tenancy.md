# Recipe: Multi-Tenancy Support

## 🎯 Mikor Használd?

- **SaaS alkalmazás** fejlesztésekor
- **Multi-company** rendszerhez
- **Franchise** kezelő alkalmazáshoz
- **White-label** megoldásokhoz
- **Izolált tenant** adatok szükségesek

## ⏱️ Implementációs Idő: 1-2 nap

## 📋 Előfeltételek

- ✅ Core boilerplate telepítve
- ✅ Haladó Laravel tudás
- ✅ Adatbázis design tapasztalat
- ✅ Domain routing ismerete
- ⚠️ Cache és session kezelés ismerete

## 🚀 Quick Start

```bash
# 1. Stancl Tenancy package telepítése
composer require stancl/tenancy

# 2. Tenancy konfiguráció publikálása
php artisan vendor:publish --provider="Stancl\Tenancy\TenancyServiceProvider"

# 3. Central domains migration futtatása
php artisan migrate

# 4. Tenant migration létrehozása
php artisan make:migration CreateTenantsTable

# 5. Tenancy inicializálása
php artisan tenancy:install
```

## 📖 Részletes Implementáció

### 1. Tenancy Package Konfiguráció

#### Tenancy Config

```php
// config/tenancy.php
<?php

return [
    'tenant_model' => \App\Models\Tenant::class,
    'id_generator' => \Stancl\Tenancy\UUIDGenerator::class,

    'domain_model' => \App\Models\Domain::class,

    'central_domains' => [
        '127.0.0.1',
        'localhost',
        env('CENTRAL_DOMAIN', 'app.test'),
    ],

    'bootstrappers' => [
        \Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class,
        \Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper::class,
        \Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper::class,
        \Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper::class,
    ],

    'database' => [
        'central_connection' => env('DB_CONNECTION', 'mysql'),
        
        'template_tenant_connection' => null,

        'prefix_base' => 'tenant',
        'suffix_base' => '',

        'managers' => [
            'mysql' => \Stancl\Tenancy\TenantDatabaseManagers\MySQLDatabaseManager::class,
            'pgsql' => \Stancl\Tenancy\TenantDatabaseManagers\PostgreSQLDatabaseManager::class,
            'sqlite' => \Stancl\Tenancy\TenantDatabaseManagers\SQLiteDatabaseManager::class,
        ],
    ],

    'cache' => [
        'tag_base' => 'tenant',
    ],

    'filesystem' => [
        'suffix_base' => 'tenant',
        'disks' => [
            'local',
            'public',
        ],
    ],

    'redis' => [
        'prefix_base' => 'tenant',
    ],

    'features' => [
        // \Stancl\Tenancy\Features\UserImpersonation::class,
        // \Stancl\Tenancy\Features\TelescopeTags::class,
        \Stancl\Tenancy\Features\UniversalRoutes::class,
        \Stancl\Tenancy\Features\TenantConfig::class,
        // \Stancl\Tenancy\Features\CrossDomainRedirect::class,
        // \Stancl\Tenancy\Features\ViteBundler::class,
    ],

    'storage_drivers' => [
        'db' => \Stancl\Tenancy\StorageDrivers\Database\DatabaseStorageDriver::class,
    ],
];
```

#### Environment Configuration

```env
# Central database (tenant management)
CENTRAL_DOMAIN=app.test
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saas_central
DB_USERNAME=root
DB_PASSWORD=

# Tenant database template
TENANT_DB_HOST=127.0.0.1
TENANT_DB_PORT=3306
TENANT_DB_USERNAME=root
TENANT_DB_PASSWORD=

# Cache/Redis configuration for tenancy
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 2. Tenant & Domain Models

#### Tenant Model

```php
// app/Models/Tenant.php
<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'name',
        'email',
        'plan',
        'trial_ends_at',
        'subscription_ends_at',
        'is_active',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email', 
            'plan',
            'trial_ends_at',
            'subscription_ends_at',
            'is_active',
        ];
    }

    // Tenant status helpers
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function isSubscriptionActive(): bool
    {
        return $this->subscription_ends_at && $this->subscription_ends_at->isFuture();
    }

    public function isActive(): bool
    {
        return $this->is_active && ($this->isOnTrial() || $this->isSubscriptionActive());
    }

    public function getPrimaryDomain(): ?string
    {
        return $this->domains()->where('is_primary', true)->first()?->domain;
    }

    // Helper method to run code in tenant context
    public function run(callable $callback)
    {
        return tenancy()->initialize($this)->run($callback);
    }
}
```

#### Domain Model

```php
// app/Models/Domain.php
<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

class Domain extends BaseDomain
{
    protected $fillable = [
        'domain',
        'tenant_id',
        'is_primary',
        'certificate_status',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function makePrimary(): void
    {
        $this->tenant->domains()->update(['is_primary' => false]);
        $this->update(['is_primary' => true]);
    }
}
```

### 3. Central Database Migrations

#### Tenants Migration

```php
// database/migrations/2024_01_01_000001_create_tenants_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('plan')->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
```

#### Domains Migration

```php
// database/migrations/2024_01_01_000002_create_domains_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->increments('id');
            $table->string('domain')->unique();
            $table->string('tenant_id');
            $table->boolean('is_primary')->default(false);
            $table->string('certificate_status')->default('none');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
```

### 4. Tenant Database Structure

#### Tenant Migration Template

```php
// database/migrations/tenant/2024_01_01_000001_create_tenant_users_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('user');
            $table->json('permissions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

#### Tenant Specific Models

```php
// app/Models/Tenant/User.php
<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    // Tenant context scope
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['owner', 'admin']);
    }
}
```

### 5. Tenant Management Service

#### Tenant Service

```php
// app/Services/TenantService.php
<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Tenant;
use App\Models\Tenant\User as TenantUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Stancl\Tenancy\Facades\Tenancy;

class TenantService
{
    public function createTenant(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            // Create tenant in central database
            $tenant = Tenant::create([
                'name' => $data['company_name'],
                'email' => $data['email'],
                'plan' => $data['plan'] ?? 'trial',
                'trial_ends_at' => now()->addDays(14),
                'is_active' => true,
            ]);

            // Create domain
            $domain = $tenant->domains()->create([
                'domain' => $data['domain'],
                'is_primary' => true,
            ]);

            // Initialize tenant database
            $tenant->create();

            // Create tenant admin user
            $tenant->run(function () use ($data) {
                TenantUser::create([
                    'name' => $data['admin_name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'role' => 'owner',
                    'is_active' => true,
                ]);

                // Run tenant seeders
                $this->seedTenantDatabase();
            });

            return $tenant;
        });
    }

    public function deleteTenant(Tenant $tenant): bool
    {
        return DB::transaction(function () use ($tenant) {
            // Delete tenant database
            $tenant->delete();

            // Delete domains
            $tenant->domains()->delete();

            // Delete tenant record
            $tenant->forceDelete();

            return true;
        });
    }

    public function suspendTenant(Tenant $tenant): bool
    {
        return $tenant->update(['is_active' => false]);
    }

    public function activateTenant(Tenant $tenant): bool
    {
        return $tenant->update(['is_active' => true]);
    }

    public function addDomain(Tenant $tenant, string $domain, bool $isPrimary = false): Domain
    {
        if ($isPrimary) {
            $tenant->domains()->update(['is_primary' => false]);
        }

        return $tenant->domains()->create([
            'domain' => $domain,
            'is_primary' => $isPrimary,
        ]);
    }

    public function changePlan(Tenant $tenant, string $plan, ?\Carbon\Carbon $subscriptionEnds = null): bool
    {
        return $tenant->update([
            'plan' => $plan,
            'subscription_ends_at' => $subscriptionEnds,
            'trial_ends_at' => null, // End trial when changing to paid plan
        ]);
    }

    protected function seedTenantDatabase(): void
    {
        // Create default roles and permissions for tenant
        \Artisan::call('db:seed', [
            '--class' => 'TenantSeeder',
            '--database' => 'tenant',
        ]);
    }

    public function getTenantByDomain(string $domain): ?Tenant
    {
        $domainModel = Domain::where('domain', $domain)->first();
        return $domainModel?->tenant;
    }

    public function getTenantStats(Tenant $tenant): array
    {
        return $tenant->run(function () {
            return [
                'users_count' => TenantUser::count(),
                'active_users_count' => TenantUser::active()->count(),
                'storage_used' => $this->getTenantStorageUsage(),
                'last_activity' => TenantUser::latest('updated_at')->first()?->updated_at,
            ];
        });
    }

    protected function getTenantStorageUsage(): int
    {
        // Calculate tenant storage usage
        $path = storage_path('app/tenant' . tenant('id'));
        if (!is_dir($path)) {
            return 0;
        }

        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
            $size += $file->getSize();
        }

        return $size;
    }
}
```

### 6. Route Configuration

#### Route Service Provider

```php
// app/Providers/RouteServiceProvider.php
public function boot(): void
{
    parent::boot();

    $this->configureRateLimiting();

    $this->routes(function () {
        // Central routes (tenant management)
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));

        Route::middleware('web')
            ->group(base_path('routes/web.php'));

        // Tenant routes
        Route::middleware([
            'web',
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class,
        ])->group(base_path('routes/tenant.php'));
    });
}
```

#### Tenant Routes

```php
// routes/tenant.php
<?php

use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\UserController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    
    Route::get('/', function () {
        return view('tenant.welcome');
    });

    Route::middleware(['auth:tenant'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');
        
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('tenant.users.index');
            Route::post('/', [UserController::class, 'store'])->name('tenant.users.store');
            Route::get('/{user}', [UserController::class, 'show'])->name('tenant.users.show');
            Route::put('/{user}', [UserController::class, 'update'])->name('tenant.users.update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('tenant.users.destroy');
        });
    });
});
```

### 7. Tenant Authentication

#### Tenant Auth Guard

```php
// config/auth.php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],

    'tenant' => [
        'driver' => 'session',
        'provider' => 'tenant_users',
    ],

    'api' => [
        'driver' => 'sanctum',
        'provider' => 'users',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],

    'tenant_users' => [
        'driver' => 'eloquent',
        'model' => App\Models\Tenant\User::class,
    ],
],
```

#### Tenant Auth Controller

```php
// app/Http/Controllers/Tenant/AuthController.php
<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('tenant.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('tenant')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('tenant.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('tenant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }

    public function showRegistrationForm()
    {
        return view('tenant.auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

        Auth::guard('tenant')->login($user);

        return redirect(route('tenant.dashboard'));
    }
}
```

### 8. Central Admin Management

#### Central Tenant Controller

```php
// app/Http/Controllers/Admin/TenantController.php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function index()
    {
        $tenants = Tenant::with('domains')->paginate(20);
        return view('admin.tenants.index', compact('tenants'));
    }

    public function show(Tenant $tenant)
    {
        $stats = $this->tenantService->getTenantStats($tenant);
        return view('admin.tenants.show', compact('tenant', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'admin_name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants',
            'domain' => 'required|string|unique:domains',
            'password' => 'required|string|min:8',
            'plan' => 'required|in:trial,basic,premium',
        ]);

        $tenant = $this->tenantService->createTenant($validated);

        return redirect()->route('admin.tenants.show', $tenant)
            ->with('success', 'Tenant created successfully');
    }

    public function suspend(Tenant $tenant)
    {
        $this->tenantService->suspendTenant($tenant);
        return back()->with('success', 'Tenant suspended');
    }

    public function activate(Tenant $tenant)
    {
        $this->tenantService->activateTenant($tenant);
        return back()->with('success', 'Tenant activated');
    }

    public function destroy(Tenant $tenant)
    {
        $this->tenantService->deleteTenant($tenant);
        return redirect()->route('admin.tenants.index')
            ->with('success', 'Tenant deleted successfully');
    }
}
```

#### Filament Tenant Resource

```php
// app/Filament/Resources/TenantResource.php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Models\Tenant;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Tenant Management';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->email()
                ->required()
                ->maxLength(255),

            Select::make('plan')
                ->options([
                    'trial' => 'Trial',
                    'basic' => 'Basic',
                    'premium' => 'Premium',
                ])
                ->required(),

            DateTimePicker::make('trial_ends_at'),
            DateTimePicker::make('subscription_ends_at'),

            Toggle::make('is_active')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable(),

                BadgeColumn::make('plan')
                    ->colors([
                        'secondary' => 'trial',
                        'success' => 'basic',
                        'warning' => 'premium',
                    ]),

                BooleanColumn::make('is_active'),

                TextColumn::make('trial_ends_at')
                    ->dateTime(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                EditAction::make(),
                
                Action::make('impersonate')
                    ->label('Login as')
                    ->icon('heroicon-o-user')
                    ->url(fn (Tenant $record) => route('tenant.impersonate', $record))
                    ->openUrlInNewTab(),

                Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-pause')
                    ->action(fn (Tenant $record) => $record->update(['is_active' => false]))
                    ->requiresConfirmation()
                    ->visible(fn (Tenant $record) => $record->is_active),

                Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-play')
                    ->action(fn (Tenant $record) => $record->update(['is_active' => true]))
                    ->visible(fn (Tenant $record) => !$record->is_active),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
```

## 🧪 Testing

### Multi-Tenancy Tests

```php
// tests/Feature/TenancyTest.php
<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Domain;
use App\Models\Tenant\User as TenantUser;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenancyTest extends TestCase
{
    use RefreshDatabase;

    protected TenantService $tenantService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenantService = app(TenantService::class);
    }

    public function test_can_create_tenant()
    {
        $data = [
            'company_name' => 'Test Company',
            'admin_name' => 'John Doe',
            'email' => 'admin@testcompany.com',
            'domain' => 'testcompany.app.test',
            'password' => 'password123',
            'plan' => 'trial',
        ];

        $tenant = $this->tenantService->createTenant($data);

        $this->assertInstanceOf(Tenant::class, $tenant);
        $this->assertEquals('Test Company', $tenant->name);
        $this->assertEquals('trial', $tenant->plan);
        
        // Check domain was created
        $this->assertCount(1, $tenant->domains);
        $this->assertEquals('testcompany.app.test', $tenant->getPrimaryDomain());

        // Check tenant user was created
        $tenant->run(function () {
            $this->assertEquals(1, TenantUser::count());
            $user = TenantUser::first();
            $this->assertEquals('owner', $user->role);
        });
    }

    public function test_tenant_isolation()
    {
        // Create two tenants
        $tenant1 = $this->tenantService->createTenant([
            'company_name' => 'Company 1',
            'admin_name' => 'Admin 1',
            'email' => 'admin1@company1.com',
            'domain' => 'company1.app.test',
            'password' => 'password123',
            'plan' => 'trial',
        ]);

        $tenant2 = $this->tenantService->createTenant([
            'company_name' => 'Company 2',
            'admin_name' => 'Admin 2',
            'email' => 'admin2@company2.com',
            'domain' => 'company2.app.test',
            'password' => 'password123',
            'plan' => 'trial',
        ]);

        // Create users in each tenant
        $tenant1->run(function () {
            TenantUser::create([
                'name' => 'User 1',
                'email' => 'user1@company1.com',
                'password' => bcrypt('password'),
                'role' => 'user',
            ]);
        });

        $tenant2->run(function () {
            TenantUser::create([
                'name' => 'User 2',
                'email' => 'user2@company2.com',
                'password' => bcrypt('password'),
                'role' => 'user',
            ]);
        });

        // Verify isolation
        $tenant1->run(function () {
            $this->assertEquals(2, TenantUser::count()); // admin + user
            $this->assertNull(TenantUser::where('email', 'user2@company2.com')->first());
        });

        $tenant2->run(function () {
            $this->assertEquals(2, TenantUser::count()); // admin + user
            $this->assertNull(TenantUser::where('email', 'user1@company1.com')->first());
        });
    }

    public function test_domain_routing()
    {
        $tenant = $this->tenantService->createTenant([
            'company_name' => 'Test Company',
            'admin_name' => 'Admin',
            'email' => 'admin@test.com',
            'domain' => 'test.app.test',
            'password' => 'password123',
            'plan' => 'trial',
        ]);

        // Test tenant route access
        $response = $this->get('http://test.app.test/');
        $response->assertOk();

        // Test central domain doesn't access tenant routes
        $response = $this->get('/');
        $response->assertOk();
    }
}
```

## ⚡ Performance Optimizations

### Database Connection Pooling

```php
// config/database.php
'tenant' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => null, // Will be set dynamically
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
    'engine' => null,
    'options' => [
        PDO::ATTR_PERSISTENT => true, // Connection pooling
    ],
],
```

### Tenant Cache Optimization

```php
// app/Services/TenantCacheService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Stancl\Tenancy\Facades\Tenancy;

class TenantCacheService
{
    public function remember(string $key, $ttl, callable $callback)
    {
        $tenantKey = $this->getTenantCacheKey($key);
        return Cache::remember($tenantKey, $ttl, $callback);
    }

    public function forget(string $key): bool
    {
        $tenantKey = $this->getTenantCacheKey($key);
        return Cache::forget($tenantKey);
    }

    public function flush(): bool
    {
        $pattern = $this->getTenantCacheKey('*');
        return Cache::tags([tenant('id')])->flush();
    }

    protected function getTenantCacheKey(string $key): string
    {
        $tenantId = tenant('id');
        return "tenant:{$tenantId}:{$key}";
    }
}
```

## 🔒 Security Considerations

### Tenant Data Isolation

```php
// app/Http/Middleware/EnsureTenantDataIsolation.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Facades\Tenancy;

class EnsureTenantDataIsolation
{
    public function handle(Request $request, Closure $next)
    {
        // Ensure we're in tenant context
        if (!Tenancy::initialized()) {
            abort(403, 'Tenant context not initialized');
        }

        // Add tenant ID to all query builders automatically
        $this->addGlobalScope();

        return $next($request);
    }

    protected function addGlobalScope(): void
    {
        // This would add tenant_id to all queries automatically
        // Implementation depends on your data structure
    }
}
```

### Cross-Tenant Access Prevention

```php
// app/Http/Middleware/PreventCrossTenantAccess.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventCrossTenantAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user('tenant');
        
        if ($user && !$this->userBelongsToCurrentTenant($user)) {
            auth('tenant')->logout();
            return redirect()->route('tenant.login')
                ->withErrors(['auth' => 'Session invalid for this domain']);
        }

        return $next($request);
    }

    protected function userBelongsToCurrentTenant($user): bool
    {
        // Verify user belongs to current tenant
        // This depends on your user-tenant relationship structure
        return true;
    }
}
```

## 📚 További Olvasnivaló

- [Stancl Tenancy Documentation](https://tenancyforlaravel.com/docs)
- [Multi-Tenant Database Design Patterns](https://docs.microsoft.com/en-us/azure/sql-database/saas-tenancy-app-design-patterns)
- [Laravel Tenancy Best Practices](https://tenancyforlaravel.com/docs/v3/optional-features/)

## 🆘 Troubleshooting

### Gyakori Problémák

**Problem**: Tenant database nem jön létre
```bash
# Ellenőrizd a database permissions-t
# Ellenőrizd a tenancy konfig database manager-t
```

**Problem**: Cross-tenant data leak
```bash
# Implementálj global scopokat
# Használj tenant-aware middleware-t minden route-on
```

**Problem**: Session confusion between tenants
```bash
# Használj különböző session guard-okat
# Implementálj domain-specific session handling-et
```

**Problem**: Cache collision between tenants
```bash
# Használj tenant-prefixed cache kulcsokat
# Implementálj tenant-aware cache tageket
```


