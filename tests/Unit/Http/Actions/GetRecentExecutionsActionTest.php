<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Tests\Unit\Http\Actions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use LaravelPlus\Commander\Contracts\CommanderServiceInterface;
use LaravelPlus\Commander\Http\Actions\GetRecentExecutionsAction;
use LaravelPlus\Commander\Tests\TestCase;
use Mockery;

final class GetRecentExecutionsActionTest extends TestCase
{
    private GetRecentExecutionsAction $action;

    private CommanderServiceInterface $mockCommanderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockCommanderService = Mockery::mock(CommanderServiceInterface::class);
        $this->action = new GetRecentExecutionsAction($this->mockCommanderService);
    }

    public function test_execute_returns_recent_executions_with_default_limit(): void
    {
        $expectedExecutions = [
            ['command' => 'help', 'status' => 'success', 'executed_at' => '2024-01-01 10:00:00'],
            ['command' => 'list', 'status' => 'success', 'executed_at' => '2024-01-01 09:55:00'],
        ];

        $mockCollection = Mockery::mock(Collection::class);
        $mockCollection->shouldReceive('toArray')
            ->once()
            ->andReturn($expectedExecutions);

        $this->mockCommanderService->shouldReceive('getRecentExecutions')
            ->with(10)
            ->once()
            ->andReturn($mockCollection);

        $response = $this->action->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedExecutions, $data);
    }

    public function test_execute_returns_recent_executions_with_custom_limit(): void
    {
        $customLimit = 5;
        $action = new GetRecentExecutionsAction($this->mockCommanderService, $customLimit);

        $expectedExecutions = [
            ['command' => 'help', 'status' => 'success'],
        ];

        $mockCollection = Mockery::mock(Collection::class);
        $mockCollection->shouldReceive('toArray')
            ->once()
            ->andReturn($expectedExecutions);

        $this->mockCommanderService->shouldReceive('getRecentExecutions')
            ->with($customLimit)
            ->once()
            ->andReturn($mockCollection);

        $response = $action->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedExecutions, $data);
    }

    public function test_execute_handles_service_exception(): void
    {
        $this->mockCommanderService->shouldReceive('getRecentExecutions')
            ->with(10)
            ->once()
            ->andThrow(new Exception('Failed to get recent executions'));

        $response = $this->action->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertFalse($data['success']);
        $this->assertEquals('Failed to get recent executions', $data['message']);
    }

    public function test_execute_returns_empty_array_when_no_executions(): void
    {
        $mockCollection = Mockery::mock(Collection::class);
        $mockCollection->shouldReceive('toArray')
            ->once()
            ->andReturn([]);

        $this->mockCommanderService->shouldReceive('getRecentExecutions')
            ->with(10)
            ->once()
            ->andReturn($mockCollection);

        $response = $this->action->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $data);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
