<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Tests\Unit\Http\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use LaravelPlus\Commander\Contracts\CommanderServiceInterface;
use LaravelPlus\Commander\Http\Actions\GetCommandsAction;
use LaravelPlus\Commander\Tests\TestCase;
use Mockery;

class GetCommandsActionTest extends TestCase
{
    private GetCommandsAction $action;
    private CommanderServiceInterface $mockCommanderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockCommanderService = Mockery::mock(CommanderServiceInterface::class);
        $this->action = new GetCommandsAction($this->mockCommanderService);
    }

    public function test_execute_returns_commands_data(): void
    {
        $expectedCommands = [
            ['name' => 'help', 'description' => 'Display help'],
            ['name' => 'list', 'description' => 'List commands'],
        ];

        $mockCollection = Mockery::mock(Collection::class);
        $mockCollection->shouldReceive('toArray')
            ->once()
            ->andReturn($expectedCommands);

        $this->mockCommanderService->shouldReceive('getCommandsWithExecutionData')
            ->once()
            ->andReturn($mockCollection);

        $response = $this->action->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedCommands, $data);
    }

    public function test_execute_handles_service_exception(): void
    {
        $this->mockCommanderService->shouldReceive('getCommandsWithExecutionData')
            ->once()
            ->andThrow(new \Exception('Service error'));

        $response = $this->action->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertFalse($data['success']);
        $this->assertEquals('Service error', $data['message']);
    }

    public function test_execute_returns_empty_array_when_no_commands(): void
    {
        $mockCollection = Mockery::mock(Collection::class);
        $mockCollection->shouldReceive('toArray')
            ->once()
            ->andReturn([]);

        $this->mockCommanderService->shouldReceive('getCommandsWithExecutionData')
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