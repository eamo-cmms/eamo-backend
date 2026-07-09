<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Services\User\ListUsersService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndexUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, ListUsersService $service): AnonymousResourceCollection
    {
        $users = $service->execute($request->integer('per_page', 10));

        return UserResource::collection($users);
    }
}
