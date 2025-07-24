<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Contracts;

use Illuminate\Support\Collection;

interface CommandRepositoryInterface
{
    /**
     * Get all available Artisan commands
     */
    public function getAllCommands(): Collection;

    /**
     * Get a specific command by name
     */
    public function getCommand(string $name): ?array;

    /**
     * Get commands by category/namespace
     */
    public function getCommandsByNamespace(string $namespace): Collection;

    /**
     * Search commands by name or description
     */
    public function searchCommands(string $query): Collection;

    /**
     * Get command categories/namespaces
     */
    public function getCommandCategories(): Collection;
}
