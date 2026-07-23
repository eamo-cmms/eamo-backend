<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Equipment;

final class UpdateEquipmentErrorsAction
{
    use AsAction;

    public function asController(Request $request, string $id): JsonResponse
    {
        $equipment = Equipment::findOrFail($id);

        $data = $request->validate([
            'equipment_error_ids' => ['required', 'array'],
            'equipment_error_ids.*' => ['string', 'exists:eamo_equipment_errors,id'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        $errorIds = array_values(array_unique(array_filter($data['equipment_error_ids'])));
        $occurredAt = $data['occurred_at'] ?? null;

        DB::transaction(function () use ($equipment, $errorIds, $occurredAt): void {
            $now = now();

            // Soft delete definition records no longer in the list
            DB::table('eamo_equipment_error_logs')
                ->where('equipment_id', $equipment->id)
                ->whereNull('occurred_at')
                ->whereNotIn('equipment_error_id', $errorIds)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => $now]);

            // Restore soft-deleted or insert new definition records for requested error IDs
            foreach ($errorIds as $errorId) {
                $definition = DB::table('eamo_equipment_error_logs')
                    ->where('equipment_id', $equipment->id)
                    ->where('equipment_error_id', $errorId)
                    ->first();

                if ($definition) {
                    DB::table('eamo_equipment_error_logs')
                        ->where('id', $definition->id)
                        ->update([
                            'occurred_at' => $definition->occurred_at ?? $occurredAt,
                            'deleted_at' => null,
                            'updated_at' => $now,
                        ]);
                } else {
                    DB::table('eamo_equipment_error_logs')->insert([
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'equipment_id' => $equipment->id,
                        'equipment_error_id' => $errorId,
                        'occurred_at' => $occurredAt,
                        'restarted_at' => null,
                        'handled_at' => null,
                        'is_synced' => false,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => null,
                    ]);
                }
            }
        });

        return response()->json(
            $equipment->fresh()->load(['equipmentCategory', 'equipmentErrors', 'equipmentState', 'equipmentImages'])
        );
    }
}

