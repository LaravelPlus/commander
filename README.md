# LaravelPlus Commander

[![Tests](https://github.com/laravelplus/commander/actions/workflows/tests.yml/badge.svg)](https://github.com/laravelplus/commander/actions)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/laravelplus/commander.svg)](https://packagist.org/packages/laravelplus/commander)
[![Total Downloads](https://img.shields.io/packagist/dt/laravelplus/commander.svg)](https://packagist.org/packages/laravelplus/commander)
[![Monthly Downloads](https://img.shields.io/packagist/dm/laravelplus/commander.svg)](https://packagist.org/packages/laravelplus/commander)
[![License](https://img.shields.io/github/license/laravelplus/commander.svg)](https://github.com/laravelplus/commander/blob/main/LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/laravelplus/commander.svg)](https://packagist.org/packages/laravelplus/commander)
[![Laravel Version](https://img.shields.io/badge/Laravel-12.x%2B-brightgreen.svg)](https://laravel.com)
[![Code Style](https://img.shields.io/badge/code%20style-PSR--12-brightgreen.svg)](https://www.php-fig.org/psr/psr-12/)
[![Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen.svg)](https://github.com/laravelplus/commander)
[![PHP 8.4+](https://img.shields.io/badge/PHP-8.4%2B-blue.svg)](https://php.net)
[![Vue 3](https://img.shields.io/badge/Vue-3.x-green.svg)](https://vuejs.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-CSS-38B2AC.svg)](https://tailwindcss.com)

A modern Laravel package for tracking and managing command executions with detailed analytics, beautiful UI, and comprehensive command management features.

## 📋 Requirements

- **PHP**: 8.4 or higher
- **Laravel**: 12.x or higher
- **Vue.js**: 3.x (included via CDN)
- **Tailwind CSS**: 3.x (included via CDN)

## 📦 Installation

### Production Installation

For production projects, install via Composer:

```bash
composer require laravelplus/commander
```

### Development Installation

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

### Setup Steps

1. **Publish migrations and config:**
```bash
php artisan vendor:publish --tag=commander-migrations
php artisan vendor:publish --tag=commander-config
```

2. **Run migrations:**
```bash
php artisan migrate
```

3. **Access the interface:**
   - Visit: `http://your-app.com/admin/commander`
   - Default URL can be configured in `config/commander.php`

## ⚙️ Configuration

The package is highly configurable through the `config/commander.php` file:

### Basic Configuration

```php
return [
    'enabled' => env('COMMANDER_ENABLED', true),
    
    // Interface Configuration
    'url' => env('COMMANDER_URL', 'admin/commander'),
    'route_prefix' => env('COMMANDER_ROUTE_PREFIX', 'admin'),
    'route_name_prefix' => env('COMMANDER_ROUTE_NAME_PREFIX', 'commander'),
    'middleware' => env('COMMANDER_MIDDLEWARE', ['auth', 'web']),
    
    // Tracking Configuration
    'track_output' => env('COMMANDER_TRACK_OUTPUT', true),
    'track_arguments' => env('COMMANDER_TRACK_ARGUMENTS', true),
    'track_options' => env('COMMANDER_TRACK_OPTIONS', true),
    'track_execution_time' => env('COMMANDER_TRACK_EXECUTION_TIME', true),
    'track_user' => env('COMMANDER_TRACK_USER', true),
    
    // Storage Configuration
    'max_output_length' => env('COMMANDER_MAX_OUTPUT_LENGTH', 10000),
    'retention_days' => env('COMMANDER_RETENTION_DAYS', 90),
];
```

### Command Management Configuration

```php
// Commands to ignore from tracking (still appear in interface)
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

// Commands to exclude from interface (completely hidden)
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

// Commands that appear but cannot be executed (read-only)
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
```

## 🧪 Testing

The package includes comprehensive test coverage with 54 tests and 166 assertions:

```bash
# Run all tests
php vendor/bin/phpunit

# Run specific test suites
php vendor/bin/phpunit --testsuite=Unit
php vendor/bin/phpunit --testsuite=Feature

# Run with coverage report
php vendor/bin/phpunit --coverage-html coverage/
```

### Test Coverage

- ✅ **Unit Tests**: 51 tests covering all action classes, controllers, and services
- ✅ **Feature Tests**: 3 tests covering API endpoints and dashboard functionality
- ✅ **Database Tests**: Full database testing with SQLite in-memory database
- ✅ **Mock Testing**: Comprehensive mocking of dependencies
- ✅ **Error Handling**: Tests for all error scenarios and edge cases
- ✅ **PHPUnit 11**: Latest testing framework with improved performance

### Test Structure

```
tests/
├── Feature/
│   └── DashboardTest.php          # API endpoint tests
├── Unit/
│   ├── Http/
│   │   ├── Actions/
│   │   │   ├── BaseActionTest.php
│   │   │   ├── GetCommandsActionTest.php
│   │   │   ├── RunCommandActionTest.php
│   │   │   ├── GetDashboardStatsActionTest.php
│   │   │   ├── RetryCommandActionTest.php
│   │   │   ├── ShowDashboardActionTest.php
│   │   │   ├── GetRecentExecutionsActionTest.php
│   │   │   └── SearchCommandsActionTest.php
│   │   └── Controllers/
│   │       └── CommandsControllerTest.php
│   └── TestCase.php               # Base test case
└── phpunit.xml                    # Test configuration
```

## �� URL Configuration

You can customize the commander interface URL by setting the `COMMANDER_URL` environment variable:

```env
# Default: http://127.0.0.1:8000/admin/commander
COMMANDER_URL=admin/commander

# Custom URL examples:
COMMANDER_URL=admin/tools/commands
COMMANDER_URL=management/command-center
COMMANDER_URL=system/command-executor
```

## ✨ Features

### 🎯 Core Functionality
- ✅ **Command Execution Tracking**: Track when commands were last run with detailed metrics
- ✅ **Execution History**: View detailed history of all command executions with search and filtering
- ✅ **Performance Analytics**: Track execution times, success rates, and performance trends
- ✅ **User Tracking**: Track which user executed each command with audit trail
- ✅ **Real-time Interface**: Modern Vue 3 powered interface with real-time updates

### 🎨 User Interface
- ✅ **Beautiful Modern UI**: Clean, responsive design with Tailwind CSS
- ✅ **Command Arguments Support**: Dynamic forms for commands with arguments and options
- ✅ **Confirmation Dialogs**: Safety-first approach with confirmation before execution
- ✅ **Improved Modals**: Better modal design that doesn't get cut off
- ✅ **Visual Indicators**: Clear status indicators for command states
- ✅ **Responsive Design**: Works perfectly on desktop, tablet, and mobile

### 🔧 Command Management
- ✅ **Disabled Commands**: Mark commands as disabled (read-only) in the interface
- ✅ **Excluded Commands**: Completely hide commands from the interface
- ✅ **Pattern Matching**: Support for wildcard patterns in command filtering
- ✅ **Command Categories**: Organize commands by namespace/category
- ✅ **Search & Filter**: Powerful search and filtering capabilities

### 📊 Analytics & Monitoring
- ✅ **Dashboard Statistics**: Overview of command execution metrics
- ✅ **Activity Monitoring**: Real-time activity feed with filtering
- ✅ **Failed Command Tracking**: Dedicated interface for failed commands
- ✅ **Retry Functionality**: Retry failed commands with original parameters
- ✅ **Schedule Management**: Interface for managing scheduled commands

### ⚙️ Configuration & Security
- ✅ **Highly Configurable**: Extensive configuration options
- ✅ **Security Features**: CSRF protection, user authentication
- ✅ **Output Storage**: Store command output with configurable limits
- ✅ **Retention Policy**: Automatic cleanup of old execution records
- ✅ **Environment Support**: Different settings for different environments

## 🎯 Usage

### Accessing the Interface

Once installed, you can access the commander interface at:

```
http://your-app.com/admin/commander
```

### Interface Features

#### 📋 Command List
- View all available commands with their descriptions
- See last execution time and status
- Filter by category or search by name
- Execute commands with confirmation dialog

#### ⚙️ Command Arguments
- Dynamic forms for commands with arguments
- Support for required and optional arguments
- Default value handling
- Option flags and values

#### 🔄 Execution History
- Detailed execution history for each command
- Success/failure status tracking
- Execution time and output storage
- User tracking for audit purposes

#### 📊 Analytics Dashboard
- Overview of command execution metrics
- Success rate statistics
- Popular commands tracking
- Recent activity feed

#### ❌ Failed Commands
- Dedicated interface for failed commands
- Retry functionality with original parameters
- Error analysis and debugging
- Bulk retry operations

#### ⏰ Schedule Management
- Interface for managing scheduled commands
- Next run time calculations
- Schedule status monitoring
- Manual execution of scheduled commands

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

## 🔌 API Endpoints

The package provides several API endpoints for managing command executions:

### Get Commands with History

```http
GET /admin/commander/api/list
```

Returns all commands with their last execution time and statistics.

### Get Command History

```http
GET /admin/commander/api/{commandName}/history
```

Returns paginated history of executions for a specific command.

### Get Command Statistics

```http
GET /admin/commander/api/{commandName}/stats
```

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Setup

```bash
# Clone the repository
git clone https://github.com/laravelplus/commander.git

# Install dependencies
composer install

# Run tests
php vendor/bin/phpunit

# Run code style checks
./vendor/bin/pint
```

### Pull Request Process

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass (`php vendor/bin/phpunit`)
6. Commit your changes (`git commit -m 'Add some amazing feature'`)
7. Push to the branch (`git push origin feature/amazing-feature`)
8. Open a Pull Request

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Acknowledgments

- Built with [Laravel 12+](https://laravel.com)
- UI powered by [Vue 3](https://vuejs.org)
- Styled with [Tailwind CSS](https://tailwindcss.com)
- Testing with [PHPUnit](https://phpunit.de)
- Requires [PHP 8.4+](https://php.net)

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/laravelplus/commander/issues)
- **Discussions**: [GitHub Discussions](https://github.com/laravelplus/commander/discussions)
- **Documentation**: [Wiki](https://github.com/laravelplus/commander/wiki)

---

**Made with ❤️ by the LaravelPlus Team**