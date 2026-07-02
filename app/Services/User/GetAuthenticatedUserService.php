<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GetAuthenticatedUserService
{
    /**
     * Get the currently authenticated user.
     */
    public function execute(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user;
    }
}
