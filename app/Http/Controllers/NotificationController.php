<?php

namespace App\Http\Controllers;

class NotificationController extends Controller
{
    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Notifications marked as read.');
    }
}
