<?php

declare(strict_types=1);

namespace Modules\Equipment\ParameterLog\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ParameterLog\Requests\SaveEquipmentParameterLogRequest;

final class SaveEquipmentParameterLogAction
{
    use AsAction;

    public function asController(SaveEquipmentParameterLogRequest $request)
    {
        // TODO: Implement custom logic
        return response()->json([]);
    }
}
