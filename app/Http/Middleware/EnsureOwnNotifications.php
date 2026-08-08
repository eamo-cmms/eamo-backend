<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnNotifications
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(Response::HTTP_UNAUTHORIZED, 'Unauthorized.');
        }

        // Get user identifier from route parameter (e.g. {user}) or request body 'user_id'
        $routeUser = $request->route('user') ?? $request->input('user_id');

        if ($routeUser) {
            // Support both object binding (User model instance) and raw string/ID
            $routeUserId = is_object($routeUser) ? $routeUser->id : $routeUser;

            if ($user->id !== $routeUserId) {
                abort(Response::HTTP_FORBIDDEN, 'ERR_FORBIDDEN_OWN_NOTIFICATIONS');
            }
        }

        return $next($request);
    }
}
