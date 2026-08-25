<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Requests\Equipment;

use Illuminate\Foundation\Http\FormRequest;

final class MarkLastMaintenanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('markLastMaintenance', \Modules\Masterdata\Equipment\Models\Equipment::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'datetime' => ['required', 'date'],
        ];
    }
}
