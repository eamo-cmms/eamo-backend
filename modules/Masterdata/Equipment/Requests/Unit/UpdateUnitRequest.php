<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Requests\Unit;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:32', "unique:eamo_units,code,{$id},id"],
            'description' => ['nullable', 'string'],
        ];
    }
}
