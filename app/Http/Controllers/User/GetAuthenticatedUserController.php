<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Services\User\GetAuthenticatedUserService;
use Illuminate\Http\Response;

class GetAuthenticatedUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(GetAuthenticatedUserService $service): UserResource|Response
    {
        $user = $service->execute();

        if (! $user) {
            return response()->noContent(Response::HTTP_UNAUTHORIZED);
        }

        return new UserResource($user);
    }
}
