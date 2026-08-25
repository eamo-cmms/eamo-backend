<?php

declare(strict_types=1);

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReadAllNotificationsController extends Controller
{
    /**
     * Mark all notifications as read.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        $user->unreadNotifications->markAsRead();

        return response()->json(['message' => __('notification.read_all_success')]);
    }
}
