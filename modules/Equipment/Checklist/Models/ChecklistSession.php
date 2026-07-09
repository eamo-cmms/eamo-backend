<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Models;

use App\Concerns\HasDefaultRouteBinding;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * @property string $id
 * @property string $equipment_id
 * @property string $session_date
 * @property string $created_by
 * @property string $created_at
 * @property string $updated_at
 */
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Modules\Equipment\Checklist\Builders\ChecklistSessionQueryBuilder;
use Modules\Masterdata\Equipment\Models\Equipment;

final class ChecklistSession extends Model
{
    use HasDefaultRouteBinding, HasUuids;

    /**
     * @param  QueryBuilder  $query
     * @return ChecklistSessionQueryBuilder<ChecklistSession>
     */
    public function newEloquentBuilder($query): ChecklistSessionQueryBuilder
    {
        return new ChecklistSessionQueryBuilder($query);
    }

    protected $table = 'eamo_checklist_sessions';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'session_date',
        'equipment_id',
    ];

    protected $casts = [
        'session_date' => 'date',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function details()
    {
        return $this->hasMany(ChecklistDetail::class, 'session_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'eamo_checklist_session_users',
            'checklist_session_id',
            'user_id'
        );
    }
}
