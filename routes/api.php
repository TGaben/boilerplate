<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API Health Check - no rate limiting needed
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is healthy',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
    ]);
})->name('api.health');

// Public API endpoints with general rate limiting
Route::middleware(['throttle:api'])->group(function () {

    // Documentation Search API (already has specific rate limiting in web routes)
    Route::get('/docs/search', [App\Http\Controllers\DocsController::class, 'search'])
        ->name('api.docs.search');

    // System information endpoint
    Route::get('/system/info', function () {
        return response()->json([
            'success' => true,
            'data' => [
                'app_name' => config('app.name'),
                'version' => '1.0.0',
                'environment' => app()->environment(),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
            ],
        ]);
    })->name('api.system.info');
});

// Authentication required endpoints with admin rate limiting
Route::middleware(['auth:sanctum', 'throttle:admin'])->group(function () {

    // User profile endpoint
    Route::get('/user', function () {
        return response()->json([
            'success' => true,
            'data' => auth()->user(),
        ]);
    })->name('api.user.profile');

    // Admin endpoints for testing rate limiting
    Route::prefix('admin')->name('api.admin.')->group(function () {

        Route::get('/users', function () {
            return response()->json([
                'success' => true,
                'message' => 'This endpoint is protected by admin rate limiting (120 requests/min)',
                'current_user' => auth()->user()?->only(['id', 'name', 'email']),
            ]);
        })->name('users')->middleware('permission:view users');

        Route::post('/cache/clear', function () {
            return response()->json([
                'success' => true,
                'message' => 'This would clear cache - admin rate limited',
                'timestamp' => now()->toISOString(),
            ]);
        })->name('cache.clear')->middleware('role:admin');
    });
});

// Rate limiting demonstration endpoints
Route::prefix('test')->name('api.test.')->group(function () {

    // Test general API rate limiting (60 requests/min)
    Route::get('/rate-limit', function () {
        return response()->json([
            'success' => true,
            'message' => 'This endpoint is rate limited to 60 requests per minute per user/IP',
            'timestamp' => now()->toISOString(),
            'ip' => request()->ip(),
            'user_id' => auth()->id(),
        ]);
    })->middleware('throttle:api')->name('rate-limit');

    // Test strict rate limiting (custom limiter)
    Route::get('/strict-rate-limit', function () {
        return response()->json([
            'success' => true,
            'message' => 'This endpoint has strict rate limiting - 10 requests per minute',
            'timestamp' => now()->toISOString(),
        ]);
    })->middleware('throttle:10,1')->name('strict-rate-limit');

    // Test registration rate limiting simulation
    Route::post('/registration-test', function () {
        return response()->json([
            'success' => true,
            'message' => 'This simulates registration rate limiting (3 per hour per IP)',
            'ip' => request()->ip(),
        ]);
    })->middleware('throttle:registration')->name('registration-test');

    // Test auth rate limiting simulation
    Route::post('/auth-test', function () {
        return response()->json([
            'success' => true,
            'message' => 'This simulates auth rate limiting (5 per minute per email+IP)',
            'email' => request()->input('email'),
            'ip' => request()->ip(),
        ]);
    })->middleware('throttle:auth')->name('auth-test');
});
