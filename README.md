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

# Publish auth configuration (custom guards)
php artisan vendor:publish --tag=redbird-auth

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

### 🚀 Fully Automated Releases

This package uses **fully automated releases**! Simply bump the version and push to main:

```bash
# Option 1: Manual version bump + auto-release
./scripts/bump-composer-version.sh patch
git push origin main

# Option 2: Quick release (auto-bumps patch version)
./scripts/release.sh
```

**What happens automatically:**
1. ✅ **Tests run** - CI ensures everything works
2. ✅ **Tag created** - Semantic versioning tag (v0.2.1)
3. ✅ **GitHub release** - Professional release notes
4. ✅ **Packagist updated** - Package available immediately

### Quick Release Script

For the easiest releases:

```bash
# Auto-release with default message
./scripts/release.sh

# Auto-release with custom message
./scripts/release.sh "Add authentication improvements"
```

### Manual Version Control

For manual control over version bumping:

```bash
# Bump composer.json version locally
./scripts/bump-composer-version.sh patch   # 1.0.0 → 1.0.1
./scripts/bump-composer-version.sh minor   # 1.0.0 → 1.1.0
./scripts/bump-composer-version.sh major   # 1.0.0 → 2.0.0

# Then push to trigger auto-release
git push origin main
```

### Packagist Integration

To automatically publish to Packagist:

1. **Configure Packagist webhook** to watch your GitHub repository
2. **Set up auto-update** in your Packagist package settings
3. **That's it!** - Every release will update Packagist automatically

### Workflow Details

- **Trigger**: Push to `main` branch
- **Exclusions**: Markdown files and workflow files don't trigger releases
- **Versioning**: Uses composer.json version for releases
- **Testing**: Runs full test suite before release
- **Security**: Performs security audits
- **Tags**: Creates semantic version tags (v1.0.1)
- **Releases**: Generates GitHub releases with changelog

## Testing

```bash
vendor/bin/phpunit
```

## Troubleshooting

### Common Issues

**Packagist Version Mismatch**
If you see "tag does not match version in composer.json" errors:
1. Ensure composer.json version matches your latest tag
2. Run `./scripts/release.sh` to trigger a proper release
3. Check that the auto-release workflow completed successfully

**Git Identity Errors in CI/CD**
If GitHub Actions fails with "Author identity unknown":
1. The workflow now configures Git identity automatically
2. Ensure you're using the latest workflow version
3. Check that the workflow has proper permissions

**Filament Panel Not Loading**
1. Ensure you've run `php artisan redbird:install`
2. Check that panel providers are registered in your app
3. Verify your `.env` configuration matches the panel settings

### Support

- 📧 **Email**: hello@fullstack.com
- 🐛 **Issues**: [GitHub Issues](https://github.com/jdc1898/Redbird/issues)
- 📖 **Documentation**: [Full Documentation](https://github.com/jdc1898/Redbird/wiki)

## Roadmap

- [ ] **Advanced Analytics** - More detailed SaaS metrics and reporting
- [ ] **Multi-Currency Support** - International payment processing
- [ ] **Advanced Billing** - Usage-based billing and metering
- [ ] **API Documentation** - OpenAPI/Swagger documentation
- [ ] **Mobile App Support** - React Native integration
- [ ] **White-label Options** - Custom branding and theming

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

## TODO

- [ ] Add more comprehensive test coverage
- [ ] Implement advanced billing features
- [ ] Add API documentation
- [ ]
