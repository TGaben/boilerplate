<?php

declare(strict_types=1);

use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

// Language switching routes
Route::post('/language/switch/{language}', [LanguageController::class, 'switch'])->name('language.switch');
