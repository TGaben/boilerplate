<?php

declare(strict_types=1);

use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

// Language switching routes with rate limiting
Route::post('/language/switch/{language}', [LanguageController::class, 'switch'])
    ->name('language.switch')
    ->middleware(['web', \App\Http\Middleware\SetLocale::class, 'throttle:60,1']);

// Documentation Routes (Development only) with rate limiting
Route::prefix('docs')->name('docs.')->middleware(['docs.dev'])->group(function () {
    // General docs browsing - higher limits
    Route::get('/', [App\Http\Controllers\DocsController::class, 'index'])
        ->name('index')
        ->middleware('throttle:120,1');

    // Search endpoint - specific rate limiting for search abuse prevention
    Route::get('/search', [App\Http\Controllers\DocsController::class, 'search'])
        ->name('search')
        ->middleware('throttle:docs-search');

    // Category and document viewing - moderate limits
    Route::get('/{category}', [App\Http\Controllers\DocsController::class, 'category'])
        ->name('category')
        ->middleware('throttle:100,1');

    Route::get('/{category}/{slug}', [App\Http\Controllers\DocsController::class, 'show'])
        ->name('show')
        ->middleware('throttle:100,1');
});
