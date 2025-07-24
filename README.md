# LaravelPlus Commander

A Laravel package for tracking and managing command executions with detailed analytics and history.

## Features

- ✅ **Command Execution Tracking**: Track when commands were last run
- ✅ **Execution History**: View detailed history of all command executions
- ✅ **Performance Analytics**: Track execution times and success rates
- ✅ **User Tracking**: Track which user executed each command
- ✅ **Configurable**: Highly configurable tracking settings
- ✅ **Output Storage**: Store command output with configurable limits
- ✅ **Pattern Matching**: Support for wildcard patterns in command filtering
- ✅ **Retention Policy**: Automatic cleanup of old execution records
- ✅ **Configurable URL**: Customize the interface URL

## Installation

### Option 1: Production Installation (Recommended)

For production projects, install via Composer:

```bash
composer require laravelplus/commander
```

### Option 2: Development Installation

For development or local projects, add to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/laravelplus/commander"
        }
    ],
    "require": {
        "laravelplus/commander": "*"
    }
}
```

Then run:
```bash
composer install
```

### 3. Publish migrations and config

```bash
php artisan vendor:publish --tag=commander-migrations
php artisan vendor:publish --tag=commander-config
```

### 4. Run migrations

```bash
php artisan migrate
```

## Configuration

The package is highly configurable through the `config/commander.php` file:

```php
return [
    'enabled' => env('COMMANDER_ENABLED', true),
    
    // Interface Configuration
    'url' => env('COMMANDER_URL', 'admin/commander'),
    'route_prefix' => env('COMMANDER_ROUTE_PREFIX', 'admin'),
    'route_name_prefix' => env('COMMANDER_ROUTE_NAME_PREFIX', 'commander'),
    
    'track_output' => env('COMMANDER_TRACK_OUTPUT', true),
    'track_arguments' => env('COMMANDER_TRACK_ARGUMENTS', true),
    'track_options' => env('COMMANDER_TRACK_OPTIONS', true),
    'track_execution_time' => env('COMMANDER_TRACK_EXECUTION_TIME', true),
    'track_user' => env('COMMANDER_TRACK_USER', true),
    'max_output_length' => env('COMMANDER_MAX_OUTPUT_LENGTH', 10000),
    'retention_days' => env('COMMANDER_RETENTION_DAYS', 90),
    'ignored_commands' => [
        'schedule:run',
        'queue:work',
        'migrate:*',
    ],
];
```

## URL Configuration

You can customize the commander interface URL by setting the `COMMANDER_URL` environment variable:

```env
# Default: http://127.0.0.1:8000/admin/commander
COMMANDER_URL=admin/commander

# Custom URL examples:
COMMANDER_URL=admin/tools/commands
COMMANDER_URL=management/command-center
COMMANDER_URL=system/command-executor
```

## Usage

### Basic Usage

The package automatically tracks command executions when used with the enhanced CommandsController:

```php
use LaravelPlus\Commander\Http\Controllers\CommandsController;

$controller = new CommandsController();
$controller->setCommands($yourCommandsArray);
```

### Helper Functions

The package provides helper functions for easy access:

```php
// Generate commander URL
$url = commander_url(); // http://127.0.0.1:8000/admin/commander
$url = commander_url('list'); // http://127.0.0.1:8000/admin/commander/list

// Generate commander routes
$route = commander_route('index'); // commander.index
$route = commander_route('run', ['command' => 'test']); // commander.run

// Check if commander is enabled
if (commander_enabled()) {
    // Commander functionality is available
}
```

### Manual Tracking

You can also manually track command executions:

```php
use LaravelPlus\Commander\Traits\TracksCommandExecution;

class YourController extends Controller
{
    use TracksCommandExecution;

    public function runCommand(Request $request)
    {
        $commandName = 'check:password-expiry';
        $arguments = ['--batch-size=10'];
        $options = ['--delay=2'];

        // Start tracking
        $this->startTracking($commandName, $arguments, $options);

        try {
            // Execute your command
            $result = $this->executeCommand($commandName, $arguments, $options);
            
            // Complete tracking with success
            $this->completeTracking(true, 0, $result['output']);
            
            return response()->json($result);
        } catch (Exception $e) {
            // Complete tracking with failure
            $this->completeTracking(false, 1, $e->getMessage());
            throw $e;
        }
    }
}
```

### Querying Execution Data

```php
use LaravelPlus\Commander\Models\CommandExecution;

// Get last execution time for a command
$lastExecution = CommandExecution::getLastExecutionTime('check:password-expiry');

// Get execution statistics
$stats = CommandExecution::getCommandStats('check:password-expiry', 30);

// Get recent executions
$recentExecutions = CommandExecution::recent(7)->get();

// Get failed executions
$failedExecutions = CommandExecution::failed()->get();
```

## API Endpoints

The package provides several API endpoints for managing command executions:

### Get Commands with History

```http
GET /admin/commander
```

Returns all commands with their last execution time and statistics.

### Get Command History

```http
GET /admin/commander/{commandName}/history
```

Returns paginated history of executions for a specific command.

### Get Command Statistics

```http
GET /admin/commander/{commandName}/stats
```

Returns detailed statistics for a command including success rate, average execution time, etc.

### Run Command with Tracking

```http
POST /admin/commander/run
{
    "command": "check:password-expiry",
    "arguments": ["--batch-size=10"],
    "options": ["--delay=2"]
}
```

Executes a command and tracks the execution.

## Database Schema

The package creates a `command_executions` table with the following structure:

```sql
CREATE TABLE command_executions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    command_name VARCHAR(255) NOT NULL,
    arguments TEXT NULL,
    options TEXT NULL,
    output TEXT NULL,
    return_code INT DEFAULT 0,
    success BOOLEAN DEFAULT TRUE,
    executed_by VARCHAR(255) NULL,
    environment VARCHAR(255) DEFAULT 'production',
    execution_time DECIMAL(8,3) NULL,
    started_at TIMESTAMP NOT NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_command_started (command_name, started_at),
    INDEX idx_success_started (success, started_at),
    INDEX idx_executed_by_started (executed_by, started_at)
);
```

## Environment Variables

```env
# Enable/Disable Commander
COMMANDER_ENABLED=true

# URL Configuration
COMMANDER_URL=admin/commander
COMMANDER_ROUTE_PREFIX=admin
COMMANDER_ROUTE_NAME_PREFIX=commander

# Tracking Configuration
COMMANDER_TRACK_OUTPUT=true
COMMANDER_TRACK_ARGUMENTS=true
COMMANDER_TRACK_OPTIONS=true
COMMANDER_TRACK_EXECUTION_TIME=true
COMMANDER_TRACK_USER=true
COMMANDER_MAX_OUTPUT_LENGTH=10000
COMMANDER_RETENTION_DAYS=90
COMMANDER_NOTIFY_ON_FAILURE=false
```

## Maintenance

### Cleanup Old Records

The package includes a command to clean up old execution records:

```bash
php artisan commander:cleanup
```

### Retention Policy

Records older than the configured retention period (default: 90 days) are automatically cleaned up.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## License

This package is open-sourced software licensed under the [MIT license](LICENSE). 