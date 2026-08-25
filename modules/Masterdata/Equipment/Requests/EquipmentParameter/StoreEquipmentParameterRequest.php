<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Requests\EquipmentParameter;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentParameterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \Modules\Masterdata\Equipment\Models\EquipmentParameter::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'string', 'uuid'],
            'equipment_id' => ['nullable', 'string', 'uuid', 'exists:eamo_equipment,id'],
            'unit_id' => ['nullable', 'string', 'uuid', 'exists:eamo_units,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:32', 'unique:eamo_equipment_parameters,code'],
            'product_category_id' => ['nullable', 'string', 'uuid'],
            'equipment_category_id' => ['nullable', 'string', 'uuid'],
            'standard' => ['nullable', 'numeric'],
            'standard_max' => ['nullable', 'numeric'],
            'standard_min' => ['nullable', 'numeric'],
        ];
    }
}
