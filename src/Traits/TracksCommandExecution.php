<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Traits;

use LaravelPlus\Commander\Models\CommandExecution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait TracksCommandExecution
{
    protected ?CommandExecution $executionRecord = null;
    protected float $startTime;

    /**
     * Start tracking command execution.
     */
    protected function startTracking(string $commandName, array $arguments = [], array $options = []): void
    {
        if (!config('commander.enabled', true)) {
            return;
        }

        $this->startTime = microtime(true);

        try {
            $this->executionRecord = CommandExecution::create([
                'command_name' => $commandName,
                'arguments' => config('commander.track_arguments', true) ? $arguments : null,
                'options' => config('commander.track_options', true) ? $options : null,
                'executed_by' => config('commander.track_user', true) ? Auth::id() : null,
                'environment' => app()->environment(),
                'started_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create command execution record', [
                'command' => $commandName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Complete tracking command execution.
     */
    protected function completeTracking(bool $success, int $returnCode = 0, string $output = ''): void
    {
        if (!$this->executionRecord || !config('commander.enabled', true)) {
            return;
        }

        try {
            $executionTime = config('commander.track_execution_time', true) 
                ? microtime(true) - $this->startTime 
                : null;

            $output = config('commander.track_output', true) 
                ? $this->truncateOutput($output) 
                : null;

            $this->executionRecord->update([
                'output' => $output,
                'return_code' => $returnCode,
                'success' => $success,
                'execution_time' => $executionTime,
                'completed_at' => now(),
            ]);

            // Log failed executions
            if (!$success) {
                Log::error('Command execution failed', [
                    'command' => $this->executionRecord->command_name,
                    'return_code' => $returnCode,
                    'execution_time' => $executionTime,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to update command execution record', [
                'command' => $this->executionRecord->command_name,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Truncate output if it exceeds the maximum length.
     */
    protected function truncateOutput(string $output): string
    {
        $maxLength = config('commander.max_output_length', 10000);
        
        if (strlen($output) <= $maxLength) {
            return $output;
        }

        return substr($output, 0, $maxLength) . "\n\n[Output truncated - exceeded maximum length]";
    }

    /**
     * Check if command should be tracked.
     */
    protected function shouldTrackCommand(string $commandName): bool
    {
        if (!config('commander.enabled', true)) {
            return false;
        }

        // Check ignored commands
        $ignoredCommands = config('commander.ignored_commands', []);
        foreach ($ignoredCommands as $ignored) {
            if ($this->matchesPattern($commandName, $ignored)) {
                return false;
            }
        }

        // Check tracked commands (if specified)
        $trackedCommands = config('commander.tracked_commands', []);
        if (!empty($trackedCommands)) {
            foreach ($trackedCommands as $tracked) {
                if ($this->matchesPattern($commandName, $tracked)) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    /**
     * Check if command name matches pattern (supports wildcards).
     */
    protected function matchesPattern(string $commandName, string $pattern): bool
    {
        if ($pattern === $commandName) {
            return true;
        }

        // Convert wildcard pattern to regex
        $regex = str_replace(['*', '?'], ['.*', '.'], preg_quote($pattern, '/'));
        return preg_match("/^{$regex}$/", $commandName);
    }
} 