<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Requests\EquipmentCategory;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \Modules\Masterdata\Equipment\Models\EquipmentCategory::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'string', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
