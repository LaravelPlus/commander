<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Http\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LaravelPlus\Commander\Contracts\CommanderServiceInterface;
use LaravelPlus\Commander\Traits\TracksCommandExecution;

final class RunCommandAction extends BaseAction
{
    use TracksCommandExecution;

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

        if (! $command) {
            return $this->errorResponse('Command name is required', 400);
        }

        if ($this->isCommandDisabled($command)) {
            return $this->errorResponse('This command is disabled and cannot be executed', 403);
        }

        if (! $this->shouldTrackCommand($command)) {
            return $this->executeCommandWithoutTracking($command, $arguments, $options);
        }

        $this->startTracking($command, $arguments, $options);

        try {
            $result = $this->commanderService->executeCommand($command, $arguments, $options);
            $this->completeTracking($result['success'], $result['return_code'], $result['output']);

            return response()->json($result);
        } catch (\Exception $e) {
            $this->completeTracking(false, 1, $e->getMessage());
            throw $e;
        }
    }

    private function executeCommandWithoutTracking(string $command, array $arguments, array $options): JsonResponse
    {
        $result = $this->commanderService->executeCommand($command, $arguments, $options);

        return response()->json($result);
    }

    private function isCommandDisabled(string $commandName): bool
    {
        $disabledCommands = config('commander.disabled_commands', []);

        foreach ($disabledCommands as $pattern) {
            if ($this->matchesPattern($commandName, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function matchesPattern(string $commandName, string $pattern): bool
    {
        $regex = str_replace(['*', '?'], ['.*', '.'], $pattern);
        $regex = '/^' . $regex . '$/';

        return (bool) preg_match($regex, $commandName);
    }
}
