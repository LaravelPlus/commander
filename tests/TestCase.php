<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use DatabaseTransactions;

    protected function getPackageProviders($app): array
    {
        return [
            \LaravelPlus\Commander\Providers\CommanderServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('commander.enabled', true);
        $app['config']->set('commander.url', 'commander');
        $app['config']->set('commander.middleware', []);

        // Set app key for testing
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations for the test database
        $this->artisan('migrate');
    }
}
