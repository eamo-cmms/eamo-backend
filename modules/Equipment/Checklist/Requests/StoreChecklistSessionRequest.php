<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreChecklistSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \Modules\Equipment\Checklist\Models\ChecklistSession::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'schedule_mode' => ['nullable', 'string', 'in:single,repeating'],
            'equipment_id' => ['required', 'string', 'max:36', 'exists:eamo_equipment,id'],
            'session_date' => ['required', 'date'],
            'cycle_type' => ['nullable', 'string', 'in:daily,weekly,monthly,yearly'],
            'cycle_interval' => ['nullable', 'integer', 'min:1'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['required', 'string', 'exists:users,id'],
            // Optional checklist details nested
            'details' => ['nullable', 'array'],
            'details.*.checklist_id' => ['required', 'string', 'max:36'],
            'details.*.description' => ['nullable', 'string'],
        ];
    }
}
