<?php

declare(strict_types=1);

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReadNotificationController extends Controller
{
    /**
     * Mark a notification as read.
     */
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $notification = $user->notifications()->find($id);

        if (! $notification) {
            return response()->json(['message' => 'Notification not found'], Response::HTTP_NOT_FOUND);
        }

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read']);
    }
}
