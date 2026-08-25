<?php

declare(strict_types=1);

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReadNotificationController extends Controller
{
    /**
     * Mark a notification as read.
     */
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        $notification = $user->notifications()->find($id);
        abort_unless($notification, 404, __('notification.not_found'));

        $notification->markAsRead();

        return response()->json(['message' => __('notification.read_success')]);
    }
}
