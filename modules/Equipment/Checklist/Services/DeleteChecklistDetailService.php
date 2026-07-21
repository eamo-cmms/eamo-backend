<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Services;

use Modules\Equipment\Checklist\Models\ChecklistDetail;
use Modules\Equipment\Services\EquipmentCascadeSoftDeleteService;

final class DeleteChecklistDetailService
{
    public function __construct(
        private readonly EquipmentCascadeSoftDeleteService $cascadeService
    ) {}

    /**
     * @return array{message: string}
     */
    public function execute(string $id): array
    {
        $detail = ChecklistDetail::findOrFail($id);
        $this->cascadeService->deleteChecklistDetail($detail);

        return [
            'message' => 'Checklist detail deleted successfully.',
        ];
    }
}
