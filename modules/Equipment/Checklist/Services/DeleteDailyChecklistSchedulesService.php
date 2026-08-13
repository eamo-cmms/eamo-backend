<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Services;

use Carbon\Carbon;
use Modules\Equipment\Checklist\Queries\ChecklistScheduleQuery;

final class DeleteDailyChecklistSchedulesService
{
    public function __construct() {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{deleted_count: int, message: string}
     */
    public function execute(array $data): array
    {
        $schedules = ChecklistScheduleQuery::make()
            ->forSession($data['session_id'])
            ->forEquipment($data['equipment_id'])
            ->forDate(Carbon::parse($data['date'])->toDateString())
            ->get();

        $schedules->each->delete();

        return [
            'deleted_count' => $schedules->count(),
            'message' => 'Checklist schedules deleted successfully.',
        ];
    }
}
