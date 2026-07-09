<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreChecklistSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'equipment_id' => ['required', 'string', 'max:36', 'exists:eamo_equipment,id'],
            'session_date' => ['required', 'date'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['required', 'string', 'exists:users,id'],
            // Optional checklist details nested
            'details' => ['nullable', 'array'],
            'details.*.checklist_id' => ['required', 'string', 'max:36'],
            'details.*.result' => ['required', 'in:pass,fail'],
            'details.*.description' => ['nullable', 'string'],
        ];
    }
}
