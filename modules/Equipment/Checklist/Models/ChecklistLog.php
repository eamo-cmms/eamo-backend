<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Models;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $checklist_schedule_id
 * @property 'pending'|'completed' $status
 * @property 'pass'|'fail'|null $result
 * @property CarbonImmutable|null $checked_at
 * @property string $created_at
 * @property string $updated_at
 */
final class ChecklistLog extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'eamo_checklist_logs';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'checklist_schedule_id',
        'status',
        'result',
        'checked_at',
    ];

    protected $casts = [
        'checked_at' => 'immutable_datetime',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array<string>
     */
    protected $with = [
        'users',
    ];

    /**
     * Get the checklist schedule that owns the log.
     */
    public function checklistSchedule(): BelongsTo
    {
        return $this->belongsTo(ChecklistSchedule::class, 'checklist_schedule_id');
    }

    /**
     * The users associated with the checklist log.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'eamo_checklist_log_users',
            'checklist_log_id',
            'user_id'
        )->wherePivotNull('deleted_at');
    }
}
