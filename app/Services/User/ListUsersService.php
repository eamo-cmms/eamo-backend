<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListUsersService
{
    /**
     * List all users.
     *
     * @param int|null $perPage
     * @return LengthAwarePaginator
     */
    public function execute(?int $perPage = null): LengthAwarePaginator
    {
        return User::with('department.company')->paginate($perPage ?? 10);
    }
}
