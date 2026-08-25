<?php

declare(strict_types=1);

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndexPermissionController extends Controller
{
    /**
     * Get all available permissions grouped by category.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $query = Permission::query();

        if ($request->filled('role')) {
            $role = (string) $request->query('role');
            $query->where(function ($q) use ($role) {
                $q->whereJsonContains('allowed_roles', $role)
                    ->orWhereNull('allowed_roles');
            });
        }

        $permissions = $query->get()->groupBy('group_name');

        return response()->json([
            'data' => $permissions,
        ]);
    }
}
