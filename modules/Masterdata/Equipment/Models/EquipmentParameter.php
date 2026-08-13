<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Models;

use Carbon\CarbonImmutable;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Equipment\ParameterLog\Models\EquipmentParameterLog;

/**
 * Class EquipmentParameter
 *
 * @property string $id
 * @property string $equipment_id
 * @property string|null $unit_id
 * @property string $name
 * @property string $code
 * @property string|null $product_category_id
 * @property string|null $equipment_category_id
 * @property float|null $standard
 * @property float|null $standard_max
 * @property float|null $standard_min
 * @property-read Equipment $equipment
 * @property-read EquipmentCategory|null $equipmentCategory
 * @property-read Unit|null $unit
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 */
final class EquipmentParameter extends Model
{
    use CascadeSoftDeletes, HasUuids, SoftDeletes;

    protected array $cascadeDeletes = ['parameterLogs'];

    public $incrementing = false;

    protected $fillable = [
        'name',
        'equipment_id',
        'unit_id',
        'product_category_id',
        'equipment_category_id',
        'code',
        'standard',
        'standard_max',
        'standard_min',
    ];

    protected $keyType = 'string';

    protected $table = 'eamo_equipment_parameters';

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function equipmentCategory(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function parameterLogs(): HasMany
    {
        return $this->hasMany(EquipmentParameterLog::class, 'equipment_parameter_id');
    }

    protected function casts(): array
    {
        return [
            'standard' => 'float',
            'standard_max' => 'float',
            'standard_min' => 'float',
        ];
    }
}
