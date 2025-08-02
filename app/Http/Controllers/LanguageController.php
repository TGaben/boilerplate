<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Switch the application language.
     */
    public function switch(Request $request, string $language): RedirectResponse
    {
        // Get available languages from config
        $availableLanguages = array_keys(config('languages.available', []));

        // Validate the language parameter
        if (! in_array($language, $availableLanguages, true)) {
            return redirect()->back()->with('error', 'Language not supported.');
        }

        // Store the language in session
        Session::put(config('languages.session_key', 'locale'), $language);

        // Set the application locale for the current request
        App::setLocale($language);

        // Redirect back to the previous page
        return redirect()->back()->with('success', 'Language changed successfully.');
    }

    /**
     * Get the current language.
     */
    public function current(): string
    {
        return Session::get(
            config('languages.session_key', 'locale'),
            config('languages.default', config('app.locale', 'en')),
        );
    }

    /**
     * Get all available languages.
     */
    public function available(): array
    {
        return config('languages.available', []);
    }
}
