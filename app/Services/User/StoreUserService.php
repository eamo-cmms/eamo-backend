<?php

namespace App\Services\User;

use App\Enums\UserRole;
use App\Models\User;

class StoreUserService
{
    /**
     * Store a new user.
     *
     * @param  array{name: string, email: string, password: string, department_id?: string|null, role?: UserRole|string|null}  $data
     */
    public function execute(array $data): User
    {
        $data['role'] = $data['role'] ?? UserRole::User;

        return User::create($data);
    }
}
