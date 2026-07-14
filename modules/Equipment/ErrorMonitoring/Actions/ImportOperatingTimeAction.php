<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Actions;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ErrorMonitoring\Models\OperatingTime;
use Modules\Equipment\ErrorMonitoring\Requests\ImportOperatingTimeRequest;
use Modules\Equipment\ErrorMonitoring\Requests\StoreOperatingTimeRequest;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

final class ImportOperatingTimeAction
{
    use AsAction;

    public function asController(ImportOperatingTimeRequest $request): JsonResponse
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

        $requiredFields = ['equipment_id', 'planned_stop_time', 'start_time', 'end_time'];
        $fieldMappings = [
            'equipment_id' => ['equipment_id', 'equipment id', 'equipmentid', 'mã thiết bị', 'code'],
            'equipment_name' => ['equipment_name', 'equipment name', 'equipmentname', 'tên thiết bị'],
            'planned_stop_time' => ['planned_stop_time', 'planned stop time', 'plannedstoptime', 'planned_stop', 'planned stop', 'thời gian dừng kế hoạch'],
            'unplanned_stop_time' => ['unplanned_stop_time', 'unplanned stop time', 'unplannedstoptime', 'unplanned_stop', 'unplanned stop', 'thời gian dừng không kế hoạch'],
            'start_time' => ['start_time', 'start time', 'starttime', 'start', 'bắt đầu', 'thời gian bắt đầu'],
            'end_time' => ['end_time', 'end time', 'endtime', 'end', 'kết thúc', 'thời gian kết thúc'],
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
        $importedIntervals = [];

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

            $rawStartTime = $row[$columns['start_time']] ?? null;
            $rawEndTime = $row[$columns['end_time']] ?? null;

            $startTime = $this->parseDateTime($rawStartTime);
            $endTime = $this->parseDateTime($rawEndTime);

            $data = [
                'equipment_id' => $row[$columns['equipment_id']] ?? null,
                'equipment_name' => isset($columns['equipment_name']) ? ($row[$columns['equipment_name']] ?? null) : null,
                'planned_stop_time' => $row[$columns['planned_stop_time']] ?? null,
                'unplanned_stop_time' => isset($columns['unplanned_stop_time']) ? ($row[$columns['unplanned_stop_time']] ?? null) : null,
                'start_time' => $startTime ? $startTime->toDateTimeString() : $rawStartTime,
                'end_time' => $endTime ? $endTime->toDateTimeString() : $rawEndTime,
            ];

            // Normalize type representation
            if ($data['planned_stop_time'] !== null && trim((string) $data['planned_stop_time']) !== '') {
                $data['planned_stop_time'] = filter_var($data['planned_stop_time'], FILTER_VALIDATE_FLOAT) !== false
                    ? (float) $data['planned_stop_time']
                    : $data['planned_stop_time'];
            }
            if ($data['unplanned_stop_time'] !== null && trim((string) $data['unplanned_stop_time']) !== '') {
                $data['unplanned_stop_time'] = filter_var($data['unplanned_stop_time'], FILTER_VALIDATE_FLOAT) !== false
                    ? (float) $data['unplanned_stop_time']
                    : $data['unplanned_stop_time'];
            }

            $storeRequest = new StoreOperatingTimeRequest;
            $validator = Validator::make($data, $storeRequest->rules());

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $errorMsg) {
                    $errors[] = "Row {$rowNumber}: {$errorMsg}";
                }

                continue;
            }

            $validatedData = $validator->validated();

            // Local duplicate overlap validation within the same Excel/CSV file
            $equipmentId = $validatedData['equipment_id'];
            $parsedStartTime = Carbon::parse($validatedData['start_time']);
            $parsedEndTime = Carbon::parse($validatedData['end_time']);

            $hasLocalOverlap = false;
            if (isset($importedIntervals[$equipmentId])) {
                foreach ($importedIntervals[$equipmentId] as $interval) {
                    if ($parsedStartTime->lt($interval['end_time']) && $parsedEndTime->gt($interval['start_time'])) {
                        $errors[] = "Row {$rowNumber}: The operating time overlaps with another row in this file for the same equipment.";
                        $hasLocalOverlap = true;
                        break;
                    }
                }
            }

            if ($hasLocalOverlap) {
                continue;
            }

            // Database overlap validation
            $overlapExists = OperatingTime::query()
                ->where('equipment_id', $equipmentId)
                ->where('start_time', '<', $parsedEndTime)
                ->where('end_time', '>', $parsedStartTime)
                ->exists();

            if ($overlapExists) {
                $errors[] = "Row {$rowNumber}: The operating time overlaps with an existing operating time in the database for this equipment.";

                continue;
            }

            // Calculation fields
            $diffInMinutes = $parsedStartTime->diffInMinutes($parsedEndTime);
            $workingTime = round($diffInMinutes / 60.0, 2);

            $plannedStopTime = (float) $validatedData['planned_stop_time'];
            $unplannedStopTime = (float) ($validatedData['unplanned_stop_time'] ?? 0);

            $plannedOperatingTime = max(0.0, $workingTime - $plannedStopTime);
            $actualOperatingTime = max(0.0, $plannedOperatingTime - $unplannedStopTime);

            $availabilityFactor = $plannedOperatingTime > 0
                ? round(($actualOperatingTime / $plannedOperatingTime) * 100, 2)
                : 0.0;

            $validatedData['working_time'] = $workingTime;
            $validatedData['planned_operating_time'] = $plannedOperatingTime;
            $validatedData['actual_operating_time'] = $actualOperatingTime;
            $validatedData['availability_factor'] = $availabilityFactor;
            $validatedData['date'] = Carbon::now('Asia/Ho_Chi_Minh')->toDateString();

            // Track for subsequent row duplicate checks
            $importedIntervals[$equipmentId][] = [
                'start_time' => $parsedStartTime,
                'end_time' => $parsedEndTime,
            ];

            $recordsToInsert[] = $validatedData;
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
                OperatingTime::create($record);
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => count($recordsToInsert).' operating time records imported successfully.',
        ], 201);
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
