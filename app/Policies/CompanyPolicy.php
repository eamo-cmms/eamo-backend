<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;

class CompanyPolicy extends BasePolicy
{
    /**
     * Determine whether the user can create companies.
     */
    public function create(User $user): bool
    {
        // Engineer is strictly forbidden from organization management
        if ($user->hasRole(UserRole::Engineer) || $this->isGuest($user)) {
            return false;
        }

        return $user->hasPermission('company.create');
    }

    /**
     * Determine whether the user can update the company.
     */
    public function update(User $user, ?Company $company = null): bool
    {
        if ($user->hasRole(UserRole::Engineer) || $this->isGuest($user)) {
            return false;
        }

        return $user->hasPermission('company.update');
    }

    /**
     * Determine whether the user can delete the company.
     */
    public function delete(User $user, ?Company $company = null): bool
    {
        if ($user->hasRole(UserRole::Engineer) || $this->isGuest($user)) {
            return false;
        }

        return $user->hasPermission('company.delete');
    }
}
