<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Tests\Unit\Http\Actions;

use Illuminate\Http\JsonResponse;
use LaravelPlus\Commander\Contracts\CommanderServiceInterface;
use LaravelPlus\Commander\Http\Actions\GetDashboardStatsAction;
use LaravelPlus\Commander\Tests\TestCase;
use Mockery;

class GetDashboardStatsActionTest extends TestCase
{
    private GetDashboardStatsAction $action;
    private CommanderServiceInterface $mockCommanderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockCommanderService = Mockery::mock(CommanderServiceInterface::class);
        $this->action = new GetDashboardStatsAction($this->mockCommanderService);
    }

    public function test_execute_returns_dashboard_stats(): void
    {
        $expectedStats = [
            'total_commands' => 150,
            'total_executions' => 1250,
            'successful_executions' => 1200,
            'failed_executions' => 50,
            'success_rate' => 96.0,
            'avg_execution_time' => 2.5,
            'recent_activity' => [
                ['command' => 'help', 'status' => 'success', 'executed_at' => '2024-01-01 10:00:00'],
                ['command' => 'list', 'status' => 'success', 'executed_at' => '2024-01-01 09:55:00'],
            ],
        ];

        $this->mockCommanderService->shouldReceive('getDashboardStats')
            ->once()
            ->andReturn($expectedStats);

        $response = $this->action->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($data['success']);
        $this->assertEquals('Dashboard statistics loaded successfully', $data['message']);
        $this->assertEquals($expectedStats, $data['data']);
    }

    public function test_execute_handles_service_exception(): void
    {
        $this->mockCommanderService->shouldReceive('getDashboardStats')
            ->once()
            ->andThrow(new \Exception('Failed to load dashboard stats'));

        $response = $this->action->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertFalse($data['success']);
        $this->assertEquals('Failed to load dashboard stats', $data['message']);
    }

    public function test_execute_returns_empty_stats_when_no_data(): void
    {
        $expectedStats = [
            'total_commands' => 0,
            'total_executions' => 0,
            'successful_executions' => 0,
            'failed_executions' => 0,
            'success_rate' => 0.0,
            'avg_execution_time' => 0.0,
            'recent_activity' => [],
        ];

        $this->mockCommanderService->shouldReceive('getDashboardStats')
            ->once()
            ->andReturn($expectedStats);

        $response = $this->action->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($data['success']);
        $this->assertEquals('Dashboard statistics loaded successfully', $data['message']);
        $this->assertEquals($expectedStats, $data['data']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 