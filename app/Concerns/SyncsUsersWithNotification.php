<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Models\User;
use App\Notifications\UserPivotAssignmentNotification;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait SyncsUsersWithNotification
{
    /**
     * Sync users to a belongsToMany relation and send notifications to added and removed users.
     *
     * @param  array<int, string>  $newIds
     * @return array<string, array<int, string>> The sync results containing attached, detached, and updated IDs.
     */
    protected function syncUsersAndNotify(
        BelongsToMany $relation,
        array $newIds,
        string $entityType,
        string $entityId,
        string $entityLabel
    ): array {
        $results = $relation->sync($newIds);

        $attachedIds = array_filter($results['attached'] ?? []);
        $detachedIds = array_filter($results['detached'] ?? []);

        // Map entity type to friendly English name for message
        $entityNameEn = match ($entityType) {
            'checklist_session' => 'checklist session',
            'maintenance_schedule' => 'maintenance schedule',
            'error_log' => 'error log',
            'maintenance_item' => 'maintenance item',
            default => 'task',
        };

        if (! empty($attachedIds)) {
            $message = "You have been assigned to the {$entityNameEn} \"{$entityLabel}\".";
            User::findMany($attachedIds)->each(function (User $user) use ($entityType, $entityId, $entityLabel, $message) {
                $user->notify(new UserPivotAssignmentNotification(
                    type: 'assigned',
                    entityType: $entityType,
                    entityId: $entityId,
                    entityLabel: $entityLabel,
                    message: $message
                ));
            });
        }

        if (! empty($detachedIds)) {
            $message = "You have been unassigned from the {$entityNameEn} \"{$entityLabel}\".";
            User::findMany($detachedIds)->each(function (User $user) use ($entityType, $entityId, $entityLabel, $message) {
                $user->notify(new UserPivotAssignmentNotification(
                    type: 'unassigned',
                    entityType: $entityType,
                    entityId: $entityId,
                    entityLabel: $entityLabel,
                    message: $message
                ));
            });
        }

        return $results;
    }
}
