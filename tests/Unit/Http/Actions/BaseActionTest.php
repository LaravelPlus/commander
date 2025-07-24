<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Tests\Unit\Http\Actions;

use Exception;
use Illuminate\Http\JsonResponse;
use LaravelPlus\Commander\Contracts\CommanderServiceInterface;
use LaravelPlus\Commander\Http\Actions\BaseAction;
use LaravelPlus\Commander\Tests\TestCase;
use Mockery;

class BaseActionTest extends TestCase
{
    private BaseAction $baseAction;
    private CommanderServiceInterface $mockCommanderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockCommanderService = Mockery::mock(CommanderServiceInterface::class);
        $this->baseAction = new class($this->mockCommanderService) extends BaseAction {
            public function execute(): JsonResponse
            {
                return $this->successResponse(['test' => 'data'], 'Test success');
            }

            public function testDirectResponse(mixed $data): JsonResponse
            {
                return $this->directResponse($data);
            }

            public function testErrorResponse(string $message, int $statusCode = 500, mixed $data = null): JsonResponse
            {
                return $this->errorResponse($message, $statusCode, $data);
            }

            public function testHandleException(Exception $e, string $context = 'Test'): JsonResponse
            {
                return $this->handleException($e, $context);
            }

            public function testExecuteWithExceptionHandling(callable $callback, string $context = 'Test'): JsonResponse
            {
                return $this->executeWithExceptionHandling($callback, $context);
            }

            public function testSuccessResponse(mixed $data = null, string $message = 'Success'): JsonResponse
            {
                return $this->successResponse($data, $message);
            }
        };
    }

    public function test_success_response_with_data(): void
    {
        $response = $this->baseAction->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($data['success']);
        $this->assertEquals('Test success', $data['message']);
        $this->assertEquals(['test' => 'data'], $data['data']);
    }

    public function test_success_response_without_data(): void
    {
        $baseAction = new class($this->mockCommanderService) extends BaseAction {
            public function execute(): JsonResponse
            {
                return $this->successResponse();
            }
        };

        $response = $baseAction->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($data['success']);
        $this->assertEquals('Success', $data['message']);
        $this->assertArrayNotHasKey('data', $data);
    }

    public function test_direct_response(): void
    {
        $testData = ['key' => 'value'];
        $response = $this->baseAction->testDirectResponse($testData);
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($testData, $data);
    }

    public function test_error_response(): void
    {
        $response = $this->baseAction->testErrorResponse('Test error', 400, ['error' => 'details']);
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertFalse($data['success']);
        $this->assertEquals('Test error', $data['message']);
        $this->assertEquals(['error' => 'details'], $data['data']);
    }

    public function test_error_response_without_data(): void
    {
        $response = $this->baseAction->testErrorResponse('Test error');
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertFalse($data['success']);
        $this->assertEquals('Test error', $data['message']);
        $this->assertArrayNotHasKey('data', $data);
    }

    public function test_handle_exception(): void
    {
        $exception = new Exception('Test exception message');
        $response = $this->baseAction->testHandleException($exception, 'TestContext');
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertFalse($data['success']);
        $this->assertEquals('Test exception message', $data['message']);
    }

    public function test_execute_with_exception_handling_success(): void
    {
        $response = $this->baseAction->testExecuteWithExceptionHandling(
            fn (): JsonResponse => $this->baseAction->testSuccessResponse(['test' => 'data']),
            'TestContext'
        );
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($data['success']);
    }

    public function test_execute_with_exception_handling_exception(): void
    {
        $response = $this->baseAction->testExecuteWithExceptionHandling(
            fn (): JsonResponse => throw new Exception('Test exception'),
            'TestContext'
        );
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertFalse($data['success']);
        $this->assertEquals('Test exception', $data['message']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 