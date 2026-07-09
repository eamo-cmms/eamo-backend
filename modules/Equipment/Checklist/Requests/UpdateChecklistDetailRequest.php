<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChecklistDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_id' => [
                'required',
                'string',
                'min:1',
                'max:36',
                'exists:eamo_checklist_sessions,id',
            ],
            'checklists' => ['required', 'array'],
            'checklists.*.checklist_id' => [
                'required',
                'string',
                'min:1',
                'max:36',
            ],
            'checklists.*.result' => ['required', 'in:pass,fail'],
            'checklists.*.description' => ['nullable', 'string'],
        ];
    }
}
