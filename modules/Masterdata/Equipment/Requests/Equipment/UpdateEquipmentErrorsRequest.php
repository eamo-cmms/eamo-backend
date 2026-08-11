<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Requests\Equipment;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateEquipmentErrorsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int|string>>
     */
    public function rules(): array
    {
        return [
            'equipment_error_ids' => ['required', 'array'],
            'equipment_error_ids.*' => ['string', 'exists:eamo_equipment_errors,id'],
            'occurred_at' => ['nullable', 'date'],
        ];
    }
}
