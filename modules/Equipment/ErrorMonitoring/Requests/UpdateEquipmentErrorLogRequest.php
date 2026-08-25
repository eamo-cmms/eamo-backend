<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Requests;

use App\Models\User;
use App\Rules\IsValidId;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Masterdata\Equipment\Models\Equipment;
use Modules\Masterdata\Equipment\Models\EquipmentError;

/**
 * @property-read string|null $name
 */
final class UpdateEquipmentErrorLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', \Modules\Equipment\ErrorMonitoring\Models\EquipmentErrorLog::class) ?? false;
    }

    /**
     * Normalize datetime fields to Asia/Ho_Chi_Minh (UTC+7) standard format.
     */
    protected function prepareForValidation(): void
    {
        $fields = ['occurred_at', 'handled_at', 'restarted_at'];

        $merge = [];
        foreach ($fields as $field) {
            $value = $this->input($field);
            if (! empty($value)) {
                try {
                    $merge[$field] = Carbon::parse($value)
                        ->setTimezone('Asia/Ho_Chi_Minh')
                        ->format('Y-m-d H:i:s');
                } catch (\Throwable) {
                    // Leave invalid values as-is; validation rule 'date' will reject them
                }
            }
        }

        if (! empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'equipment_id' => [
                'sometimes',
                'required',
                'string',
                'min:1',
                'max:36',
                new IsValidId,
                Rule::exists(Equipment::class, 'id'),
            ],
            'equipment_error_id' => [
                'sometimes',
                'required',
                'string',
                'min:1',
                'max:36',
                new IsValidId,
                Rule::exists(EquipmentError::class, 'id'),
            ],
            'occurred_at' => ['sometimes', 'required', 'date'],
            'restarted_at' => ['nullable', 'date'],
            'handled_at' => ['nullable', 'date'],
            'is_handled' => ['nullable', 'boolean'],
            'handler_ids' => ['nullable', 'array'],
            'handler_ids.*' => [
                'required',
                'string',
                'max:36',
                new IsValidId,
                Rule::exists(User::class, 'id'),
            ],
        ];
    }
}
