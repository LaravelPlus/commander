<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Tests\Feature;

use Illuminate\Support\Facades\Gate;
use LaravelPlus\Commander\Models\CommandExecution;
use LaravelPlus\Commander\Tests\TestCase;

final class AuthorizationTest extends TestCase
{
    public function test_routes_404_when_gate_denies(): void
    {
        Gate::define('viewCommander', fn ($user = null) => false);

        $this->get('/commander/api/list')->assertNotFound();
        $this->post('/commander/api/run', ['command' => 'help'])->assertNotFound();
        $this->post('/commander/api/retry', ['command' => 'help'])->assertNotFound();
    }

    public function test_retry_respects_allow_list(): void
    {
        $this->postJson('/commander/api/retry', ['command' => 'migrate:fresh'])->assertStatus(403);
    }

    public function test_run_records_a_single_execution(): void
    {
        $this->postJson('/commander/api/run', ['command' => 'help'])->assertOk();

        $this->assertSame(1, CommandExecution::where('command_name', 'help')->count());
    }
}
