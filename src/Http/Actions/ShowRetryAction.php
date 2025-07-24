<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Http\Actions;

use Illuminate\View\View;

final class ShowRetryAction
{
    public function execute(): View
    {
        return view('commander::commands.retry');
    }
}
