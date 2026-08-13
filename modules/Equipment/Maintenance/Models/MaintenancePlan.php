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
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Masterdata\Equipment\Models\Equipment;
use Modules\Masterdata\Equipment\Models\EquipmentError;
use Dyrynda\Database\Support\CascadeSoftDeletes;

/**
 * Class MaintenancePlan
 *
 * @property string $id
 * @property string $plan_code
 * @property string $maintenance_type
 * @property string $equipment_id
 * @property string $notes
 * @property string $maintenance_category_id
 * @property CarbonImmutable|null $date
 * @property CarbonImmutable|null $start_time
 * @property CarbonImmutable|null $end_time
 * @property CarbonImmutable|null $actual_start_time
 * @property CarbonImmutable|null $actual_end_time
 * @property string|null $cycle_type
 * @property int $cycle_interval
 * @property int|null $occurrences
 * @property string $created_at
 * @property string $updated_at
 */
final class MaintenancePlan extends Model
{
    protected static function boot(): void
    {
        parent::boot();

        self::saved(function (self $plan) {
            if ($plan->actual_end_time && $plan->user_id) {
                $equipment = $plan->equipment;
                if ($equipment) {
                    $dateStr = $plan->date;
                    if ($dateStr instanceof \DateTimeInterface) {
                        $dateStr = $dateStr->format('Y-m-d');
                    }
                    $datetime = $dateStr
                        ? trim($dateStr.' '.$plan->actual_end_time)
                        : now()->toDateTimeString();

                    $equipment->update([
                        'last_maintenance' => [
                            'equipment_id' => $plan->equipment_id,
                            'maintenance_plan_id' => $plan->id,
                            'datetime' => $datetime,
                            'user_id' => $plan->user_id,
                        ],
                    ]);
                }
            }
        });
    }

    use CascadeSoftDeletes, HasDefaultRouteBinding, HasUuids, SoftDeletes;

    protected array $cascadeDeletes = ['maintenanceSchedule'];

    protected $fillable = [
        'equipment_id',
        'date',
        'plan_code',
        'start_time',
        'actual_start_time',
        'end_time',
        'actual_end_time',
        'cycle_type',
        'cycle_interval',
        'occurrences',
        'notes',
        'user_id',
        'maintenance_type',
        'maintenance_category_id',
    ];

    protected $appends = [
        'schedule_mode',
    ];

    public function getScheduleModeAttribute(): string
    {
        return empty($this->attributes['cycle_type'] ?? null) ? 'single' : 'repeating';
    }

    protected $casts = [
        // 'occurred_at' => 'immutable_datetime',
        // 'restarted_at' => 'immutable_datetime',
        // 'handled_at' => 'immutable_datetime',
    ];

    /**
     * @return BelongsTo<Equipment, $this>
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * @return BelongsTo<EquipmentError, $this>
     */
    public function equipmentError(): BelongsTo
    {
        return $this->belongsTo(EquipmentError::class, 'equipment_error_id', 'id');
    }

    public function maintenanceCategory(): BelongsTo
    {
        return $this->belongsTo(MaintenanceCategory::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'eamo_maintenance_plan_user',
            'maintenance_plan_id',
            'user_id'
        )->wherePivotNull('deleted_at');
    }

    public function maintenanceSchedule()
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'eamo_maintenance_plans';
}
