<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

final class CommanderRouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware('web')
                ->prefix(config('commander.route_prefix', 'admin'))
                ->name(config('commander.route_name_prefix', 'commander') . '.')
                ->group(function () {
                    $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
                });
        });
    }
} 