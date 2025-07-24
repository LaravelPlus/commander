<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Contracts;

use Illuminate\Database\Eloquent\Collection;
use LaravelPlus\Commander\Models\CommandExecution;

interface CommandExecutionRepositoryInterface
{
    /**
     * Get the last execution time for a specific command
     */
    public function getLastExecutionTime(string $commandName): ?CommandExecution;

    /**
     * Get the last execution for a specific command
     */
    public function getLastExecution(string $commandName): ?CommandExecution;

    /**
     * Get command statistics for a specific command
     */
    public function getCommandStats(string $commandName, int $days = 30): array;

    /**
     * Get recent executions for a command
     */
    public function getCommandHistory(string $commandName, int $limit = 50): Collection;

    /**
     * Create a new command execution record
     */
    public function create(array $data): CommandExecution;

    /**
     * Update an existing command execution record
     */
    public function update(CommandExecution $execution, array $data): bool;

    /**
     * Get all successful executions
     */
    public function getSuccessfulExecutions(int $limit = 100): Collection;

    /**
     * Get all failed executions
     */
    public function getFailedExecutions(int $limit = 100): Collection;

    /**
     * Get executions by user
     */
    public function getExecutionsByUser(string $userId, int $limit = 50): Collection;

    /**
     * Get executions by date range
     */
    public function getExecutionsByDateRange(string $startDate, string $endDate): Collection;

    /**
     * Clean up old execution records
     */
    public function cleanupOldRecords(int $days = 90): int;

    /**
     * Get total execution count
     */
    public function getTotalExecutions(): int;

    /**
     * Get success rate percentage
     */
    public function getSuccessRate(): float;

    /**
     * Get average execution time
     */
    public function getAverageExecutionTime(): float;

    /**
     * Get recent activity
     */
    public function getRecentActivity(int $limit = 5): array;

    /**
     * Get recent executions
     */
    public function getRecentExecutions(int $limit = 10): Collection;

    /**
     * Get popular commands
     */
    public function getPopularCommands(int $limit = 5): Collection;

    /**
     * Get failed commands
     */
    public function getFailedCommands(): Collection;

    /**
     * Get scheduled commands
     */
    public function getScheduledCommands(): int;

    /**
     * Get activity data
     */
    public function getActivity(int $page = 1, int $perPage = 20, string $status = '', string $command = '', string $dateFrom = '', string $dateTo = ''): array;
}
