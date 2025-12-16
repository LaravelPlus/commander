<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Tests\Unit\Http\Actions;

use Illuminate\View\View;
use LaravelPlus\Commander\Http\Actions\ShowDashboardAction;
use LaravelPlus\Commander\Tests\TestCase;

final class ShowDashboardActionTest extends TestCase
{
    private ShowDashboardAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ShowDashboardAction();
    }

    public function test_execute_returns_dashboard_view(): void
    {
        $response = $this->action->execute();

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('commander::commands.dashboard', $response->getName());
    }

    public function test_execute_returns_view_with_correct_data(): void
    {
        $response = $this->action->execute();

        $this->assertInstanceOf(View::class, $response);
        $this->assertIsArray($response->getData());
    }
}
