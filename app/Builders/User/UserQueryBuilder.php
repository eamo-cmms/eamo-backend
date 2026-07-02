<?php

namespace App\Builders\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<User>
 */
class UserQueryBuilder extends Builder
{
    /**
     * Scope a query to only include active users.
     */
    public function whereActive(): self
    {
        return $this->where('is_active', true);
    }
}
