<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreMaintenanceLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \Modules\Equipment\Maintenance\Models\MaintenanceLog::class) ?? false;
    }

    /**
     * @return array<string, array<int|string>>
     */
    public function rules(): array
    {
        return [
            'equipment_id' => ['required_without:maintenance_schedule_id', 'nullable', 'string', 'exists:eamo_equipment,id'],
            'maintenance_schedule_id' => ['nullable', 'string', 'exists:eamo_maintenance_schedules,id'],
            'user_id' => ['nullable', 'string', 'exists:users,id'],
            'log_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
            'type' => ['nullable', 'string'],
        ];
    }
}
