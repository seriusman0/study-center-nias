<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $conversations = Conversation::whereHas(
            'participants',
            fn($q) => $q->where('user_id', $userId)
        )
            ->with([
                'participants:id,name',
                'lastMessage.user:id,name',
            ])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function ($conv) use ($userId) {
                $conv->unread_count = $conv->unreadCount($userId);

                if ($conv->type === 'private') {
                    $other = $conv->participants->firstWhere('id', '!=', $userId);
                    $conv->display_name   = $other?->name ?? 'Unknown';
                    $conv->display_avatar = null;
                } else {
                    $conv->display_name   = $conv->name;
                    $conv->display_avatar = $conv->avatar;
                }

                return $conv;
            });

        $users = User::where('id', '!=', $userId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $totalUnread = $conversations->sum('unread_count');

        return view('chat.index', compact('conversations', 'users', 'totalUnread'));
    }
}
