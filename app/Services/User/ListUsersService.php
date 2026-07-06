<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListUsersService
{
    /**
     * List all users.
     *
     * @return Collection<int, User>
     */
    public function execute(): Collection
    {
        return User::with('department')->get();
    }
}
