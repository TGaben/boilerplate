<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Boilerplate Features
    |--------------------------------------------------------------------------
    |
    | This section controls which boilerplate features are enabled.
    | You can easily toggle features on/off based on your project needs.
    |
    */
    'features' => [
        'demo_ui_components' => env('BOILERPLATE_DEMO_UI', true),
        'activity_logging' => env('BOILERPLATE_ACTIVITY_LOG', true),
        'strict_permissions' => env('BOILERPLATE_STRICT_PERMISSIONS', true),
        'multi_language' => env('BOILERPLATE_MULTI_LANGUAGE', true),
        'theme_switching' => env('BOILERPLATE_THEME_SWITCHING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Configuration
    |--------------------------------------------------------------------------
    |
    | These settings control the behavior and appearance of the Filament
    | admin panel. Most settings can be overridden via environment variables.
    |
    */
    'admin' => [
        'default_locale' => env('ADMIN_LOCALE', 'hu'),
        'items_per_page' => (int) env('ADMIN_ITEMS_PER_PAGE', 25),
        'enable_user_avatars' => env('ADMIN_ENABLE_AVATARS', true),
        'enable_global_search' => env('ADMIN_ENABLE_GLOBAL_SEARCH', true),
        'enable_database_notifications' => env('ADMIN_ENABLE_DB_NOTIFICATIONS', true),
        'sidebar_collapsed_by_default' => env('ADMIN_SIDEBAR_COLLAPSED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Theme Configuration
    |--------------------------------------------------------------------------
    |
    | These options control the default theme settings for the application.
    | Users can override these settings via localStorage or session.
    |
    */
    'theme' => [
        'default_mode' => env('UI_DEFAULT_THEME', 'light'), // 'light', 'dark', 'system'
        'enable_theme_toggle' => env('UI_ENABLE_THEME_TOGGLE', true),
        'preserve_theme_choice' => env('UI_PRESERVE_THEME_CHOICE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Brand Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your application's brand elements that will be used
    | throughout the UI components and admin panel.
    |
    */
    'brand' => [
        'name' => env('APP_NAME', 'Laravel Boilerplate'),
        'logo_light' => env('APP_LOGO_LIGHT', '/images/logo.svg'),
        'logo_dark' => env('APP_LOGO_DARK', '/images/logo-dark.svg'),
        'favicon' => env('APP_FAVICON', '/favicon.ico'),
        'primary_color' => env('BRAND_PRIMARY_COLOR', '#3b82f6'), // blue-500
        'secondary_color' => env('BRAND_SECONDARY_COLOR', '#64748b'), // slate-500
    ],

    /*
    |--------------------------------------------------------------------------
    | Demo UI Kit Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the UI kit demonstration components.
    | This controls what components are showcased on the demo page.
    |
    */
    'demo' => [
        'components' => [
            'buttons' => env('DEMO_SHOW_BUTTONS', true),
            'cards' => env('DEMO_SHOW_CARDS', true),
            'forms' => env('DEMO_SHOW_FORMS', true),
            'alerts' => env('DEMO_SHOW_ALERTS', true),
            'modals' => env('DEMO_SHOW_MODALS', true),
            'navigation' => env('DEMO_SHOW_NAVIGATION', true),
            'typography' => env('DEMO_SHOW_TYPOGRAPHY', true),
            'layouts' => env('DEMO_SHOW_LAYOUTS', true),
            'tables' => env('DEMO_SHOW_TABLES', true),
            'badges' => env('DEMO_SHOW_BADGES', true),
            'avatars' => env('DEMO_SHOW_AVATARS', true),
            'progress' => env('DEMO_SHOW_PROGRESS', true),
            'tabs' => env('DEMO_SHOW_TABS', true),
            'dropdowns' => env('DEMO_SHOW_DROPDOWNS', true),
            'pagination' => env('DEMO_SHOW_PAGINATION', true),
        ],
        'show_code_examples' => env('DEMO_SHOW_CODE', true),
        'enable_copy_to_clipboard' => env('DEMO_ENABLE_CLIPBOARD', true),
        'syntax_highlighting' => env('DEMO_SYNTAX_HIGHLIGHTING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | These settings help optimize the application performance.
    |
    */
    'performance' => [
        'lazy_load_components' => env('PERFORMANCE_LAZY_LOAD', true),
        'preload_critical_css' => env('PERFORMANCE_PRELOAD_CSS', true),
        'optimize_images' => env('PERFORMANCE_OPTIMIZE_IMAGES', true),
        'enable_response_caching' => env('PERFORMANCE_RESPONSE_CACHE', false),
        'cache_duration_minutes' => (int) env('PERFORMANCE_CACHE_DURATION', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Security-related configuration options for the boilerplate.
    |
    */
    'security' => [
        'enable_password_history' => env('SECURITY_PASSWORD_HISTORY', false),
        'password_history_count' => (int) env('SECURITY_PASSWORD_HISTORY_COUNT', 5),
        'force_password_change_days' => (int) env('SECURITY_FORCE_PASSWORD_CHANGE', 0),
        'enable_login_attempts_logging' => env('SECURITY_LOG_LOGIN_ATTEMPTS', true),
        'max_login_attempts' => (int) env('SECURITY_MAX_LOGIN_ATTEMPTS', 5),
        'login_attempt_lockout_minutes' => (int) env('SECURITY_LOCKOUT_MINUTES', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Settings
    |--------------------------------------------------------------------------
    |
    | Settings that are useful during development but should be disabled
    | in production environments.
    |
    */
    'development' => [
        'show_debug_info' => env('BOILERPLATE_DEBUG_INFO', false),
        'enable_query_logging' => env('BOILERPLATE_QUERY_LOG', false),
        'show_route_info' => env('BOILERPLATE_ROUTE_INFO', false),
        'enable_profiling' => env('BOILERPLATE_PROFILING', false),
    ],
];
