<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Tests\Unit\Http\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LaravelPlus\Commander\Contracts\CommanderServiceInterface;
use LaravelPlus\Commander\Http\Actions\RunCommandAction;
use LaravelPlus\Commander\Tests\TestCase;
use Mockery;

class RunCommandActionTest extends TestCase
{
    private RunCommandAction $action;
    private CommanderServiceInterface $mockCommanderService;
    private Request $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockCommanderService = Mockery::mock(CommanderServiceInterface::class);
        $this->mockRequest = Mockery::mock(Request::class);
        $this->action = new RunCommandAction($this->mockCommanderService, $this->mockRequest);
        
        // Disable command tracking for tests
        config(['commander.enabled' => false]);
    }

    public function test_execute_returns_error_when_no_command_provided(): void
    {
        $this->mockRequest->shouldReceive('input')
            ->with('command')
            ->once()
            ->andReturn(null);

        $this->mockRequest->shouldReceive('input')
            ->with('arguments', [])
            ->once()
            ->andReturn([]);

        $this->mockRequest->shouldReceive('input')
            ->with('options', [])
            ->once()
            ->andReturn([]);

        $response = $this->action->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertFalse($data['success']);
        $this->assertEquals('Command name is required', $data['message']);
    }

    public function test_execute_returns_error_when_command_is_disabled(): void
    {
        // Mock the config to return disabled commands
        config(['commander.disabled_commands' => ['disabled:*']]);

        $this->mockRequest->shouldReceive('input')
            ->with('command')
            ->once()
            ->andReturn('disabled:command');

        $this->mockRequest->shouldReceive('input')
            ->with('arguments', [])
            ->once()
            ->andReturn([]);

        $this->mockRequest->shouldReceive('input')
            ->with('options', [])
            ->once()
            ->andReturn([]);

        // Mock the disabled command check
        $this->mockCommanderService->shouldReceive('executeCommand')
            ->never();

        $response = $this->action->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertFalse($data['success']);
        $this->assertEquals('This command is disabled and cannot be executed', $data['message']);
    }

    public function test_execute_successfully_runs_command(): void
    {
        $expectedResult = [
            'success' => true,
            'return_code' => 0,
            'output' => 'Command executed successfully',
        ];

        $this->mockRequest->shouldReceive('input')
            ->with('command')
            ->once()
            ->andReturn('help');

        $this->mockRequest->shouldReceive('input')
            ->with('arguments', [])
            ->once()
            ->andReturn([]);

        $this->mockRequest->shouldReceive('input')
            ->with('options', [])
            ->once()
            ->andReturn([]);

        $this->mockCommanderService->shouldReceive('executeCommand')
            ->with('help', [], [])
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

        $this->mockRequest->shouldReceive('input')
            ->with('arguments', [])
            ->once()
            ->andReturn([]);

        $this->mockRequest->shouldReceive('input')
            ->with('options', [])
            ->once()
            ->andReturn([]);

        $this->mockCommanderService->shouldReceive('executeCommand')
            ->with('help', [], [])
            ->once()
            ->andThrow(new \Exception('Command execution failed'));

        $response = $this->action->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertFalse($data['success']);
        $this->assertEquals('Command execution failed', $data['message']);
    }

    public function test_execute_with_arguments_and_options(): void
    {
        $expectedResult = [
            'success' => true,
            'return_code' => 0,
            'output' => 'Command with args executed',
        ];

        $arguments = ['arg1', 'arg2'];
        $options = ['--verbose' => true, '--format' => 'json'];

        $this->mockRequest->shouldReceive('input')
            ->with('command')
            ->once()
            ->andReturn('list');

        $this->mockRequest->shouldReceive('input')
            ->with('arguments', [])
            ->once()
            ->andReturn($arguments);

        $this->mockRequest->shouldReceive('input')
            ->with('options', [])
            ->once()
            ->andReturn($options);

        $this->mockCommanderService->shouldReceive('executeCommand')
            ->with('list', $arguments, $options)
            ->once()
            ->andReturn($expectedResult);

        $response = $this->action->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedResult, $data);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 