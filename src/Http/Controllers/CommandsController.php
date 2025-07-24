<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Http\Controllers;

use App\Http\Controllers\Controller;
use LaravelPlus\Commander\Models\CommandExecution;
use LaravelPlus\Commander\Traits\TracksCommandExecution;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Log;

final class CommandsController extends Controller
{
    use TracksCommandExecution;

    private array $commands = [
        // Commands will be populated from the main application
    ];

    /**
     * Get all available commands with execution history.
     */
    public function getCommands(): JsonResponse
    {
        try {
            $commands = [];

            foreach ($this->commands as $name => $commandData) {
                $lastExecution = CommandExecution::getLastExecutionTime($name);
                $stats = CommandExecution::getCommandStats($name, 30);

                $commands[] = [
                    'name' => $name,
                    'description' => $commandData['description'],
                    'help' => $commandData['help'],
                    'synopsis' => $name,
                    'category' => $commandData['category'],
                    'arguments' => $commandData['arguments'],
                    'options' => $commandData['options'],
                    'locked' => $commandData['locked'] ?? false,
                    'dev' => $commandData['dev'] ?? false,
                    'last_execution' => $lastExecution ? [
                        'started_at' => $lastExecution->started_at,
                        'completed_at' => $lastExecution->completed_at,
                        'execution_time' => $lastExecution->execution_time,
                        'success' => $lastExecution->success,
                    ] : null,
                    'stats' => $stats,
                ];
            }

            return response()->json($commands);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error loading commands: ' . $e->getMessage(),
                'commands_count' => 0,
            ], 500);
        }
    }

    /**
     * Get command execution history.
     */
    public function getCommandHistory(string $commandName): JsonResponse
    {
        try {
            $executions = CommandExecution::where('command_name', $commandName)
                ->orderBy('started_at', 'desc')
                ->paginate(20);

            return response()->json($executions);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error loading command history: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get command statistics.
     */
    public function getCommandStats(string $commandName): JsonResponse
    {
        try {
            $stats = CommandExecution::getCommandStats($commandName, 30);
            return response()->json($stats);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error loading command stats: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run a specific command with tracking.
     */
    public function runCommand(Request $request): JsonResponse
    {
        $command = $request->input('command');
        $arguments = $request->input('arguments', []);
        $options = $request->input('options', []);

        // Check if command should be tracked
        if (!$this->shouldTrackCommand($command)) {
            return $this->executeCommandWithoutTracking($command, $arguments, $options);
        }

        // Start tracking
        $this->startTracking($command, $arguments, $options);

        try {
            // Execute command
            $result = $this->executeCommand($command, $arguments, $options);
            
            // Complete tracking
            $this->completeTracking(
                $result['success'],
                $result['return_code'],
                $result['output']
            );

            return response()->json($result);

        } catch (Exception $e) {
            // Complete tracking with failure
            $this->completeTracking(false, 1, $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute command without tracking.
     */
    private function executeCommandWithoutTracking(string $command, array $arguments, array $options): JsonResponse
    {
        try {
            $result = $this->executeCommand($command, $arguments, $options);
            return response()->json($result);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute the actual command.
     */
    private function executeCommand(string $command, array $arguments, array $options): array
    {
        // Check if command exists
        if (!isset($this->commands[$command])) {
            throw new Exception('Command not found');
        }

        $commandInfo = $this->commands[$command];

        // Check if command is locked
        if ($commandInfo['locked']) {
            throw new Exception('Command is locked');
        }

        // Check if command is for development (block in production only)
        if ($commandInfo['dev'] && app()->environment('production')) {
            throw new Exception('Development command blocked in production');
        }

        // Build the command string
        $commandString = $command;

        // Add arguments
        foreach ($arguments as $argName => $argValue) {
            if (!empty($argValue)) {
                $commandString .= ' ' . escapeshellarg($argValue);
            }
        }

        // Add options
        foreach ($options as $optionName => $optionValue) {
            if ($optionValue === true) {
                $commandString .= ' --' . $optionName;
            } elseif ($optionValue !== false && !empty($optionValue)) {
                $commandString .= ' --' . $optionName . '=' . escapeshellarg($optionValue);
            }
        }

        // Execute the command
        $output = [];
        $returnCode = 0;
        $artisanPath = base_path('artisan');
        $fullCommand = "php {$artisanPath} {$commandString}";

        exec("{$fullCommand} 2>&1", $output, $returnCode);

        return [
            'success' => $returnCode === 0,
            'output' => implode("\n", $output),
            'return_code' => $returnCode,
            'command_executed' => "php {$artisanPath} {$commandString}",
        ];
    }

    /**
     * Set the commands array.
     */
    public function setCommands(array $commands): void
    {
        $this->commands = $commands;
    }
} 