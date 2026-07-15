<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistSchedule;
use Modules\Equipment\Checklist\Models\ChecklistSession;
use Modules\Equipment\Checklist\Requests\UpdateChecklistSessionRequest;
use Modules\Equipment\Checklist\Services\ChecklistSessionUpdateService;
use Throwable;

final class UpdateChecklistSessionAction
{
    use AsAction;

    public function __construct(
        private readonly ChecklistSessionUpdateService $updateService
    ) {}

    /**
     * @throws Throwable
     */
    public function asController(string $id, UpdateChecklistSessionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $session = DB::transaction(function () use ($id, $data) {
            $session = ChecklistSession::findOrFail($id);

            return $this->updateService->update($session, $data);
        });

        $sessionResponse = $session->toArray();
        if (isset($data['session_date'])) {
            $sessionResponse['session_date'] = $data['session_date'];
        } else {
            $latestSchedule = ChecklistSchedule::where('checklist_session_id', $session->id)->latest('date')->first();
            $latestDate = $latestSchedule?->date;
            $sessionResponse['session_date'] = $latestDate
                ? CarbonImmutable::parse($latestDate)->toDateString()
                : null;
        }
        $sessionResponse['users'] = $session->users;

        return response()->json($sessionResponse);
    }
}
