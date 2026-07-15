<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JudgeSessionRequest extends FormRequest
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
            'results' => ['required', 'array'],
            'results.*.checklist_id' => ['required', 'string', 'max:36'],
            'results.*.result' => ['required', 'in:pass,fail'],
            'results.*.description' => ['nullable', 'string'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['required', 'string', 'exists:users,id'],
            'timestamp' => ['nullable', 'string'],
        ];
    }
}
