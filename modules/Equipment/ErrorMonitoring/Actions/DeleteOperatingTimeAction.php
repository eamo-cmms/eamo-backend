<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ErrorMonitoring\Models\OperatingTime;

final class DeleteOperatingTimeAction
{
    use AsAction;

    public function asController(string $id): JsonResponse
    {
        $time = OperatingTime::findOrFail($id);
        $time->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Operating time deleted successfully',
        ]);
    }
}
