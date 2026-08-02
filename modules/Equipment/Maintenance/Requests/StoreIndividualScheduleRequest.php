<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreIndividualScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'maintenance_item_id' => ['nullable', 'string', 'exists:eamo_maintenance_items,id'],
            'date' => ['required', 'string', 'date_format:Y-m-d'],
            'is_adhoc' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['string', 'exists:users,id'],
        ];
    }
}
