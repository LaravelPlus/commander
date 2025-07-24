<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Http\Actions;

use Illuminate\View\View;

final class ShowActivityAction
{
    public function execute(): View
    {
        return view('commander::commands.activity');
    }
}
