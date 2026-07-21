<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Equipment\Checklist\Models\ChecklistDetail;

final class IndexChecklistDetailService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        return ChecklistDetail::query()
            ->filter($filters)
            ->paginate($perPage);
    }
}
