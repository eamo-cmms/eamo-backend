<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Requests\EquipmentCategory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEquipmentCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', \Modules\Masterdata\Equipment\Models\EquipmentCategory::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
