<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Requests\StandardParameter;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStandardParameterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'equipment_id' => ['nullable', 'string', 'uuid', 'exists:eamo_equipment,id'],
            'equipment_parameter_id' => ['required', 'string', 'uuid', 'exists:eamo_equipment_parameters,id'],
            'standard' => ['required', 'numeric'],
            'standard_max' => ['required', 'numeric'],
            'standard_min' => ['required', 'numeric'],
            'unit_id' => ['nullable', 'string', 'uuid', 'exists:eamo_units,id'],
        ];
    }
}
