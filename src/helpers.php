<?php

declare(strict_types=1);

if (!function_exists('commander_url')) {
    /**
     * Generate the URL for the commander interface.
     *
     * @param string $path
     * @return string
     */
    function commander_url(string $path = ''): string
    {
        $baseUrl = config('commander.url', 'admin/commander');
        return url($baseUrl . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('commander_route')) {
    /**
     * Generate a route name for the commander package.
     *
     * @param string $name
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    function commander_route(string $name, array $parameters = [], bool $absolute = true): string
    {
        $routeName = config('commander.route_name_prefix', 'commander') . '.' . $name;
        return route($routeName, $parameters, $absolute);
    }
}

if (!function_exists('commander_enabled')) {
    /**
     * Check if commander is enabled.
     *
     * @return bool
     */
    function commander_enabled(): bool
    {
        return config('commander.enabled', true);
    }
} 