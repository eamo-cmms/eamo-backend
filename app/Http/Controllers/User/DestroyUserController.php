<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\User\DestroyUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Gate;

class DestroyUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, User $user, DestroyUserService $service): JsonResponse
    {
        Gate::authorize('delete', $user);

        $service->execute($user);

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }
}
