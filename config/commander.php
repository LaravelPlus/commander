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
    'middleware' => env('COMMANDER_MIDDLEWARE', ['auth', 'web']),

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
    | Commands to Exclude from Interface
    |--------------------------------------------------------------------------
    |
    | Specify which commands should be excluded from the commander interface.
    | These commands won't appear in the list, schedule, or any other views.
    | Use wildcards like 'vendor:*' to exclude all commands starting with 'vendor:'.
    |
    */

    'excluded_commands' => [
        'vendor:*',
        'package:*',
        'tinker',
        'serve',
        'test:*',
        'dusk:*',
        'make:*',
        'stub:*',
        'clear-compiled',
        'down',
        'up',
        'env',
        'key:generate',
        'key:generate',
        'optimize',
        'optimize:clear',
        'config:clear',
        'route:clear',
        'view:clear',
        'cache:clear',
        'cache:forget',
        'cache:table',
        'session:table',
        'queue:table',
        'queue:failed-table',
        'queue:batches-table',
        'notifications:table',
        'event:generate',
        'listener:generate',
        'mail:make',
        'middleware:make',
        'provider:make',
        'request:make',
        'resource:make',
        'rule:make',
        'seeder:make',
        'test:make',
        'channel:make',
        'command:make',
        'component:make',
        'controller:make',
        'event:make',
        'exception:make',
        'factory:make',
        'job:make',
        'listener:make',
        'mail:make',
        'middleware:make',
        'model:make',
        'notification:make',
        'observer:make',
        'policy:make',
        'provider:make',
        'request:make',
        'resource:make',
        'rule:make',
        'seeder:make',
        'test:make',
    ],

    /*
    |--------------------------------------------------------------------------
    | Disabled Commands
    |--------------------------------------------------------------------------
    |
    | Specify which commands should be disabled (read-only) in the interface.
    | These commands will appear in the list but cannot be executed.
    | Use wildcards like 'dangerous:*' to disable all commands starting with 'dangerous:'.
    |
    */

    'disabled_commands' => [
        'migrate:fresh',
        'migrate:refresh',
        'migrate:reset',
        'db:wipe',
        'config:clear',
        'route:clear',
        'view:clear',
        'cache:clear',
        'optimize:clear',
        'event:clear',
        'queue:restart',
        'queue:flush',
        'queue:forget',
        'queue:retry',
        'queue:retry-batch',
        'queue:work',
        'queue:listen',
        'schedule:run',
        'schedule:list',
        'schedule:test',
        'schedule:work',
        'vendor:publish',
        'vendor:install',
        'vendor:update',
        'package:discover',
        'package:clear-cache',
        'package:optimize',
        'package:cache',
        'package:config',
        'package:route',
        'package:view',
        'package:lang',
        'package:migrate',
        'package:seed',
        'package:publish',
        'package:install',
        'package:update',
        'package:uninstall',
        'package:list',
        'package:show',
        'package:outdated',
        'package:update',
        'package:install',
        'package:remove',
        'package:require',
        'package:require-dev',
        'package:update',
        'package:install',
        'package:remove',
        'package:require',
        'package:require-dev',
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
