<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    use HasUuids;

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

    /**
     * @return HasMany<StandardParameter, $this>
     */
    public function standardParameters(): HasMany
    {
        return $this->hasMany(StandardParameter::class);
    }

    protected function casts(): array
    {
        return [];
    }
}
