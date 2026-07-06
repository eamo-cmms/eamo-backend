<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Resources\User\UserResource;
use App\Services\User\StoreUserService;

class StoreUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreUserRequest $request, StoreUserService $service): UserResource
    {
        $user = $service->execute($request->validated());

        return new UserResource($user);
    }
}
