<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Models;

use App\Concerns\HasDefaultRouteBinding;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $id
 * @property string $checklist_detail_id
 * @property string $result
 * @property string $created_at
 * @property string $updated_at
 */
final class ChecklistLog extends Model
{
    use HasDefaultRouteBinding, HasUuids;

    protected $table = 'eamo_checklist_logs';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'checklist_detail_id',
        'result',
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
     * Get the checklist detail that owns the log.
     */
    public function checklistDetail(): BelongsTo
    {
        return $this->belongsTo(ChecklistDetail::class, 'checklist_detail_id');
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
        );
    }
}
