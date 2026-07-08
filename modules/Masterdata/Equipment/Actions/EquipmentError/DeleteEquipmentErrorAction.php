<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentError;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\EquipmentError;

final class DeleteEquipmentErrorAction
{
    use AsAction;

    public function asController(Request $request, string $id): JsonResponse
    {
        $error = EquipmentError::findOrFail($id);
        $error->delete();

        return response()->json(['message' => 'Equipment error deleted successfully.']);
    }
}
