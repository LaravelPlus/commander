<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Http\Actions;

use Exception;
use Illuminate\Http\JsonResponse;
use LaravelPlus\Commander\Contracts\CommanderServiceInterface;
use Log;

abstract class BaseAction
{
    public function __construct(
        protected CommanderServiceInterface $commanderService,
    ) {}

    /**
     * Execute the action and return a JSON response
     */
    abstract public function execute(): JsonResponse;

    /**
     * Create a successful JSON response
     */
    protected function successResponse(mixed $data = null, string $message = 'Success'): JsonResponse
    {
        $response = ['success' => true, 'message' => $message];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response);
    }

    /**
     * Create a direct data response (without wrapping in data property)
     */
    protected function directResponse(mixed $data): JsonResponse
    {
        return response()->json($data);
    }

    /**
     * Create an error JSON response
     */
    protected function errorResponse(string $message, int $statusCode = 500, mixed $data = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Handle exceptions with consistent error logging and response
     */
    protected function handleException(Exception $e, string $context = 'Action execution'): JsonResponse
    {
        Log::error("Commander {$context} error", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return $this->errorResponse($e->getMessage());
    }

    /**
     * Execute action with exception handling
     */
    protected function executeWithExceptionHandling(callable $callback, string $context = 'Action'): JsonResponse
    {
        try {
            return $callback();
        } catch (Exception $e) {
            return $this->handleException($e, $context);
        }
    }
}
