<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Services;

use Modules\Equipment\Checklist\Models\ChecklistSession;

final class ShowChecklistSessionService
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function execute(string $id, array $options): ChecklistSession
    {
        $relations = ['users'];
        if (! empty($options['with_details']) || ! empty($options['include_details'])) {
            $relations[] = 'details';
        }

        $query = ChecklistSession::query()->with($relations);

        if (! empty($options['only_trashed'])) {
            $query->onlyTrashed();
        } elseif (! empty($options['with_trashed'])) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }
}
