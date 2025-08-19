<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Environment Variables Validation
    |--------------------------------------------------------------------------
    |
    | This configuration defines which environment variables are required
    | and optional for different environments (development, testing, production).
    |
    */

    'validation' => [
        /*
        |--------------------------------------------------------------------------
        | Required Variables (All Environments)
        |--------------------------------------------------------------------------
        */
        'required' => [
            'APP_NAME' => 'string',
            'APP_ENV' => 'string|in:local,development,testing,staging,production',
            'APP_KEY' => 'string|min:32',
            'APP_DEBUG' => 'boolean',
            'APP_URL' => 'url',
            'DB_CONNECTION' => 'string',
            'CACHE_STORE' => 'string',
            'SESSION_DRIVER' => 'string',
            'QUEUE_CONNECTION' => 'string',
        ],

        /*
        |--------------------------------------------------------------------------
        | Environment-Specific Required Variables
        |--------------------------------------------------------------------------
        */
        'required_by_env' => [
            'production' => [
                'DB_HOST' => 'string',
                'DB_PORT' => 'integer',
                'DB_DATABASE' => 'string',
                'DB_USERNAME' => 'string',
                'DB_PASSWORD' => 'string',
                'REDIS_HOST' => 'string',
                'REDIS_PASSWORD' => 'string',
                'MAIL_HOST' => 'string',
                'MAIL_PORT' => 'integer',
                'MAIL_USERNAME' => 'string',
                'MAIL_PASSWORD' => 'string',
                'SESSION_SECURE_COOKIES' => 'boolean',
                'SESSION_ENCRYPT' => 'boolean',
            ],
            'development' => [
                'DB_HOST' => 'string',
                'DB_DATABASE' => 'string',
                'DB_USERNAME' => 'string',
            ],
            'testing' => [
                'DB_DATABASE' => 'string',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Optional Variables with Defaults
        |--------------------------------------------------------------------------
        */
        'optional' => [
            // Boilerplate Features
            'BOILERPLATE_DEMO_UI' => 'boolean',
            'BOILERPLATE_ACTIVITY_LOG' => 'boolean',
            'BOILERPLATE_STRICT_PERMISSIONS' => 'boolean',
            'BOILERPLATE_MULTI_LANGUAGE' => 'boolean',
            'BOILERPLATE_THEME_SWITCHING' => 'boolean',

            // Admin Panel
            'ADMIN_LOCALE' => 'string',
            'ADMIN_ITEMS_PER_PAGE' => 'integer|min:5|max:100',
            'ADMIN_ENABLE_AVATARS' => 'boolean',
            'ADMIN_ENABLE_GLOBAL_SEARCH' => 'boolean',
            'ADMIN_ENABLE_DB_NOTIFICATIONS' => 'boolean',
            'ADMIN_SIDEBAR_COLLAPSED' => 'boolean',

            // UI Theme
            'UI_DEFAULT_THEME' => 'string|in:light,dark,system',
            'UI_ENABLE_THEME_TOGGLE' => 'boolean',
            'UI_PRESERVE_THEME_CHOICE' => 'boolean',

            // Performance
            'PERFORMANCE_LAZY_LOAD' => 'boolean',
            'PERFORMANCE_PRELOAD_CSS' => 'boolean',
            'PERFORMANCE_OPTIMIZE_IMAGES' => 'boolean',
            'PERFORMANCE_RESPONSE_CACHE' => 'boolean',
            'PERFORMANCE_CACHE_DURATION' => 'integer|min:0',

            // Security
            'SECURITY_PASSWORD_HISTORY' => 'boolean',
            'SECURITY_PASSWORD_HISTORY_COUNT' => 'integer|min:0|max:20',
            'SECURITY_FORCE_PASSWORD_CHANGE' => 'integer|min:0',
            'SECURITY_LOG_LOGIN_ATTEMPTS' => 'boolean',
            'SECURITY_MAX_LOGIN_ATTEMPTS' => 'integer|min:1|max:50',
            'SECURITY_LOCKOUT_MINUTES' => 'integer|min:0|max:1440',

            // Development
            'BOILERPLATE_DEBUG_INFO' => 'boolean',
            'BOILERPLATE_QUERY_LOG' => 'boolean',
            'BOILERPLATE_ROUTE_INFO' => 'boolean',
            'BOILERPLATE_PROFILING' => 'boolean',
        ],

        /*
        |--------------------------------------------------------------------------
        | Security Variables (Must Not Be Default)
        |--------------------------------------------------------------------------
        */
        'security_sensitive' => [
            'APP_KEY' => [
                'forbidden_values' => ['', 'base64:TESTING_KEY_REPLACE_THIS_VALUE'],
                'production_only' => false,
            ],
            'DB_PASSWORD' => [
                'forbidden_values' => ['', 'password', '123456', 'secret'],
                'production_only' => true,
            ],
            'REDIS_PASSWORD' => [
                'forbidden_values' => ['', 'password', '123456'],
                'production_only' => true,
            ],
            'MAIL_PASSWORD' => [
                'forbidden_values' => ['', 'password', '123456'],
                'production_only' => true,
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Warning Variables (Should Be Changed)
        |--------------------------------------------------------------------------
        */
        'should_customize' => [
            'APP_NAME' => [
                'default_values' => ['Laravel', 'Laravel Boilerplate', 'Laravel Boilerplate Dev'],
                'warning' => 'Consider customizing the application name for your project',
            ],
            'MAIL_FROM_ADDRESS' => [
                'default_values' => ['hello@example.com', 'noreply@example.com', 'dev@boilerplate.local'],
                'warning' => 'Update the mail from address to your domain',
            ],
            'APP_URL' => [
                'default_values' => ['http://localhost', 'https://your-domain.com'],
                'warning' => 'Set the correct application URL for your environment',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Template Configurations
    |--------------------------------------------------------------------------
    */
    'templates' => [
        'path' => base_path('templates/environments'),
        'available' => [
            'development' => [
                'name' => 'Development',
                'description' => 'Local development with debugging enabled',
                'file' => 'env.development',
            ],
            'testing' => [
                'name' => 'Testing',
                'description' => 'Optimized for running tests with SQLite',
                'file' => 'env.testing',
            ],
            'production' => [
                'name' => 'Production',
                'description' => 'Security and performance optimized for production',
                'file' => 'env.production',
            ],
            'ci' => [
                'name' => 'CI/CD',
                'description' => 'Optimized for continuous integration pipelines',
                'file' => 'env.ci',
            ],
        ],
    ],
];
