<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserPivotAssignmentNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param  string  $type  'assigned' | 'unassigned'
     * @param  string  $entityType  'checklist_session' | 'maintenance_schedule' | 'error_log' | 'maintenance_item'
     */
    public function __construct(
        public string $type,
        public string $entityType,
        public string $entityId,
        public string $entityLabel,
        public string $message
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'entity_label' => $this->entityLabel,
            'message' => $this->message,
        ];
    }
}
