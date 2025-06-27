<?php

namespace Fullstack\Redbird\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Schema;

class InstallCommand extends Command
{
    protected $signature = 'redbird:install {--force : Overwrite existing files}';

    protected $description = 'Install the Redbird SaaS package';

    public function handle(): int
    {
        $this->info('Installing Redbird SaaS Package...');

        // Check if this is an existing application
        $this->checkExistingApplication();

        // Check if User model uses HasRoles trait
        $this->checkUserModelHasRolesTrait();

        // Publish configuration
        $this->call('vendor:publish', [
            '--tag' => 'redbird-config',
            '--force' => $this->option('force'),
        ]);

        // Install Spatie Permissions first (needed for migrations and seeder)
        $this->installPermissions();

        // Check if Cashier is already installed
        $cashierAlreadyInstalled = class_exists('Laravel\\Cashier\\CashierServiceProvider');

        if (!$cashierAlreadyInstalled) {
            // Publish Laravel Cashier migrations first (required for subscriptions)
            $this->call('vendor:publish', [
                '--tag' => 'cashier-migrations',
                '--force' => $this->option('force'),
            ]);
        } else {
            $this->info('Laravel Cashier already installed, skipping migration publishing');
        }

        // Publish our migrations (these depend on Cashier migrations)
        $this->call('vendor:publish', [
            '--tag' => 'redbird-migrations',
            '--force' => $this->option('force'),
        ]);

        // Publish seeders
        $this->call('vendor:publish', [
            '--tag' => 'redbird-seeders',
            '--force' => $this->option('force'),
        ]);

        // Publish views
        $this->call('vendor:publish', [
            '--tag' => 'redbird-views',
            '--force' => $this->option('force'),
        ]);

        // Publish view components
        $this->call('vendor:publish', [
            '--tag' => 'redbird-components',
            '--force' => $this->option('force'),
        ]);

        // Create required models (needed before migrations)
        $this->createRequiredModels();

        // Run migrations
        if ($this->confirm('Would you like to run the migrations now?', true)) {
            $this->call('migrate');

            // Run the roles and permissions seeder
            $this->info('Seeding roles and permissions...');
            $this->call('db:seed', [
                '--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder',
            ]);
            $this->info('✅ Roles and permissions seeded');

            // Setup demo data
            if ($this->confirm('Would you like to set up demo data (admin, tenant, and member users)?', true)) {
                $this->setupDemoData();
            }
        }

        // Install Filament
        if ($this->confirm('Would you like to install Filament admin panel?', true)) {
            $this->installFilament();

            // Publish Filament resources
            $this->publishFilamentResources();
        }

        // Install Laravel Cashier
        $this->installCashier();

        $this->info('✅ Redbird package installed successfully!');
        $this->line('');
        $this->line('Next steps:');
        $this->line('1. Configure your .env file with database and payment settings');
        $this->line('2. Visit /admin to access the admin panel');
        $this->line('3. Change default passwords for security');

        return self::SUCCESS;
    }

    private function installFilament(): void
    {
        $this->info('Installing Filament...');

        // Run Filament install command
        try {
            $this->info('Running filament:install...');
            $this->call('filament:install', [
                '--no-interaction' => true,
            ]);
            $this->info('✅ Filament install completed');
        } catch (\Exception $e) {
            $this->warn('Filament install command failed: ' . $e->getMessage());

            // Try alternative command names
            try {
                $this->info('Trying filament:install-panels...');
                $this->call('filament:install-panels', [
                    '--no-interaction' => true,
                ]);
                $this->info('✅ Filament panels install completed');
            } catch (\Exception $e2) {
                $this->warn('Alternative Filament install command also failed: ' . $e2->getMessage());
                $this->warn('You may need to run "php artisan filament:install --no-interaction" manually');
            }
        }

        // Publish Filament assets (CSS, JS, etc.)
        $this->call('vendor:publish', [
            '--tag' => 'filament-assets',
            '--force' => $this->option('force'),
        ]);

        // Publish Filament configuration
        $this->call('vendor:publish', [
            '--tag' => 'filament-config',
            '--force' => $this->option('force'),
        ]);

        // Generate panel providers from config
        $this->generatePanelProviders();

        $this->info('✅ Filament panels generated from config');
    }

    private function generatePanelProviders(): void
    {
        $panels = config('redbird.panels', []);

        if (empty($panels)) {
            $this->warn('No panels configured in redbird.panels');
            return;
        }

        $this->line('');
        $this->info('Generating Filament Panel Providers...');
        $this->table(['Panel ID', 'Path', 'Domain', 'Provider Class'],
            collect($panels)->map(function ($config, $panelId) {
                return [
                    $panelId,
                    '/' . $config['path'],
                    $config['domain'] ?? 'N/A',
                    ucfirst($panelId) . 'PanelProvider'
                ];
            })->toArray()
        );

        foreach ($panels as $panelId => $panelConfig) {
            $this->generatePanelProvider($panelId, $panelConfig);
        }

        $this->line('');
        $this->info('✅ All panel providers generated successfully!');
    }

    private function generatePanelProvider(string $panelId, array $config): void
    {
        $providerName = ucfirst($panelId) . 'PanelProvider';
        $providerPath = app_path("Providers/Filament/{$providerName}.php");

        // Create directory if it doesn't exist
        $directory = dirname($providerPath);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        // Skip if file exists and not forcing
        if (File::exists($providerPath) && !$this->option('force')) {
            $this->line("Panel provider {$providerName} already exists, skipping...");
            return;
        }

        // Get stub content
        $stubPath = __DIR__ . '/../../stubs/filament-panel-provider.stub';
        if (!File::exists($stubPath)) {
            $this->error("Stub file not found: {$stubPath}");
            return;
        }

        $stubContent = File::get($stubPath);

        // Replace placeholders
        $content = $this->replacePlaceholders($stubContent, $panelId, $config);

        // Write the file
        File::put($providerPath, $content);

        $this->info("✅ Generated {$providerName} at {$providerPath}");

        // Register the provider in bootstrap/providers.php if it exists
        $this->registerPanelProvider($providerName);
    }

    private function replacePlaceholders(string $content, string $panelId, array $config): string
    {
        $className = ucfirst($panelId) . 'PanelProvider';
        $classDirectory = ucfirst($panelId);

        // Handle auth guard - use first guard if multiple, or 'web' as default
        $guards = $config['guard'] ?? ['web'];
        $primaryGuard = is_array($guards) ? $guards[0] : $guards;

        $replacements = [
            '{{ class }}' => $className,
            '{{ panel_id }}' => $panelId,
            '{{ panel_path }}' => $config['path'],
            '{{ class_directory }}' => $classDirectory,
            '{{ panel_domain }}' => isset($config['domain']) && $config['domain']
                ? "\n            ->domain('{$config['domain']}')"
                : '',
            '{{ auth_guard }}' => $primaryGuard !== 'web'
                ? "\n            ->authGuard('{$primaryGuard}')"
                : '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    private function registerPanelProvider(string $providerName): void
    {
        $providerClass = "App\\Providers\\Filament\\{$providerName}";

        // Try Laravel 11+ bootstrap/providers.php first
        if ($this->registerInBootstrapProviders($providerClass, $providerName)) {
            return;
        }

        // Fallback to Laravel 10 config/app.php
        if ($this->registerInConfigApp($providerClass, $providerName)) {
            return;
        }

        // If neither works, show manual instructions
        $this->warn('Could not automatically register the panel provider.');
        $this->line('Please manually add the following to your providers:');
        $this->line("    {$providerClass}::class,");
    }

    private function registerInBootstrapProviders(string $providerClass, string $providerName): bool
    {
        $bootstrapProvidersPath = base_path('bootstrap/providers.php');

        if (!File::exists($bootstrapProvidersPath)) {
            return false;
        }

        $content = File::get($bootstrapProvidersPath);

        // Check if provider is already registered
        if (strpos($content, $providerClass) !== false) {
            $this->line("✓ {$providerName} already registered in bootstrap/providers.php");
            return true;
        }

        // Add the provider to the array
        $content = str_replace(
            '];',
            "    {$providerClass}::class,\n];",
            $content
        );

        File::put($bootstrapProvidersPath, $content);
        $this->info("✅ Registered {$providerName} in bootstrap/providers.php");

        return true;
    }

    private function registerInConfigApp(string $providerClass, string $providerName): bool
    {
        $configAppPath = config_path('app.php');

        if (!File::exists($configAppPath)) {
            return false;
        }

        $content = File::get($configAppPath);

        // Check if provider is already registered
        if (strpos($content, $providerClass) !== false) {
            $this->line("✓ {$providerName} already registered in config/app.php");
            return true;
        }

        // Look for the providers array and add our provider
        $pattern = "/'providers'\s*=>\s*\[[\s\S]*?\]/";
        if (preg_match($pattern, $content, $matches)) {
            $providersArray = $matches[0];

            // Add our provider before the closing bracket
            $newProvidersArray = str_replace(
                '],',
                "        {$providerClass}::class,\n    ],",
                $providersArray
            );

            $content = str_replace($providersArray, $newProvidersArray, $content);
            File::put($configAppPath, $content);

            $this->info("✅ Registered {$providerName} in config/app.php");
            return true;
        }

        return false;
    }

    private function installPermissions(): void
    {
        $this->info('Installing Laravel Permissions...');

        // Publish Spatie permissions config and migrations
        $this->call('vendor:publish', [
            '--provider' => 'Spatie\Permission\PermissionServiceProvider',
        ]);

        $this->info('✅ Laravel Permissions setup complete');
    }

    private function installCashier(): void
    {
        $this->info('Installing Laravel Cashier...');

        // Check if Cashier is already installed
        if (class_exists('Laravel\\Cashier\\CashierServiceProvider')) {
            $this->info('Laravel Cashier already installed, skipping migration publishing');
        } else {
            // Publish Cashier migrations
            $this->call('vendor:publish', [
                '--tag' => 'cashier-migrations',
            ]);
        }

        $this->info('✅ Laravel Cashier setup complete');
        $this->line('Don\'t forget to add your Stripe keys to your .env file:');
        $this->line('STRIPE_KEY=your-stripe-key');
        $this->line('STRIPE_SECRET=your-stripe-secret');
    }

    private function setupDemoData(): void
    {
        $this->info('Setting up demo data...');

        // Create Admin User
        $adminUser = $this->createOrGetUser('admin@example.com', 'Admin User');
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            try {
                if (method_exists($adminUser, 'hasRole') && !$adminUser->hasRole($adminRole)) {
                    $adminUser->assignRole($adminRole);
                    $this->info('✅ Assigned admin role to admin@example.com');
                } else {
                    $this->info('ℹ️  admin@example.com already has admin role');
                }
            } catch (\Exception $e) {
                // Fallback: try direct assignment
                try {
                    $adminUser->assignRole($adminRole);
                    $this->info('✅ Assigned admin role to admin@example.com');
                } catch (\Exception $e2) {
                    $this->warn('⚠️  Could not assign admin role: ' . $e2->getMessage());
                }
            }
        }

        // Create Tenant Admin User
        $tenantUser = $this->createOrGetUser('tenant@example.com', 'Tenant Admin');
        $tenantRole = Role::where('name', 'tenant')->first();
        if ($tenantRole) {
            try {
                if (method_exists($tenantUser, 'hasRole') && !$tenantUser->hasRole($tenantRole)) {
                    $tenantUser->assignRole($tenantRole);
                    $this->info('✅ Assigned tenant role to tenant@example.com');
                } else {
                    $this->info('ℹ️  tenant@example.com already has tenant role');
                }
            } catch (\Exception $e) {
                // Fallback: try direct assignment
                try {
                    $tenantUser->assignRole($tenantRole);
                    $this->info('✅ Assigned tenant role to tenant@example.com');
                } catch (\Exception $e2) {
                    $this->warn('⚠️  Could not assign tenant role: ' . $e2->getMessage());
                }
            }
        }

        // Create Member User
        $memberUser = $this->createOrGetUser('member@example.com', 'Member User');
        $memberRole = Role::where('name', 'member')->first();
        if ($memberRole) {
            try {
                if (method_exists($memberUser, 'hasRole') && !$memberUser->hasRole($memberRole)) {
                    $memberUser->assignRole($memberRole);
                    $this->info('✅ Assigned member role to member@example.com');
                } else {
                    $this->info('ℹ️  member@example.com already has member role');
                }
            } catch (\Exception $e) {
                // Fallback: try direct assignment
                try {
                    $memberUser->assignRole($memberRole);
                    $this->info('✅ Assigned member role to member@example.com');
                } catch (\Exception $e2) {
                    $this->warn('⚠️  Could not assign member role: ' . $e2->getMessage());
                }
            }
        }

        $this->line('');
        $this->line('Demo Users Created:');
        $this->line('Admin: admin@example.com / password (Access: /admin)');
        $this->line('Tenant: tenant@example.com / password (Access: /tenant)');
        $this->line('Member: member@example.com / password (Access: /member)');
        $this->line('');
        $this->warn('⚠️  Remember to change these passwords for security!');
        $this->line('');
    }

    private function createOrGetUser(string $email, string $name)
    {
        $userClass = config('auth.providers.users.model', 'App\\Models\\User');
        $user = $userClass::where('email', $email)->first();

        if (!$user) {
            $user = $userClass::create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $this->info("✅ Created new user: {$email}");
        } else {
            $this->info("ℹ️  User already exists: {$email}");
        }

        // Refresh the model to ensure traits are loaded
        $user = $this->refreshUserModel($user);

        return $user;
    }

    private function refreshUserModel($user)
    {
        $userClass = get_class($user);
        return $userClass::find($user->id);
    }

    private function checkExistingApplication(): void
    {
        $this->info('Checking for potential conflicts in existing application...');

        $warnings = [];

        // Check if User model already exists
        $userModel = config('auth.providers.users.model', 'App\\Models\\User');
        if (class_exists($userModel)) {
            $warnings[] = "User model already exists in {$userModel} - the package will use this model";
        }

        // Check if Filament is already installed
        if (class_exists('Filament\\FilamentServiceProvider')) {
            $warnings[] = 'Filament appears to be already installed - this may cause conflicts';
        }

        // Check if Spatie Permissions is already installed
        if (class_exists('Spatie\\Permission\\PermissionServiceProvider')) {
            $warnings[] = 'Spatie Permissions package appears to be already installed';
        }

        // Check if Laravel Cashier is already installed
        if (class_exists('Laravel\\Cashier\\CashierServiceProvider')) {
            $warnings[] = 'Laravel Cashier appears to be already installed';
        }

        // Check for existing tables that might conflict
        $conflictingTables = ['products', 'prices', 'invoices', 'discounts', 'promo_codes'];
        foreach ($conflictingTables as $table) {
            try {
                if (Schema::hasTable($table)) {
                    $warnings[] = "Table '{$table}' already exists - migration may fail";
                }
            } catch (\Exception $e) {
                // Database connection issues, skip table checks
                break;
            }
        }

        // Check for existing Stripe columns in users table
        try {
            if (Schema::hasTable('users')) {
                $columns = Schema::getColumnListing('users');
                $stripeColumns = ['stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at'];
                $existingStripeColumns = array_intersect($columns, $stripeColumns);

                if (!empty($existingStripeColumns)) {
                    $columnList = implode(', ', $existingStripeColumns);
                    $warnings[] = "Stripe columns already exist in users table: {$columnList} - Cashier migrations may fail";
                }
            }
        } catch (\Exception $e) {
            // Database connection issues, skip column checks
        }

        // Check for existing roles
        if (class_exists('Spatie\\Permission\\Models\\Role')) {
            try {
                if (Schema::hasTable('roles')) {
                    $existingRoles = \Spatie\Permission\Models\Role::whereIn('name', ['admin', 'tenant', 'member'])->get();
                    if ($existingRoles->isNotEmpty()) {
                        $roleNames = $existingRoles->pluck('name')->implode(', ');
                        $warnings[] = "Roles already exist: {$roleNames} - they may be overwritten";
                    }

                    // Check if any users already have these roles
                    $userModel = config('auth.providers.users.model', 'App\\Models\\User');
                    if (class_exists($userModel) && method_exists($userModel, 'role') && Schema::hasTable('model_has_roles')) {
                        $usersWithRoles = $userModel::role(['admin', 'tenant', 'member'])->get();
                        if ($usersWithRoles->isNotEmpty()) {
                            $userEmails = $usersWithRoles->pluck('email')->implode(', ');
                            $warnings[] = "Users already have these roles: {$userEmails}";
                        }
                    }
                }
            } catch (\Exception $e) {
                // Database connection issues, skip role checks
                $warnings[] = "Could not check existing roles due to database connection issues";
            }
        }

        if (!empty($warnings)) {
            $this->warn('⚠️  Potential conflicts detected in existing application:');
            foreach ($warnings as $warning) {
                $this->line("  • {$warning}");
            }
            $this->line('');

            if (!$this->confirm('Do you want to continue with the installation?', false)) {
                $this->info('Installation cancelled.');
                exit(0);
            }
            $this->line('');
        } else {
            $this->info('✅ No conflicts detected.');
        }
    }

    private function publishFilamentResources(): void
    {
        $this->info('Publishing Filament resources...');

        // Use vendor publish for consistency
        $this->call('vendor:publish', [
            '--tag' => 'redbird-filament',
            '--force' => $this->option('force'),
        ]);

        $this->info('✅ Filament resources published successfully!');
        $this->line('');
        $this->line('Resources published to:');
        $this->line('  • app/Filament/Admin/');
        $this->line('  • app/Filament/Tenant/');
        $this->line('  • app/Filament/Member/');
        $this->line('');
        $this->line('You can now customize these resources as needed.');
    }

    private function checkUserModelHasRolesTrait(): void
    {
        $this->info('Checking if User model uses HasRoles trait...');

        $userModel = config('auth.providers.users.model', 'App\\Models\\User');
        if (class_exists($userModel)) {
            $traits = class_uses_recursive($userModel);
            if (in_array('Spatie\\Permission\\Traits\\HasRoles', $traits)) {
                $this->info('✅ User model uses HasRoles trait');
            } else {
                $this->warn('⚠️  User model does not use HasRoles trait');
                $this->line('Please add the HasRoles trait to your User model to use Spatie Permissions');
                if (! $this->confirm('Continue anyway?', false)) {
                    $this->info('Installation cancelled.');
                    exit(0);
                }
            }
        } else {
            $this->warn('⚠️  User model does not exist');
        }
    }

    private function createRequiredModels(): void
    {
        $this->info('Copying required models from src/Models to app/Models...');

        $srcModelsPath = __DIR__ . '/../Models';
        $appModelsPath = app_path('Models');
        $createdCount = 0;
        $skippedCount = 0;

        if (!File::exists($srcModelsPath)) {
            $this->warn('No models found in src/Models.');
            return;
        }

        $modelFiles = File::files($srcModelsPath);
        foreach ($modelFiles as $file) {
            $modelName = $file->getFilename();
            $targetPath = $appModelsPath . '/' . $modelName;

            if (File::exists($targetPath)) {
                $this->line("  ℹ️  Model {$modelName} already exists, skipping...");
                $skippedCount++;
                continue;
            }

            // Read and update namespace
            $content = File::get($file->getPathname());
            $content = preg_replace('/namespace\\s+[^;]+;/', 'namespace App\\Models;', $content);
            // Create Models directory if it doesn't exist
            if (!File::exists($appModelsPath)) {
                File::makeDirectory($appModelsPath, 0755, true);
            }
            File::put($targetPath, $content);
            $this->info("  ✅ Copied model: {$modelName}");
            $createdCount++;
        }

        $this->info("✅ Models copied: {$createdCount}, skipped: {$skippedCount}");
    }
}
