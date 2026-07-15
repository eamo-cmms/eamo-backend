<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Equipment;

final class UpdateEquipmentParentAction
{
    use AsAction;

    /**
     * Update the parent_id of the specified equipment.
     */
    public function asController(Request $request, string $id): JsonResponse
    {
        $equipment = Equipment::findOrFail($id);

        $data = $request->validate([
            'parent_id' => [
                'nullable',
                'string',
                'uuid',
                'exists:eamo_equipment,id',
                Rule::notIn([$id]),
            ],
        ]);

        $parentId = $data['parent_id'];

        if ($parentId !== null) {
            $parent = Equipment::find($parentId);
            $ancestor = $parent;

            while ($ancestor !== null) {
                if ($ancestor->id === $id) {
                    return response()->json([
                        'message' => 'The parent equipment cannot be a child or descendant of this equipment.',
                        'errors' => [
                            'parent_id' => ['Circular reference detected.'],
                        ],
                    ], 422);
                }
                $ancestor = $ancestor->parent;
            }
        }

        $equipment->update([
            'parent_id' => $parentId,
        ]);

        return response()->json($equipment->load(['equipmentCategory', 'equipmentState', 'parent']));
    }
}
