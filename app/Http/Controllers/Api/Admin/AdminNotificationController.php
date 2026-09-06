<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    /// GET /admin/admin-notifications?unread_only=1
    /// Paginated list (latest first); optional unread-only filter.
    public function index(Request $request): JsonResponse
    {
        $query = AdminNotification::query()->latest();
        if ($request->boolean('unread_only')) {
            $query->whereNull('read_at');
        }
        return response()->json($query->paginate(20));
    }

    /// POST /admin/admin-notifications/{notif}/mark-read
    public function markRead(AdminNotification $notification): JsonResponse
    {
        $notification->update(['read_at' => now()]);
        return response()->json(['message' => 'Notification marked as read.']);
    }
}
