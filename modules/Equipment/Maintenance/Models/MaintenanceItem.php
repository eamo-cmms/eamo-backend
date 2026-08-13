<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use Dyrynda\Database\Support\CascadeSoftDeletes;

/**
 * @property string $id
 * @property string $name
 * @property string|null $description
 * @property string $maintenance_category_id
 * @property string $created_at
 * @property string $updated_at
 * @property-read Collection<int, User> $users
 */
class MaintenanceItem extends Model
{
    protected $table = 'eamo_maintenance_items';

    use CascadeSoftDeletes, HasUuids, SoftDeletes;

    protected array $cascadeDeletes = ['maintenanceSchedules'];

    protected $fillable = [
        'name',
        'description',
        'maintenance_category_id',
    ];

    public function maintenanceCategory(): BelongsTo
    {
        return $this->belongsTo(MaintenanceCategory::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'eamo_maintenance_item_user',
            'maintenance_item_id',
            'user_id'
        )->wherePivotNull('deleted_at');
    }

    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class, 'maintenance_item_id');
    }
}
