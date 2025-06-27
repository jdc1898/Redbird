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

        // Merge custom guards into existing auth configuration
        $this->mergeAuthGuards();
    }

    private function mergeAuthGuards(): void
    {
        $this->app['config']->set('auth.guards.admin', [
            'driver' => 'session',
            'provider' => 'users',
        ]);

        $this->app['config']->set('auth.guards.tenant', [
            'driver' => 'session',
            'provider' => 'users',
        ]);
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

        // Publish auth guards configuration
        $this->publishes([
            __DIR__.'/../config/guards.php' => config_path('auth-guards.php'),
        ], 'redbird-auth');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'redbird-migrations');

        // Publish assets
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/redbird'),
        ], 'redbird-views');

        // Load package views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'redbird');

        // Load package migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
