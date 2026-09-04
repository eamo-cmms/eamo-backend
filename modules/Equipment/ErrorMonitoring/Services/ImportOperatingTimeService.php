<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Services;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Equipment\ErrorMonitoring\Models\OperatingTime;
use Modules\Equipment\ErrorMonitoring\Requests\StoreOperatingTimeRequest;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

final class ImportOperatingTimeService
{
    private const REQUIRED_FIELDS = ['equipment_id', 'planned_stop_time', 'start_time', 'end_time'];

    private const FIELD_MAPPINGS = [
        'equipment_id' => ['equipment_id', 'equipment id', 'equipmentid', 'mã thiết bị', 'code'],
        'equipment_name' => ['equipment_name', 'equipment name', 'equipmentname', 'tên thiết bị'],
        'planned_stop_time' => ['planned_stop_time', 'planned stop time', 'plannedstoptime', 'planned_stop', 'planned stop', 'thời gian dừng kế hoạch'],
        'unplanned_stop_time' => ['unplanned_stop_time', 'unplanned stop time', 'unplannedstoptime', 'unplanned_stop', 'unplanned stop', 'thời gian dừng không kế hoạch'],
        'start_time' => ['start_time', 'start time', 'starttime', 'start', 'bắt đầu', 'thời gian bắt đầu'],
        'end_time' => ['end_time', 'end time', 'endtime', 'end', 'kết thúc', 'thời gian kết thúc'],
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
     * Parse and validate all data rows from spreadsheet.
     *
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array<string, int>  $columns
     * @return array{errors: array<string>, records: array<int, array<string, mixed>>}
     */
    public function parseAndValidateRows(array $rows, array $columns): array
    {
        $errors = [];
        $recordsToInsert = [];
        $importedIntervals = [];
        $rules = (new StoreOperatingTimeRequest)->rules();

        foreach ($rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2; // +1 for 0-index, +1 for header row

            if ($this->isRowEmpty($row)) {
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

            $validator = Validator::make($data, $rules);

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
            $validatedData['date'] = now()->toDateString();

            // Track for subsequent row duplicate checks
            $importedIntervals[$equipmentId][] = [
                'start_time' => $parsedStartTime,
                'end_time' => $parsedEndTime,
            ];

            $recordsToInsert[] = $validatedData;
        }

        return [
            'errors' => $errors,
            'records' => $recordsToInsert,
        ];
    }

    /**
     * Persist validated records to the database inside a transaction.
     *
     * @param  array<int, array<string, mixed>>  $records
     */
    public function saveRecords(array $records): int
    {
        DB::transaction(function () use ($records): void {
            foreach ($records as $record) {
                OperatingTime::create($record);
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
