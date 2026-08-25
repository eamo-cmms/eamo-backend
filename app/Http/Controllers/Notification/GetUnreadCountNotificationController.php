<?php

declare(strict_types=1);

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetUnreadCountNotificationController extends Controller
{
    /**
     * Get the count of unread notifications.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        return response()->json(['unread_count' => $user->unreadNotifications()->count()]);
    }
}
