<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Requests\Equipment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\NotIn;

final class UpdateEquipmentParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int|string|NotIn>>
     */
    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'parent_id' => [
                'nullable',
                'string',
                'uuid',
                'exists:eamo_equipment,id',
                Rule::notIn([$id]),
            ],
        ];
    }
}
