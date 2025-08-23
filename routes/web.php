<?php

declare(strict_types=1);

use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

// Language switching routes - use admin middleware to match Filament
Route::post('/language/switch/{language}', [LanguageController::class, 'switch'])
    ->name('language.switch')
    ->middleware(['web', \App\Http\Middleware\SetLocale::class]);

// Documentation Routes (Development only)
Route::prefix('docs')->name('docs.')->middleware('docs.dev')->group(function () {
    Route::get('/', [App\Http\Controllers\DocsController::class, 'index'])->name('index');
    Route::get('/search', [App\Http\Controllers\DocsController::class, 'search'])->name('search');
    Route::get('/{category}', [App\Http\Controllers\DocsController::class, 'category'])->name('category');
    Route::get('/{category}/{slug}', [App\Http\Controllers\DocsController::class, 'show'])->name('show');
});
