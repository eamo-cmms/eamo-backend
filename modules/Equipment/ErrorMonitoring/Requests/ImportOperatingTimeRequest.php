<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Requests;

use Illuminate\Validation\Rules\File;

final class ImportOperatingTimeRequest extends StoreOperatingTimeRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('import', \Modules\Equipment\ErrorMonitoring\Models\OperatingTime::class) ?? false;
    }

    /**
     * @return array<string, array<int, string|File>>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                File::types(['xlsx', 'xls', 'csv', 'txt'])->max(10240),
            ],
        ];
    }

    /**
     * @return array<int, \Closure>
     */
    public function after(): array
    {
        return [];
    }
}
