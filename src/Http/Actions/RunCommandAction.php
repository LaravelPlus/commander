<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Http\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LaravelPlus\Commander\Contracts\CommanderServiceInterface;

final class RunCommandAction extends BaseAction
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
            fn (): JsonResponse => $this->runCommand(),
            'RunCommand',
        );
    }

    private function runCommand(): JsonResponse
    {
        $command = $this->request->input('command');
        $arguments = $this->request->input('arguments', []);
        $options = $this->request->input('options', []);

        if (!$command) {
            return $this->errorResponse('Command name is required', 400);
        }

        if (!$this->isCommandAllowed($command)) {
            return $this->errorResponse('This command is disabled and cannot be executed', 403);
        }

        // The service records the execution; tracking here too would double-count it.
        return response()->json($this->commanderService->executeCommand($command, $arguments, $options));
    }
}
