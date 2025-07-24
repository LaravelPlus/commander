<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

final class CommandExecution extends Model
{
    use HasFactory;

    protected $fillable = [
        'command_name',
        'arguments',
        'options',
        'output',
        'return_code',
        'success',
        'executed_by',
        'environment',
        'execution_time',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'arguments' => 'array',
        'options' => 'array',
        'success' => 'boolean',
        'execution_time' => 'decimal:3',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user who executed the command.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'executed_by', 'id');
    }

    /**
     * Scope for successful executions.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope for failed executions.
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope for recent executions.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('started_at', '>=', now()->subDays($days));
    }

    /**
     * Get the last execution time for a specific command.
     */
    public static function getLastExecutionTime(string $commandName): ?self
    {
        return static::where('command_name', $commandName)
            ->where('success', true)
            ->latest('started_at')
            ->first();
    }

    /**
     * Get execution statistics for a command.
     */
    public static function getCommandStats(string $commandName, int $days = 30): array
    {
        $executions = static::where('command_name', $commandName)
            ->where('started_at', '>=', now()->subDays($days))
            ->get();

        return [
            'total_executions' => $executions->count(),
            'successful_executions' => $executions->where('success', true)->count(),
            'failed_executions' => $executions->where('success', false)->count(),
            'average_execution_time' => $executions->whereNotNull('execution_time')->avg('execution_time'),
            'last_execution' => $executions->sortByDesc('started_at')->first(),
            'success_rate' => $executions->count() > 0 
                ? round(($executions->where('success', true)->count() / $executions->count()) * 100, 2)
                : 0,
        ];
    }
} 