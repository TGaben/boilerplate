# Recipe: API Development

## 🎯 Mikor Használd?

- **RESTful API** építésekor
- **Mobile app backend** készítésekor
- **SPA alkalmazás** API végpontjaihoz
- **Microservice** architektúra implementálásakor
- **Third-party integráció** biztosításakor

## ⏱️ Implementációs Idő: 2-3 óra

## 📋 Előfeltételek

- ✅ Core boilerplate telepítve
- ✅ Alapvető Laravel tudás
- ✅ REST API alapok ismerete
- ⚠️ Authentication rendszer ismerete ajánlott

## 🚀 Quick Start

```bash
# 1. Laravel Sanctum telepítése
composer require laravel/sanctum

# 2. Sanctum konfigurálása
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate

# 3. API routes és middleware beállítása
# (lásd részletes lépések)

# 4. API Resource osztályok létrehozása
php artisan make:resource UserResource
php artisan make:resource UserCollection
```

## 📖 Részletes Implementáció

### 1. Laravel Sanctum Beállítása

#### Sanctum Konfiguráció

```php
// config/sanctum.php
return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        Sanctum::currentApplicationUrlWithPort()
    ))),

    'guard' => ['web'],

    'expiration' => null, // Soha nem járnak le

    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],
];
```

#### Kernel Middleware

```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'api' => [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];
```

### 2. API Authentication

#### AuthController Létrehozása

```php
// app/Http/Controllers/Api/AuthController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function user(Request $request)
    {
        return new UserResource($request->user());
    }
}
```

#### Login Request Validation

```php
// app/Http/Requests/Api/LoginRequest.php
<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ];
    }
}
```

### 3. API Resources

#### User Resource

```php
// app/Http/Resources/UserResource.php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'roles' => $this->roles->pluck('name'),
            'permissions' => $this->getAllPermissions()->pluck('name'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

#### User Collection

```php
// app/Http/Resources/UserCollection.php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->count(),
                'current_page' => $request->get('page', 1),
                'per_page' => $request->get('per_page', 15),
            ],
        ];
    }
}
```

### 4. API Controllers

#### Base API Controller

```php
// app/Http/Controllers/Api/ApiController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    protected function successResponse($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function errorResponse(string $message = 'Error', int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
```

#### User API Controller

```php
// app/Http/Controllers/Api/UserController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreUserRequest;
use App\Http\Requests\Api\UpdateUserRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    public function index(Request $request)
    {
        $this->authorize('users.read');

        $users = User::with('roles')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate($request->get('per_page', 15));

        return new UserCollection($users);
    }

    public function show(User $user)
    {
        $this->authorize('users.read');

        return new UserResource($user->load('roles', 'permissions'));
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('users.create');

        $user = User::create($request->validated());

        if ($request->roles) {
            $user->assignRole($request->roles);
        }

        return $this->successResponse(
            new UserResource($user->load('roles')),
            'User created successfully',
            201
        );
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('users.update');

        $user->update($request->validated());

        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return $this->successResponse(
            new UserResource($user->load('roles')),
            'User updated successfully'
        );
    }

    public function destroy(User $user)
    {
        $this->authorize('users.delete');

        if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
            return $this->errorResponse(
                'Cannot delete the last admin user',
                403
            );
        }

        $user->delete();

        return $this->successResponse(null, 'User deleted successfully');
    }
}
```

### 5. API Routes

```php
// routes/api.php
<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Resource routes
    Route::apiResource('users', UserController::class);
    
    // Additional API routes here...
});
```

### 6. API Permission Integration

#### Permission-based Middleware

```php
// app/Http/Middleware/ApiPermission.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! $request->user() || ! $request->user()->can($permission)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions',
                'required_permission' => $permission,
            ], 403);
        }

        return $next($request);
    }
}
```

#### Route Permission Protection

```php
// routes/api.php
Route::middleware(['auth:sanctum', 'api.permission:users.read'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'api.permission:users.create'])->group(function () {
    Route::post('/users', [UserController::class, 'store']);
});
```

## 🧪 API Testing

### Feature Tests

```php
// tests/Feature/Api/AuthTest.php
<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email'],
                'token'
            ]);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertOk()
            ->assertJson(['message' => 'Logged out successfully']);
    }
}
```

### API Endpoint Tests

```php
// tests/Feature/Api/UserApiTest.php
public function test_user_with_permission_can_list_users()
{
    $user = $this->createUserWithPermissions(['users.read']);
    User::factory()->count(3)->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/users');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'email', 'roles']
            ],
            'meta' => ['total', 'current_page', 'per_page']
        ]);
}

public function test_user_without_permission_cannot_access_users()
{
    $user = $this->createUserWithoutPermissions();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/users');

    $response->assertForbidden();
}
```

## 📄 API Documentation

### OpenAPI/Swagger Integration

```bash
# Swagger dokumentáció generáláshoz
composer require darkaonline/l5-swagger

# Config publikálás
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
```

### Controller Annotations

```php
/**
 * @OA\Get(
 *     path="/api/users",
 *     summary="Get list of users",
 *     tags={"Users"},
 *     security={{ "sanctum": {} }},
 *     @OA\Parameter(
 *         name="search",
 *         in="query",
 *         description="Search term",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
 *             @OA\Property(property="meta", type="object")
 *         )
 *     ),
 *     @OA\Response(response=403, description="Forbidden")
 * )
 */
public function index(Request $request)
{
    // ...
}
```

## ⚡ Performance Optimalizáció

### API Response Caching

```php
// app/Http/Controllers/Api/UserController.php
public function index(Request $request)
{
    $cacheKey = 'users_list_' . md5($request->getQueryString());
    
    $users = Cache::remember($cacheKey, 300, function () use ($request) {
        return User::with('roles')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->paginate($request->get('per_page', 15));
    });

    return new UserCollection($users);
}
```

### Rate Limiting

```php
// app/Providers/RouteServiceProvider.php
protected function configureRateLimiting()
{
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
    });

    RateLimiter::for('api-strict', function (Request $request) {
        return Limit::perMinute(10)->by(optional($request->user())->id ?: $request->ip());
    });
}
```

## 🔒 Biztonsági Szempontok

### Token Biztonság

```php
// Token lejárati idő beállítása
$token = $user->createToken('api-token', ['*'], now()->addDays(30));

// Scoped tokens
$token = $user->createToken('limited-access', ['users:read']);
```

### API Validation

```php
// app/Http/Requests/Api/StoreUserRequest.php
public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'roles' => 'array|exists:roles,name',
    ];
}

protected function failedValidation(Validator $validator)
{
    throw new HttpResponseException(
        response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ], 422)
    );
}
```

## 🎯 Következő Lépések

1. **API Versioning** implementálása (`/api/v1/`, `/api/v2/`)
2. **GraphQL endpoint** hozzáadása
3. **Webhook system** fejlesztése
4. **API Analytics** és monitoring
5. **Third-party integrations** (Stripe, PayPal, stb.)

## 📚 További Olvasnivaló

- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
- [API Resources](https://laravel.com/docs/eloquent-resources)
- [API Rate Limiting](https://laravel.com/docs/rate-limiting)
- [OpenAPI/Swagger](https://swagger.io/specification/)

## 🆘 Troubleshooting

### Gyakori Problémák

**Problem**: Token authentication nem működik
```bash
# Ellenőrizd hogy a Sanctum middleware be van-e állítva
# Kernel.php middleware groups 'api' részében
```

**Problem**: CORS hibák
```bash
# Laravel CORS package telepítése
composer require fruitcake/laravel-cors
```

**Problem**: Permission denied errors
```bash
# Ellenőrizd hogy a felhasználónak vannak-e megfelelő jogosultságai
# És hogy a middleware megfelelően van-e beállítva
```


