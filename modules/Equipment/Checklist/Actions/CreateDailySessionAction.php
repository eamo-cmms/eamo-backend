<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistDetail;
use Modules\Equipment\Checklist\Models\ChecklistSession;
use Modules\Masterdata\Equipment\Models\Equipment;

final class CreateDailySessionAction
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

        // Verify if a session already exists
        $exists = ChecklistSession::query()
            ->where('equipment_id', $equipmentId)
            ->whereDate('session_date', $date)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Checklist session already exists for this date.',
            ], 422);
        }

        $equipment = Equipment::findOrFail($equipmentId);

        // Create a new session dynamically
        $session = ChecklistSession::create([
            'id' => (string) Str::uuid(),
            'name' => "Checklist - {$equipment->name} - {$date->toDateString()}",
            'equipment_id' => $equipmentId,
            'session_date' => $date,
        ]);

        // Sync the user who initiated it to the session
        $currentUser = $request->user();
        if ($currentUser) {
            $session->users()->sync([$currentUser->id]);
        }

        // Populate default checklist items
        $defaultItems = [
            'CHK-ID-001' => 'Visual inspection of machine body for damage',
            'CHK-ID-002' => 'Verify emergency stop switch function',
            'CHK-ID-003' => 'Check coolant / fluid levels',
            'CHK-ID-004' => 'Verify safety guard sensors and interlocks',
            'CHK-ID-005' => 'Check power cables and grounding wires',
            'CHK-ID-006' => 'Verify calibration parameters',
        ];

        foreach ($defaultItems as $checklistId => $description) {
            ChecklistDetail::create([
                'id' => (string) Str::uuid(),
                'session_id' => $session->id,
                'checklist_id' => $checklistId,
                'description' => $description,
            ]);
        }

        // Refresh the session with details and logs (eager loaded by default)
        $session->load('details');

        return response()->json($session, 201);
    }
}
