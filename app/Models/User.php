<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Builders\User\UserQueryBuilder;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;
use Modules\Equipment\Checklist\Models\ChecklistSession;
use Modules\Equipment\Maintenance\Models\MaintenancePlan;

#[Fillable(['name', 'email', 'password', 'department_id', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements OAuthenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    /**
     * @param  Builder  $query
     * @return UserQueryBuilder<User>
     */
    public function newEloquentBuilder($query): UserQueryBuilder
    {
        return new UserQueryBuilder($query);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * Check if the user has an exact role.
     */
    public function hasRole(UserRole|string $role): bool
    {
        $roleEnum = is_string($role) ? UserRole::tryFrom($role) : $role;

        return $this->role === $roleEnum;
    }

    /**
     * Check if the user's role meets or exceeds a minimum required role.
     */
    public function atLeastRole(UserRole|string $role): bool
    {
        $roleEnum = is_string($role) ? UserRole::tryFrom($role) : $role;

        if (! $roleEnum || ! $this->role instanceof UserRole) {
            return false;
        }

        return $this->role->atLeast($roleEnum);
    }

    /**
     * Get the department that the user belongs to.
     *
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function checklistSessions(): BelongsToMany
    {
        return $this->belongsToMany(
            ChecklistSession::class,
            'eamo_checklist_session_users',
            'user_id',
            'checklist_session_id'
        );
    }

    public function maintenancePlans(): BelongsToMany
    {
        return $this->belongsToMany(
            MaintenancePlan::class,
            'eamo_maintenance_plan_user',
            'user_id',
            'maintenance_plan_id'
        );
    }
}
