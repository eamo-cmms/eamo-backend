<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Services;

use Modules\Equipment\Checklist\Models\ChecklistSession;

final class DeleteChecklistSessionService
{
    public function __construct() {}

    /**
     * @return array{message: string}
     */
    public function execute(string $id): array
    {
        $session = ChecklistSession::findOrFail($id);
        $session->delete();

        return [
            'message' => __('checklist.session_deleted'),
        ];
    }
}
