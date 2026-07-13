<?php

declare(strict_types=1);

namespace Modules\Equipment\ParameterLog\Requests;

use App\Rules\IsValidId;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read string|null $name
 */
final class UpdateEquipmentParameterLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'equipment_id' => [
                'required',
                'string',
                'min:1',
                new IsValidId,
                'max:36',
            ],
            'equipment_parameter_id' => [
                'required',
                'string',
                'min:1',
                new IsValidId,
                'max:36',
            ],
            'unit_id' => [
                'sometimes',
                'nullable',
                'string',
                'min:0',
                new IsValidId,
                'max:36',
            ],
            'value' => ['required', 'string', 'min:1', 'max:36'],
            'product_id' => ['sometimes', 'nullable', 'string', 'max:36'],
            'lot_id' => ['sometimes', 'nullable', 'string', 'max:36'],
            'component_id' => ['sometimes', 'nullable', 'string', 'max:36'],
        ];
    }
}
