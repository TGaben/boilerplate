<?php

declare(strict_types=1);

use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

// Language switching routes - use admin middleware to match Filament
Route::post('/language/switch/{language}', [LanguageController::class, 'switch'])
    ->name('language.switch')
    ->middleware(['web', \App\Http\Middleware\SetLocale::class]);
