<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy extends BasePolicy
{
    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): bool
    {
        // Engineer is strictly forbidden from organization user management
        if ($user->hasRole(UserRole::Engineer) || $this->isGuest($user)) {
            return false;
        }

        return $user->hasPermission('user.create');
    }

    /**
     * Determine whether the user can update the target user.
     */
    public function update(User $user, ?User $targetUser = null): bool
    {
        if ($this->isGuest($user)) {
            return false;
        }

        // Self update is always permitted for any authenticated user
        if ($targetUser !== null && $user->id === $targetUser->id) {
            return true;
        }

        // Updating other users is strictly forbidden for Engineer
        if ($user->hasRole(UserRole::Engineer)) {
            return false;
        }

        if ($targetUser === null) {
            return $user->hasPermission('user.update');
        }

        return $user->hasPermission('user.update');
    }

    /**
     * Determine whether the user can delete the target user.
     */
    public function delete(User $user, ?User $targetUser = null): bool
    {
        // Cannot delete self
        if ($targetUser !== null && $user->id === $targetUser->id) {
            return false;
        }

        // Engineer is strictly forbidden from user deletion
        if ($user->hasRole(UserRole::Engineer) || $this->isGuest($user)) {
            return false;
        }

        return $user->hasPermission('user.delete');
    }
}
