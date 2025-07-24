<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Services;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use LaravelPlus\Commander\Contracts\CommandExecutionRepositoryInterface;
use LaravelPlus\Commander\Contracts\CommandRepositoryInterface;
use LaravelPlus\Commander\Contracts\CommanderServiceInterface;

final class CommanderService implements CommanderServiceInterface
{
    public function __construct(
        private CommandExecutionRepositoryInterface $executionRepository,
        private CommandRepositoryInterface $commandRepository
    ) {}

    /**
     * Get all commands with their execution data
     */
    public function getCommandsWithExecutionData(): Collection
    {
        $commands = $this->commandRepository->getAllCommands();

        return $commands->map(function ($command) {
            $lastExecution = $this->executionRepository->getLastExecutionTime($command['name']);
            $stats = $this->executionRepository->getCommandStats($command['name'], 30);

            return array_merge($command, [
                'last_execution' => $lastExecution ? [
                    'started_at' => $lastExecution->started_at,
                    'completed_at' => $lastExecution->completed_at,
                    'execution_time' => $lastExecution->execution_time,
                    'success' => $lastExecution->success,
                    'return_code' => $lastExecution->return_code,
                ] : null,
                'stats' => $stats,
            ]);
        });
    }

    /**
     * Execute a command and track its execution
     */
    public function executeCommand(string $commandName, array $arguments = [], array $options = []): array
    {
        $startTime = microtime(true);
        $startedAt = now();

        try {
            // Create execution record
            $execution = $this->executionRepository->create([
                'command_name' => $commandName,
                'arguments' => $arguments,
                'options' => $options,
                'started_at' => $startedAt,
                'executed_by' => auth()->id(),
                'environment' => config('app.env'),
            ]);

            // Execute the command
            $output = $this->runArtisanCommand($commandName, $arguments, $options);
            $returnCode = 0;
            $success = true;

        } catch (Exception $e) {
            $output = $e->getMessage();
            $returnCode = 1;
            $success = false;
            Log::error('Command execution failed', [
                'command' => $commandName,
                'arguments' => $arguments,
                'options' => $options,
                'error' => $e->getMessage(),
            ]);
        }

        $endTime = microtime(true);
        $executionTime = round($endTime - $startTime, 3);

        // Update execution record
        $this->executionRepository->update($execution, [
            'output' => $this->truncateOutput($output),
            'return_code' => $returnCode,
            'success' => $success,
            'execution_time' => $executionTime,
            'completed_at' => now(),
        ]);

        return [
            'success' => $success,
            'output' => $output,
            'return_code' => $returnCode,
            'execution_time' => $executionTime,
            'started_at' => $startedAt,
            'completed_at' => now(),
        ];
    }

    /**
     * Get command execution history
     */
    public function getCommandHistory(string $commandName, int $limit = 50): Collection
    {
        return $this->executionRepository->getCommandHistory($commandName, $limit);
    }

    /**
     * Get command statistics
     */
    public function getCommandStats(string $commandName, int $days = 30): array
    {
        return $this->executionRepository->getCommandStats($commandName, $days);
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(): array
    {
        $totalCommands = $this->commandRepository->getAllCommands()->count();
        $totalExecutions = $this->executionRepository->getTotalExecutions();
        $successfulExecutions = $this->executionRepository->getSuccessfulExecutions();
        $failedExecutions = $this->executionRepository->getFailedExecutions();
        $avgExecutionTime = $this->executionRepository->getAverageExecutionTime();
        $recentActivity = $this->executionRepository->getRecentActivity(5);
        $scheduledCommands = $this->executionRepository->getScheduledCommands();

        return [
            'total_commands' => $totalCommands,
            'total_executions' => $totalExecutions,
            'successful_executions' => $successfulExecutions,
            'failed_executions' => $failedExecutions,
            'scheduled_commands' => $scheduledCommands,
            'success_rate' => $totalExecutions > 0 ? round(($successfulExecutions / $totalExecutions) * 100, 2) : 0,
            'avg_execution_time' => $avgExecutionTime,
            'recent_activity' => $recentActivity,
        ];
    }

    /**
     * Get recent command executions
     */
    public function getRecentExecutions(int $limit = 10): Collection
    {
        return $this->executionRepository->getRecentExecutions($limit);
    }

    /**
     * Get popular commands
     */
    public function getPopularCommands(int $limit = 5): Collection
    {
        return $this->executionRepository->getPopularCommands($limit);
    }

    /**
     * Get failed commands
     */
    public function getFailedCommands(): Collection
    {
        return $this->executionRepository->getFailedCommands();
    }

    /**
     * Retry a command
     */
    public function retryCommand(string $commandName): array
    {
        $lastExecution = $this->executionRepository->getLastExecution($commandName);

        if (!$lastExecution) {
            throw new Exception("No previous execution found for command: {$commandName}");
        }

        return $this->executeCommand(
            $commandName,
            $lastExecution->arguments ?? [],
            $lastExecution->options ?? []
        );
    }

    /**
     * Get activity data
     */
    public function getActivity(int $page = 1, int $perPage = 20, string $status = '', string $command = '', string $dateFrom = '', string $dateTo = ''): array
    {
        return $this->executionRepository->getActivity($page, $perPage, $status, $command, $dateFrom, $dateTo);
    }

    /**
     * Search commands
     */
    public function searchCommands(string $query): Collection
    {
        return $this->commandRepository->searchCommands($query);
    }

    /**
     * Get commands by category
     */
    public function getCommandsByCategory(string $category): Collection
    {
        return $this->commandRepository->getCommandsByCategory($category);
    }

    /**
     * Cleanup old records
     */
    public function cleanupOldRecords(?int $days = null): int
    {
        $days = $days ?? config('commander.retention_days', 90);
        return $this->executionRepository->cleanupOldRecords($days);
    }

    /**
     * Run an Artisan command
     */
    private function runArtisanCommand(string $commandName, array $arguments = [], array $options = []): string
    {
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        
        $exitCode = Artisan::call($commandName, array_merge($arguments, $options), $output);
        
        if ($exitCode !== 0) {
            throw new Exception("Command failed with exit code: {$exitCode}");
        }
        
        return $output->fetch();
    }

    /**
     * Truncate output to prevent database overflow
     */
    private function truncateOutput(string $output): string
    {
        $maxLength = config('commander.max_output_length', 10000);
        
        if (strlen($output) > $maxLength) {
            return substr($output, 0, $maxLength) . "\n... (truncated)";
        }
        
        return $output;
    }
}
