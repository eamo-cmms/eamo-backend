<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateMaintenancePlanRequest extends FormRequest
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
            'plan_code' => ['nullable', 'string', 'max:255'],
            'equipment_id' => ['nullable', 'string', 'exists:eamo_equipment,id'],
            'maintenance_category_id' => ['nullable', 'string', 'exists:eamo_maintenance_categories,id'],
            'maintenance_type' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'start_time' => ['nullable', 'string', 'max:255'],
            'end_time' => ['nullable', 'string', 'max:255'],
            'cycle_type' => ['nullable', 'string', 'in:daily,weekly,monthly,yearly'],
            'cycle_interval' => ['required_with:cycle_type', 'nullable', 'integer', 'min:1'],
            'occurrences' => ['required_with:cycle_type', 'nullable', 'integer', 'min:1', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'schedules' => ['nullable', 'array'],
            'schedules.*.id' => ['nullable', 'string', 'exists:eamo_maintenance_schedules,id'],
            'schedules.*.maintenance_item_id' => ['required_with:schedules', 'string', 'exists:eamo_maintenance_items,id'],
            'schedules.*.date' => ['required_with:schedules', 'date_format:Y-m-d'],
            'schedules.*.user_ids' => ['nullable', 'array'],
            'schedules.*.user_ids.*' => ['string', 'exists:users,id'],
        ];
    }
}
