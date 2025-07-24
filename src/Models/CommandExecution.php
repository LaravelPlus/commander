<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * Get the user who executed the command
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class), 'executed_by');
    }

    /**
     * Scope for successful executions
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope for failed executions
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope for recent executions
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('started_at', '>=', now()->subDays($days));
    }

    /**
     * Get formatted execution time
     */
    public function getFormattedExecutionTimeAttribute(): string
    {
        if (!$this->execution_time) {
            return 'N/A';
        }

        if ($this->execution_time < 1) {
            return round($this->execution_time * 1000, 0) . 'ms';
        }

        return round($this->execution_time, 2) . 's';
    }

    /**
     * Get status badge
     */
    public function getStatusBadgeAttribute(): string
    {
        return $this->success ? 'success' : 'danger';
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute(): string
    {
        return $this->success ? 'Success' : 'Failed';
    }
}
