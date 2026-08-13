<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Equipment\ParameterLog\Models\EquipmentParameterLog;

/**
 * Class Unit
 *
 * @property string $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 */
final class Unit extends Model
{
    use HasUuids, SoftDeletes;

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'code',
        'description',
    ];

    protected $keyType = 'string';

    protected $table = 'eamo_units';

    /**
     * @return HasMany<EquipmentParameter, $this>
     */
    public function equipmentParameters(): HasMany
    {
        return $this->hasMany(EquipmentParameter::class);
    }

    public function parameterLogs(): HasMany
    {
        return $this->hasMany(EquipmentParameterLog::class, 'unit_id');
    }

    protected static function booted(): void
    {
        static::deleting(function (self $unit): void {
            $unit->equipmentParameters()->update(['unit_id' => null]);
            $unit->parameterLogs()->update(['unit_id' => null]);
        });
    }

    protected function casts(): array
    {
        return [];
    }
}
