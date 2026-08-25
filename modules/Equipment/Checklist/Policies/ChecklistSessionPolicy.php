<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Policies\BasePolicy;
use Modules\Equipment\Checklist\Models\ChecklistSession;

class ChecklistSessionPolicy extends BasePolicy
{
    /**
     * Determine whether the user can create checklist sessions.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('checklist_session.create');
    }

    /**
     * Determine whether the user can update the checklist session.
     */
    public function update(User $user, ?ChecklistSession $session = null): bool
    {
        return $user->hasPermission('checklist_session.update');
    }

    /**
     * Determine whether the user can delete the checklist session.
     */
    public function delete(User $user, ?ChecklistSession $session = null): bool
    {
        return $user->hasPermission('checklist_session.delete');
    }

    /**
     * Determine whether the user can judge the checklist session.
     */
    public function judge(User $user, ?ChecklistSession $session = null): bool
    {
        if (! $user->hasPermission('checklist_session.judge')) {
            return false;
        }

        if ($user->hasRole(UserRole::Manager)) {
            return true;
        }

        if ($user->hasRole(UserRole::Engineer)) {
            if ($session === null) {
                return true;
            }

            return $session->users()->where('users.id', $user->id)->exists();
        }

        return false;
    }
}
