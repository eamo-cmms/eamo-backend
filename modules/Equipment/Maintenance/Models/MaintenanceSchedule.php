<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Models;

use App\Concerns\HasDefaultRouteBinding;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Masterdata\Equipment\Models\Equipment;

/**
 * Class MaintenanceSchedule
 *
 * @property string $id
 * @property string $equipment_id
 * @property string $maintenance_item_id
 * @property string $maintenance_plan_id
 * @property CarbonImmutable $date
 * @property bool $is_rescheduled
 * @property CarbonImmutable|null $original_date
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 */
final class MaintenanceSchedule extends Model
{
    protected $table = 'eamo_maintenance_schedules';

    use HasDefaultRouteBinding, HasUuids;

    protected $fillable = [
        'equipment_id',
        'maintenance_item_id',
        'maintenance_plan_id',
        'date',
        'is_rescheduled',
        'original_date',
    ];

    protected $casts = [
        'is_rescheduled' => 'boolean',
    ];

    public function maintenancePlan(): BelongsTo
    {
        return $this->belongsTo(MaintenancePlan::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function maintenanceItem(): BelongsTo
    {
        return $this->belongsTo(MaintenanceItem::class);
    }

    // public function maintenanceLog(): HasOne
    // {
    //     return $this->hasOne(MaintenanceLog::class, 'maintenance_schedule_id', 'id');
    // }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class, 'maintenance_schedule_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'eamo_maintenance_schedule_user',
            'maintenance_schedule_id',
            'user_id'
        );
    }
}
