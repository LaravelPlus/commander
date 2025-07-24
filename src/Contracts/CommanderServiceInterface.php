<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Contracts;

use Illuminate\Support\Collection;

interface CommanderServiceInterface
{
    /**
     * Get all commands with their execution data
     */
    public function getCommandsWithExecutionData(): Collection;

    /**
     * Execute a command and track its execution
     */
    public function executeCommand(string $commandName, array $arguments = [], array $options = []): array;

    /**
     * Get command execution history
     */
    public function getCommandHistory(string $commandName, int $limit = 50): Collection;

    /**
     * Get command statistics
     */
    public function getCommandStats(string $commandName, int $days = 30): array;

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(): array;

    /**
     * Get recent command executions
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
     * Retry a command
     */
    public function retryCommand(string $commandName): array;

    /**
     * Get activity data
     */
    public function getActivity(int $page = 1, int $perPage = 20, string $status = '', string $command = '', string $dateFrom = '', string $dateTo = ''): array;

    /**
     * Search commands
     */
    public function searchCommands(string $query): Collection;

    /**
     * Get commands by category
     */
    public function getCommandsByCategory(string $category): Collection;

    /**
     * Cleanup old records
     */
    public function cleanupOldRecords(?int $days = null): int;
} 