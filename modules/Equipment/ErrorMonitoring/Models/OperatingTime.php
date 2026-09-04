<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class OperatingTime
 *
 * @property string $id
 * @property string $equipment_id
 * @property string|null $equipment_name
 * @property float $working_time
 * @property float $planned_stop_time
 * @property float $unplanned_stop_time
 * @property float $planned_operating_time
 * @property float $actual_operating_time
 * @property float $availability_factor
 * @property CarbonImmutable $start_time
 * @property CarbonImmutable $end_time
 * @property CarbonImmutable|null $date
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Equipment $equipment
 */
final class OperatingTime extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [

        'equipment_id',
        'equipment_name',
        'working_time',
        'planned_stop_time',
        'unplanned_stop_time',
        'planned_operating_time',
        'actual_operating_time',
        'availability_factor',
        'start_time',
        'end_time',
        'date',
    ];

    protected $table = 'eamo_operating_times';

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        self::saving(function (self $model) {
            $overlapExists = self::query()
                ->where('equipment_id', $model->equipment_id)
                ->where('start_time', '<', $model->end_time)
                ->where('end_time', '>', $model->start_time)
                ->when($model->exists, function ($query) use ($model) {
                    $query->where('id', '!=', $model->id);
                })
                ->exists();

            if ($overlapExists) {
                throw new \InvalidArgumentException('The operating time overlaps with an existing operating time for this equipment.');
            }
        });
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(\Modules\Masterdata\Equipment\Models\Equipment::class, 'equipment_id');
    }

    public function calculateMetrics(): self
    {
        if ($this->start_time && $this->end_time) {
            $diffInMinutes = $this->start_time->diffInMinutes($this->end_time);
            $this->working_time = round($diffInMinutes / 60.0, 2);

            $plannedStopTime = (float) ($this->planned_stop_time ?? 0);
            $unplannedStopTime = (float) ($this->unplanned_stop_time ?? 0);

            $this->planned_operating_time = max(0.0, $this->working_time - $plannedStopTime);
            $this->actual_operating_time = max(0.0, $this->planned_operating_time - $unplannedStopTime);

            $this->availability_factor = $this->planned_operating_time > 0
                ? round(($this->actual_operating_time / $this->planned_operating_time) * 100, 2)
                : 0.0;
        }

        return $this;
    }
}
