<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Requests\StoreChecklistDetailRequest;
use Modules\Equipment\Checklist\Services\StoreChecklistDetailService;
use Throwable;

final class StoreChecklistDetailAction
{
    use AsAction;

    public function __construct(
        private readonly StoreChecklistDetailService $service
    ) {}

    /**
     * @throws Throwable
     */
    public function asController(StoreChecklistDetailRequest $request): JsonResponse
    {
        $result = $this->service->execute($request->validated(), $request->user());

        return response()->json($result, 201);
    }
}
