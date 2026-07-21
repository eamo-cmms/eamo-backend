<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Requests\IndexChecklistDetailRequest;
use Modules\Equipment\Checklist\Services\IndexChecklistDetailService;

final class IndexChecklistDetailAction
{
    use AsAction;

    public function __construct(
        private readonly IndexChecklistDetailService $service
    ) {}

    public function asController(IndexChecklistDetailRequest $request): JsonResponse
    {
        $details = $this->service->execute($request->all());

        return response()->json($details);
    }
}
