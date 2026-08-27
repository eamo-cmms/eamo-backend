<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Models;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Modules\Equipment\Maintenance\Builders\MaintenanceLogQueryBuilder;
use Modules\Masterdata\Equipment\Models\Equipment;

/**
 * Class MaintenanceLog
 *
 * @property string $id
 * @property string|null $equipment_id
 * @property string|null $maintenance_schedule_id
 * @property string|null $user_id
 * @property string|null $type
 * @property string|null $note
 * @property CarbonImmutable $log_date
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 */
final class MaintenanceLog extends Model
{
    protected $table = 'eamo_maintenance_logs';

    use HasUuids, SoftDeletes;

    /**
     * @param QueryBuilder $query
     * @return MaintenanceLogQueryBuilder<MaintenanceLog>
     */
    public function newEloquentBuilder($query): MaintenanceLogQueryBuilder
    {
        return new MaintenanceLogQueryBuilder($query);
    }

    protected $fillable = [
        'equipment_id',
        'maintenance_schedule_id',
        'user_id',
        'log_date',
        'note',
        'result',
        'type',
    ];

    protected $casts = [
        'log_date' => 'date',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function maintenanceSchedule(): BelongsTo
    {
        return $this->belongsTo(MaintenanceSchedule::class, 'maintenance_schedule_id');
    }
}
