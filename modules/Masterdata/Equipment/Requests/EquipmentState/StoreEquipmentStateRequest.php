<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Requests\EquipmentState;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'string', 'uuid'],
            'equipment_id' => ['required', 'string', 'uuid', 'exists:eamo_equipment,id'],
            'state' => ['nullable', 'string', 'max:255'],
        ];
    }
}
