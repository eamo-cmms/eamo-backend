<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Queries;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Equipment\Checklist\Models\ChecklistSchedule;

/**
 * Dynamic query builder for eamo_checklist_schedules.
 */
final class ChecklistScheduleQuery
{
    /** @var Builder<ChecklistSchedule> */
    private Builder $query;

    /** @var array<string> */
    private array $relations = [];

    public function __construct()
    {
        $this->query = ChecklistSchedule::query();
    }

    public static function make(): self
    {
        return new self;
    }

    // ─── Eager Load Toggles ──────────────────────────────────────────────────

    public function withSession(): self
    {
        $this->relations[] = 'checklistSession';

        return $this;
    }

    public function withEquipment(): self
    {
        $this->relations[] = 'equipment';

        return $this;
    }

    public function withDetail(): self
    {
        $this->relations[] = 'checklistDetail';

        return $this;
    }

    public function withUsers(): self
    {
        $this->relations[] = 'users';

        return $this;
    }

    public function withLogs(): self
    {
        $this->relations[] = 'logs';

        return $this;
    }

    // ─── Dynamic Filters ─────────────────────────────────────────────────────

    public function forEquipment(string $equipmentId): self
    {
        $this->query->where('equipment_id', $equipmentId);

        return $this;
    }

    public function forSession(string $sessionId): self
    {
        $this->query->where('checklist_session_id', $sessionId);

        return $this;
    }

    public function forDetail(string $detailId): self
    {
        $this->query->where('checklist_detail_id', $detailId);

        return $this;
    }

    public function dateRange(?string $from, ?string $to): self
    {
        $this->query
            ->when(filled($from), fn (Builder $q) => $q->where('date', '>=', $from))
            ->when(filled($to), fn (Builder $q) => $q->where('date', '<=', $to));

        return $this;
    }

    public function forDate(string $date): self
    {
        $this->query->whereDate('date', $date);

        return $this;
    }

    public function assignedTo(string $userId): self
    {
        $this->query->whereHas(
            'users',
            fn (Builder $q) => $q->where('users.id', $userId),
        );

        return $this;
    }

    public function includeTrashed(bool $only = false): self
    {
        $only ? $this->query->onlyTrashed() : $this->query->withTrashed();

        return $this;
    }

    // ─── Ordering ────────────────────────────────────────────────────────────

    public function orderByDate(string $direction = 'asc'): self
    {
        $this->query->orderBy('date', $direction);

        return $this;
    }

    // ─── Terminal Methods ─────────────────────────────────────────────────────

    /** @return LengthAwarePaginator<ChecklistSchedule> */
    public function paginate(int $perPage = 50): LengthAwarePaginator
    {
        return $this->build()->paginate($perPage);
    }

    /** @return Collection<int, ChecklistSchedule> */
    public function get(): Collection
    {
        return $this->build()->get();
    }

    public function first(): ?ChecklistSchedule
    {
        return $this->build()->first();
    }

    // ─── Internal ─────────────────────────────────────────────────────────────

    /** @return Builder<ChecklistSchedule> */
    private function build(): Builder
    {
        if ($this->relations !== []) {
            $this->query->with(array_unique($this->relations));
        }

        return $this->query;
    }
}
