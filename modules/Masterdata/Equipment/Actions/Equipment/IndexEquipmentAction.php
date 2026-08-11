<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Equipment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
final class IndexEquipmentAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $query = Equipment::query()
            ->with(['equipmentCategory', 'equipmentParameters.unit', 'equipmentErrors', 'equipmentState', 'equipmentImages'])
            ->withChecklistSessionsAndDetails()
            ->filter($request->all());

        if ($request->boolean('all') || $request->input('paginate') === 'false') {
            $equipments = $query->get();
            $this->appendOperatingTimes($equipments);
            return response()->json($equipments);
        }

        $equipment = $query->paginate($request->integer('per_page', 15));
        $this->appendOperatingTimes($equipment->getCollection());

        return response()->json($equipment);
    }

    private function appendOperatingTimes($equipments): void
    {
        if ($equipments->isEmpty()) {
            return;
        }

        $equipmentIds = $equipments->pluck('id')->all();

        // 1. Fetch total operating times sum from beginning of time for these equipments
        $opTimeSums = DB::table('eamo_operating_times')
            ->whereIn('equipment_id', $equipmentIds)
            ->whereNull('deleted_at')
            ->select('equipment_id', DB::raw('SUM(actual_operating_time) AS total_op'))
            ->groupBy('equipment_id')
            ->get()
            ->keyBy('equipment_id');

        // 2. Fetch operating times sum after cutoff for equipments with last_maintenance
        $withCutoffIds = $equipments
            ->filter(fn ($e) => ! empty($e->last_maintenance['datetime']))
            ->all();

        $opTimeSumsAfterCutoff = collect();
        if (! empty($withCutoffIds)) {
            $afterCutoffMap = [];
            foreach ($withCutoffIds as $equipment) {
                $cutoff = Carbon::parse($equipment->last_maintenance['datetime']);
                $sum = DB::table('eamo_operating_times')
                    ->where('equipment_id', $equipment->id)
                    ->whereNull('deleted_at')
                    ->where('start_time', '>=', $cutoff)
                    ->sum('actual_operating_time');
                $afterCutoffMap[$equipment->id] = $sum;
            }
            $opTimeSumsAfterCutoff = collect($afterCutoffMap);
        }

        // 3. Append actual_operating_time to each equipment object
        foreach ($equipments as $equipment) {
            $hasCutoff = ! empty($equipment->last_maintenance['datetime']);
            if ($hasCutoff) {
                $actualOp = (float) ($opTimeSumsAfterCutoff->get($equipment->id) ?? 0);
            } else {
                $actualOp = (float) ($opTimeSums->get($equipment->id)->total_op ?? 0);
            }
            $equipment->actual_operating_time = round($actualOp, 2);
        }
    }
}
