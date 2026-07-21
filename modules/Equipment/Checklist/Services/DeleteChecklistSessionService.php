<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Services;

use Modules\Equipment\Checklist\Models\ChecklistSession;
use Modules\Equipment\Services\EquipmentCascadeSoftDeleteService;

final class DeleteChecklistSessionService
{
    public function __construct(
        private readonly EquipmentCascadeSoftDeleteService $cascadeService
    ) {}

    /**
     * @return array{message: string}
     */
    public function execute(string $id): array
    {
        $session = ChecklistSession::findOrFail($id);
        $this->cascadeService->deleteChecklistSession($session);

        return [
            'message' => 'Checklist session deleted successfully.',
        ];
    }
}
