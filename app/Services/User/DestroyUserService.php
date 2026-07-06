<?php

namespace App\Services\User;

use App\Models\User;

class DestroyUserService
{
    /**
     * Delete an existing user.
     */
    public function execute(User $user): void
    {
        $user->delete();
    }
}
