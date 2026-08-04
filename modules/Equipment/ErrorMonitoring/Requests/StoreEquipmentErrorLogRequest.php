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
 * @property-read string $name
 */
final class StoreEquipmentErrorLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize datetime fields sent as Vietnam local time (UTC+7) to UTC.
     * Frontend sends plain "YYYY-MM-DD HH:mm:ss" strings in VN local time.
     * Laravel/MySQL expects UTC so we must convert before validation & storage.
     */
    protected function prepareForValidation(): void
    {
        $vnTimezone = 'Asia/Ho_Chi_Minh';
        $fields = ['occurred_at', 'handled_at', 'restarted_at'];

        $merge = [];
        foreach ($fields as $field) {
            $value = $this->input($field);
            if (! empty($value)) {
                try {
                    $merge[$field] = Carbon::parse($value, $vnTimezone)
                        ->setTimezone('UTC')
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
                'required',
                'string',
                'min:1',
                'max:36',
                new IsValidId,
                Rule::exists(Equipment::class, 'id'),
            ],
            'equipment_error_id' => [
                'required',
                'string',
                'min:1',
                'max:36',
                new IsValidId,
                Rule::exists(EquipmentError::class, 'id'),
            ],
            'occurred_at' => ['nullable', 'date'],
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
