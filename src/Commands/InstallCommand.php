<?php

namespace Fullstack\Redbird\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'redbird:install {--force : Overwrite existing files}';

    protected $description = 'Install the Redbird SaaS package';

    public function handle(): int
    {
        $this->info('Installing Redbird SaaS Package...');

        // Publish configuration
        $this->call('vendor:publish', [
            '--tag' => 'redbird-config',
            '--force' => $this->option('force'),
        ]);

        // Install Spatie Permissions first (needed for migrations and seeder)
        $this->installPermissions();

        // Publish Laravel Cashier migrations first (required for subscriptions)
        $this->call('vendor:publish', [
            '--tag' => 'cashier-migrations',
            '--force' => $this->option('force'),
        ]);

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

        // Run migrations
        if ($this->confirm('Would you like to run the migrations now?', true)) {
            $this->call('migrate');

            // Run the roles and permissions seeder
            $this->info('Seeding roles and permissions...');
            $this->call('db:seed', [
                '--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder',
            ]);
            $this->info('✅ Roles and permissions seeded');
        }

        // Install Filament
        if ($this->confirm('Would you like to install Filament admin panel?', true)) {
            $this->installFilament();
        }

        // Install Laravel Cashier
        $this->installCashier();

        $this->info('✅ Redbird package installed successfully!');
        $this->line('');
        $this->line('Next steps:');
        $this->line('1. Configure your .env file with database and payment settings');
        $this->line('2. Create a Filament user: php artisan make:filament-user');
        $this->line('3. Visit /admin to access the admin panel');

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

        // Publish Cashier migrations
        $this->call('vendor:publish', [
            '--tag' => 'cashier-migrations',
        ]);

        $this->info('✅ Laravel Cashier setup complete');
        $this->line('Don\'t forget to add your Stripe keys to your .env file:');
        $this->line('STRIPE_KEY=your-stripe-key');
        $this->line('STRIPE_SECRET=your-stripe-secret');
    }
}
