<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Requests\Equipment;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \Modules\Masterdata\Equipment\Models\Equipment::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:32', 'unique:eamo_equipment,code'],
            'name' => ['nullable', 'string', 'max:255'],
            'equipment_category_id' => ['nullable', 'uuid'],
            'uploaded_images' => ['nullable', 'array'],
            'uploaded_images.*' => ['file', 'image', 'max:2048'],
            'state' => ['nullable', 'string', 'max:255'],
            'device_id' => ['nullable', 'uuid'],
            'parent_id' => ['nullable', 'string', 'uuid', 'exists:eamo_equipment,id'],
            'maintenance_interval_hours' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'equipment_error_ids' => ['nullable', 'array'],
            'equipment_error_ids.*' => ['string', 'exists:eamo_equipment_errors,id'],
            'equipment_parameters' => ['nullable', 'array'],
            'equipment_parameters.*.id' => ['nullable', 'string', 'uuid'],
            'equipment_parameters.*.code' => ['required', 'string', 'max:32'],
            'equipment_parameters.*.name' => ['required', 'string', 'max:255'],
            'equipment_parameters.*.unit_id' => ['nullable', 'string', 'uuid'],
        ];
    }
}
