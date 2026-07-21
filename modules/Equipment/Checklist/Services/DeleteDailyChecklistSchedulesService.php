<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Services;

use Carbon\Carbon;
use Modules\Equipment\Checklist\Queries\ChecklistScheduleQuery;
use Modules\Equipment\Services\EquipmentCascadeSoftDeleteService;

final class DeleteDailyChecklistSchedulesService
{
    public function __construct(
        private readonly EquipmentCascadeSoftDeleteService $cascadeService
    ) {}

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

        $this->cascadeService->deleteChecklistSchedules($schedules);

        return [
            'deleted_count' => $schedules->count(),
            'message' => 'Checklist schedules deleted successfully.',
        ];
    }
}
