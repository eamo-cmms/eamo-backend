<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Services;

use Modules\Equipment\Checklist\Models\ChecklistDetail;

final class DeleteChecklistDetailService
{
    public function __construct() {}

    /**
     * @return array{message: string}
     */
    public function execute(string $id): array
    {
        $detail = ChecklistDetail::findOrFail($id);
        $detail->delete();

        return [
            'message' => 'Checklist detail deleted successfully.',
        ];
    }
}
