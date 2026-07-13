<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Requests;

use App\Models\User;
use App\Rules\IsValidId;
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
        return true;
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
