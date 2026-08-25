<?php

declare(strict_types=1);

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetUserPermissionsController extends Controller
{
    /**
     * Get the list of dynamic permission IDs assigned to a user.
     */
    public function __invoke(Request $request, User $user): JsonResponse
    {
        $permissions = $user->permissions()->pluck('permissions.id');

        return response()->json([
            'user_id' => $user->id,
            'role' => $user->role?->value ?? (string) $user->role,
            'permissions' => $permissions,
        ]);
    }
}
