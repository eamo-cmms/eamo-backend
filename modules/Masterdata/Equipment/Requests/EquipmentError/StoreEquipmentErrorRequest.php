<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Requests\EquipmentError;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentErrorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \Modules\Masterdata\Equipment\Models\EquipmentError::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'string', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'reason' => ['nullable', 'string'],
            'fix' => ['nullable', 'string'],
            'protection_measures' => ['nullable', 'string'],
            'equipment_ids' => ['nullable', 'array'],
            'equipment_ids.*' => ['string', 'uuid'],
        ];
    }
}
