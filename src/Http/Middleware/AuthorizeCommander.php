<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class AuthorizeCommander
{
    /**
     * Deny (404) unless the host app defines the gate ability and it allows the user.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ability = config('commander.gate', 'viewCommander');

        abort_unless(Gate::has($ability) && Gate::allows($ability), 404);

        return $next($request);
    }
}
