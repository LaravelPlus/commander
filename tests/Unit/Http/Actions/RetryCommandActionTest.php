<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Tests\Unit\Http\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LaravelPlus\Commander\Contracts\CommanderServiceInterface;
use LaravelPlus\Commander\Http\Actions\RetryCommandAction;
use LaravelPlus\Commander\Tests\TestCase;
use Mockery;

class RetryCommandActionTest extends TestCase
{
    private RetryCommandAction $action;
    private CommanderServiceInterface $mockCommanderService;
    private Request $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockCommanderService = Mockery::mock(CommanderServiceInterface::class);
        $this->mockRequest = Mockery::mock(Request::class);
        $this->action = new RetryCommandAction($this->mockCommanderService, $this->mockRequest);
    }

    public function test_execute_returns_error_when_no_command_provided(): void
    {
        $this->mockRequest->shouldReceive('input')
            ->with('command')
            ->once()
            ->andReturn(null);

        $response = $this->action->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertFalse($data['success']);
        $this->assertEquals('Command name is required', $data['message']);
    }

    public function test_execute_successfully_retries_command(): void
    {
        $expectedResult = [
            'success' => true,
            'return_code' => 0,
            'output' => 'Command retried successfully',
        ];

        $this->mockRequest->shouldReceive('input')
            ->with('command')
            ->once()
            ->andReturn('help');

        $this->mockCommanderService->shouldReceive('retryCommand')
            ->with('help')
            ->once()
            ->andReturn($expectedResult);

        $response = $this->action->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedResult, $data);
    }

    public function test_execute_handles_service_exception(): void
    {
        $this->mockRequest->shouldReceive('input')
            ->with('command')
            ->once()
            ->andReturn('help');

        $this->mockCommanderService->shouldReceive('retryCommand')
            ->with('help')
            ->once()
            ->andThrow(new \Exception('Failed to retry command'));

        $response = $this->action->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertFalse($data['success']);
        $this->assertEquals('Failed to retry command', $data['message']);
    }

    public function test_execute_with_empty_command_string(): void
    {
        $this->mockRequest->shouldReceive('input')
            ->with('command')
            ->once()
            ->andReturn('');

        $response = $this->action->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertFalse($data['success']);
        $this->assertEquals('Command name is required', $data['message']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 