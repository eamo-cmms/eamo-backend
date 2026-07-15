<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Models;

use App\Concerns\HasDefaultRouteBinding;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
// use Modules\Masterdata\Checklist\Infrastructure\Models\Checklist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Modules\Equipment\Checklist\Builders\ChecklistDetailQueryBuilder;

/**
 * @property string $id
 * @property string $checklist_id
 * @property string $session_id
 * @property string $description
 * @property string $result
 * @property string $created_at
 * @property string $updated_at
 */
final class ChecklistDetail extends Model
{
    use HasDefaultRouteBinding, HasUuids;

    /**
     * @param  QueryBuilder  $query
     * @return ChecklistDetailQueryBuilder<ChecklistDetail>
     */
    public function newEloquentBuilder($query): ChecklistDetailQueryBuilder
    {
        return new ChecklistDetailQueryBuilder($query);
    }

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'eamo_checklist_details';

    protected $fillable = [
        'checklist_id',
        'session_id',
        'description',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array<string>
     */
    protected $with = [
        'logs',
    ];

    // public function checklist(): BelongsTo
    // {
    //     return $this->belongsTo(Checklist::class, 'checklist_id');
    // }
    public function session()
    {
        return $this->belongsTo(ChecklistSession::class, 'session_id'); // ✅ CORRECT
    }

    /**
     * Get the logs for the checklist detail.
     */
    public function logs()
    {
        return $this->hasMany(ChecklistLog::class, 'checklist_detail_id');
    }
}
