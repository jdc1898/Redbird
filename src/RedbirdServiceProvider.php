<?php

namespace Fullstack\Redbird;

use Fullstack\Redbird\Commands\InstallCommand;
use Illuminate\Support\ServiceProvider;

class RedbirdServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/redbird.php', 'redbird');
    }

    public function boot(): void
    {
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }

        // Publish configuration file
        $this->publishes([
            __DIR__.'/../config/redbird.php' => config_path('redbird.php'),
        ], 'redbird-config');


        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'redbird-migrations');

        // Publish seeders
        $this->publishes([
            __DIR__.'/../database/seeders' => database_path('seeders'),
        ], 'redbird-seeders');

        // Publish assets
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/redbird'),
        ], 'redbird-views');

        // Load package views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'redbird');

        // Load package migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Publish auth configuration for custom guards
        $this->mergeAuthGuards();
    }

    protected function mergeAuthGuards(): void
    {
        $existingGuards = config('auth.guards', []);

        $newGuards = [
            'admin' => [
                'driver' => 'session',
                'provider' => 'users',
            ],
            'tenant' => [
                'driver' => 'session',
                'provider' => 'users',
            ],
        ];

        // Only add guards that don't already exist
        foreach ($newGuards as $key => $value) {
            if (!array_key_exists($key, $existingGuards)) {
                $existingGuards[$key] = $value;
            }
        }

        config(['auth.guards' => $existingGuards]);
    }
}
