<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LaravelPlus\Commander\Http\Controllers\CommandsController;

/*
|--------------------------------------------------------------------------
| Commander Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your commander package.
| These routes are loaded by the CommanderRouteServiceProvider.
|
*/

$commanderUrl = config('commander.url', 'commander');

Route::group([
    'middleware' => ['auth'],
    'prefix' => $commanderUrl,
], function () {
    
    // Main commander interface
    Route::get('/', [CommandsController::class, 'index'])->name('index');
    
    // API endpoints
    Route::get('/state', [CommandsController::class, 'getState'])->name('state');
    Route::get('/list', [CommandsController::class, 'getCommands'])->name('list');
    Route::post('/run', [CommandsController::class, 'runCommand'])->name('run');
    
    // Command history and statistics
    Route::get('/{commandName}/history', [CommandsController::class, 'getCommandHistory'])->name('history');
    Route::get('/{commandName}/stats', [CommandsController::class, 'getCommandStats'])->name('stats');
    
    // Debug and test endpoints
    Route::get('/debug', [CommandsController::class, 'debug'])->name('debug');
    Route::get('/test', [CommandsController::class, 'test'])->name('test');
}); 