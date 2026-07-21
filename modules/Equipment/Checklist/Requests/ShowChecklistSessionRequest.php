<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowChecklistSessionRequest extends FormRequest
{
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
        ];
    }
}
