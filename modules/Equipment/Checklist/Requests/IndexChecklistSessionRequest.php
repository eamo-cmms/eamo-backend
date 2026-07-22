<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Requests;

use App\Concerns\PreparesBooleanInputs;
use Illuminate\Foundation\Http\FormRequest;

class IndexChecklistSessionRequest extends FormRequest
{
    use PreparesBooleanInputs;

    protected function prepareForValidation(): void
    {
        $this->prepareBooleans(['only_trashed', 'with_trashed']);
    }

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
