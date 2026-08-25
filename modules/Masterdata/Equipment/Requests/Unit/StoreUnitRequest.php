<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Requests\Unit;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \Modules\Masterdata\Equipment\Models\Unit::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'string', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:32', 'unique:eamo_units,code'],
            'description' => ['nullable', 'string'],
        ];
    }
}
