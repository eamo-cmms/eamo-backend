<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Requests;

use App\Concerns\PreparesBooleanInputs;
use Illuminate\Foundation\Http\FormRequest;

class ShowChecklistSessionRequest extends FormRequest
{
    use PreparesBooleanInputs;

    protected function prepareForValidation(): void
    {
        $this->prepareBooleans(['with_details', 'include_details', 'only_trashed', 'with_trashed']);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'with_details' => ['nullable', 'boolean'],
            'include_details' => ['nullable', 'boolean'],
            'only_trashed' => ['nullable', 'boolean'],
            'with_trashed' => ['nullable', 'boolean'],
            'date' => ['nullable', 'date'],
        ];
    }
}
