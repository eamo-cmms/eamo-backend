<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreMaintenancePlanRequest extends FormRequest
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
            'schedule_mode' => ['nullable', 'string', 'in:single,repeating'],
            'equipment_id' => ['required', 'string', 'exists:eamo_equipment,id'],
            'maintenance_category_id' => ['required', 'string', 'exists:eamo_maintenance_categories,id'],
            'maintenance_type' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['nullable', 'string', 'max:255'],
            'end_time' => ['nullable', 'string', 'max:255'],
            'cycle_type' => ['nullable', 'string', 'in:daily,weekly,monthly,yearly'],
            'cycle_interval' => ['required_with:cycle_type', 'nullable', 'integer', 'min:1'],
            'occurrences' => ['required_with:cycle_type', 'nullable', 'integer', 'min:1', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'schedules' => [
                Rule::requiredIf(fn () => empty($this->input('cycle_type'))),
                'array',
            ],
            'schedules.*.maintenance_item_id' => ['required', 'string', 'exists:eamo_maintenance_items,id'],
            'schedules.*.date' => ['required', 'date_format:Y-m-d'],
            'schedules.*.user_ids' => ['nullable', 'array'],
            'schedules.*.user_ids.*' => ['string', 'exists:users,id'],
        ];
    }
}
