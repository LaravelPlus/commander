<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Repositories;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use LaravelPlus\Commander\Contracts\CommandRepositoryInterface;
use ReflectionClass;

final class CommandRepository implements CommandRepositoryInterface
{
    /**
     * Get all available Artisan commands from the main application
     */
    public function getAllCommands(): Collection
    {
        $commands = collect();

        // Get all registered commands from the main application
        $artisanCommands = Artisan::all();

        foreach ($artisanCommands as $name => $command) {
            if ($this->shouldIncludeCommand($name, $command)) {
                $commands->push([
                    'name' => $name,
                    'description' => $command->getDescription(),
                    'help' => $command->getHelp(),
                    'arguments' => $this->getCommandArguments($command),
                    'options' => $this->getCommandOptions($command),
                    'class' => get_class($command),
                    'disabled' => $this->isCommandDisabled($name),
                ]);
            }
        }

        return $commands->sortBy('name');
    }

    /**
     * Get a specific command by name from the main application
     */
    public function getCommand(string $name): ?array
    {
        $command = Artisan::all()[$name] ?? null;

        if (!$command || !$this->shouldIncludeCommand($name, $command)) {
            return null;
        }

        return [
            'name' => $name,
            'description' => $command->getDescription(),
            'help' => $command->getHelp(),
            'arguments' => $this->getCommandArguments($command),
            'options' => $this->getCommandOptions($command),
            'class' => get_class($command),
            'disabled' => $this->isCommandDisabled($name),
        ];
    }

    /**
     * Get commands by category/namespace from the main application
     */
    public function getCommandsByNamespace(string $namespace): Collection
    {
        return $this->getAllCommands()->filter(fn ($command) => str_starts_with($command['name'], $namespace));
    }

    /**
     * Search commands by name or description in the main application
     */
    public function searchCommands(string $query): Collection
    {
        return $this->getAllCommands()->filter(fn ($command) => str_contains(mb_strtolower($command['name']), mb_strtolower($query)) ||
                   str_contains(mb_strtolower($command['description']), mb_strtolower($query)));
    }

    /**
     * Get command categories/namespaces from the main application
     */
    public function getCommandCategories(): Collection
    {
        return $this->getAllCommands()
            ->map(function ($command) {
                $parts = explode(':', $command['name']);

                return $parts[0] ?? 'general';
            })
            ->unique()
            ->sort()
            ->values();
    }

    /**
     * Check if a command should be included in the list
     * This filters out package-specific commands and only shows main app commands
     */
    private function shouldIncludeCommand(string $name, $command): bool
    {
        $ignoredCommands = config('commander.ignored_commands', []);
        $excludedCommands = config('commander.excluded_commands', []);

        // Always ignore package-specific commands
        $ignoredCommands[] = 'commander:*';
        $ignoredCommands[] = 'package:*';

        // Check ignored commands (for tracking)
        foreach ($ignoredCommands as $pattern) {
            if ($this->matchesPattern($name, $pattern)) {
                return false;
            }
        }

        // Check excluded commands (for interface display)
        foreach ($excludedCommands as $pattern) {
            if ($this->matchesPattern($name, $pattern)) {
                return false;
            }
        }

        // Only include commands that are part of the main application
        // Exclude commands from vendor packages and other packages
        $commandClass = get_class($command);

        return !(str_starts_with($commandClass, 'LaravelPlus\\Commander\\'));
    }

    /**
     * Get command arguments from the main application command
     */
    private function getCommandArguments($command): array
    {
        $arguments = [];

        // Only try to get arguments for Laravel commands
        if (!$command instanceof Command) {
            return $arguments;
        }

        $reflection = new ReflectionClass($command);

        try {
            $signatureProperty = $reflection->getProperty('signature');
            $signatureProperty->setAccessible(true);
            $signature = $signatureProperty->getValue($command);

            // Only parse if signature is not null
            if ($signature && preg_match_all('/\{([^}]+)\}/', $signature, $matches)) {
                foreach ($matches[1] as $match) {
                    $parts = explode('=', $match);
                    $argName = mb_trim($parts[0]);
                    $default = isset($parts[1]) ? mb_trim($parts[1]) : null;

                    $arguments[] = [
                        'name' => $argName,
                        'default' => $default,
                        'required' => $default === null,
                    ];
                }
            }
        } catch (Exception $e) {
            // If we can't access the signature, return empty array
        }

        return $arguments;
    }

    /**
     * Get command options from the main application command
     */
    private function getCommandOptions($command): array
    {
        $options = [];

        // Only try to get options for Laravel commands
        if (!$command instanceof Command) {
            return $options;
        }

        try {
            $reflection = new ReflectionClass($command);
            $optionsProperty = $reflection->getProperty('options');
            $optionsProperty->setAccessible(true);
            $commandOptions = $optionsProperty->getValue($command);

            foreach ($commandOptions as $name => $option) {
                $options[] = [
                    'name' => $name,
                    'shortcut' => $option['shortcut'] ?? null,
                    'description' => $option['description'] ?? '',
                    'default' => $option['default'] ?? null,
                    'required' => $option['required'] ?? false,
                ];
            }
        } catch (Exception $e) {
            // If we can't access the options, return empty array
        }

        return $options;
    }

    /**
     * Check if command name matches a pattern
     */
    private function matchesPattern(string $commandName, string $pattern): bool
    {
        // Convert wildcard pattern to regex
        $regex = str_replace(['*', '?'], ['.*', '.'], $pattern);
        $regex = '/^' . $regex . '$/';

        return (bool) preg_match($regex, $commandName);
    }

    /**
     * Check if a command is disabled
     */
    private function isCommandDisabled(string $commandName): bool
    {
        $disabledCommands = config('commander.disabled_commands', []);

        foreach ($disabledCommands as $pattern) {
            if ($this->matchesPattern($commandName, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
