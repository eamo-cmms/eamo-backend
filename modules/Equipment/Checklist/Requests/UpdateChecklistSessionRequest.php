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
            'equipment_id' => ['sometimes', 'string', 'max:36', 'exists:eamo_equipment,id'],
            'session_date' => ['sometimes', 'date'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['required', 'string', 'exists:users,id'],
        ];
    }
}
