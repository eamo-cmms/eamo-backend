<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EquipmentErrorDefinitionPivot extends Pivot
{
    use HasUuids;

    protected $table = 'eamo_equipment_error_logs';

    public $incrementing = false;

    protected $keyType = 'string';
}
