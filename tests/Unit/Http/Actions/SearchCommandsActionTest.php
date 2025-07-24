<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Tests\Unit\Http\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use LaravelPlus\Commander\Contracts\CommanderServiceInterface;
use LaravelPlus\Commander\Http\Actions\SearchCommandsAction;
use LaravelPlus\Commander\Tests\TestCase;
use Mockery;

class SearchCommandsActionTest extends TestCase
{
    private SearchCommandsAction $action;
    private CommanderServiceInterface $mockCommanderService;
    private Request $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockCommanderService = Mockery::mock(CommanderServiceInterface::class);
        $this->mockRequest = Mockery::mock(Request::class);
        $this->action = new SearchCommandsAction($this->mockCommanderService, $this->mockRequest);
    }

    public function test_execute_returns_search_results(): void
    {
        $searchQuery = 'help';
        $expectedResults = new Collection([
            ['name' => 'help', 'description' => 'Display help for a command'],
            ['name' => 'help:list', 'description' => 'List all available commands'],
        ]);

        $this->mockRequest->shouldReceive('input')
            ->with('query', '')
            ->once()
            ->andReturn($searchQuery);

        $this->mockCommanderService->shouldReceive('searchCommands')
            ->with($searchQuery)
            ->once()
            ->andReturn($expectedResults);

        $response = $this->action->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($data['success']);
        $this->assertEquals('Commands search completed successfully', $data['message']);
        $this->assertEquals($expectedResults->toArray(), $data['data']);
    }

    public function test_execute_returns_empty_results_when_no_query(): void
    {
        $this->mockRequest->shouldReceive('input')
            ->with('query', '')
            ->once()
            ->andReturn('');

        $this->mockCommanderService->shouldReceive('searchCommands')
            ->with('')
            ->once()
            ->andReturn(new Collection([]));

        $response = $this->action->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($data['success']);
        $this->assertEquals('Commands search completed successfully', $data['message']);
        $this->assertEquals([], $data['data']);
    }

    public function test_execute_handles_service_exception(): void
    {
        $searchQuery = 'help';

        $this->mockRequest->shouldReceive('input')
            ->with('query', '')
            ->once()
            ->andReturn($searchQuery);

        $this->mockCommanderService->shouldReceive('searchCommands')
            ->with($searchQuery)
            ->once()
            ->andThrow(new \Exception('Search failed'));

        $response = $this->action->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertFalse($data['success']);
        $this->assertEquals('Search failed', $data['message']);
    }

    public function test_execute_with_special_characters_in_query(): void
    {
        $searchQuery = 'help --format';
        $expectedResults = new Collection([
            ['name' => 'help', 'description' => 'Display help with format'],
        ]);

        $this->mockRequest->shouldReceive('input')
            ->with('query', '')
            ->once()
            ->andReturn($searchQuery);

        $this->mockCommanderService->shouldReceive('searchCommands')
            ->with($searchQuery)
            ->once()
            ->andReturn($expectedResults);

        $response = $this->action->execute();
        $data = $response->getData(true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($data['success']);
        $this->assertEquals('Commands search completed successfully', $data['message']);
        $this->assertEquals($expectedResults->toArray(), $data['data']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 