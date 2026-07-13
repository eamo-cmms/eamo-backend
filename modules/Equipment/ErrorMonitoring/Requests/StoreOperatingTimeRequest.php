<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreOperatingTimeRequest extends FormRequest
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
            'equipment_id' => 'required|string|max:36',
            'equipment_name' => 'nullable|string',
            'planned_stop_time' => 'required|numeric',
            'unplanned_stop_time' => 'nullable|numeric',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ];
    }
}
