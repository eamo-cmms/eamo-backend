<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Engineer = 'engineer';
    case User = 'user';

    /**
     * Get the hierarchy level of the role.
     * Higher level means more privileges.
     */
    public function level(): int
    {
        return match ($this) {
            self::Admin => 4,
            self::Manager => 3,
            self::Engineer => 2,
            self::User => 1,
        };
    }

    /**
     * Check if this role meets or exceeds a minimum role level.
     */
    public function atLeast(UserRole $minimum): bool
    {
        return $this->level() >= $minimum->level();
    }
}
