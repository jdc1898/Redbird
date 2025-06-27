# Redbird SaaS Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/fullstack/redbird.svg)](https://packagist.org/packages/fullstack/redbird)
[![Tests](https://github.com/jdc1898/Redbird/actions/workflows/auto-release.yml/badge.svg)](https://github.com/jdc1898/Redbird/actions/workflows/auto-release.yml)
[![License](https://img.shields.io/packagist/l/fullstack/redbird.svg)](https://packagist.org/packages/fullstack/redbird)

A comprehensive Laravel SaaS package with Filament admin panel, user management, and subscription billing.

## 🚀 Recent Updates

- **v0.2.5** - Fixed Packagist version synchronization and improved auto-release workflow
- **v0.2.4** - Enhanced Git identity configuration in CI/CD pipeline
- **v0.2.0** - Added multi-panel support with admin, tenant, and member panels
- **v0.1.0** - Initial release with core SaaS functionality

## Features

- 🔥 **Filament Admin Panel** - Beautiful, modern admin interface
- 👥 **User Management** - Complete user registration, authentication, and profile management
- 🔐 **Role & Permission System** - Powered by Spatie Laravel Permission
- 💳 **Subscription Billing** - Laravel Cashier integration with Stripe
- 🏢 **Multi-tenancy Ready** - Optional multi-tenant architecture
- 📧 **Email Verification** - Built-in email verification system
- 🔒 **Two-Factor Authentication** - Optional 2FA support
- 🚀 **API Ready** - RESTful API endpoints
- 📊 **Dashboard Widgets** - MRR charts, subscription stats, and product analytics
- 🎨 **Customizable UI** - Publishable views and configurable themes
- 🔧 **Automated Releases** - CI/CD pipeline with automated versioning and deployment
- 📦 **Packagist Integration** - Automatic package updates and distribution

## Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
- MySQL/PostgreSQL database

## User Model Requirements

The package uses your application's default User model (configured in `config/auth.php`). Your User model must include the Spatie Permission traits to enable role management:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;

    // ... your existing code
}
```

If you don't have the Spatie Permission package installed, the installation command will install it for you.

## Installation

### 1. Install the Package

```bash
composer require fullstack/redbird
```

### 2. Run the Installation Command

```bash
php artisan redbird:install
```

This command will:
- Publish configuration files
- Publish and run migrations
- Seed roles and permissions from config
- Generate Filament panel providers from config
- Register panel providers in bootstrap/providers.php (Laravel 11+) or config/app.php
- Publish Filament assets (CSS, JS) for proper styling
- Configure Laravel Permissions
- Set up Laravel Cashier (optional)

### 3. Create an Admin User

```bash
php artisan make:filament-user
```

### 4. Configure Your Environment

Add the following to your `.env` file:

```env
# Redbird Configuration
REDBIRD_APP_NAME="Your SaaS App"
REDBIRD_ADMIN_PATH=admin

# Stripe Configuration (if using subscriptions)
STRIPE_KEY=your-stripe-publishable-key
STRIPE_SECRET=your-stripe-secret-key
STRIPE_WEBHOOK_SECRET=your-stripe-webhook-secret

# Feature Flags
REDBIRD_SUBSCRIPTIONS_ENABLED=true
REDBIRD_USER_REGISTRATION=true
REDBIRD_EMAIL_VERIFICATION=true
```

### 5. Troubleshooting CSS Issues

If you experience broken CSS in the admin panel, ensure Filament assets are properly published:

```bash
# Publish Filament assets manually if needed
php artisan vendor:publish --tag=filament-assets

# Clear cache and recompile assets
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## Usage

### Accessing the Admin Panel

Visit `/admin` (or your configured admin path) to access the Filament admin panel.

### Dashboard Widgets

Redbird includes several built-in dashboard widgets for SaaS analytics:

- **MRR Stats Widget** - Monthly Recurring Revenue tracking
- **MRR Chart Widget** - Visual MRR trends over time
- **Active Subscriptions Widget** - Real-time subscription count
- **Product Stats Widget** - Product performance metrics
- **Price Stats Widget** - Pricing analytics

### Subscription Management

The package includes comprehensive subscription management:

```php
// Create a subscription
$user->newSubscription('default', 'price_123')->create();

// Check subscription status
if ($user->subscription('default')->active()) {
    // User has active subscription
}

// Handle subscription changes
$user->subscription('default')->swap('price_456');
```

### Multi-Panel Architecture

Redbird supports multiple Filament panels for different user types:

- **Admin Panel** (`/admin`) - For super admins and system management
- **Tenant Panel** (`/tenant`) - For tenant/organization management
- **Member Panel** (`/member`) - For end users and customers

Each panel can have its own:
- Authentication guard
- Domain/subdomain
- Custom styling
- Specific permissions

### Configuration

The package configuration can be found in `config/redbird.php`. You can customize:

- **Panel Settings** - Define multiple Filament panels with paths, domains, and guards
- Subscription management
- Multi-tenancy options
- Feature flags
- Default permissions and roles

#### Panel Configuration

Define your Filament panels in the `panels` section of the config:

```php
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
```

During installation, this will generate:
- `app/Providers/Filament/AdminPanelProvider.php` → `/admin`
- `app/Providers/Filament/TenantPanelProvider.php` → `/tenant`
- `app/Providers/Filament/MemberPanelProvider.php` → `/member`

### Publishing Assets

You can publish specific assets using tags:

```bash
# Publish configuration only
php artisan vendor:publish --tag=redbird-config

# Publish migrations only
php artisan vendor:publish --tag=redbird-migrations

# Publish seeders only
php artisan vendor:publish --tag=redbird-seeders

# Publish views only
php artisan vendor:publish --tag=redbird-views

# Publish Filament assets (CSS, JS)
php artisan vendor:publish --tag=filament-assets

# Force overwrite existing files
php artisan redbird:install --force
```

## Commands

- `php artisan redbird:install` - Install the package
- `php artisan redbird:install --force` - Reinstall and overwrite existing files

## Testing

```bash
vendor/bin/phpunit
```

## Troubleshooting

### Common Issues

**User Model Missing HasRoles Trait**
If you get "Call to undefined method assignRole()" errors:
1. Ensure your User model includes `use Spatie\Permission\Traits\HasRoles;`
2. Run `php artisan redbird:install` again to set up roles

**Existing Application Conflicts**
The installation command will detect potential conflicts in existing applications:
- Existing User models
- Already installed packages (Filament, Spatie Permissions, Cashier)
- Conflicting database tables
- Existing roles and permissions

**Filament Panel Not Loading**
1. Ensure you've run `php artisan redbird:install`
2. Check that panel providers are registered in your app
3. Verify your `.env` configuration matches the panel settings

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
