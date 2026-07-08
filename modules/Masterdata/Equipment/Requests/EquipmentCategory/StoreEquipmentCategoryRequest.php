<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Requests\EquipmentCategory;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'string', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
