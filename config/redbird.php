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
            'guard' => 'admin',
            'colors' => [
                'primary' => '#6366f1',
            ],
        ],
        'tenant' => [
            'path' => env('REDBIRD_TENANT_PATH', 'tenant'),
            'guard' => 'tenant',
            'colors' => [
                'primary' => '#d946ef',
            ]
        ],
        'member' => [
            'path' => env('REDBIRD_MEMBER_PATH', 'member'),
            'guard' => 'web',
            'colors' => [
                'primary' => '#10b981',
            ]
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
        'admin' => 'Administrator',
        'tenant' => 'Tenant Administrator',
        'member' => 'Member',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Roles
    |--------------------------------------------------------------------------
    |
    | Default roles to create during installation
    |
    */
    'seed' => [
        'member' => [
            'name' => 'member',
            'display_name' => 'Member',
            'description' => 'A member of the application with basic access rights.',
            'role_guard' => 'member',
            'guards' => [
                [
                    'name' => 'member',
                    'permissions' => [
                        [
                            'name' => 'access-member-panel',
                            'display_name' => 'Access Member Panel',
                            'description' => 'Allows the user to access the member panel.',
                        ],
                    ],
                ],
            ],
        ],
        'tenant' => [
            'name' => 'tenant',
            'display_name' => 'Tenant Admin',
            'description' => 'An administrator with access to manage users, roles, and application settings.',
            'role_guard' => 'tenant',
            'guards' => [
                [
                    'name' => 'tenant',
                    'permissions' => [
                        [
                            'name' => 'tenant-panel',
                            'display_name' => 'Access Tenant Admin Panel',
                            'description' => 'Allows the user to access the tenant admin panel.',
                        ],
                    ],
                ],
            ],
        ],
        'admin' => [
            'name' => 'admin',
            'display_name' =>  'Admin',
            'description' => 'An administrator with full access to all application features and settings.',
            'role_guard' => 'admin',
            'guards' => [
                [
                    'name' => 'admin',
                    'permissions' => [
                        [
                            'name' => 'admin-panel',
                            'display_name' => 'Access Admin Panel',
                            'description' => 'Allows the user to access the Admin panel.',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
