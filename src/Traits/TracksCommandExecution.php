<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Traits;

use Illuminate\Support\Facades\App;
use LaravelPlus\Commander\Contracts\CommandExecutionRepositoryInterface;
use LaravelPlus\Commander\Models\CommandExecution;

trait TracksCommandExecution
{
    protected ?CommandExecution $executionRecord = null;

    protected float $startTime;

    /**
     * Start tracking a command execution
     */
    protected function startTracking(string $commandName, array $arguments = [], array $options = []): void
    {
        if (!config('commander.enabled', true)) {
            return;
        }

        $this->startTime = microtime(true);

        $repository = App::make(CommandExecutionRepositoryInterface::class);

        $this->executionRecord = $repository->create([
            'command_name' => $commandName,
            'arguments' => config('commander.track_arguments', true) ? $arguments : null,
            'options' => config('commander.track_options', true) ? $options : null,
            'started_at' => now(),
            'executed_by' => config('commander.track_user', true) ? auth()->id() : null,
            'environment' => config('app.env'),
        ]);
    }

    /**
     * Complete tracking a command execution
     */
    protected function completeTracking(bool $success, int $returnCode = 0, string $output = ''): void
    {
        if (!$this->executionRecord || !config('commander.enabled', true)) {
            return;
        }

        $endTime = microtime(true);
        $executionTime = round($endTime - $this->startTime, 3);

        $repository = App::make(CommandExecutionRepositoryInterface::class);

        $repository->update($this->executionRecord, [
            'output' => config('commander.track_output', true) ? $this->truncateOutput($output) : null,
            'return_code' => $returnCode,
            'success' => $success,
            'execution_time' => config('commander.track_execution_time', true) ? $executionTime : null,
            'completed_at' => now(),
        ]);

        $this->executionRecord = null;
    }

    /**
     * Truncate output if it exceeds the maximum length
     */
    protected function truncateOutput(string $output): string
    {
        $maxLength = config('commander.max_output_length', 10000);

        if (mb_strlen($output) <= $maxLength) {
            return $output;
        }

        return mb_substr($output, 0, $maxLength) . "\n\n[Output truncated - see logs for full output]";
    }

    /**
     * Check if a command should be tracked
     */
    protected function shouldTrackCommand(string $commandName): bool
    {
        if (!config('commander.enabled', true)) {
            return false;
        }

        $trackedCommands = config('commander.tracked_commands', []);
        $ignoredCommands = config('commander.ignored_commands', []);

        // If specific commands are tracked, only track those
        if (!empty($trackedCommands)) {
            foreach ($trackedCommands as $pattern) {
                if ($this->matchesPattern($commandName, $pattern)) {
                    return true;
                }
            }

            return false;
        }

        // Check if command is ignored
        foreach ($ignoredCommands as $pattern) {
            if ($this->matchesPattern($commandName, $pattern)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if command name matches a pattern
     */
    protected function matchesPattern(string $commandName, string $pattern): bool
    {
        // Convert wildcard pattern to regex
        $regex = str_replace(['*', '?'], ['.*', '.'], $pattern);
        $regex = '/^' . $regex . '$/';

        return (bool) preg_match($regex, $commandName);
    }
}
