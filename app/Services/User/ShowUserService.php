<?php

namespace App\Services\User;

use App\Models\User;

class ShowUserService
{
    /**
     * Show a single user.
     */
    public function execute(User $user): User
    {
        return $user->load('department');
    }
}
