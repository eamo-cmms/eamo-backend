<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Models;

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
 * Class ChecklistSchedule
 *
 * @property string $id
 * @property string $equipment_id
 * @property string $checklist_session_id
 * @property string $checklist_detail_id
 * @property CarbonImmutable $date
 * @property bool $is_rescheduled
 * @property CarbonImmutable|null $original_date
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 */
final class ChecklistSchedule extends Model
{
    use HasDefaultRouteBinding, HasUuids;

    protected $table = 'eamo_checklist_schedules';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'equipment_id',
        'checklist_session_id',
        'checklist_detail_id',
        'date',
        'is_rescheduled',
        'original_date',
    ];

    protected $casts = [
        'is_rescheduled' => 'boolean',
    ];

    public function checklistSession(): BelongsTo
    {
        return $this->belongsTo(ChecklistSession::class, 'checklist_session_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function checklistDetail(): BelongsTo
    {
        return $this->belongsTo(ChecklistDetail::class, 'checklist_detail_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ChecklistLog::class, 'checklist_schedule_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'eamo_checklist_schedule_user',
            'checklist_schedule_id',
            'user_id'
        );
    }
}
