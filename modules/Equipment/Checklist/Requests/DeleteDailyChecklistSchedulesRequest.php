<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteDailyChecklistSchedulesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_id' => ['required', 'string', 'exists:eamo_checklist_sessions,id'],
            'equipment_id' => ['required', 'string', 'exists:eamo_equipment,id'],
            'date' => ['required', 'date'],
        ];
    }
}
