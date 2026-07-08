<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\StandardParameter;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\StandardParameter;

final class DeleteStandardParameterAction
{
    use AsAction;

    public function asController(Request $request, string $id): JsonResponse
    {
        $parameter = StandardParameter::findOrFail($id);
        $parameter->delete();

        return response()->json(['message' => 'Standard parameter deleted successfully.']);
    }
}
