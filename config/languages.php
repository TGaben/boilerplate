<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Available Languages
    |--------------------------------------------------------------------------
    |
    | List of available languages for the application.
    | Each language should have a 'code', 'name', 'native_name' and 'flag' keys.
    |
    */
    'available' => [
        'hu' => [
            'code' => 'hu',
            'name' => 'Hungarian',
            'native_name' => 'Magyar',
            'flag' => '🇭🇺',
            'direction' => 'ltr',
        ],
        'en' => [
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'flag' => '🇺🇸',
            'direction' => 'ltr',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    |
    | The default language code that will be used when no language
    | is specified or when the requested language is not available.
    |
    */
    'default' => 'hu',

    /*
    |--------------------------------------------------------------------------
    | Fallback Language
    |--------------------------------------------------------------------------
    |
    | The fallback language that will be used when a translation
    | is missing in the current language.
    |
    */
    'fallback' => 'hu',

    /*
    |--------------------------------------------------------------------------
    | Session Key
    |--------------------------------------------------------------------------
    |
    | The session key that will be used to store the current language.
    |
    */
    'session_key' => 'locale',

    /*
    |--------------------------------------------------------------------------
    | Cookie Settings
    |--------------------------------------------------------------------------
    |
    | Settings for the language cookie that can be used as an alternative
    | to session-based language storage.
    |
    */
    'cookie' => [
        'name' => 'app_locale',
        'lifetime' => 365 * 24 * 60, // 1 year in minutes
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'httpOnly' => true,
        'sameSite' => 'lax',
    ],
];
