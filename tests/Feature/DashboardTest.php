<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Tests\Feature;

use LaravelPlus\Commander\Tests\TestCase;

final class DashboardTest extends TestCase
{
    public function test_dashboard_api_returns_stats(): void
    {
        $response = $this->get('/commander/api/dashboard');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'total_commands',
                'total_executions',
                'successful_executions',
                'failed_executions',
                'scheduled_commands',
                'success_rate',
                'avg_execution_time',
                'recent_activity',
            ],
        ]);
    }

    public function test_popular_commands_api_returns_data(): void
    {
        $response = $this->get('/commander/api/popular');

        $response->assertStatus(200);

        // Since there's no data, we expect an empty array
        $data = $response->json();
        $this->assertIsArray($data);
    }

    public function test_recent_executions_api_returns_data(): void
    {
        $response = $this->get('/commander/api/recent');

        $response->assertStatus(200);

        // Since there's no data, we expect an empty array
        $data = $response->json();
        $this->assertIsArray($data);
    }
}
