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
        $this->routes(function (): void {
            // Load web routes
            Route::middleware('web')
                ->group(function (): void {
                    $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
                });

            // Load API routes
            Route::middleware('web')
                ->group(function (): void {
                    $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
                });
        });
    }
}
