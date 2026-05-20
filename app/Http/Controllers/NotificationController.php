<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display a listing of the notifications.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $tab = $request->query('tab', 'unread');

        $query = match ($tab) {
            'read' => $user->readNotifications(),
            'all' => $user->notifications(),
            default => $user->unreadNotifications(),
        };

        $notifications = $query->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('notifications.index', compact('notifications', 'tab'));
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(DatabaseNotification $notification)
    {
        // Validate ownership to avoid cross-tenant security issues
        if ($notification->notifiable_id !== auth()->id() || $notification->notifiable_type !== get_class(auth()->user())) {
            abort(403);
        }

        $notification->markAsRead();

        return back()->with('success', app()->getLocale() === 'ar' ? 'تم تحديد التنبيه كمقروء' : 'Notification marked as read');
    }

    /**
     * Mark all unread notifications of the user as read.
     */
    public function markAllAsRead()
    {
        $user = auth()->user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
        }

        return back()->with('success', app()->getLocale() === 'ar' ? 'تم تحديد جميع التنبيهات كمقروءة' : 'All notifications marked as read');
    }
}
