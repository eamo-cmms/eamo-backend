<?php

declare(strict_types=1);

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndexNotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        $notifications = $user->notifications()->paginate(15);

        return response()->json($notifications);
    }
}
