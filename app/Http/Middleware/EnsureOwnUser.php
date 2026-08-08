<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnUser
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $parameter = null): Response
    {
        $authUser = $request->user();

        if (! $authUser) {
            abort(Response::HTTP_UNAUTHORIZED, 'Unauthorized.');
        }

        $parameterName = $parameter ?? 'user';
        $targetUser = $request->route($parameterName) ?? $request->route('user_id') ?? $request->input('user_id');

        if ($targetUser !== null) {
            $targetUserId = is_object($targetUser) ? $targetUser->id : (string) $targetUser;

            if ($authUser->id !== $targetUserId) {
                abort(Response::HTTP_FORBIDDEN, 'ERR_FORBIDDEN_OWN_USER');
            }
        }

        return $next($request);
    }
}
