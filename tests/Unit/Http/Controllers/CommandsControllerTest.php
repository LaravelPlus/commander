<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Tests\Unit\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use LaravelPlus\Commander\Contracts\CommanderServiceInterface;
use LaravelPlus\Commander\Http\Controllers\CommandsController;
use LaravelPlus\Commander\Tests\TestCase;
use Mockery;

final class CommandsControllerTest extends TestCase
{
    private CommandsController $controller;

    private CommanderServiceInterface $mockCommanderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockCommanderService = Mockery::mock(CommanderServiceInterface::class);
        $this->controller = new CommandsController($this->mockCommanderService);
    }

    public function test_index_returns_dashboard_view(): void
    {
        $response = $this->controller->index();

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('commander::commands.dashboard', $response->getName());
    }

    public function test_list_returns_list_view(): void
    {
        $response = $this->controller->list();

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('commander::commands.list', $response->getName());
    }

    public function test_schedule_returns_schedule_view(): void
    {
        $response = $this->controller->schedule();

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('commander::commands.schedule', $response->getName());
    }

    public function test_retry_returns_retry_view(): void
    {
        $response = $this->controller->retry();

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('commander::commands.retry', $response->getName());
    }

    public function test_activity_returns_activity_view(): void
    {
        $response = $this->controller->activity();

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('commander::commands.activity', $response->getName());
    }

    public function test_get_commands_delegates_to_action(): void
    {
        $mockRequest = Mockery::mock(Request::class);
        $expectedResponse = new JsonResponse(['commands' => []]);

        // Mock the action to return a response
        $this->mockCommanderService->shouldReceive('getCommandsWithExecutionData')
            ->once()
            ->andReturn(collect([]));

        $response = $this->controller->getCommands();

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_run_command_delegates_to_action(): void
    {
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('input')->with('command')->andReturn('help');
        $mockRequest->shouldReceive('input')->with('arguments', [])->andReturn([]);
        $mockRequest->shouldReceive('input')->with('options', [])->andReturn([]);

        $this->mockCommanderService->shouldReceive('executeCommand')
            ->with('help', [], [])
            ->andReturn(['success' => true]);

        $response = $this->controller->runCommand($mockRequest);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_get_command_history_delegates_to_action(): void
    {
        $commandName = 'help';
        $expectedHistory = new Collection(['execution1', 'execution2']);

        $this->mockCommanderService->shouldReceive('getCommandHistory')
            ->with($commandName)
            ->once()
            ->andReturn($expectedHistory);

        $response = $this->controller->getCommandHistory($commandName);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_get_command_stats_delegates_to_action(): void
    {
        $commandName = 'help';
        $expectedStats = ['total' => 10, 'success' => 8];

        $this->mockCommanderService->shouldReceive('getCommandStats')
            ->with($commandName)
            ->once()
            ->andReturn($expectedStats);

        $response = $this->controller->getCommandStats($commandName);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_get_dashboard_stats_delegates_to_action(): void
    {
        $expectedStats = ['total_commands' => 150];

        $this->mockCommanderService->shouldReceive('getDashboardStats')
            ->once()
            ->andReturn($expectedStats);

        $response = $this->controller->getDashboardStats();

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_get_recent_executions_delegates_to_action(): void
    {
        $expectedExecutions = new Collection(['execution1', 'execution2']);

        $this->mockCommanderService->shouldReceive('getRecentExecutions')
            ->once()
            ->andReturn($expectedExecutions);

        $response = $this->controller->getRecentExecutions();

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_get_popular_commands_delegates_to_action(): void
    {
        $expectedCommands = new Collection(['command1', 'command2']);

        $this->mockCommanderService->shouldReceive('getPopularCommands')
            ->once()
            ->andReturn($expectedCommands);

        $response = $this->controller->getPopularCommands();

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_get_failed_commands_delegates_to_action(): void
    {
        $expectedCommands = new Collection(['failed1', 'failed2']);

        $this->mockCommanderService->shouldReceive('getFailedCommands')
            ->once()
            ->andReturn($expectedCommands);

        $response = $this->controller->getFailedCommands();

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_retry_command_delegates_to_action(): void
    {
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('input')->with('command')->andReturn('help');
        $expectedResult = ['success' => true];

        $this->mockCommanderService->shouldReceive('retryCommand')
            ->with('help')
            ->once()
            ->andReturn($expectedResult);

        $response = $this->controller->retryCommand($mockRequest);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_get_activity_delegates_to_action(): void
    {
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('input')->with('page', 1)->andReturn(1);
        $mockRequest->shouldReceive('input')->with('per_page', 20)->andReturn(20);
        $mockRequest->shouldReceive('input')->with('status', '')->andReturn('');
        $mockRequest->shouldReceive('input')->with('command', '')->andReturn('');
        $mockRequest->shouldReceive('input')->with('dateFrom', '')->andReturn('');
        $mockRequest->shouldReceive('input')->with('dateTo', '')->andReturn('');

        $expectedActivity = ['activity1', 'activity2'];

        $this->mockCommanderService->shouldReceive('getActivity')
            ->with(1, 20, '', '', '', '')
            ->once()
            ->andReturn($expectedActivity);

        $response = $this->controller->getActivity($mockRequest);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_search_commands_delegates_to_action(): void
    {
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('input')->with('query', '')->andReturn('help');

        $expectedResults = new Collection(['result1', 'result2']);

        $this->mockCommanderService->shouldReceive('searchCommands')
            ->with('help')
            ->once()
            ->andReturn($expectedResults);

        $response = $this->controller->searchCommands($mockRequest);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_get_commands_by_category_delegates_to_action(): void
    {
        $category = 'artisan';
        $expectedCommands = new Collection(['command1', 'command2']);

        $this->mockCommanderService->shouldReceive('getCommandsByCategory')
            ->with($category)
            ->once()
            ->andReturn($expectedCommands);

        $response = $this->controller->getCommandsByCategory($category);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_cleanup_records_delegates_to_action(): void
    {
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('input')->with('days', 90)->andReturn(30);

        $expectedResult = 10;

        $this->mockCommanderService->shouldReceive('cleanupOldRecords')
            ->with(30)
            ->once()
            ->andReturn($expectedResult);

        $response = $this->controller->cleanupRecords($mockRequest);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
