<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Http\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LaravelPlus\Commander\Contracts\CommanderServiceInterface;

final class RetryCommandAction extends BaseAction
{
    public function __construct(
        CommanderServiceInterface $commanderService,
        private Request $request,
    ) {
        parent::__construct($commanderService);
    }

    public function execute(): JsonResponse
    {
        return $this->executeWithExceptionHandling(
            fn (): JsonResponse => $this->retryCommand(),
            'RetryCommand',
        );
    }

    private function retryCommand(): JsonResponse
    {
        $commandName = $this->request->input('command');

        if (!$commandName) {
            return $this->errorResponse('Command name is required', 400);
        }

        $result = $this->commanderService->retryCommand($commandName);

        return response()->json($result);
    }
}
