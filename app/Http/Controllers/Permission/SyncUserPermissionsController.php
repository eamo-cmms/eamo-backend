<?php

declare(strict_types=1);

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\SyncUserPermissionsRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class SyncUserPermissionsController extends Controller
{
    /**
     * Update (sync) the dynamic permissions for a user.
     */
    public function __invoke(SyncUserPermissionsRequest $request, User $user): JsonResponse
    {
        $permissions = (array) $request->validated('permissions', []);

        $user->syncPermissions($permissions);

        return response()->json([
            'message' => 'Permissions updated successfully.',
            'user_id' => $user->id,
            'permissions' => $user->permissions()->pluck('permissions.id'),
        ]);
    }
}
