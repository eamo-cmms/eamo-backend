<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\User;

class DepartmentPolicy extends BasePolicy
{
    /**
     * Determine whether the user can create departments.
     */
    public function create(User $user): bool
    {
        // Engineer is strictly forbidden from organization management
        if ($user->hasRole(UserRole::Engineer) || $this->isGuest($user)) {
            return false;
        }

        return $user->hasPermission('department.create');
    }

    /**
     * Determine whether the user can update the department.
     */
    public function update(User $user, ?Department $department = null): bool
    {
        if ($user->hasRole(UserRole::Engineer) || $this->isGuest($user)) {
            return false;
        }

        return $user->hasPermission('department.update');
    }

    /**
     * Determine whether the user can delete the department.
     */
    public function delete(User $user, ?Department $department = null): bool
    {
        if ($user->hasRole(UserRole::Engineer) || $this->isGuest($user)) {
            return false;
        }

        return $user->hasPermission('department.delete');
    }
}
