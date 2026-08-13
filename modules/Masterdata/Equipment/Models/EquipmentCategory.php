<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Models;

use Carbon\CarbonImmutable;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class EquipmentCategory
 *
 * @property string $id
 * @property string $code
 * @property string $name
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 */
final class EquipmentCategory extends Model
{
    use CascadeSoftDeletes, HasUuids, SoftDeletes;

    protected array $cascadeDeletes = ['equipment', 'equipmentParameters'];

    public $incrementing = false;

    protected $fillable = [
        'id',
        'code',
        'name',
    ];

    protected $keyType = 'string';

    protected $table = 'eamo_equipment_categories';

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    public function equipmentParameters(): HasMany
    {
        return $this->hasMany(EquipmentParameter::class);
    }

    protected function casts(): array
    {
        return [];
    }
}
