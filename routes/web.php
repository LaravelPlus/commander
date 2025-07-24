<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LaravelPlus\Commander\Http\Actions\ShowActivityAction;
use LaravelPlus\Commander\Http\Actions\ShowDashboardAction;
use LaravelPlus\Commander\Http\Actions\ShowDebugAction;
use LaravelPlus\Commander\Http\Actions\ShowListAction;
use LaravelPlus\Commander\Http\Actions\ShowRetryAction;
use LaravelPlus\Commander\Http\Actions\ShowScheduleAction;
use LaravelPlus\Commander\Http\Actions\ShowTestAction;

$commanderUrl = config('commander.url', 'commander');

Route::group([
    'middleware' => config('commander.middleware', ['auth', 'web']),
    'prefix' => $commanderUrl,
], function (): void {
    // Main interface routes
    Route::get('/', fn (): Illuminate\View\View => (new ShowDashboardAction())->execute())->name('commander.index');

    Route::get('/list', fn (): Illuminate\View\View => (new ShowListAction())->execute())->name('commander.list');

    Route::get('/schedule', fn (): Illuminate\View\View => (new ShowScheduleAction())->execute())->name('commander.schedule');

    Route::get('/retry', fn (): Illuminate\View\View => (new ShowRetryAction())->execute())->name('commander.retry');

    Route::get('/activity', fn (): Illuminate\View\View => (new ShowActivityAction())->execute())->name('commander.activity');

    // Debug and test routes (only in non-production)
    if (app()->environment('local', 'development')) {
        Route::get('/debug', fn (): Illuminate\View\View => (new ShowDebugAction())->execute())->name('commander.debug');

        Route::get('/test', fn (): Illuminate\View\View => (new ShowTestAction())->execute())->name('commander.test');
    }
});
