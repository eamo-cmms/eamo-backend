<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDailySessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'equipment_id' => ['required', 'string', 'exists:eamo_equipment,id'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }
}
