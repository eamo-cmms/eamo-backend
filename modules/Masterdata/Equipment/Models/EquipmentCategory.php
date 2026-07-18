<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Equipment\Services\EquipmentCascadeSoftDeleteService;

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
    use HasUuids, SoftDeletes;

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

    protected static function booted(): void
    {
        static::deleting(function (self $category): bool|null {
            if ($category->isForceDeleting()) {
                return null;
            }

            $cascadeService = app(EquipmentCascadeSoftDeleteService::class);
            if ($cascadeService->isDeletingCategory($category)) {
                return null;
            }

            $cascadeService->deleteCategory($category);

            return false;
        });
    }

    protected function casts(): array
    {
        return [];
    }
}
