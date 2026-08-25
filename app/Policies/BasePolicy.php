<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

abstract class BasePolicy
{
    /**
     * Determine whether the user can view any models (Read-only allowed for all authenticated users including Guest).
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model (Read-only allowed for all authenticated users including Guest).
     */
    public function view(?User $user, mixed $model = null): bool
    {
        return true;
    }

    /**
     * Check if the user is a Guest (returns true if Guest).
     */
    protected function isGuest(?User $user): bool
    {
        return $user !== null && $user->hasRole(UserRole::Guest);
    }

    /**
     * Check if the user is at least an Engineer and not a Guest.
     */
    protected function isAtLeastEngineer(User $user): bool
    {
        return ! $this->isGuest($user) && $user->atLeastRole(UserRole::Engineer);
    }

    /**
     * Check if the user is at least a Manager and not a Guest.
     */
    protected function isAtLeastManager(User $user): bool
    {
        return ! $this->isGuest($user) && $user->atLeastRole(UserRole::Manager);
    }
}
