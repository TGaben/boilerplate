<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Recipe System Configuration
    |--------------------------------------------------------------------------
    |
    | This file manages the configuration for the Laravel Boilerplate Recipe
    | system. Recipes are optional features and extensions that can be applied
    | to extend the core boilerplate functionality.
    |
    */

    'path' => base_path('docs/recipes'),

    'installed_recipes_file' => base_path('.boilerplate/installed-recipes.json'),

    'templates_path' => base_path('stubs/recipes'),

    /*
    |--------------------------------------------------------------------------
    | Available Recipes
    |--------------------------------------------------------------------------
    |
    | This defines the catalog of available recipes with their metadata,
    | difficulty level, estimated implementation time, and dependencies.
    |
    */

    'catalog' => [
        'api-development' => [
            'name' => 'API Development',
            'description' => 'RESTful API endpoints Laravel Sanctum autentikációval',
            'difficulty' => 'easy', // easy, medium, advanced
            'estimated_time' => '2-3 óra',
            'category' => 'backend',
            'tags' => ['api', 'rest', 'sanctum', 'authentication'],
            'dependencies' => [],
            'packages' => ['laravel/sanctum'],
            'documentation' => 'api-development.md',
            'version' => '1.0.0',
            'author' => 'Laravel Boilerplate Team',
        ],

        'file-uploads' => [
            'name' => 'File Upload System',
            'description' => 'Komplett fájlfeltöltés és -kezelés képoptimalizálással',
            'difficulty' => 'medium',
            'estimated_time' => '3-4 óra',
            'category' => 'storage',
            'tags' => ['files', 'uploads', 'images', 'storage', 'optimization'],
            'dependencies' => [],
            'packages' => ['intervention/image', 'spatie/laravel-medialibrary'],
            'documentation' => 'file-uploads.md',
            'version' => '1.0.0',
            'author' => 'Laravel Boilerplate Team',
        ],

        'multi-tenancy' => [
            'name' => 'Multi-Tenancy Support',
            'description' => 'SaaS alkalmazásokhoz komplett multi-tenant architektúra',
            'difficulty' => 'advanced',
            'estimated_time' => '1-2 nap',
            'category' => 'architecture',
            'tags' => ['saas', 'tenancy', 'isolation', 'multi-tenant'],
            'dependencies' => [],
            'packages' => ['stancl/tenancy'],
            'documentation' => 'multi-tenancy.md',
            'version' => '1.0.0',
            'author' => 'Laravel Boilerplate Team',
        ],

        'email-templates' => [
            'name' => 'Email Templates & Notifications',
            'description' => 'Testreszabható email sablonok és értesítési rendszer',
            'difficulty' => 'medium',
            'estimated_time' => '2-3 óra',
            'category' => 'communication',
            'tags' => ['email', 'notifications', 'mailable', 'templates'],
            'dependencies' => [],
            'packages' => ['spatie/laravel-mail-preview'],
            'documentation' => 'email-templates.md',
            'version' => '1.0.0',
            'author' => 'Laravel Boilerplate Team',
        ],

        'advanced-permissions' => [
            'name' => 'Advanced Permission System',
            'description' => 'Komplex jogosultságkezelés context-aware szabályokkal',
            'difficulty' => 'advanced',
            'estimated_time' => '4-6 óra',
            'category' => 'security',
            'tags' => ['permissions', 'policies', 'authorization', 'rbac'],
            'dependencies' => [],
            'packages' => [],
            'documentation' => 'advanced-permissions.md',
            'version' => '1.0.0',
            'author' => 'Laravel Boilerplate Team',
        ],

        'performance-optimization' => [
            'name' => 'Performance & Caching',
            'description' => 'Redis cache, query optimization és performance monitoring',
            'difficulty' => 'medium',
            'estimated_time' => '3-4 óra',
            'category' => 'performance',
            'tags' => ['cache', 'redis', 'optimization', 'monitoring'],
            'dependencies' => [],
            'packages' => ['predis/predis', 'spatie/laravel-query-builder'],
            'documentation' => 'performance-optimization.md',
            'version' => '1.0.0',
            'author' => 'Laravel Boilerplate Team',
        ],

        'reporting-dashboard' => [
            'name' => 'Reporting & Analytics',
            'description' => 'Beépített riporting dashboard Chart.js-szel',
            'difficulty' => 'medium',
            'estimated_time' => '4-5 óra',
            'category' => 'analytics',
            'tags' => ['reporting', 'charts', 'analytics', 'dashboard'],
            'dependencies' => [],
            'packages' => ['spatie/laravel-analytics'],
            'documentation' => 'reporting-dashboard.md',
            'version' => '1.0.0',
            'author' => 'Laravel Boilerplate Team',
        ],

        'websocket-realtime' => [
            'name' => 'WebSocket & Real-time Features',
            'description' => 'Real-time értesítések és live updates Pusher/Laravel Echo-val',
            'difficulty' => 'advanced',
            'estimated_time' => '5-6 óra',
            'category' => 'realtime',
            'tags' => ['websocket', 'realtime', 'pusher', 'echo', 'broadcasting'],
            'dependencies' => [],
            'packages' => ['pusher/pusher-php-server', 'laravel/echo'],
            'documentation' => 'websocket-realtime.md',
            'version' => '1.0.0',
            'author' => 'Laravel Boilerplate Team',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Recipe Categories
    |--------------------------------------------------------------------------
    |
    | Organize recipes into logical categories for better discovery.
    |
    */

    'categories' => [
        'backend' => [
            'name' => 'Backend Features',
            'description' => 'Server-side funkciók és API-k',
            'icon' => '🔧',
        ],
        'frontend' => [
            'name' => 'Frontend Components',
            'description' => 'UI komponensek és frontend funkciók',
            'icon' => '🎨',
        ],
        'storage' => [
            'name' => 'Storage & Files',
            'description' => 'Fájlkezelés és tárolási megoldások',
            'icon' => '💾',
        ],
        'architecture' => [
            'name' => 'Architecture Patterns',
            'description' => 'Architektúrális minták és struktúrák',
            'icon' => '🏗️',
        ],
        'communication' => [
            'name' => 'Communication',
            'description' => 'Email, SMS és push értesítések',
            'icon' => '📧',
        ],
        'security' => [
            'name' => 'Security & Auth',
            'description' => 'Biztonsági funkciók és autentikáció',
            'icon' => '🔒',
        ],
        'performance' => [
            'name' => 'Performance',
            'description' => 'Teljesítmény optimalizáció és cache',
            'icon' => '⚡',
        ],
        'analytics' => [
            'name' => 'Analytics & Reporting',
            'description' => 'Adatelemzés és riporting eszközök',
            'icon' => '📊',
        ],
        'realtime' => [
            'name' => 'Real-time Features',
            'description' => 'WebSocket és real-time kommunikáció',
            'icon' => '⚡',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Recipe Installation Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for recipe installation behavior.
    |
    */

    'installation' => [
        'auto_install_dependencies' => env('RECIPE_AUTO_INSTALL_DEPS', true),
        'backup_modified_files' => env('RECIPE_BACKUP_FILES', true),
        'run_migrations_after_install' => env('RECIPE_AUTO_MIGRATE', true),
        'clear_cache_after_install' => env('RECIPE_CLEAR_CACHE', true),
    ],
];
