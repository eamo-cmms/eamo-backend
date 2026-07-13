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
            'equipment_category_id' => ['nullable', 'uuid'],
            'uploaded_images' => ['nullable', 'array'],
            'uploaded_images.*' => ['file', 'image', 'max:2048'],
            'existing_image_ids' => ['nullable', 'array'],
            'existing_image_ids.*' => ['string', 'uuid'],
            'state' => ['nullable', 'string', 'max:255'],
            'device_id' => ['nullable', 'uuid'],
            'maintenance_interval_hours' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'last_maintenance' => ['nullable', 'array'],
            'equipment_error_ids' => ['nullable', 'array'],
            'equipment_error_ids.*' => ['string', 'uuid'],
            'equipment_parameters' => ['nullable', 'array'],
            'equipment_parameters.*.id' => ['nullable', 'string', 'uuid'],
            'equipment_parameters.*.code' => ['required', 'string', 'max:32'],
            'equipment_parameters.*.name' => ['required', 'string', 'max:255'],
            'equipment_parameters.*.unit_id' => ['nullable', 'string', 'uuid'],
        ];
    }
}
