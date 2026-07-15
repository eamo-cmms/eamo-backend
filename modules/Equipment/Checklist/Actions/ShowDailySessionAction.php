<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistSession;

final class ShowDailySessionAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $request->validate([
            'equipment_id' => ['required', 'string', 'exists:eamo_equipment,id'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $equipmentId = $request->input('equipment_id');
        $dateString = $request->input('date') ?? Carbon::today()->toDateString();
        $date = Carbon::parse($dateString);

        // Find existing session for the equipment on this date
        $session = ChecklistSession::query()
            ->where('equipment_id', $equipmentId)
            ->whereDate('session_date', $date)
            ->first();

        if (! $session) {
            return response()->json([
                'message' => 'Checklist session not found for this date.',
            ], 404);
        }

        return response()->json($session);
    }
}
