<?php

namespace App\Services\User;

use App\Models\User;

class StoreUserService
{
    /**
     * Store a new user.
     *
     * @param  array{name: string, email: string, password: string, department_id?: string|null, role?: string|null}  $data
     */
    public function execute(array $data): User
    {
        return User::create($data);
    }
}
