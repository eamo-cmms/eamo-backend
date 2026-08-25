<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Enums\UserRole;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property \App\Enums\UserRole|string|null $role
 */
trait HasPermissions
{
    /**
     * Default permissions matrix by role (used when user has no explicit custom overrides).
     *
     * @var array<string, array<int, string>>
     */
    public const DEFAULT_ROLE_PERMISSIONS = [
        'manager' => [
            'company.create', 'company.update', 'company.delete',
            'department.create', 'department.update', 'department.delete',
            'user.create', 'user.update', 'user.delete',
            'equipment.create', 'equipment.update', 'equipment.delete',
            'equipment.mark_maintenance', 'equipment.update_parent', 'equipment.update_errors',
            'equipment_category.manage', 'equipment_parameter.manage', 'equipment_error.manage', 'equipment_state.manage', 'unit.manage',
            'checklist_session.create', 'checklist_session.update', 'checklist_session.delete', 'checklist_session.judge',
            'checklist_detail.manage', 'checklist_schedule.complete', 'checklist_schedule.delete_daily',
            'maintenance_plan.create', 'maintenance_plan.update', 'maintenance_plan.delete', 'maintenance_plan.judge',
            'maintenance_schedule.update', 'maintenance_schedule.complete', 'maintenance_schedule.delete',
            'maintenance_log.create', 'maintenance_log.update', 'maintenance_log.delete',
            'maintenance_category.manage', 'maintenance_item.manage',
            'equipment_error_log.create', 'equipment_error_log.update', 'equipment_error_log.delete',
            'operating_time.create', 'operating_time.update', 'operating_time.delete', 'operating_time.import',
            'equipment_parameter_log.create', 'equipment_parameter_log.update', 'equipment_parameter_log.delete', 'equipment_parameter_log.import', 'equipment_parameter_log.save',
        ],
        'engineer' => [
            'equipment.create', 'equipment.update', 'equipment.delete',
            'equipment.mark_maintenance', 'equipment.update_parent', 'equipment.update_errors',
            'equipment_category.manage', 'equipment_parameter.manage', 'equipment_error.manage', 'equipment_state.manage', 'unit.manage',
            'checklist_session.create', 'checklist_session.update', 'checklist_session.delete', 'checklist_session.judge',
            'checklist_detail.manage', 'checklist_schedule.complete', 'checklist_schedule.delete_daily',
            'maintenance_plan.create', 'maintenance_plan.update', 'maintenance_plan.delete', 'maintenance_plan.judge',
            'maintenance_schedule.update', 'maintenance_schedule.complete', 'maintenance_schedule.delete',
            'maintenance_log.create', 'maintenance_log.update', 'maintenance_log.delete',
            'maintenance_category.manage', 'maintenance_item.manage',
            'equipment_error_log.create', 'equipment_error_log.update', 'equipment_error_log.delete',
            'operating_time.create', 'operating_time.update', 'operating_time.delete', 'operating_time.import',
            'equipment_parameter_log.create', 'equipment_parameter_log.update', 'equipment_parameter_log.delete', 'equipment_parameter_log.import', 'equipment_parameter_log.save',
        ],
        'user' => [
            'equipment_error_log.create', 'equipment_error_log.update',
            'maintenance_log.create', 'maintenance_log.update',
            'checklist_detail.manage',
        ],
    ];

    /**
     * Get all dynamic permissions assigned to the user.
     *
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user', 'user_id', 'permission_id')
            ->withTimestamps();
    }

    /**
     * Check if the user has a specific permission.
     */
    public function hasPermission(string $permissionCode): bool
    {
        // Admin always has all permissions
        if ($this->hasRole(UserRole::Admin)) {
            return true;
        }

        // Guest never has any mutation permissions
        if ($this->hasRole(UserRole::Guest)) {
            return false;
        }

        // Engineer is strictly forbidden from organization permissions
        if ($this->hasRole(UserRole::Engineer) && (
            str_starts_with($permissionCode, 'company.') ||
            str_starts_with($permissionCode, 'department.') ||
            str_starts_with($permissionCode, 'user.')
        )) {
            return false;
        }

        // 1. If user has explicit permissions assigned in pivot table, strictly evaluate them
        if ($this->relationLoaded('permissions')) {
            if ($this->permissions->isNotEmpty()) {
                return $this->permissions->contains('id', $permissionCode);
            }
        } elseif ($this->permissions()->exists()) {
            return $this->permissions()->where('permissions.id', $permissionCode)->exists();
        }

        // 2. Fallback: Default role permissions when user has no custom permission overrides
        $userRole = $this->role?->value ?? (string) $this->role;
        $defaultPermissions = self::DEFAULT_ROLE_PERMISSIONS[$userRole] ?? [];

        return in_array($permissionCode, $defaultPermissions, true);
    }

    /**
     * Sync permissions assigned to this user.
     *
     * @param  array<int, string>  $permissionCodes
     */
    public function syncPermissions(array $permissionCodes): void
    {
        $this->permissions()->sync($permissionCodes);
    }
}
