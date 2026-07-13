<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateOperatingTimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    public function rules(): array
    {
        return [
            'equipment_id' => 'sometimes|required|string|max:36',
            'equipment_name' => 'nullable|string',
            'planned_stop_time' => 'sometimes|required|numeric',
            'unplanned_stop_time' => 'nullable|numeric',
            'start_time' => 'sometimes|required|date',
            'end_time' => 'sometimes|required|date|after:start_time',
        ];
    }
}
