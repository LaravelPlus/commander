<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LaravelPlus\Commander\Contracts\CommanderServiceInterface;
use LaravelPlus\Commander\Http\Actions\CleanupRecordsAction;
use LaravelPlus\Commander\Http\Actions\GetActivityAction;
use LaravelPlus\Commander\Http\Actions\GetCommandHistoryAction;
use LaravelPlus\Commander\Http\Actions\GetCommandsAction;
use LaravelPlus\Commander\Http\Actions\GetCommandsByCategoryAction;
use LaravelPlus\Commander\Http\Actions\GetCommandStatsAction;
use LaravelPlus\Commander\Http\Actions\GetDashboardStatsAction;
use LaravelPlus\Commander\Http\Actions\GetFailedCommandsAction;
use LaravelPlus\Commander\Http\Actions\GetPopularCommandsAction;
use LaravelPlus\Commander\Http\Actions\GetRecentExecutionsAction;
use LaravelPlus\Commander\Http\Actions\RetryCommandAction;
use LaravelPlus\Commander\Http\Actions\RunCommandAction;
use LaravelPlus\Commander\Http\Actions\SearchCommandsAction;

$commanderUrl = config('commander.url', 'commander');

Route::group([
    'middleware' => config('commander.middleware', ['auth', 'web']),
    'prefix' => $commanderUrl,
], function (): void {
    // API routes for command management
    Route::get('/api/list', fn (): Illuminate\Http\JsonResponse => (new GetCommandsAction(app(CommanderServiceInterface::class)))->execute())->name('commander.api.list');

    Route::post('/api/run', fn (): Illuminate\Http\JsonResponse => (new RunCommandAction(app(CommanderServiceInterface::class), request()))->execute())->name('commander.api.run');

    Route::get('/api/{commandName}/history', fn (string $commandName): Illuminate\Http\JsonResponse => (new GetCommandHistoryAction(app(CommanderServiceInterface::class), $commandName))->execute())->name('commander.api.history');

    Route::get('/api/{commandName}/stats', fn (string $commandName): Illuminate\Http\JsonResponse => (new GetCommandStatsAction(app(CommanderServiceInterface::class), $commandName))->execute())->name('commander.api.stats');

    // Additional API routes
    Route::get('/api/dashboard', fn (): Illuminate\Http\JsonResponse => (new GetDashboardStatsAction(app(CommanderServiceInterface::class)))->execute())->name('commander.api.dashboard');

    Route::get('/api/recent', fn (): Illuminate\Http\JsonResponse => (new GetRecentExecutionsAction(app(CommanderServiceInterface::class)))->execute())->name('commander.api.recent');

    Route::get('/api/popular', fn (): Illuminate\Http\JsonResponse => (new GetPopularCommandsAction(app(CommanderServiceInterface::class), 5))->execute())->name('commander.api.popular');

    Route::get('/api/failed', fn (): Illuminate\Http\JsonResponse => (new GetFailedCommandsAction(app(CommanderServiceInterface::class)))->execute())->name('commander.api.failed');

    Route::post('/api/retry', fn (): Illuminate\Http\JsonResponse => (new RetryCommandAction(app(CommanderServiceInterface::class), request()))->execute())->name('commander.api.retry');

    Route::get('/api/activity', fn (): Illuminate\Http\JsonResponse => (new GetActivityAction(app(CommanderServiceInterface::class), request()))->execute())->name('commander.api.activity');

    Route::get('/api/search', fn (): Illuminate\Http\JsonResponse => (new SearchCommandsAction(app(CommanderServiceInterface::class), request()))->execute())->name('commander.api.search');

    Route::get('/api/category/{category}', fn (string $category): Illuminate\Http\JsonResponse => (new GetCommandsByCategoryAction(app(CommanderServiceInterface::class), $category))->execute())->name('commander.api.category');

    Route::post('/api/cleanup', fn (): Illuminate\Http\JsonResponse => (new CleanupRecordsAction(app(CommanderServiceInterface::class), request()))->execute())->name('commander.api.cleanup');

    // Debug and test routes (only in non-production)
    if (app()->environment('local', 'development')) {
        Route::get('/api/test', fn (): Illuminate\Http\JsonResponse => (new GetCommandsAction(app(CommanderServiceInterface::class)))->execute())->name('commander.api.test')->middleware([]);
    }
});

// Development-only routes without authentication
if (app()->environment('local', 'development')) {
    Route::group([
        'prefix' => $commanderUrl,
        'middleware' => ['web'],
    ], function (): void {
        Route::get('/api/dev/list', fn (): Illuminate\Http\JsonResponse => (new GetCommandsAction(app(CommanderServiceInterface::class)))->execute())->name('commander.api.dev.list');

        Route::post('/api/dev/run', fn (): Illuminate\Http\JsonResponse => (new RunCommandAction(app(CommanderServiceInterface::class), request()))->execute())->name('commander.api.dev.run')->withoutMiddleware(['web']);
    });
}
