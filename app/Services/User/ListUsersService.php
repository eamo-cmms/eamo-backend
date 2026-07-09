<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListUsersService
{
    /**
     * Get a list of all users.
     *
     * @param int|null $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function execute(?int $perPage = null, array $filters = []): LengthAwarePaginator
    {
        $query = User::with('department.company')->filter($filters);

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage ?? 10);
    }
}
