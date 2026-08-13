<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Unit;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Unit;

final class IndexUnitAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $query = Unit::query();

        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        } elseif ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('code', 'like', "%{$q}%");
            });
        }

        $units = $query->paginate($request->integer('per_page', 100));

        return response()->json($units);
    }
}
