<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Requests;

use App\Concerns\PreparesBooleanInputs;
use Illuminate\Foundation\Http\FormRequest;

class ShowDailySessionRequest extends FormRequest
{
    use PreparesBooleanInputs;

    protected function prepareForValidation(): void
    {
        $this->prepareBooleans(['only_trashed', 'with_trashed']);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'equipment_id' => ['required', 'string', 'exists:eamo_equipment,id'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'only_trashed' => ['nullable', 'boolean'],
            'with_trashed' => ['nullable', 'boolean'],
        ];
    }
}
