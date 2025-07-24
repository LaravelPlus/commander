<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Repositories;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use LaravelPlus\Commander\Contracts\CommandExecutionRepositoryInterface;
use LaravelPlus\Commander\Models\CommandExecution;
use Log;

final class CommandExecutionRepository implements CommandExecutionRepositoryInterface
{
    public function __construct(
        private CommandExecution $model
    ) {}

    public function getLastExecutionTime(string $commandName): ?CommandExecution
    {
        return $this->model
            ->where('command_name', $commandName)
            ->orderBy('started_at', 'desc')
            ->first();
    }

    public function getLastExecution(string $commandName): ?CommandExecution
    {
        return $this->model
            ->where('command_name', $commandName)
            ->orderBy('started_at', 'desc')
            ->first();
    }

    public function getCommandStats(string $commandName, int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);

        $stats = $this->model
            ->where('command_name', $commandName)
            ->where('started_at', '>=', $startDate)
            ->selectRaw('
                COUNT(*) as total_executions,
                SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful_executions,
                SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as failed_executions,
                AVG(execution_time) as avg_execution_time,
                MAX(execution_time) as max_execution_time,
                MIN(execution_time) as min_execution_time
            ')
            ->first();

        if (!$stats) {
            return [
                'total_executions' => 0,
                'successful_executions' => 0,
                'failed_executions' => 0,
                'success_rate' => 0,
                'avg_execution_time' => 0,
                'max_execution_time' => 0,
                'min_execution_time' => 0,
            ];
        }

        $total = $stats->total_executions;
        $successful = $stats->successful_executions;
        $successRate = $total > 0 ? round(($successful / $total) * 100, 2) : 0;

        return [
            'total_executions' => $total,
            'successful_executions' => $successful,
            'failed_executions' => $stats->failed_executions,
            'success_rate' => $successRate,
            'avg_execution_time' => round((float) ($stats->avg_execution_time ?? 0), 3),
            'max_execution_time' => round((float) ($stats->max_execution_time ?? 0), 3),
            'min_execution_time' => round((float) ($stats->min_execution_time ?? 0), 3),
        ];
    }

    public function getCommandHistory(string $commandName, int $limit = 50): Collection
    {
        return $this->model
            ->where('command_name', $commandName)
            ->orderBy('started_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function create(array $data): CommandExecution
    {
        return $this->model->create($data);
    }

    public function update(CommandExecution $execution, array $data): bool
    {
        return $execution->update($data);
    }

    public function getSuccessfulExecutions(int $limit = 100): Collection
    {
        return $this->model
            ->where('success', true)
            ->orderBy('started_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getFailedExecutions(int $limit = 100): Collection
    {
        return $this->model
            ->where('success', false)
            ->orderBy('started_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getExecutionsByUser(string $userId, int $limit = 50): Collection
    {
        return $this->model
            ->where('executed_by', $userId)
            ->orderBy('started_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getExecutionsByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model
            ->whereBetween('started_at', [$startDate, $endDate])
            ->orderBy('started_at', 'desc')
            ->get();
    }

    public function cleanupOldRecords(int $days = 90): int
    {
        $cutoffDate = Carbon::now()->subDays($days);

        return $this->model
            ->where('started_at', '<', $cutoffDate)
            ->delete();
    }

    public function getTotalExecutions(): int
    {
        return $this->model->count();
    }

    public function getSuccessRate(): float
    {
        $total = $this->model->count();
        if ($total === 0) {
            return 0;
        }

        $successful = $this->model->where('success', true)->count();

        return round(($successful / $total) * 100, 2);
    }

    public function getRecentExecutions(int $limit = 10): Collection
    {
        return $this->model
            ->orderBy('started_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getPopularCommands(int $limit = 5): Collection
    {
        return $this->model
            ->selectRaw('command_name, COUNT(*) as execution_count')
            ->groupBy('command_name')
            ->orderBy('execution_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getFailedCommands(): Collection
    {
        return $this->model
            ->where('success', false)
            ->selectRaw('command_name, MAX(started_at) as last_failed_at, COUNT(*) as failure_count')
            ->groupBy('command_name')
            ->orderBy('last_failed_at', 'desc')
            ->get();
    }

    public function getLastFailedExecution(string $commandName): ?CommandExecution
    {
        return $this->model
            ->where('command_name', $commandName)
            ->where('success', false)
            ->orderBy('started_at', 'desc')
            ->first();
    }

    public function getActivity(int $page = 1, int $perPage = 20, string $status = '', string $command = '', string $dateFrom = '', string $dateTo = ''): array
    {
        try {
            $query = $this->model;

            // Apply filters
            if ($status !== '') {
                $query->where('success', $status === 'success');
            }

            if ($command !== '') {
                $query->where('command_name', 'like', "%{$command}%");
            }

            if ($dateFrom !== '') {
                $query->where('started_at', '>=', $dateFrom);
            }

            if ($dateTo !== '') {
                $query->where('started_at', '<=', $dateTo . ' 23:59:59');
            }

            // Get paginated results
            $executions = $query->orderBy('started_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return [
                'data' => $executions->items(),
                'pagination' => [
                    'current_page' => $executions->currentPage(),
                    'last_page' => $executions->lastPage(),
                    'per_page' => $executions->perPage(),
                    'total' => $executions->total(),
                    'from' => $executions->firstItem(),
                    'to' => $executions->lastItem(),
                ],
            ];
        } catch (Exception $e) {
            Log::error('Commander getActivity error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function getAverageExecutionTime(): float
    {
        $avg = $this->model
            ->whereNotNull('execution_time')
            ->avg('execution_time');

        return round((float) ($avg ?? 0), 3);
    }

    public function getRecentActivity(int $limit = 5): array
    {
        return $this->model
            ->orderBy('started_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getScheduledCommands(): int
    {
        // For now, return 0 as scheduled commands functionality is not implemented
        // This can be extended later to integrate with Laravel's task scheduler
        return 0;
    }
}
