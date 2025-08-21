# Testing Foundation

Ez a dokumentum a Laravel Boilerplate beépített tesztelési alapjait és helper rendszerét mutatja be.

## 🧪 Áttekintés

A boilerplate komplett tesztelési alapot biztosít:
- **PHPUnit** konfigurálva Laravel-hez
- **Helper Traits** gyakori tesztelési feladatokhoz
- **Factory-k** test data generálásához
- **Filament Testing** admin panel teszteléshez
- **Database Testing** sqlite in-memory-val

## 🏗️ Test Foundation

### Base TestCase

```php
// tests/TestCase.php
abstract class TestCase extends BaseTestCase
{
    use BoilerplateTestHelpers;    // Boilerplate-specifikus helpers
    use FilamentTestHelpers;       // Filament admin panel helpers
    use RefreshDatabase;           // Database reset minden teszt után
}
```

### Test Environment

```env
# .env.testing vagy templates/environments/env.testing
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
CACHE_DRIVER=array
MAIL_MAILER=array
```

## 👥 User & Permission Testing

### Boilerplate Test Helpers

```php
// tests/Traits/BoilerplateTestHelpers.php használata

class ExampleTest extends TestCase
{
    public function test_admin_can_access_users()
    {
        // Admin felhasználó létrehozása
        $admin = $this->createAdminUser();
        
        $response = $this->actingAs($admin)->get('/admin/users');
        
        $response->assertStatus(200);
    }
    
    public function test_user_with_specific_role()
    {
        // Felhasználó specifikus szerepkörrel
        $manager = $this->createUserWithRole('manager');
        
        $this->assertUserHasRole($manager, 'manager');
    }
    
    public function test_user_with_permissions()
    {
        // Felhasználó specifikus jogosultságokkal
        $user = $this->createUserWithPermissions([
            'users.read',
            'users.update'
        ]);
        
        $this->assertUserHasPermission($user, 'users.read');
        $this->assertUserCannotPerform($user, 'users.delete');
    }
}
```

### Standard Test Users

```php
public function test_with_standard_users()
{
    // Létrehoz: admin, manager, user típusú felhasználókat
    $users = $this->setupStandardTestUsers();
    
    $admin = $users['admin'];
    $manager = $users['manager'];
    $regularUser = $users['user'];
    
    // Admin hozzáfér mindenhez
    $this->assertUserCanAccessAdminPanel($admin);
    
    // Manager korlátozott hozzáférés
    $this->assertUserHasRole($manager, 'manager');
    
    // Regular user nincs admin hozzáférés
    $this->assertUserCannotAccessAdminPanel($regularUser);
}
```

## 🎛️ Filament Admin Panel Testing

### Filament Test Helpers

```php
// tests/Traits/FilamentTestHelpers.php használata

class UserResourceTest extends TestCase
{
    public function test_admin_can_list_users()
    {
        $admin = $this->createAdminUser();
        User::factory()->count(5)->create();
        
        // Admin panel route tesztelés
        $this->assertAdminRouteAccessible('/admin/users', $admin);
    }
    
    public function test_filament_resource_access()
    {
        $admin = $this->createAdminUser();
        $user = $this->createUserWithoutPermissions();
        
        // UserResource hozzáférés tesztelése
        $results = $this->testFilamentResourceAccess(
            '/admin/users',
            $admin,     // Hozzáférhet
            $user       // Nem férhet hozzá
        );
        
        $this->assertTrue($results['admin_can_access']);
        $this->assertFalse($results['user_can_access']);
    }
    
    public function test_filament_permissions()
    {
        $user = $this->createUserWithPermissions(['users.read']);
        
        $this->assertUserHasFilamentPermission(
            $user, 
            'viewAny', 
            UserResource::class
        );
    }
}
```

### Dashboard Testing

```php
public function test_admin_dashboard_loads()
{
    $admin = $this->createAdminUser();
    
    $this->assertAdminDashboardLoads($admin);
}

public function test_dashboard_performance()
{
    $admin = $this->createAdminUser();
    
    // Performance assertion (max 500ms)
    $this->assertAdminPerformance($admin, 500);
}
```

## 🗄️ Database Testing

### Factory Usage

```php
public function test_user_creation()
{
    // Factory használata
    $user = User::factory()->create([
        'email' => 'test@example.com'
    ]);
    
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com'
    ]);
}

public function test_user_with_roles()
{
    $user = User::factory()
        ->hasAttached(Role::factory()->create(['name' => 'editor']))
        ->create();
        
    $this->assertTrue($user->hasRole('editor'));
}
```

### Database Assertions

```php
public function test_database_operations()
{
    $user = $this->createAdminUser();
    
    // Database assertions
    $this->assertDatabaseCount('users', 1);
    $this->assertModelExists($user);
    
    $user->delete();
    
    $this->assertModelMissing($user);
    $this->assertSoftDeleted($user); // Ha soft delete van
}
```

### Activity Logging Tests

```php
public function test_activity_logging()
{
    $user = $this->createAdminUser();
    
    // Tevékenység létrehozása
    $post = Post::factory()->create(['user_id' => $user->id]);
    
    // Activity log ellenőrzése
    $this->assertActivityLogged('created', $post, $user);
    
    // Activity részletek
    $activity = $this->getLastActivity();
    $this->assertEquals('created', $activity->description);
    $this->assertEquals($user->id, $activity->causer_id);
}
```

## 🌐 HTTP Testing

### Route Testing

```php
public function test_protected_routes()
{
    // Vendég felhasználó
    $this->get('/admin')->assertRedirect('/login');
    
    // Authentikált felhasználó
    $user = $this->createUserWithoutPermissions();
    $this->actingAs($user)->get('/admin')->assertForbidden();
    
    // Admin felhasználó
    $admin = $this->createAdminUser();
    $this->actingAs($admin)->get('/admin')->assertOk();
}

public function test_api_endpoints()
{
    $user = $this->createUserWithPermissions(['api.access']);
    
    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/users');
        
    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'email']
            ]
        ]);
}
```

### Form Testing

```php
public function test_user_creation_form()
{
    $admin = $this->createAdminUser();
    
    $response = $this->actingAs($admin)->post('/admin/users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'roles' => ['user']
    ]);
    
    $response->assertRedirect();
    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com'
    ]);
}
```

## 🔒 Permission & Policy Testing

### Permission Tests

```php
public function test_user_permissions()
{
    $user = $this->createUserWithPermissions([
        'posts.create',
        'posts.read'
    ]);
    
    // Direct permission testing
    $this->assertTrue($user->can('posts.create'));
    $this->assertFalse($user->can('posts.delete'));
    
    // Laravel authorization testing
    $this->assertTrue($user->can('create', Post::class));
    $this->assertFalse($user->can('delete', Post::factory()->create()));
}
```

### Policy Testing

```php
public function test_post_policy()
{
    $author = $this->createUserWithPermissions(['posts.update']);
    $otherUser = $this->createUserWithPermissions(['posts.update']);
    $admin = $this->createAdminUser();
    
    $post = Post::factory()->create(['user_id' => $author->id]);
    
    // Szerző szerkesztheti saját posztját
    $this->assertTrue($author->can('update', $post));
    
    // Más felhasználó nem szerkesztheti
    $this->assertFalse($otherUser->can('update', $post));
    
    // Admin mindig szerkesztheti
    $this->assertTrue($admin->can('update', $post));
}
```

## ⚡ Performance Testing

### Response Time Tests

```php
public function test_page_load_performance()
{
    $admin = $this->createAdminUser();
    
    $startTime = microtime(true);
    
    $response = $this->actingAs($admin)->get('/admin/users');
    
    $endTime = microtime(true);
    $loadTime = ($endTime - $startTime) * 1000; // milliseconds
    
    $response->assertOk();
    $this->assertLessThan(500, $loadTime, 'Page load too slow');
}
```

### Database Query Tests

```php
public function test_n_plus_one_prevention()
{
    User::factory()->count(10)->create();
    $admin = $this->createAdminUser();
    
    // Query count tracking
    $queryCount = 0;
    DB::listen(function ($query) use (&$queryCount) {
        $queryCount++;
    });
    
    $this->actingAs($admin)->get('/admin/users');
    
    // Biztosítjuk, hogy nincs N+1 probléma
    $this->assertLessThan(5, $queryCount);
}
```

## 🧹 Test Helpers

### Custom Assertions

```php
// tests/Traits/BoilerplateTestHelpers.php

protected function assertUserCanPerformAction(User $user, string $action, $resource = null): void
{
    if ($resource) {
        $this->assertTrue($user->can($action, $resource));
    } else {
        $this->assertTrue($user->can($action));
    }
}

protected function assertUserCannotPerform(User $user, string $permission): void
{
    $this->assertFalse($user->can($permission));
}

protected function assertRoleHasPermissions(string $roleName, array $permissions): void
{
    $role = Role::findByName($roleName);
    
    foreach ($permissions as $permission) {
        $this->assertTrue($role->hasPermissionTo($permission));
    }
}
```

### Data Helpers

```php
protected function createPostWithAuthor(?User $author = null): Post
{
    return Post::factory()->create([
        'user_id' => $author?->id ?? $this->createUserWithPermissions(['posts.create'])->id
    ]);
}

protected function createCompleteUserHierarchy(): array
{
    return [
        'admin' => $this->createAdminUser(),
        'manager' => $this->createUserWithRole('manager'),
        'editor' => $this->createUserWithRole('editor'),
        'user' => $this->createUserWithoutPermissions(),
    ];
}
```

## 🏃‍♂️ Running Tests

### Basic Commands

```bash
# Összes teszt futtatása
php artisan test

# Sail környezetben
./vendor/bin/sail test

# Specifikus teszt fájl
php artisan test tests/Feature/UserTest.php

# Specifikus teszt metódus
php artisan test --filter test_admin_can_create_users

# Coverage jelentés
php artisan test --coverage
```

### Parallel Testing

```bash
# Parallel teszt futtatás
php artisan test --parallel

# Specifikus process count
php artisan test --parallel --processes=4
```

### Quality Checks

```bash
# Teljes quality check (tests + lint + stan)
./scripts/quality-check.sh

# Csak tesztek
./scripts/quality-check.sh --tests-only

# Tesztek kihagyása
./scripts/quality-check.sh --skip-tests
```

## 📊 Test Coverage

### Coverage Reports

```bash
# HTML coverage report
php artisan test --coverage-html=coverage-report

# Text coverage
php artisan test --coverage-text

# Minimum coverage threshold
php artisan test --min=80
```

### Coverage Configuration

```xml
<!-- phpunit.xml -->
<coverage>
    <include>
        <directory suffix=".php">./app</directory>
    </include>
    <exclude>
        <directory>./app/Console/Commands</directory>
        <file>./app/Http/Middleware/Authenticate.php</file>
    </exclude>
</coverage>
```

## 💡 Best Practices

### Test Organization

1. **Feature Tests** - End-to-end user workflows
2. **Unit Tests** - Isolated component testing  
3. **Integration Tests** - Component interaction testing
4. **Database Tests** - Data integrity and relationships

### Test Naming

```php
// ✅ Jó
public function test_admin_can_create_user_with_valid_data()
public function test_user_cannot_access_admin_panel_without_permission()
public function test_password_reset_email_is_sent_to_valid_user()

// ❌ Rossz
public function testUser()
public function testCreate()
public function testValidation()
```

### Test Data

1. **Factory-k használata** fix értékek helyett
2. **Minimal data** csak a teszthez szükséges adatok
3. **Cleanup** automatikus (RefreshDatabase)
4. **Realistic data** production-like scenarios

### Performance

1. **In-memory database** (SQLite :memory:)
2. **Parallel execution** ahol lehetséges
3. **Selective testing** feature branch-eken
4. **Mocking** external services

## 📚 További Olvasnivaló

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Filament Testing](https://filamentphp.com/docs/panels/testing)
- [Spatie Permission Testing](https://spatie.be/docs/laravel-permission/testing)


