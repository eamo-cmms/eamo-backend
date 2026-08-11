<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreMaintenanceLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int|string>>
     */
    public function rules(): array
    {
        return [
            'maintenance_schedule_id' => ['required', 'string', 'exists:eamo_maintenance_schedules,id'],
            'result' => ['required', 'string', 'in:Completed,Partial,Failed'],
            'note' => ['nullable', 'string'],
            'type' => ['nullable', 'string'],
        ];
    }
}
