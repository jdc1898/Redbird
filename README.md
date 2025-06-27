# Redbird SaaS Package

A comprehensive Laravel SaaS package with Filament admin panel, user management, and subscription billing.

update
## Features

- 🔥 **Filament Admin Panel** - Beautiful, modern admin interface
- 👥 **User Management** - Complete user registration, authentication, and profile management
- 🔐 **Role & Permission System** - Powered by Spatie Laravel Permission
- 💳 **Subscription Billing** - Laravel Cashier integration with Stripe
- 🏢 **Multi-tenancy Ready** - Optional multi-tenant architecture
- 📧 **Email Verification** - Built-in email verification system
- 🔒 **Two-Factor Authentication** - Optional 2FA support
- 🚀 **API Ready** - RESTful API endpoints

## Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
- MySQL/PostgreSQL database

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
- Generate Filament panel providers from config
- Register panel providers in bootstrap/providers.php (Laravel 11+) or config/app.php
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

## Usage

### Accessing the Admin Panel

Visit `/admin` (or your configured admin path) to access the Filament admin panel.

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

# Publish views only
php artisan vendor:publish --tag=redbird-views

# Force overwrite existing files
php artisan redbird:install --force
```

**Note:** Custom authentication guards (`admin` and `tenant`) are automatically merged into your Laravel auth configuration during package registration.

### Customization

#### Views

Publish the views to customize the UI:

```bash
php artisan vendor:publish --tag=redbird-views
```

Views will be published to `resources/views/vendor/redbird/`.

#### Configuration

Publish the config file to customize package behavior:

```bash
php artisan vendor:publish --tag=redbird-config
```

## Commands

- `php artisan redbird:install` - Install the package
- `php artisan redbird:install --force` - Reinstall and overwrite existing files

## Releasing

### Automated Releases

This package uses GitHub Actions for automated releases. When you create a GitHub release:

1. **Create a new release** on GitHub with a tag (e.g., `v1.2.3`)
2. **Publish the release** - this triggers the automated workflow
3. The workflow will:
   - Update `composer.json` version
   - Create a GitHub release
   - Push the updated version back to the repository

### Manual Releases

For manual releases, use the provided script:

```bash
# Bump patch version (1.0.0 → 1.0.1)
./scripts/bump-version.sh patch

# Bump minor version (1.0.0 → 1.1.0)
./scripts/bump-version.sh minor

# Bump major version (1.0.0 → 2.0.0)
./scripts/bump-version.sh major
```

The script will:
- Update the version in `composer.json`
- Commit the change
- Create and push a git tag
- You can then create a GitHub release from the tag

### Packagist Integration

To automatically publish to Packagist:

1. **Configure Packagist webhook** to watch your GitHub repository
2. **Set up auto-update** in your Packagist package settings
3. When you create a GitHub release, Packagist will automatically update

## Testing

```bash
vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email security@fullstack.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Credits

- [Fullstack LLC](https://github.com/fullstack-llc)
- [All Contributors](../../contributors)

Built with ❤️ using:
- [Laravel](https://laravel.com)
- [Filament](https://filamentphp.com)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- [Laravel Cashier](https://laravel.com/docs/billing)
