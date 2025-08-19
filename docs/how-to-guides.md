# Útmutató Gyűjtemény

Lépésről lépésre tutorialok a gyakori fejlesztési feladatokhoz a Laravel Vállalati Boilerplate-ben.

## 📋 Tartalomjegyzék

### 🚀 Boilerplate Scaffolding Parancsok (Új!)
- [Boilerplate Resource Generálása](#boilerplate-resource-generálása)
- [Szerepkör Létrehozása Alapértelmezett Jogosultságokkal](#szerepkör-létrehozása-alapértelmezett-jogosultságokkal)
- [Jogosultság Struktúra Szinkronizálása](#jogosultság-struktúra-szinkronizálása)
- [Alkalmazás Teljes Újrainitiálása](#alkalmazás-teljes-újrainitiálása)

### 🎛️ Manuális Fejlesztési Útmutatók
- [Új Filament Resource Hozzáadása](#új-filament-resource-hozzáadása)
- [Egyedi Szerepkörök és Jogosultságok Létrehozása](#egyedi-szerepkörök-és-jogosultságok-létrehozása)
- [Egyedi Artisan Parancsok Futtatása](#egyedi-artisan-parancsok-futtatása)
- [Új Frontend Komponens Hozzáadása](#új-frontend-komponens-hozzáadása)
- [Admin Dashboard Testreszabása](#admin-dashboard-testreszabása)
- [Fejlesztői Környezet Beállítása](#fejlesztői-környezet-beállítása)
- [Átfogó Tesztek Írása](#átfogó-tesztek-írása)
- [Produkciós Telepítés](#produkciós-telepítés)

---

## 🚀 Boilerplate Resource Generálása

### Teljes CRUD Rendszer Automatikus Létrehozása

Az `make:boilerplate-resource` parancs egyetlen lépésben létrehoz minden szükséges fájlt egy teljes CRUD rendszerhez:

```bash
# Alap használat - teljes scaffold
./vendor/bin/sail artisan make:boilerplate-resource Product

# Opciók használata
./vendor/bin/sail artisan make:boilerplate-resource Product --force --skip-tests --skip-seeder
```

#### Automatikusan Generált Fájlok

A parancs a következő fájlokat hozza létre:

1. **Model**: `app/Models/Product.php`
   - Alap fillable mezők
   - Spatie Activity Log integráció
   - Timestamp kezelés

2. **Migration**: `database/migrations/xxxx_create_products_table.php`
   - ID, timestamps mezők
   - Alapvető index-ek

3. **Factory**: `database/factories/ProductFactory.php`
   - Faker alapú teszt adatok
   - Kapcsolódó modell referenciák

4. **Policy**: `app/Policies/ProductPolicy.php`
   - CRUD jogosultság ellenőrzések
   - Admin és felhasználói szintű hozzáférés

5. **Filament Resource**: `app/Filament/Resources/ProductResource.php`
   - Alapvető form és table konfiguráció
   - Search és filter funkciók
   - Bulk actions

6. **Seeder**: `database/seeders/ProductSeeder.php` (opcionális)
   - Teszt adatok generálása
   - Factory használat

7. **Feature Test**: `tests/Feature/Filament/ProductResourceComprehensiveTest.php` (opcionális)
   - CRUD műveletek tesztelése
   - Jogosultság ellenőrzések
   - Admin panel integráció tesztek

#### Használat Után Szükséges Lépések

```bash
# 1. Migration futtatása
./vendor/bin/sail artisan migrate

# 2. Jogosultságok szinkronizálása (automatikusan felismeri az új modellt)
./vendor/bin/sail artisan boilerplate:setup-permissions

# 3. Seeder futtatása (ha létrehoztad)
./vendor/bin/sail artisan db:seed --class=ProductSeeder

# 4. Tesztek futtatása
./vendor/bin/sail artisan test --filter=ProductResourceComprehensiveTest
```

#### Elérhető Opciók

- `--force`: Felülírja a meglévő fájlokat
- `--skip-tests`: Kihagyja a teszt fájlok generálását
- `--skip-seeder`: Kihagyja a seeder létrehozását

#### Példa Kimenet

```
📋 Model létrehozása...
   ✅ Model létrehozása sikeres!
📋 Migration létrehozása...
   ✅ Migration létrehozása sikeres!
📋 Factory létrehozása...
   ✅ Factory létrehozása sikeres!
📋 Policy létrehozása...
   ✅ Policy létrehozása sikeres!
📋 Filament Resource létrehozása...
   ✅ Filament Resource létrehozása sikeres!
📋 Seeder létrehozása...
   ✅ Seeder létrehozása sikeres!
📋 Tesztek létrehozása...
   ✅ Tesztek létrehozása sikeres!

✅ Boilerplate Resource sikeresen létrehozva!

🚀 Következő lépések:
1. Futtasd a migrációt: sail artisan migrate
2. Szinkronizáld a jogosultságokat: sail artisan boilerplate:setup-permissions
3. Teszteld a funkcionalitást: sail artisan test --filter=ProductResourceComprehensiveTest
```

---

## 🔐 Szerepkör Létrehozása Alapértelmezett Jogosultságokkal

### Új Szerepkörök Gyors Beállítása

Az `make:boilerplate-role` parancs egyszerűsíti új szerepkörök létrehozását:

```bash
# Alap szerepkör létrehozása alapértelmezett jogosultságokkal
./vendor/bin/sail artisan make:boilerplate-role editor

# Egyedi jogosultságok megadása
./vendor/bin/sail artisan make:boilerplate-role moderator --permissions=view_any_user,view_user,update_user

# Egyedi guard megadása
./vendor/bin/sail artisan make:boilerplate-role api-user --guard=api

# Meglévő szerepkör felülírása
./vendor/bin/sail artisan make:boilerplate-role editor --force
```

#### Alapértelmezett Jogosultságok

Ha nem adsz meg egyedi jogosultságokat, a parancs automatikusan hozzárendeli a következő alapvető jogosultságokat:

- `view_any_user` - Felhasználók listázása
- `view_user` - Felhasználó megtekintése
- `view_any_activity` - Tevékenységek listázása
- `view_activity` - Tevékenység megtekintése

#### Szerepkör Névkonvenciók

A szerepkör neve követnie kell ezeket a szabályokat:
- Kisbetűvel kezdődik
- Csak kisbetűket és alsóvonásokat tartalmazhat
- Betűvel végződik

**Helyes példák**: `editor`, `content_manager`, `moderator`
**Helytelen példák**: `Editor`, `content-manager`, `moderator_`

#### Elérhető Opciók

- `--permissions=*`: Egyedi jogosultságok listája (vesszővel elválasztva)
- `--guard=web`: Guard megadása (alapértelmezett: web)
- `--force`: Meglévő szerepkör felülírása

#### Használati Példák

```bash
# Marketing csapat szerepkör
./vendor/bin/sail artisan make:boilerplate-role marketing_manager --permissions=view_any_user,create_user,update_user

# API felhasználók szerepkör
./vendor/bin/sail artisan make:boilerplate-role api_client --guard=api --permissions=view_any_product,view_product

# Tartalomkezelő szerepkör
./vendor/bin/sail artisan make:boilerplate-role content_editor --permissions=view_any_user,view_user,view_any_activity
```

#### Szerepkör Hozzárendelése Felhasználóhoz

A létrehozott szerepkört így rendelheted hozzá felhasználókhoz:

```php
// Egy felhasználóhoz
$user = User::find(1);
$user->assignRole('editor');

// Többszörös szerepkör
$user->assignRole(['editor', 'moderator']);

// Programozott hozzárendelés
User::where('email', 'like', '%@company.com')->get()->each(function ($user) {
    $user->assignRole('editor');
});
```

---

## ⚙️ Jogosultság Struktúra Szinkronizálása

### Automatikus Jogosultság Felismerés és Szinkronizálás

Az `boilerplate:setup-permissions` parancs automatikusan felismeri és létrehozza az összes szükséges jogosultságot:

```bash
# Alapvető szinkronizálás
./vendor/bin/sail artisan boilerplate:setup-permissions

# Teljes reset és újraépítés
./vendor/bin/sail artisan boilerplate:setup-permissions --reset

# Admin szerepkör szinkronizálása az összes jogosultsággal
./vendor/bin/sail artisan boilerplate:setup-permissions --sync-admin

# Dry-run mód - változtatások előnézete
./vendor/bin/sail artisan boilerplate:setup-permissions --dry-run
```

#### Mit Csinál a Parancs?

1. **Core Jogosultságok**: Alapvető rendszer jogosultságok létrehozása
2. **Model Alapú Jogosultságok**: Automatikus felismerés `app/Models/` mappából
3. **Filament Jogosultságok**: Filament resource-ok alapján
4. **Admin Szinkronizálás**: Admin szerepkör frissítése az összes jogosultsággal

#### Automatikusan Felismert Jogosultságok

**Minden modellhez** (pl. `User`, `Product`, `Order`):
- `view_any_{model}` - Lista megtekintése
- `view_{model}` - Egyedi elem megtekintése  
- `create_{model}` - Új elem létrehozása
- `update_{model}` - Elem szerkesztése
- `delete_{model}` - Elem törlése
- `delete_any_{model}` - Tömeges törlés

**Core Jogosultságok**:
- `view_any_activity`, `view_activity` - Tevékenység napló
- `access_admin_panel` - Admin panel hozzáférés
- `manage_settings` - Rendszerbeállítások

#### Opciók Részletesen

- `--reset`: Törli az összes meglévő jogosultságot és újraépíti
- `--sync-admin`: Az 'admin' szerepkört szinkronizálja az összes jogosultsággal
- `--dry-run`: Csak megmutatja mit csinálna, de nem alkalmazza a változtatásokat

#### Használati Példák

```bash
# Új modell hozzáadása után jogosultságok frissítése
./vendor/bin/sail artisan make:model Invoice
./vendor/bin/sail artisan boilerplate:setup-permissions

# Teljes jogosultság rendszer újraépítése fejlesztés során
./vendor/bin/sail artisan boilerplate:setup-permissions --reset --sync-admin

# Ellenőrzés hogy milyen jogosultságok jönnének létre
./vendor/bin/sail artisan boilerplate:setup-permissions --dry-run
```

#### Kimenet Példa

```
🔑 Boilerplate Jogosultság Struktúra Beállítása

✅ Core jogosultságok (5 db):
   - access_admin_panel
   - manage_settings
   - view_any_activity
   - view_activity

✅ User model jogosultságok (6 db):
   - view_any_user, view_user, create_user
   - update_user, delete_user, delete_any_user

✅ Product model jogosultságok (6 db):
   - view_any_product, view_product, create_product
   - update_product, delete_product, delete_any_product

🔄 Admin szerepkör szinkronizálva (17 jogosultság)

📊 Összesítés:
   Szerepkörök: 3 (admin, user, editor)
   Jogosultságok: 17
   Felhasználók: 5
```

---

## 🔄 Alkalmazás Teljes Újrainitiálása

### Fejlesztői Környezet Gyors Reset

Az `boilerplate:fresh-install` parancs egyetlen lépésben újraindítja a teljes alkalmazást:

```bash
# Alap fresh install seeding nélkül
./vendor/bin/sail artisan boilerplate:fresh-install

# Teljes fresh install alapadatokkal
./vendor/bin/sail artisan boilerplate:fresh-install --seed

# Force mód (éles környezetben is futtatható - VESZÉLYES!)
./vendor/bin/sail artisan boilerplate:fresh-install --force --seed

# NPM assets kihagyása
./vendor/bin/sail artisan boilerplate:fresh-install --seed --skip-npm

# Egyedi környezet megadása
./vendor/bin/sail artisan boilerplate:fresh-install --seed --environment=testing
```

#### Mit Csinál a Parancs?

1. **Biztonsági Ellenőrzések**: Éles környezetben megakadályozza a futtatást
2. **Cache Törlése**: Összes Laravel cache tisztítása
3. **Adatbázis Reset**: `migrate:fresh` futtatása
4. **Jogosultságok**: Automatikus jogosultság struktúra beállítása
5. **Seeding**: Alapadatok feltöltése (opcionális)
6. **NPM Build**: Frontend assets újraépítése (opcionális)
7. **Optimalizáció**: Cache-ek újraépítése

#### Biztonsági Funkciók

⚠️ **FIGYELEM**: Ez a parancs **TÖRLI az összes adatot** az adatbázisból!

- **Éles környezet védelem**: Automatikusan megakadályozza az éles környezetben való futtatást
- **Megerősítés**: Interaktív megerősítést kér a törlés előtt
- **Force mód**: `--force` flag-gel felülbírálható (VESZÉLYES!)

#### Elérhető Opciók

- `--seed`: Alapadatok feltöltése a seeder-ekkel
- `--force`: Biztonsági ellenőrzések felülbírálása (NE használd élesben!)
- `--skip-npm`: NPM telepítés és build kihagyása
- `--environment=local`: Környezet explicit megadása

#### Használati Esetek

**Fejlesztés során**:
```bash
# Gyors development reset
./vendor/bin/sail artisan boilerplate:fresh-install --seed
```

**Tesztelés előtt**:
```bash
# Clean slate tesztekhez
./vendor/bin/sail artisan boilerplate:fresh-install --seed --environment=testing
```

**CI/CD Pipeline-ban**:
```bash
# Automated testing setup
./vendor/bin/sail artisan boilerplate:fresh-install --seed --skip-npm --force
```

#### Kimenet Példa

```
🚀 Laravel Boilerplate Fresh Install

⚠️  FIGYELMEZTETÉS: Ez a parancs TÖRLI az összes adatot!
   Környezet: local
   Seedingel: Igen
   NPM build: Igen

 Biztosan folytatod? (yes/no) [no]: yes

📋 Cache törlése...
   ✅ Cache törlése sikeres!
📋 Adatbázis törlése és migráció...
   ✅ Adatbázis törlése és migráció sikeres!
📋 Jogosultságok beállítása...
   ✅ Jogosultságok beállítása sikeres!
📋 Alap adatok feltöltése...
   ✅ Alap adatok feltöltése sikeres!
📋 NPM függőségek telepítése...
   ✅ NPM függőségek telepítése sikeres!
📋 Assets build...
   ✅ Assets build sikeres!
📋 Végleges optimalizáció...
   ✅ Végleges optimalizáció sikeres!

🎉 Fresh Install Sikeres!

📋 Bejelentkezési adatok:
   Admin: admin@boilerplate.local / password
   
🔗 Linkek:
   Alkalmazás: http://localhost
   Admin: http://localhost/admin
   Mailpit: http://localhost:8025

⚡ Következő lépések:
   1. Teszteld az admin bejelentkezést
   2. Ellenőrizd a frontend működését
   3. Futtasd a teszteket: sail artisan test
```

#### Hibaelhárítás

**NPM hibák**:
```bash
# NPM cache tisztítása
npm cache clean --force

# Node modules újratelepítése
rm -rf node_modules package-lock.json
npm install
```

**Jogosultság hibák**:
```bash
# Storage jogosultságok javítása
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:www-data storage bootstrap/cache
```

---

## 🎛️ Új Filament Resource Hozzáadása

### Termékkezelő Rendszer Létrehozása

Hozzunk létre egy teljes CRUD rendszert termékek kezeléséhez.

#### 1. Lépés: Model és Migration Létrehozása

```bash
# Model létrehozása migration-nal
./vendor/bin/sail artisan make:model Product -m

# Factory létrehozása teszteléshez
./vendor/bin/sail artisan make:factory ProductFactory
```

#### 2. Lépés: Migration Definiálása

Szerkeszd a `database/migrations/xxxx_create_products_table.php` fájlt:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('category');
            $table->string('sku')->unique();
            $table->timestamps();
            
            $table->index(['is_active', 'category']);
            $table->index('sku');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

#### 3. Lépés: Model Konfigurálása

Szerkeszd az `app/Models/Product.php` fájlt:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'is_active',
        'category',
        'sku',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'stock' => 'integer',
    ];

    // Tevékenység naplózás konfiguráció
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Kapcsolatok
    public function orders()
    {
        return $this->belongsToMany(Order::class)->withPivot('quantity', 'price');
    }

    // Scope-ok
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    // Accessor-ok
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }
}
```

#### 4. Lépés: Filament Resource Létrehozása

```bash
./vendor/bin/sail artisan make:filament-resource Product --generate
```

#### 5. Lépés: Resource Konfigurálása

Szerkeszd az `app/Filament/Resources/ProductResource.php` fájlt:

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Raktár';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Termék Információk')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Név')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $context, $state, callable $set) {
                                if ($context === 'create') {
                                    $set('sku', strtoupper(substr(md5($state), 0, 8)));
                                }
                            }),

                        Forms\Components\TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->unique(Product::class, 'sku', ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\Select::make('category')
                            ->label('Kategória')
                            ->options([
                                'electronics' => 'Elektronika',
                                'clothing' => 'Ruházat',
                                'books' => 'Könyvek',
                                'home' => 'Otthon & Kert',
                                'sports' => 'Sport',
                            ])
                            ->required()
                            ->searchable(),

                        Forms\Components\Textarea::make('description')
                            ->label('Leírás')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Árazás és Raktár')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Ár')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->maxValue(999999.99),

                        Forms\Components\TextInput::make('stock')
                            ->label('Raktárkészlet')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(999999),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktív')
                            ->default(true),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Név')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Kategória')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'electronics' => 'info',
                        'clothing' => 'warning',
                        'books' => 'success',
                        'home' => 'primary',
                        'sports' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('price')
                    ->label('Ár')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Készlet')
                    ->numeric()
                    ->sortable()
                    ->color(fn (int $state): string => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger')),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktív')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Létrehozva')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategória')
                    ->options([
                        'electronics' => 'Elektronika',
                        'clothing' => 'Ruházat',
                        'books' => 'Könyvek',
                        'home' => 'Otthon & Kert',
                        'sports' => 'Sport',
                    ]),

                Tables\Filters\Filter::make('in_stock')
                    ->label('Raktáron')
                    ->query(fn (Builder $query): Builder => $query->where('stock', '>', 0)),

                Tables\Filters\Filter::make('active')
                    ->label('Aktív')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Kiválasztottak Aktiválása')
                        ->icon('heroicon-o-check')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
```

#### 6. Lépés: Migration Futtatása

```bash
./vendor/bin/sail artisan migrate
```

#### 7. Lépés: Jogosultságok Létrehozása

Add hozzá a `database/seeders/RolesAndPermissionsSeeder.php` fájlhoz:

```php
$permissions = [
    // Meglévő jogosultságok...
    
    // Termék jogosultságok
    'view_any_product',
    'view_product',
    'create_product',
    'update_product',
    'delete_product',
    'delete_any_product',
];
```

```bash
./vendor/bin/sail artisan db:seed --class=RolesAndPermissionsSeeder
```

## 🔐 Egyedi Szerepkörök és Jogosultságok Létrehozása

### Manager Szerepkör Hozzáadása

#### 1. Lépés: Migration Létrehozása Szerepkörhöz

```bash
./vendor/bin/sail artisan make:migration add_manager_role_and_permissions
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Manager szerepkör létrehozása
        $managerRole = Role::create(['name' => 'manager']);
        
        // Manager-specifikus jogosultságok létrehozása
        $permissions = [
            'view_reports',
            'manage_inventory',
            'approve_orders',
            'view_analytics',
        ];
        
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        
        // Jogosultságok hozzárendelése manager-hez
        $managerRole->givePermissionTo([
            'view_reports',
            'manage_inventory',
            'approve_orders',
            'view_analytics',
            'view_any_product',
            'view_product',
            'create_product',
            'update_product',
        ]);
    }

    public function down(): void
    {
        Role::where('name', 'manager')->delete();
        Permission::whereIn('name', [
            'view_reports',
            'manage_inventory', 
            'approve_orders',
            'view_analytics',
        ])->delete();
    }
};
```

#### 2. Lépés: User Model Frissítése (ha szükséges)

```php
// Az app/Models/User.php fájlban

public function canAccessPanel(Panel $panel): bool
{
    if ($panel->getId() === 'admin') {
        return $this->hasAnyRole(['admin', 'manager']);
    }
    return true;
}
```

## ⚡ Egyedi Artisan Parancsok Futtatása

### Laravel Sail Használata

```bash
# Alapvető artisan parancsok
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
./vendor/bin/sail artisan cache:clear

# Queue kezelés
./vendor/bin/sail artisan queue:work
./vendor/bin/sail artisan queue:restart

# Egyedi parancsok
./vendor/bin/sail artisan make:command ProcessMonthlyReports
./vendor/bin/sail artisan app:process-monthly-reports

# Karbantartási mód
./vendor/bin/sail artisan down --message="Adatbázis frissítése"
./vendor/bin/sail artisan up

# Teljesítmény optimalizáció
./vendor/bin/sail artisan config:cache
./vendor/bin/sail artisan route:cache
./vendor/bin/sail artisan view:cache

# Tesztelési parancsok
./vendor/bin/sail artisan test
./vendor/bin/sail artisan test --coverage
./vendor/bin/sail artisan test --filter=UserTest
```

### Egyedi Parancs Létrehozása

```bash
./vendor/bin/sail artisan make:command GenerateUserReport
```

```php
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateUserReport extends Command
{
    protected $signature = 'app:generate-user-report {--format=csv : Kimeneti formátum}';

    protected $description = 'Átfogó felhasználói jelentés generálása';

    public function handle(): int
    {
        $this->info('Felhasználói jelentés generálása...');

        $users = User::with('roles')
            ->withCount('activities')
            ->get();

        $format = $this->option('format');

        match ($format) {
            'csv' => $this->generateCsv($users),
            'json' => $this->generateJson($users),
            default => $this->displayTable($users),
        };

        $this->info('Jelentés sikeresen generálva!');
        return Command::SUCCESS;
    }

    private function displayTable($users): void
    {
        $this->table(
            ['ID', 'Név', 'Email', 'Szerepkörök', 'Tevékenységek'],
            $users->map(fn ($user) => [
                $user->id,
                $user->name,
                $user->email,
                $user->roles->pluck('name')->join(', '),
                $user->activities_count,
            ])
        );
    }
}
```

## 🎨 Új Frontend Komponens Hozzáadása

### Státusz Jelvény Komponens Létrehozása

#### 1. Lépés: Blade Komponens Létrehozása

```bash
./vendor/bin/sail artisan make:component StatusBadge
```

#### 2. Lépés: Komponens Osztály Definiálása

Szerkeszd az `app/View/Components/StatusBadge.php` fájlt:

```php
<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StatusBadge extends Component
{
    public function __construct(
        public string $status,
        public string $size = 'md'
    ) {}

    public function render(): View
    {
        return view('components.status-badge');
    }

    public function getColorClasses(): string
    {
        return match ($this->status) {
            'active', 'success', 'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'pending', 'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'inactive', 'error', 'failed' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            'processing', 'info' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
        };
    }

    public function getSizeClasses(): string
    {
        return match ($this->size) {
            'sm' => 'px-2 py-1 text-xs',
            'md' => 'px-2.5 py-0.5 text-sm',
            'lg' => 'px-3 py-1 text-base',
            default => 'px-2.5 py-0.5 text-sm',
        };
    }
}
```

#### 3. Lépés: Komponens Template Létrehozása

Szerkeszd a `resources/views/components/status-badge.blade.php` fájlt:

```blade
<span {{ $attributes->merge([
    'class' => 'inline-flex items-center rounded-full font-medium ' . 
               $getColorClasses() . ' ' . $getSizeClasses()
]) }}>
    {{ ucfirst($status) }}
</span>
```

#### 4. Lépés: Komponens Használata

```blade
<!-- A Blade template-jeidben -->
<x-status-badge status="active" />
<x-status-badge status="pending" size="lg" />
<x-status-badge status="error" size="sm" class="ml-2" />
```

## 🎛️ Admin Dashboard Testreszabása

### Egyedi Widget-ek Hozzáadása

#### 1. Lépés: Statisztika Widget Létrehozása

```bash
./vendor/bin/sail artisan make:filament-widget ProductStatsWidget --stats-overview
```

#### 2. Lépés: Widget Konfigurálása

Szerkeszd az `app/Filament/Widgets/ProductStatsWidget.php` fájlt:

```php
<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Összes Termék', Product::count())
                ->description('Minden termék a raktárban')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),

            Stat::make('Aktív Termékek', Product::where('is_active', true)->count())
                ->description('Jelenleg elérhető')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary'),

            Stat::make('Alacsony Készletű Tételek', Product::where('stock', '<', 10)->count())
                ->description('Utánpótlás szükséges')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make('Teljes Érték', '$' . number_format(
                Product::where('is_active', true)->sum(\DB::raw('price * stock')), 2
            ))
                ->description('Raktár értéke')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
        ];
    }
}
```

#### 3. Lépés: Widget Regisztrálása a Dashboard-on

Szerkeszd az `app/Filament/Pages/Dashboard.php` fájlt:

```php
<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\ProductStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            ProductStatsWidget::class,
            // Egyéb widget-ek...
        ];
    }
}
```

## 🧪 Átfogó Tesztek Írása

### Product Resource Tesztelése

#### 1. Lépés: Teszt Fájl Létrehozása

```bash
./vendor/bin/sail artisan make:test ProductResourceTest
```

#### 2. Lépés: Átfogó Tesztek Írása

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');
    }

    /** @test */
    public function admin_can_access_product_resource_index(): void
    {
        $this->actingAs($this->adminUser)
             ->get('/admin/products')
             ->assertSuccessful();
    }

    /** @test */
    public function admin_can_create_product(): void
    {
        $productData = [
            'name' => 'Teszt Termék',
            'sku' => 'TEST123',
            'category' => 'electronics',
            'price' => 99.99,
            'stock' => 50,
            'is_active' => true,
        ];

        $this->actingAs($this->adminUser)
             ->post('/admin/products', $productData)
             ->assertRedirect();

        $this->assertDatabaseHas('products', $productData);
    }

    /** @test */
    public function admin_can_update_product(): void
    {
        $product = Product::factory()->create();
        
        $updateData = ['name' => 'Frissített Termék Név'];

        $this->actingAs($this->adminUser)
             ->put("/admin/products/{$product->id}", array_merge(
                 $product->toArray(),
                 $updateData
             ))
             ->assertRedirect();

        $this->assertDatabaseHas('products', $updateData);
    }

    /** @test */
    public function admin_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->adminUser)
             ->delete("/admin/products/{$product->id}")
             ->assertRedirect();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /** @test */
    public function regular_user_cannot_access_product_resource(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->actingAs($user)
             ->get('/admin/products')
             ->assertForbidden();
    }

    /** @test */
    public function product_activity_is_logged(): void
    {
        $product = Product::factory()->create();

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Product::class,
            'subject_id' => $product->id,
            'description' => 'created',
        ]);
    }
}
```

## 🚀 Produkciós Telepítés

### Docker-alapú Telepítés

#### 1. Lépés: Produkciós Dockerfile Létrehozása

Hozd létre a `Dockerfile.production` fájlt:

```dockerfile
FROM php:8.3-fpm-alpine

# Rendszer függőségek telepítése
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip

# PHP kiterjesztések telepítése
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Composer telepítése
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Munkakönyvtár beállítása
WORKDIR /var/www

# Alkalmazás fájlok másolása
COPY . .

# Függőségek telepítése
RUN composer install --optimize-autoloader --no-dev

# Alkalmazás kulcs generálása
RUN php artisan key:generate

# Alkalmazás optimalizálása
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Jogosultságok beállítása
RUN chown -R www-data:www-data /var/www && \
    chmod -R 755 /var/www/storage

EXPOSE 9000

CMD ["php-fpm"]
```

---

Ezek az útmutatók gyakorlati, lépésről lépésre instrukciókkat adnak a gyakori fejlesztési feladatokhoz. Minden útmutató teljes kód példákat tartalmaz és a Laravel legjobb gyakorlatait követi.
