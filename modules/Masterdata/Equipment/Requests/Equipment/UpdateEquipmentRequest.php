<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Requests\Equipment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'code' => ['required', 'string', 'max:32', "unique:eamo_equipment,code,{$id},id"],
            'name' => ['nullable', 'string', 'max:255'],
            'process_id' => ['nullable', 'uuid'],
            'factory_id' => ['nullable', 'string', 'max:255'],
            'virtual_equipment' => ['nullable', 'boolean'],
            'equipment_category_id' => ['nullable', 'uuid'],
            'image_id' => ['nullable', 'uuid'],
            'date_imported' => ['nullable', 'date'],
            'state' => ['nullable', 'boolean'],
            'device_id' => ['nullable', 'uuid'],
            'assigned_productivity_per_hour' => ['nullable', 'integer', 'min:0'],
            'assigned_machine_productivity_person' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'equipment_error_ids' => ['nullable', 'array'],
            'equipment_error_ids.*' => ['string', 'uuid'],
        ];
    }
}
