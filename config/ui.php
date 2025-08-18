<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | UI Demo Components
    |--------------------------------------------------------------------------
    |
    | This option controls whether the demo UI components are shown on the
    | welcome page. When set to false, a clean welcome page will be displayed.
    | When set to true, a comprehensive UI kit showcase will be shown.
    |
    */
    'show_demo_components' => env('UI_SHOW_DEMO', true),

    /*
    |--------------------------------------------------------------------------
    | Theme Configuration
    |--------------------------------------------------------------------------
    |
    | These options control the default theme settings for the application.
    | Users can override these settings via localStorage or session.
    |
    */
    'theme' => [
        'default_mode' => env('UI_DEFAULT_THEME', 'light'), // 'light', 'dark', 'system'
        'enable_theme_toggle' => env('UI_ENABLE_THEME_TOGGLE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Brand Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your application's brand elements that will be used
    | throughout the UI components.
    |
    */
    'brand' => [
        'name' => env('APP_NAME', 'Laravel Boilerplate'),
        'logo_light' => '/images/logo.svg',
        'logo_dark' => '/images/logo-dark.svg',
        'primary_color' => '#3b82f6', // blue-500
        'secondary_color' => '#64748b', // slate-500
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Kit Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the UI kit demonstration components.
    | This controls what components are showcased on the demo page.
    |
    */
    'demo' => [
        'components' => [
            'buttons' => true,
            'cards' => true,
            'forms' => true,
            'alerts' => true,
            'modals' => true,
            'navigation' => true,
            'typography' => true,
            'layouts' => true,
            'tables' => true,
            'badges' => true,
            'avatars' => true,
            'progress' => true,
            'tabs' => true,
            'dropdowns' => true,
            'pagination' => true,
        ],
        'show_code_examples' => env('UI_SHOW_CODE', true),
        'enable_copy_to_clipboard' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | These settings help optimize the UI performance.
    |
    */
    'performance' => [
        'lazy_load_components' => true,
        'preload_critical_css' => true,
        'optimize_images' => true,
    ],
];
