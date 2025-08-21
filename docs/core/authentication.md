# User Authentication & Permission System

Ez a dokumentum magyarázza a Laravel Boilerplate beépített autentikációs és jogosultságkezelő rendszerét.

## 🔐 Áttekintés

A boilerplate a következő komponenseket használja:
- **Laravel Breeze** - Alapvető autentikáció
- **Spatie Laravel Permission** - Szerepkör- és jogosultságkezelés
- **Filament** - Admin panel felhasználókezelés
- **Activity Log** - Felhasználói tevékenységek naplózása

## 👥 Felhasználó Modellek

### User Model

```php
// app/Models/User.php
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles; // Spatie Permission trait
    use LogsActivity; // Activity logging
    
    protected $fillable = [
        'name', 'email', 'password', 'email_verified_at'
    ];
}
```

**Alapértelmezett mezők:**
- `name` - Felhasználó neve
- `email` - Email cím (egyedi)
- `password` - Titkosított jelszó
- `email_verified_at` - Email megerősítés időpontja

## 🎭 Szerepkörök (Roles)

### Alapértelmezett Szerepkörök

**Admin** (`admin`)
- Teljes rendszerhez való hozzáférés
- Minden jogosultság automatikusan
- Filament admin panel hozzáférés

**User** (`user`)  
- Alapvető felhasználói jogosultságok
- Frontend hozzáférés
- Korlátozott admin funkciók

### Szerepkör Műveletek

```bash
# Új szerepkör létrehozása
php artisan make:boilerplate-role manager --permissions="users.read,users.update"

# Szerepkör hozzárendelése felhasználóhoz
$user->assignRole('manager');

# Szerepkör ellenőrzése
$user->hasRole('admin');
```

## 🔑 Jogosultságok (Permissions)

### Permission Struktúra

A jogosultságok **erőforrás.művelet** formátumban vannak elnevezve:

```
users.create      # Felhasználó létrehozása
users.read        # Felhasználók megtekintése
users.update      # Felhasználó szerkesztése
users.delete      # Felhasználó törlése

roles.create      # Szerepkör létrehozása
roles.read        # Szerepkörök megtekintése
roles.update      # Szerepkör szerkesztése
roles.delete      # Szerepkör törlése

admin.access      # Admin panel hozzáférés
```

### Jogosultság Szinkronizálás

```bash
# Jogosultságok újragenerálása és szinkronizálása
php artisan boilerplate:setup-permissions

# Admin szerepkör szinkronizálása (minden jogosultság)
php artisan boilerplate:setup-permissions --sync-admin

# Dry run (csak kilistázás, változtatás nélkül)
php artisan boilerplate:setup-permissions --dry-run
```

## 🚪 Middleware és Route Protection

### Alapvető Authentication Middleware

```php
// routes/web.php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    });
});
```

### Permission-based Protection

```php
// Controller-ben
class UserController extends Controller
{
    public function index()
    {
        $this->authorize('users.read');
        // ...
    }
}

// Route szinten
Route::get('/admin/users', [UserController::class, 'index'])
    ->middleware('permission:users.read');

// Blade template-ben
@can('users.create')
    <a href="{{ route('admin.users.create') }}">Új felhasználó</a>
@endcan
```

## 👤 Felhasználókezelés a Gyakorlatban

### Új Felhasználó Létrehozása

```php
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => Hash::make('password123')
]);

// Szerepkör hozzárendelése
$user->assignRole('user');

// Speciális jogosultság hozzárendelése
$user->givePermissionTo('posts.create');
```

### Többszintű Jogosultság Ellenőrzés

```php
// Több jogosultság közül bármelyik
$user->hasAnyPermission(['users.create', 'users.update']);

// Minden jogosultság egyszerre
$user->hasAllPermissions(['users.read', 'users.update']);

// Szerepkör VAGY jogosultság
$user->hasRole('admin') || $user->can('users.manage');
```

## 🏥 Admin Panel Integráció

### Filament User Resource

A Filament automatikusan integrálja a szerepkör- és jogosultságkezelést:

```php
// app/Filament/Resources/UserResource.php
public static function form(Form $form): Form
{
    return $form->schema([
        TextInput::make('name')->required(),
        TextInput::make('email')->email()->required(),
        Select::make('roles')
            ->multiple()
            ->relationship('roles', 'name')
            ->preload(),
    ]);
}
```

### Permission-based Menu

```php
// AdminPanelProvider.php
->navigationItems([
    NavigationItem::make('Users')
        ->icon('heroicon-o-users')
        ->url('/admin/users')
        ->visible(fn () => auth()->user()->can('users.read')),
])
```

## 📊 Activity Logging

### Automatikus Naplózás

```php
// User modellben
protected static $logAttributes = ['name', 'email'];
protected static $logOnlyDirty = true;

// Automatikusan naplózott események:
// - Felhasználó létrehozása
// - Adatok módosítása
// - Szerepkör változások
// - Bejelentkezések/kijelentkezések
```

### Activity Log Lekérdezése

```php
// Felhasználó összes tevékenysége
$activities = activity()
    ->causedBy($user)
    ->get();

// Típus szerinti szűrés
$logins = activity()
    ->causedBy($user)
    ->where('description', 'logged_in')
    ->get();
```

## 🔧 Testreszabás

### Egyedi Jogosultságok

```php
// database/seeders/CustomPermissionsSeeder.php
Permission::create(['name' => 'reports.view']);
Permission::create(['name' => 'reports.export']);
Permission::create(['name' => 'settings.manage']);
```

### Egyedi Szerepkörök

```php
$role = Role::create(['name' => 'content-manager']);
$role->givePermissionTo([
    'posts.create',
    'posts.read', 
    'posts.update',
    'posts.delete'
]);
```

### Policy Alapú Autorizáció

```php
// app/Policies/PostPolicy.php
public function update(User $user, Post $post)
{
    return $user->can('posts.update') && 
           ($post->user_id === $user->id || $user->hasRole('admin'));
}
```

## 🚨 Biztonsági Szempontok

### Jelszó Szabályok

```php
// config/auth.php
'password_timeout' => 10800, // 3 óra

// Jelszó validálás
Password::min(8)
    ->letters()
    ->mixedCase()
    ->numbers()
    ->symbols()
```

### Rate Limiting

```php
// config/auth.php
'throttle' => [
    'max_attempts' => 5,
    'decay_minutes' => 15,
]
```

### Session Biztonság

```php
// config/session.php
'secure' => env('SESSION_SECURE_COOKIE', true),
'http_only' => true,
'same_site' => 'strict',
```

## 🧪 Tesztelés

### Authentication Tesztek

```php
// tests/Feature/AuthTest.php
public function test_user_can_login_with_valid_credentials()
{
    $user = User::factory()->create([
        'password' => Hash::make('password123')
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password123'
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
}
```

### Permission Tesztek

```php
public function test_user_with_permission_can_access_resource()
{
    $user = $this->createUserWithPermissions(['users.read']);
    
    $response = $this->actingAs($user)->get('/admin/users');
    
    $response->assertStatus(200);
}
```

## 📚 További Olvasnivaló

- [Spatie Laravel Permission dokumentáció](https://spatie.be/docs/laravel-permission)
- [Laravel Authentication](https://laravel.com/docs/authentication)
- [Filament User Management](https://filamentphp.com/docs/panels/users)
- [Testing Authentication](https://laravel.com/docs/testing#authentication)


