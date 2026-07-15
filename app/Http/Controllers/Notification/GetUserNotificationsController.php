<?php

declare(strict_types=1);

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GetUserNotificationsController extends Controller
{
    /**
     * Get a specific user's notifications along with count.
     */
    public function __invoke(Request $request, string $userId): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $query = $user->notifications()->latest('created_at');

        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', CarbonImmutable::parse((string) $request->input('start_date')));
        }

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);
        $notifications = $query->paginate($perPage);
        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }
}
