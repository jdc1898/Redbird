<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Redbird Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the Redbird SaaS package.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | The name of your SaaS application
    |
    */
    'app_name' => env('REDBIRD_APP_NAME', 'Redbird SaaS'),

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the Filament admin panel
    |
    */
    'panels' => [
        'admin' => [
            'path' => env('REDBIRD_ADMIN_PATH', 'admin'),
            'domain' => env('REDBIRD_ADMIN_DOMAIN', null),
            'guard' => ['admin'],
        ],
        'tenant' => [
            'path' => env('REDBIRD_TENANT_PATH', 'tenant'),
            'domain' => env('REDBIRD_TENANT_DOMAIN', null),
            'guard' => ['tenant'],
        ],
        'member' => [
            'path' => env('REDBIRD_MEMBER_PATH', 'member'),
            'domain' => env('REDBIRD_MEMBER_DOMAIN', null),
            'guard' => ['web'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for subscription management
    |
    */
    'subscriptions' => [
        'enabled' => env('REDBIRD_SUBSCRIPTIONS_ENABLED', true),
        'stripe_key' => env('STRIPE_KEY'),
        'stripe_secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-tenancy Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for multi-tenant features
    |
    */
    'tenancy' => [
        'enabled' => env('REDBIRD_TENANCY_ENABLED', false),
        'model' => env('REDBIRD_TENANT_MODEL', 'App\Models\Tenant'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific features
    |
    */
    'features' => [
        'user_registration' => env('REDBIRD_USER_REGISTRATION', true),
        'email_verification' => env('REDBIRD_EMAIL_VERIFICATION', true),
        'two_factor_auth' => env('REDBIRD_TWO_FACTOR_AUTH', false),
        'api_access' => env('REDBIRD_API_ACCESS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Permissions
    |--------------------------------------------------------------------------
    |
    | Default permissions to create during installation
    |
    */
    'permissions' => [
        'admin' => [
            'access admin panel',
            'manage users',
            'manage permissions',
            'manage settings',
        ],
        'user' => [
            'access dashboard',
            'manage profile',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Roles
    |--------------------------------------------------------------------------
    |
    | Default roles to create during installation
    |
    */
    'roles' => [
        'super-admin' => 'Super Administrator',
        'admin' => 'Administrator',
        'user' => 'User',
    ],
];
