<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateChecklistSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'schedule_mode' => ['nullable', 'string', 'in:single,repeating'],
            'equipment_id' => ['sometimes', 'string', 'max:36', 'exists:eamo_equipment,id'],
            'session_date' => ['sometimes', 'date'],
            'cycle_type' => ['sometimes', 'string', 'in:daily,weekly,monthly,yearly'],
            'cycle_interval' => ['sometimes', 'integer', 'min:1'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['required', 'string', 'exists:users,id'],
            'schedules' => ['sometimes', 'array'],
            'schedules.*.id' => ['nullable', 'string', 'exists:eamo_checklist_schedules,id'],
            'schedules.*.checklist_detail_id' => ['nullable', 'string', 'exists:eamo_checklist_details,id'],
            'schedules.*.date' => ['nullable', 'date'],
            'schedules.*.user_ids' => ['nullable', 'array'],
            'schedules.*.user_ids.*' => ['required', 'string', 'exists:users,id'],
        ];
    }
}
