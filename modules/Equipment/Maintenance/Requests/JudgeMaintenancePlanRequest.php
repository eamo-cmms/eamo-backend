<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JudgeMaintenancePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_id' => [
                'required',
                'string',
                'min:1',
                'max:36',
                'exists:eamo_maintenance_plans,id',
            ],
            'results' => ['required', 'array'],
            'results.*.schedule_id' => ['required', 'string', 'max:36', 'exists:eamo_maintenance_schedules,id'],
            'results.*.result' => ['required', 'string', 'in:Completed,Partial,Failed,Pending'],
            'results.*.note' => ['nullable', 'string'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['required', 'string', 'exists:users,id'],
            'timestamp' => ['nullable', 'string', 'date', 'before:tomorrow'],
        ];
    }
}
