# Filament Admin Panel

Ez a dokumentum a Laravel Boilerplate beépített Filament admin panel használatát és testreszabását mutatja be.

## 🎛️ Áttekintés

A boilerplate egy teljesen konfigurált Filament v3 admin panelt tartalmaz:
- **Felhasználókezelés** - Users, Roles, Permissions
- **Activity Log** - Rendszer tevékenységek nyomon követése  
- **Dashboard** - Testreszabható áttekintő
- **Navigáció** - Jogosultság-alapú menürendszer
- **Tema** - Dark/Light mode váltás

## 🚀 Gyors Kezdés

### Admin Panel Elérése

```
URL: http://localhost/admin
Alapértelmezett admin:
  Email: admin@admin.com
  Jelszó: password
```

### Első Bejelentkezés Után

1. **Jelszó megváltoztatása** - Kattints a profilra (jobb felső sarok)
2. **Felhasználók kezelése** - Users menüpont
3. **Szerepkörök beállítása** - Roles menüpont
4. **Dashboard testreszabása** - Widget-ek hozzáadása/eltávolítása

## 🏗️ Filament Resources

### Meglévő Resources

**UserResource** (`app/Filament/Resources/UserResource.php`)
- Felhasználók listázása, szerkesztése, létrehozása
- Szerepkör hozzárendelés
- Email verifikáció kezelése
- Jelszó reset funkció

**RoleResource** (`app/Filament/Resources/RoleResource.php`)
- Szerepkörök kezelése
- Jogosultság hozzárendelés
- Hierarchikus szerepkör struktúra

**ActivityResource** (`app/Filament/Resources/ActivityResource.php`)
- Felhasználói tevékenységek megtekintése
- Szűrés és keresés
- Exportálási lehetőségek

### Új Resource Létrehozása

#### Automatikus Scaffolding

```bash
# Teljes CRUD resource generálása
php artisan make:boilerplate-resource Product

# Ez létrehozza:
# - app/Models/Product.php
# - database/migrations/xxx_create_products_table.php
# - app/Filament/Resources/ProductResource.php
# - app/Policies/ProductPolicy.php
# - database/factories/ProductFactory.php
# - database/seeders/ProductSeeder.php
# - tests/Feature/ProductTest.php
```

#### Manuális Resource

```bash
# Csak Filament resource
php artisan make:filament-resource Product

# Resource osztály + pages
php artisan make:filament-resource Product --generate
```

### Resource Testreszabása

#### Form Schema

```php
// app/Filament/Resources/ProductResource.php
public static function form(Form $form): Form
{
    return $form->schema([
        TextInput::make('name')
            ->required()
            ->maxLength(255),
            
        Textarea::make('description')
            ->rows(3),
            
        TextInput::make('price')
            ->numeric()
            ->prefix('$')
            ->required(),
            
        Select::make('category_id')
            ->relationship('category', 'name')
            ->required(),
            
        Toggle::make('is_active')
            ->default(true),
            
        FileUpload::make('image')
            ->image()
            ->imageEditor(),
    ]);
}
```

#### Table Columns

```php
public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('name')
                ->searchable()
                ->sortable(),
                
            TextColumn::make('category.name')
                ->sortable(),
                
            TextColumn::make('price')
                ->money('USD')
                ->sortable(),
                
            IconColumn::make('is_active')
                ->boolean(),
                
            TextColumn::make('created_at')
                ->dateTime()
                ->sortable(),
        ])
        ->filters([
            SelectFilter::make('category')
                ->relationship('category', 'name'),
                
            Filter::make('active')
                ->query(fn (Builder $query): Builder => $query->where('is_active', true)),
                
            Filter::make('created_at')
                ->form([
                    DatePicker::make('created_from'),
                    DatePicker::make('created_until'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['created_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                })
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
}
```

## 📱 Dashboard Testreszabás

### Widget-ek Létrehozása

```bash
# Stat widget (számláló)
php artisan make:filament-widget UserStatsWidget --stats

# Chart widget (grafikon)
php artisan make:filament-widget SalesChart --chart

# Table widget (táblázat)
php artisan make:filament-widget LatestOrdersWidget --table
```

### Példa: Stats Widget

```php
// app/Filament/Widgets/UserStatsWidget.php
class UserStatsWidget extends BaseWidget
{
    protected static ?string $heading = 'Felhasználó Statisztikák';
    
    protected function getStats(): array
    {
        return [
            Stat::make('Összes Felhasználó', User::count())
                ->description('32k növekedés')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('Aktív Felhasználók', User::where('last_login_at', '>=', now()->subDays(30))->count())
                ->description('7% növekedés')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('Új Regisztrációk', User::whereDate('created_at', today())->count())
                ->description('3% csökkenés')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
        ];
    }
}
```

### Dashboard Konfiguráció

```php
// app/Filament/Pages/Dashboard.php
class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            UserStatsWidget::class,
            SalesChart::class,
            LatestOrdersWidget::class,
        ];
    }
    
    public function getColumns(): int|string|array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }
}
```

## 🎨 Tema és Megjelenés

### Brand Beállítások

```php
// config/boilerplate.php
'brand' => [
    'name' => env('BRAND_NAME', 'Laravel Boilerplate'),
    'logo_light' => env('BRAND_LOGO_LIGHT', 'images/logo-light.svg'),
    'logo_dark' => env('BRAND_LOGO_DARK', 'images/logo-dark.svg'),
    'favicon' => env('BRAND_FAVICON', 'images/favicon.ico'),
],
```

### Színséma Testreszabás

```php
// app/Providers/Filament/AdminPanelProvider.php
->colors([
    'primary' => Color::Blue,
    'gray' => Color::Zinc,
])
->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
->pages([
    Pages\Dashboard::class,
])
->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
->widgets([
    Widgets\AccountWidget::class,
    Widgets\FilamentInfoWidget::class,
])
```

### Dark Mode

```php
->darkMode(false) // Letiltja
->darkMode(true)  // Kötelező dark mode
// Vagy üresen hagyva: felhasználó választhat
```

## 🔒 Jogosultságkezelés

### Resource-szintű Jogosultságok

```php
// app/Filament/Resources/ProductResource.php
public static function canViewAny(): bool
{
    return auth()->user()->can('products.read');
}

public static function canCreate(): bool
{
    return auth()->user()->can('products.create');
}

public static function canEdit(Model $record): bool
{
    return auth()->user()->can('products.update');
}

public static function canDelete(Model $record): bool
{
    return auth()->user()->can('products.delete');
}
```

### Navigáció Jogosultságok

```php
// app/Filament/Resources/ProductResource.php
public static function getNavigationGroup(): ?string
{
    return 'Termékkezelés';
}

public static function getNavigationSort(): ?int
{
    return 2;
}

public static function shouldRegisterNavigation(): bool
{
    return auth()->user()->can('products.read');
}
```

### Global Search

```php
public static function getGlobalSearchResultTitle(Model $record): string
{
    return $record->name;
}

public static function getGlobalSearchResultDetails(Model $record): array
{
    return [
        'Kategória' => $record->category?->name,
        'Ár' => '$' . number_format($record->price, 2),
    ];
}

public static function getGlobalSearchEloquentQuery(): Builder
{
    return parent::getGlobalSearchEloquentQuery()->with(['category']);
}
```

## 🔧 Speciális Funkciók

### Bulk Actions

```php
Tables\Actions\BulkAction::make('activate')
    ->label('Aktiválás')
    ->icon('heroicon-o-check')
    ->action(function (Collection $records) {
        $records->each->update(['is_active' => true]);
        
        Notification::make()
            ->success()
            ->title('Sikeresen aktiválva')
            ->body($records->count() . ' rekord aktiválva.')
            ->send();
    })
    ->deselectRecordsAfterCompletion()
    ->requiresConfirmation(),
```

### Export/Import

```bash
# Export plugin telepítése
composer require pxlrbt/filament-excel
```

```php
// Resource-ben
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

Tables\Actions\BulkActionGroup::make([
    ExportBulkAction::make()
        ->exports([
            ExcelExport::make()
                ->fromTable()
                ->withFilename('products-' . date('Y-m-d')),
        ]),
    Tables\Actions\DeleteBulkAction::make(),
]),
```

### Modal Forms

```php
Tables\Actions\Action::make('quick_edit')
    ->label('Gyors Szerkesztés')
    ->icon('heroicon-o-pencil')
    ->form([
        TextInput::make('name')->required(),
        TextInput::make('price')->numeric(),
    ])
    ->action(function (array $data, Product $record): void {
        $record->update($data);
        
        Notification::make()
            ->success()
            ->title('Frissítve')
            ->send();
    }),
```

## 📊 Monitoring és Naplózás

### Activity Log Widget

```php
// app/Filament/Widgets/RecentActivityWidget.php
class RecentActivityWidget extends BaseWidget
{
    protected static ?string $heading = 'Legutóbbi Tevékenységek';
    
    protected function getViewData(): array
    {
        return [
            'activities' => Activity::with('causer')
                ->latest()
                ->limit(10)
                ->get(),
        ];
    }
}
```

### Performance Monitoring

```php
// app/Filament/Widgets/PerformanceWidget.php
protected function getStats(): array
{
    return [
        Stat::make('Database Queries', DB::getQueryLog()->count())
            ->description('Az aktuális kéréshez'),
            
        Stat::make('Memory Usage', memory_get_usage(true) / 1024 / 1024 . ' MB')
            ->description('Peak: ' . memory_get_peak_usage(true) / 1024 / 1024 . ' MB'),
    ];
}
```

## 🧪 Tesztelés

### Resource Tesztek

```php
// tests/Feature/Filament/ProductResourceTest.php
public function test_admin_can_view_products_list()
{
    $admin = $this->createAdminUser();
    Product::factory()->count(5)->create();
    
    $this->actingAs($admin)
        ->get('/admin/products')
        ->assertSuccessful()
        ->assertSee('Termékek'); // Resource label
}

public function test_user_without_permission_cannot_access_products()
{
    $user = $this->createUserWithoutPermissions();
    
    $this->actingAs($user)
        ->get('/admin/products')
        ->assertForbidden();
}
```

### Widget Tesztek

```php
public function test_user_stats_widget_displays_correct_data()
{
    User::factory()->count(10)->create();
    
    $admin = $this->createAdminUser();
    
    $component = Livewire::test(UserStatsWidget::class)
        ->assertSee('Összes Felhasználó')
        ->assertSee('11'); // 10 + 1 admin
}
```

## 📚 További Források

- [Filament v3 Documentation](https://filamentphp.com/docs)
- [Filament Resources](https://filamentphp.com/docs/panels/resources)
- [Filament Widgets](https://filamentphp.com/docs/panels/dashboard)
- [Filament Plugins](https://filamentphp.com/plugins)

## 💡 Best Practices

1. **Jogosultságok** - Mindig ellenőrizd a resource-szintű jogosultságokat
2. **Performance** - Használj `with()` eager loading-ot related model-ekhez
3. **UX** - Adj értelmes neveket és leírásokat a mezőkhöz
4. **Testing** - Írj teszteket minden custom resource-hoz
5. **Monitoring** - Használd az Activity Log-ot fontos műveletek nyomon követésére


