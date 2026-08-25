<?php

declare(strict_types=1);

namespace Modules\Equipment\ParameterLog\Services;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Equipment\ParameterLog\Models\EquipmentParameterLog;
use Modules\Equipment\ParameterLog\Requests\StoreEquipmentParameterLogRequest;
use Modules\Masterdata\Equipment\Models\Equipment;
use Modules\Masterdata\Equipment\Models\EquipmentParameter;
use Modules\Masterdata\Equipment\Models\Unit;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

final class ImportEquipmentParameterLogService
{
    private array $equipmentCache = [];

    private array $parameterCache = [];

    private array $unitCache = [];

    private const REQUIRED_FIELDS = ['equipment_code', 'parameter_code', 'value'];

    private const FIELD_MAPPINGS = [
        'equipment_code' => ['equipment_code', 'equipment code', 'mã thiết bị', 'thiết bị', 'equipment'],
        'parameter_code' => ['parameter_code', 'parameter code', 'mã thông số', 'thông số', 'parameter'],
        'value'          => ['value', 'giá trị', 'giá trị đo', 'parameter_value', 'parameter value'],
        'unit_code'      => ['unit_code', 'unit code', 'mã đơn vị', 'đơn vị tính', 'đơn vị', 'unit'],
        'recorded_at'    => ['recorded_at', 'recorded at', 'ngày ghi', 'thời gian ghi', 'thời gian', 'timestamp'],
    ];

    /**
     * Load rows from uploaded spreadsheet file.
     *
     * @return array<int, array<int, mixed>>
     *
     * @throws \Throwable
     */
    public function loadRowsFromFile(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();

        return $worksheet->toArray();
    }

    /**
     * Resolve and validate column mappings from header row.
     *
     * @param  array<int, mixed>  $headerRow
     * @return array{columns: array<string, int>, missing: array<string>}
     */
    public function resolveHeaderColumns(array $headerRow): array
    {
        $headerMap = [];
        foreach ($headerRow as $index => $cell) {
            if ($cell !== null) {
                $normalized = strtolower(trim((string) $cell));
                $headerMap[$normalized] = $index;
            }
        }

        $columns = [];
        foreach (self::FIELD_MAPPINGS as $field => $synonyms) {
            foreach ($synonyms as $synonym) {
                if (isset($headerMap[$synonym])) {
                    $columns[$field] = $headerMap[$synonym];
                    break;
                }
            }
        }

        $missing = [];
        foreach (self::REQUIRED_FIELDS as $field) {
            if (! isset($columns[$field])) {
                $missing[] = $field;
            }
        }

        return [
            'columns' => $columns,
            'missing' => $missing,
        ];
    }

    /**
     * Parse, resolve relationships, and validate all data rows.
     *
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array<string, int>  $columns
     * @return array{errors: array<string>, records: array<array<string, mixed>>}
     */
    public function parseAndValidateRows(array $rows, array $columns, ?string $userId): array
    {
        $errors = [];
        $records = [];
        $rules = (new StoreEquipmentParameterLogRequest)->rules();

        foreach ($rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2; // +1 for 0-index, +1 for header row

            if ($this->isRowEmpty($row)) {
                continue;
            }

            // Resolve Equipment
            $rawEquipment = $row[$columns['equipment_code']] ?? null;
            $equipmentId = null;
            if ($rawEquipment !== null && trim((string) $rawEquipment) !== '') {
                $equipmentId = $this->resolveEquipmentId((string) $rawEquipment);
                if ($equipmentId === null) {
                    $errors[] = "Row {$rowNumber}: Equipment code '{$rawEquipment}' does not exist.";
                }
            }

            // Resolve Parameter
            $rawParameter = $row[$columns['parameter_code']] ?? null;
            $parameterId = null;
            if ($rawParameter !== null && trim((string) $rawParameter) !== '') {
                $parameterId = $this->resolveParameterId((string) $rawParameter);
                if ($parameterId === null) {
                    $errors[] = "Row {$rowNumber}: Parameter code '{$rawParameter}' does not exist.";
                }
            }

            // Resolve Unit
            $rawUnit = isset($columns['unit_code']) ? ($row[$columns['unit_code']] ?? null) : null;
            $unitId = null;
            if ($rawUnit !== null && trim((string) $rawUnit) !== '') {
                $unitId = $this->resolveUnitId((string) $rawUnit);
                if ($unitId === null) {
                    $errors[] = "Row {$rowNumber}: Unit code '{$rawUnit}' does not exist.";
                }
            }

            // Fallback unit from parameter if omitted
            if ($unitId === null && $parameterId !== null) {
                $unitId = EquipmentParameter::where('id', $parameterId)->value('unit_id');
            }

            // Resolve timestamp
            $recordedAt = null;
            if (isset($columns['recorded_at'])) {
                $rawRecordedAt = $row[$columns['recorded_at']] ?? null;
                $recordedAt = $this->parseDateTime($rawRecordedAt);
            }
            if ($recordedAt === null) {
                $recordedAt = now();
            }

            $val = $this->formatValue($row[$columns['value']] ?? null);

            $data = [
                'equipment_id'           => $equipmentId,
                'equipment_parameter_id' => $parameterId,
                'unit_id'                => $unitId,
                'value'                  => $val,
                'user_id'                => $userId,
                'recorded_at'            => $recordedAt ? $recordedAt->toDateTimeString() : null,
            ];

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $errorMsg) {
                    $errors[] = "Row {$rowNumber}: {$errorMsg}";
                }
                continue;
            }

            $records[] = $validator->validated();
        }

        return [
            'errors'  => $errors,
            'records' => $records,
        ];
    }

    /**
     * Persist validated records to the database inside a transaction.
     *
     * @param  array<array<string, mixed>>  $records
     */
    public function saveRecords(array $records): int
    {
        DB::transaction(function () use ($records): void {
            foreach ($records as $record) {
                EquipmentParameterLog::create($record);
            }
        });

        return count($records);
    }

    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if ($cell !== null && trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function resolveEquipmentId(string $code): ?string
    {
        $code = trim($code);
        if (isset($this->equipmentCache[$code])) {
            return $this->equipmentCache[$code];
        }

        $id = Equipment::where('code', $code)->value('id');
        if ($id) {
            return $this->equipmentCache[$code] = $id;
        }

        return null;
    }

    private function resolveParameterId(string $code): ?string
    {
        $code = trim($code);
        if (isset($this->parameterCache[$code])) {
            return $this->parameterCache[$code];
        }

        $id = EquipmentParameter::where('code', $code)->value('id');
        if ($id) {
            return $this->parameterCache[$code] = $id;
        }

        return null;
    }

    private function resolveUnitId(string $code): ?string
    {
        $code = trim($code);
        if (isset($this->unitCache[$code])) {
            return $this->unitCache[$code];
        }

        $id = Unit::where('code', $code)->value('id');
        if ($id) {
            return $this->unitCache[$code] = $id;
        }

        return null;
    }

    private function parseDateTime(mixed $value): ?Carbon
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(Date::excelToDateTimeObject((float) $value));
            }

            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function formatValue(mixed $val): ?string
    {
        if ($val === null || trim((string) $val) === '') {
            return null;
        }

        $valStr = trim((string) $val);
        if (is_numeric($valStr)) {
            $floatVal = (float) $valStr;

            return rtrim(rtrim(sprintf('%.10f', $floatVal), '0'), '.');
        }

        return $valStr;
    }
}
