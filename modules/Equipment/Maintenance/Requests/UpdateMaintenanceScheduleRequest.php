<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Requests;

use App\Rules\IsValidId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Equipment\Maintenance\Models\MaintenanceSchedule;

/**
 * @property-read string|null $name
 */
final class UpdateMaintenanceScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', \Modules\Equipment\Maintenance\Models\MaintenanceSchedule::class) ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'string',
                'min:1',
                'max:36',
                new IsValidId,
                Rule::exists(MaintenanceSchedule::class, 'id'),
            ],

            // New datetime fields
            'actual_start_time' => ['nullable', 'string', 'min:0', 'max:255'],
            'actual_end_time' => ['nullable', 'string', 'min:0', 'max:255'],
            'result' => ['nullable', 'string', 'min:0', 'max:255'],
            'note' => ['nullable', 'string', 'min:0', 'max:1000'],
            'proof' => ['nullable', 'array'],
            'proof.*' => ['string', 'min:1', 'max:255'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['string', 'exists:users,id'],
        ];
    }
}
