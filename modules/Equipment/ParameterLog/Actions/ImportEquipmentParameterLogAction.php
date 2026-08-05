<?php

declare(strict_types=1);

namespace Modules\Equipment\ParameterLog\Actions;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ParameterLog\Models\EquipmentParameterLog;
use Modules\Equipment\ParameterLog\Requests\ImportEquipmentParameterLogRequest;
use Modules\Equipment\ParameterLog\Requests\StoreEquipmentParameterLogRequest;
use Modules\Masterdata\Equipment\Models\Equipment;
use Modules\Masterdata\Equipment\Models\EquipmentParameter;
use Modules\Masterdata\Equipment\Models\Unit;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

final class ImportEquipmentParameterLogAction
{
    use AsAction;

    private array $equipmentCache = [];

    private array $parameterCache = [];

    private array $unitCache = [];

    public function asController(ImportEquipmentParameterLogRequest $request): JsonResponse
    {
        $file = $request->file('file');
        if (! $file) {
            return response()->json([
                'message' => 'No file uploaded.',
                'errors' => [
                    'file' => ['No file uploaded.'],
                ],
            ], 422);
        }

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Unable to read the uploaded file. Please ensure it is a valid Excel or CSV file.',
                'errors' => [
                    'file' => [$e->getMessage()],
                ],
            ], 422);
        }

        $headerRow = array_shift($rows);
        if (! $headerRow) {
            return response()->json([
                'message' => 'The uploaded file is empty.',
                'errors' => [
                    'file' => ['The uploaded file is empty.'],
                ],
            ], 422);
        }

        // Map headers to cell indexes case-insensitively
        $headerMap = [];
        foreach ($headerRow as $index => $cell) {
            if ($cell !== null) {
                $normalized = strtolower(trim((string) $cell));
                $headerMap[$normalized] = $index;
            }
        }

        $requiredFields = ['equipment_code', 'parameter_code', 'value'];
        $fieldMappings = [
            'equipment_code' => ['equipment_code', 'equipment code', 'mã thiết bị', 'thiết bị', 'equipment'],
            'parameter_code' => ['parameter_code', 'parameter code', 'mã thông số', 'thông số', 'parameter'],
            'value' => ['value', 'giá trị', 'giá trị đo', 'parameter_value', 'parameter value'],
            'unit_code' => ['unit_code', 'unit code', 'mã đơn vị', 'đơn vị tính', 'đơn vị', 'unit'],
            'recorded_at' => ['recorded_at', 'recorded at', 'ngày ghi', 'thời gian ghi', 'thời gian', 'timestamp'],
        ];

        $columns = [];
        foreach ($fieldMappings as $field => $synonyms) {
            foreach ($synonyms as $synonym) {
                if (isset($headerMap[$synonym])) {
                    $columns[$field] = $headerMap[$synonym];
                    break;
                }
            }
        }

        $missingRequired = [];
        foreach ($requiredFields as $field) {
            if (! isset($columns[$field])) {
                $missingRequired[] = $field;
            }
        }

        if (! empty($missingRequired)) {
            $missingText = implode(', ', $missingRequired);

            return response()->json([
                'message' => "The file is missing required headers: {$missingText}",
                'errors' => [
                    'file' => ["The file is missing required headers: {$missingText}"],
                ],
            ], 422);
        }

        $errors = [];
        $recordsToInsert = [];
        $userId = auth()->id();

        foreach ($rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2; // +1 for 0-index, +1 for header row

            // Skip completely empty rows
            $isEmpty = true;
            foreach ($row as $cell) {
                if ($cell !== null && trim((string) $cell) !== '') {
                    $isEmpty = false;
                    break;
                }
            }
            if ($isEmpty) {
                continue;
            }

            $rawEquipment = $row[$columns['equipment_code']] ?? null;
            $equipmentId = null;
            if ($rawEquipment !== null && trim((string) $rawEquipment) !== '') {
                $equipmentId = $this->resolveEquipmentId((string) $rawEquipment);
                if ($equipmentId === null) {
                    $errors[] = "Row {$rowNumber}: Equipment code '{$rawEquipment}' does not exist.";
                }
            }

            $rawParameter = $row[$columns['parameter_code']] ?? null;
            $parameterId = null;
            if ($rawParameter !== null && trim((string) $rawParameter) !== '') {
                $parameterId = $this->resolveParameterId((string) $rawParameter);
                if ($parameterId === null) {
                    $errors[] = "Row {$rowNumber}: Parameter code '{$rawParameter}' does not exist.";
                }
            }

            $rawUnit = isset($columns['unit_code']) ? ($row[$columns['unit_code']] ?? null) : null;
            $unitId = null;
            if ($rawUnit !== null && trim((string) $rawUnit) !== '') {
                $unitId = $this->resolveUnitId((string) $rawUnit);
                if ($unitId === null) {
                    $errors[] = "Row {$rowNumber}: Unit code '{$rawUnit}' does not exist.";
                }
            }

            // If unit_code is not provided or empty, resolve the default unit of the parameter
            if ($unitId === null && $parameterId !== null) {
                $unitId = EquipmentParameter::where('id', $parameterId)->value('unit_id');
            }

            $recordedAt = null;
            if (isset($columns['recorded_at'])) {
                $rawRecordedAt = $row[$columns['recorded_at']] ?? null;
                $recordedAt = $this->parseDateTime($rawRecordedAt);
            }
            if ($recordedAt === null) {
                $recordedAt = Carbon::now('Asia/Ho_Chi_Minh');
            }

            $val = isset($row[$columns['value']]) ? $row[$columns['value']] : null;
            if ($val !== null && trim((string) $val) !== '') {
                $valStr = trim((string) $val);
                if (is_numeric($valStr)) {
                    $floatVal = (float) $valStr;
                    $val = rtrim(rtrim(sprintf('%.10f', $floatVal), '0'), '.');
                } else {
                    $val = $valStr;
                }
            } else {
                $val = null;
            }

            $data = [
                'equipment_id' => $equipmentId,
                'equipment_parameter_id' => $parameterId,
                'unit_id' => $unitId,
                'value' => $val,
                'user_id' => $userId,
                'recorded_at' => $recordedAt ? $recordedAt->toDateTimeString() : null,
            ];

            $storeRequest = new StoreEquipmentParameterLogRequest;
            $validator = Validator::make($data, $storeRequest->rules());

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $errorMsg) {
                    $errors[] = "Row {$rowNumber}: {$errorMsg}";
                }

                continue;
            }

            $recordsToInsert[] = $validator->validated();
        }

        if (! empty($errors)) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'file' => $errors,
                ],
            ], 422);
        }

        if (empty($recordsToInsert)) {
            return response()->json([
                'message' => 'No records found to import.',
                'errors' => [
                    'file' => ['No records found to import.'],
                ],
            ], 422);
        }

        DB::transaction(function () use ($recordsToInsert): void {
            foreach ($recordsToInsert as $record) {
                EquipmentParameterLog::create($record);
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => count($recordsToInsert).' equipment parameter log records imported successfully.',
        ], 201);
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
}
