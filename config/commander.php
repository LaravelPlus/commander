<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Command Execution Tracking
    |--------------------------------------------------------------------------
    |
    | This configuration allows you to customize how command executions
    | are tracked and stored in your application.
    |
    */

    'enabled' => env('COMMANDER_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Interface Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the URL and interface settings for the commander.
    |
    */

    'url' => env('COMMANDER_URL', 'admin/commander'),
    'route_prefix' => env('COMMANDER_ROUTE_PREFIX', 'admin'),
    'route_name_prefix' => env('COMMANDER_ROUTE_NAME_PREFIX', 'commander'),

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the database settings for storing command executions.
    |
    */

    'table' => env('COMMANDER_TABLE', 'command_executions'),

    /*
    |--------------------------------------------------------------------------
    | Execution Tracking Settings
    |--------------------------------------------------------------------------
    |
    | Configure what information is tracked for each command execution.
    |
    */

    'track_output' => env('COMMANDER_TRACK_OUTPUT', true),
    'track_arguments' => env('COMMANDER_TRACK_ARGUMENTS', true),
    'track_options' => env('COMMANDER_TRACK_OPTIONS', true),
    'track_execution_time' => env('COMMANDER_TRACK_EXECUTION_TIME', true),
    'track_user' => env('COMMANDER_TRACK_USER', true),

    /*
    |--------------------------------------------------------------------------
    | Output Storage
    |--------------------------------------------------------------------------
    |
    | Configure how command output is stored. Large outputs can be truncated
    | to prevent database bloat.
    |
    */

    'max_output_length' => env('COMMANDER_MAX_OUTPUT_LENGTH', 10000),

    /*
    |--------------------------------------------------------------------------
    | Retention Policy
    |--------------------------------------------------------------------------
    |
    | Configure how long to keep command execution records.
    |
    */

    'retention_days' => env('COMMANDER_RETENTION_DAYS', 90),

    /*
    |--------------------------------------------------------------------------
    | Commands to Track
    |--------------------------------------------------------------------------
    |
    | Specify which commands should be tracked. Leave empty to track all commands.
    | Use wildcards like 'check:*' to track all commands starting with 'check:'.
    |
    */

    'tracked_commands' => [
        // 'check:*',
        // 'report:*',
        // 'app:*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Commands to Ignore
    |--------------------------------------------------------------------------
    |
    | Specify which commands should be ignored from tracking.
    |
    */

    'ignored_commands' => [
        'schedule:run',
        'queue:work',
        'queue:listen',
        'migrate:*',
        'db:seed',
        'config:cache',
        'route:cache',
        'view:cache',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure notifications for failed command executions.
    |
    */

    'notify_on_failure' => env('COMMANDER_NOTIFY_ON_FAILURE', false),
    'notification_channels' => [
        'mail',
        // 'slack',
        // 'discord',
    ],
]; 