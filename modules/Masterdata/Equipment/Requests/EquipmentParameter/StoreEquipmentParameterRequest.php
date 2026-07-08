<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Requests\EquipmentParameter;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentParameterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'string', 'uuid'],
            'equipment_id' => ['nullable', 'string', 'uuid', 'exists:eamo_equipment,id'],
            'unit_id' => ['nullable', 'string', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:32', 'unique:eamo_equipment_parameters,code'],
            'product_category_id' => ['nullable', 'string', 'uuid'],
            'equipment_category_id' => ['nullable', 'string', 'uuid'],
        ];
    }
}
