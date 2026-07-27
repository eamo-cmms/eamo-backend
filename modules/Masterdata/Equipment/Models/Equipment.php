<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Modules\Equipment\Checklist\Models\ChecklistDetail;
use Modules\Equipment\Checklist\Models\ChecklistSession;
use Modules\Equipment\ErrorMonitoring\Models\EquipmentErrorLog;
use Modules\Equipment\ErrorMonitoring\Models\OperatingTime;
use Modules\Equipment\Maintenance\Models\MaintenancePlan;
use Modules\Equipment\ParameterLog\Models\EquipmentParameterLog;
use Modules\Equipment\Services\EquipmentCascadeSoftDeleteService;
use Modules\Masterdata\Equipment\Builders\EquipmentQueryBuilder;

/**
 * Class Equipment
 *
 * @property string $id
 * @property string|null $parent_id
 * @property string|null $name
 * @property string $code
 * @property string|null $equipment_category_id
 * @property string|null $device_id
 * @property int|null $maintenance_interval_hours
 * @property array|null $last_maintenance
 * @property bool $is_active
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 */
final class Equipment extends Model
{
    use HasUuids, SoftDeletes;

    public $incrementing = false;

    protected $fillable = [
        'name',
        'code',
        'parent_id',
        'work_center_id',
        'equipment_category_id',
        'device_id',
        'qr_code_path',
        'maintenance_interval_hours',
        'last_maintenance',
        'is_active',
    ];

    protected $keyType = 'string';

    protected $table = 'eamo_equipment';

    /**
     * @return BelongsTo<EquipmentCategory, $this>
     */
    public function equipmentCategory(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class);
    }

    /**
     * @return BelongsTo<Equipment, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<Equipment, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return HasMany<EquipmentParameter, $this>
     */
    public function equipmentParameters(): HasMany
    {
        return $this->hasMany(EquipmentParameter::class);
    }

    /**
     * @return BelongsToMany<EquipmentError, $this>
     */
    public function equipmentErrors(): BelongsToMany
    {
        return $this->belongsToMany(
            EquipmentError::class,
            'eamo_equipment_error_logs',
            'equipment_id',
            'equipment_error_id'
        )
        ->using(EquipmentErrorDefinitionPivot::class)
        ->wherePivotNull('occurred_at')
        ->wherePivotNull('deleted_at')
        ->withTimestamps();
    }

    /**
     * @return HasOne<EquipmentState, $this>
     */
    public function equipmentState(): HasOne
    {
        return $this->hasOne(EquipmentState::class);
    }

    /**
     * @return HasMany<EquipmentImage, $this>
     */
    public function equipmentImages(): HasMany
    {
        return $this->hasMany(EquipmentImage::class);
    }

    public function checklistSessions(): HasMany
    {
        return $this->hasMany(ChecklistSession::class, 'equipment_id');
    }

    public function checklistDetails(): HasManyThrough
    {
        return $this->hasManyThrough(
            ChecklistDetail::class,
            ChecklistSession::class,
            'equipment_id',
            'session_id',
            'id',
            'id'
        );
    }

    public function maintenancePlans(): HasMany
    {
        return $this->hasMany(MaintenancePlan::class, 'equipment_id');
    }

    public function operatingTimes(): HasMany
    {
        return $this->hasMany(OperatingTime::class, 'equipment_id');
    }

    public function parameterLogs(): HasMany
    {
        return $this->hasMany(EquipmentParameterLog::class, 'equipment_id');
    }

    public function errorLogs(): HasMany
    {
        return $this->hasMany(EquipmentErrorLog::class, 'equipment_id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $equipment): void {
            if (empty($equipment->device_id)) {
                $equipment->device_id = (string) Str::uuid();
            }
        });

        static::deleting(function (self $equipment): bool|null {
            if ($equipment->isForceDeleting()) {
                return null;
            }

            $cascadeService = app(EquipmentCascadeSoftDeleteService::class);
            if ($cascadeService->isDeletingEquipment($equipment)) {
                return null;
            }

            $cascadeService->deleteEquipment($equipment);

            return false;
        });
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'maintenance_interval_hours' => 'integer',
            'last_maintenance' => 'array',
        ];
    }

    /**
     * @param  QueryBuilder  $query
     * @return EquipmentQueryBuilder<Equipment>
     */
    public function newEloquentBuilder($query): EquipmentQueryBuilder
    {
        return new EquipmentQueryBuilder($query);
    }
}
