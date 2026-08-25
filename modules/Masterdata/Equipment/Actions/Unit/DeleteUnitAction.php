<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Unit;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Unit;

final class DeleteUnitAction
{
    use AsAction;

    public function asController(Request $request, string $id): JsonResponse
    {
        $unit = Unit::findOrFail($id);
        Gate::authorize('delete', $unit);

        $unit->delete();

        return response()->json(['message' => __('equipment.unit_deleted')]);
    }
}
