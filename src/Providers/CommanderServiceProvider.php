<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Providers;

use Illuminate\Support\ServiceProvider;
use LaravelPlus\Commander\Contracts\CommandExecutionRepositoryInterface;
use LaravelPlus\Commander\Contracts\CommandRepositoryInterface;
use LaravelPlus\Commander\Contracts\CommanderServiceInterface;
use LaravelPlus\Commander\Models\CommandExecution;
use LaravelPlus\Commander\Repositories\CommandExecutionRepository;
use LaravelPlus\Commander\Repositories\CommandRepository;
use LaravelPlus\Commander\Services\CommanderService;

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

        // Register repositories
        $this->app->bind(CommandExecutionRepositoryInterface::class, CommandExecutionRepository::class);
        $this->app->bind(CommandRepositoryInterface::class, CommandRepository::class);

        // Register services
        $this->app->singleton(CommanderService::class, fn ($app) => new CommanderService(
            $app->make(CommandExecutionRepositoryInterface::class),
            $app->make(CommandRepositoryInterface::class)
        ));

        // Register the interface binding
        $this->app->bind(CommanderServiceInterface::class, CommanderService::class);

        // Register the execution model as a singleton for backward compatibility
        $this->app->singleton('commander.execution', fn () => new CommandExecution());

        $this->app->register(CommanderRouteServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load views
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'commander');

        // Publish config
        $this->publishes([
            __DIR__ . '/../../config/commander.php' => config_path('commander.php'),
        ], 'commander-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'commander-migrations');

        // Publish routes
        $this->publishes([
            __DIR__ . '/../../routes' => base_path('routes/commander'),
        ], 'commander-routes');

        // Publish views
        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/commander'),
        ], 'commander-views');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
