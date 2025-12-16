<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
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

final class CommandsController extends Controller
{
    public function __construct(
        protected CommanderServiceInterface $commanderService,
    ) {}

    // Web Interface Routes
    public function index(): View
    {
        return view('commander::commands.dashboard');
    }

    public function list(): View
    {
        return view('commander::commands.list');
    }

    public function schedule(): View
    {
        return view('commander::commands.schedule');
    }

    public function retry(): View
    {
        return view('commander::commands.retry');
    }

    public function activity(): View
    {
        return view('commander::commands.activity');
    }

    // Debug routes (only in non-production)
    public function debug(): View
    {
        return view('commander::commands.debug');
    }

    public function test(): View
    {
        return view('commander::commands.test');
    }

    // API Routes using Actions
    public function getCommands(): JsonResponse
    {
        return (new GetCommandsAction($this->commanderService))->execute();
    }

    public function runCommand(Request $request): JsonResponse
    {
        return (new RunCommandAction($this->commanderService, $request))->execute();
    }

    public function getCommandHistory(string $commandName): JsonResponse
    {
        return (new GetCommandHistoryAction($this->commanderService, $commandName))->execute();
    }

    public function getCommandStats(string $commandName): JsonResponse
    {
        return (new GetCommandStatsAction($this->commanderService, $commandName))->execute();
    }

    public function getDashboardStats(): JsonResponse
    {
        return (new GetDashboardStatsAction($this->commanderService))->execute();
    }

    public function getRecentExecutions(): JsonResponse
    {
        return (new GetRecentExecutionsAction($this->commanderService))->execute();
    }

    public function getPopularCommands(): JsonResponse
    {
        return (new GetPopularCommandsAction($this->commanderService))->execute();
    }

    public function getFailedCommands(): JsonResponse
    {
        return (new GetFailedCommandsAction($this->commanderService))->execute();
    }

    public function retryCommand(Request $request): JsonResponse
    {
        return (new RetryCommandAction($this->commanderService, $request))->execute();
    }

    public function getActivity(Request $request): JsonResponse
    {
        return (new GetActivityAction($this->commanderService, $request))->execute();
    }

    public function searchCommands(Request $request): JsonResponse
    {
        return (new SearchCommandsAction($this->commanderService, $request))->execute();
    }

    public function getCommandsByCategory(string $category): JsonResponse
    {
        return (new GetCommandsByCategoryAction($this->commanderService, $category))->execute();
    }

    public function cleanupRecords(Request $request): JsonResponse
    {
        return (new CleanupRecordsAction($this->commanderService, $request))->execute();
    }
}
