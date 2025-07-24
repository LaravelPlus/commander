<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Providers;

use Illuminate\Support\ServiceProvider;
use LaravelPlus\Commander\Models\CommandExecution;

final class CommanderServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/commander.php', 'commander'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../../config/commander.php' => config_path('commander.php'),
        ], 'commander-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'commander-migrations');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Register the model
        $this->app->singleton('commander.execution', function () {
            return new CommandExecution();
        });
    }
} 