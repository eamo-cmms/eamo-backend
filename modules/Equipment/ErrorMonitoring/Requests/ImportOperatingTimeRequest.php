<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;
use Modules\Equipment\ErrorMonitoring\Models\OperatingTime;
use Modules\Equipment\ErrorMonitoring\Services\ImportOperatingTimeService;

final class ImportOperatingTimeRequest extends FormRequest
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $validatedRecords = [];

    public function authorize(): bool
    {
        return $this->user()?->can('import', OperatingTime::class) ?? false;
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
     * Perform spreadsheet parsing and validation in the after validation hook.
     *
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $file = $this->file('file');
                if (! $file) {
                    $validator->errors()->add('file', __('error_monitoring.no_file_uploaded'));

                    return;
                }

                $service = app(ImportOperatingTimeService::class);

                try {
                    $rows = $service->loadRowsFromFile($file);
                } catch (\Throwable) {
                    $validator->errors()->add('file', __('error_monitoring.unable_to_read_file'));

                    return;
                }

                $headerRow = array_shift($rows);
                if (! $headerRow) {
                    $validator->errors()->add('file', __('error_monitoring.file_is_empty'));

                    return;
                }

                $headerResult = $service->resolveHeaderColumns($headerRow);
                if (! empty($headerResult['missing'])) {
                    $missingText = implode(', ', $headerResult['missing']);
                    $validator->errors()->add(
                        'file',
                        __('error_monitoring.missing_required_headers', ['headers' => $missingText])
                    );

                    return;
                }

                $result = $service->parseAndValidateRows(
                    $rows,
                    $headerResult['columns']
                );

                if (! empty($result['errors'])) {
                    foreach ($result['errors'] as $error) {
                        $validator->errors()->add('file', $error);
                    }

                    return;
                }

                if (empty($result['records'])) {
                    $validator->errors()->add('file', __('error_monitoring.no_records_to_import'));

                    return;
                }

                $this->validatedRecords = $result['records'];
            },
        ];
    }

    /**
     * Get records validated and prepared for insertion.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getValidatedRecords(): array
    {
        return $this->validatedRecords;
    }
}
