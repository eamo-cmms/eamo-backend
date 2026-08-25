<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Requests\EquipmentState;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEquipmentStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', \Modules\Masterdata\Equipment\Models\EquipmentState::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'equipment_id' => ['required', 'string', 'uuid', 'exists:eamo_equipment,id'],
            'state' => ['nullable', 'string', 'max:255'],
        ];
    }
}
