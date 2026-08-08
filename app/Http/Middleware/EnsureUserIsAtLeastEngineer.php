<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAtLeastEngineer
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->atLeastRole(UserRole::Engineer)) {
            abort(Response::HTTP_FORBIDDEN, 'ERR_FORBIDDEN_ENGINEER');
        }

        return $next($request);
    }
}
