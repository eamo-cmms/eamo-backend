<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateMaintenanceLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', \Modules\Equipment\Maintenance\Models\MaintenanceLog::class) ?? false;
    }

    /**
     * @return array<string, array<int|string>>
     */
    public function rules(): array
    {
        return [
            'result' => ['required', 'string', 'in:Completed,Partial,Failed'],
            'note' => ['nullable', 'string'],
        ];
    }
}
