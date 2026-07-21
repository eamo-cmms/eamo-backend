<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexChecklistSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'only_trashed' => ['nullable', 'boolean'],
            'with_trashed' => ['nullable', 'boolean'],
            'session_date' => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'equipment_id' => ['nullable', 'string'],
            'name' => ['nullable', 'string'],
            'q' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
