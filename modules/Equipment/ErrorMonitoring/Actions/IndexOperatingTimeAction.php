<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Actions;

use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

final class IndexOperatingTimeAction
{
    use AsAction;

    public function asController(Request $request): array
    {
        // TODO: Implement custom logic
        return response()->json([]);
    }
}
