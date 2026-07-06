<?php

namespace App\Services\User;

use App\Models\User;

class UpdateUserService
{
    /**
     * Update an existing user.
     *
     * @param  array{name?: string, email?: string, password?: string|null, department_id?: string|null, role?: string|null}  $data
     */
    public function execute(User $user, array $data): User
    {
        $user->update(array_filter($data, fn (mixed $value): bool => $value !== null));

        return $user;
    }
}
