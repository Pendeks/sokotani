<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()->notifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(string $notificationId): RedirectResponse
    {
        $notification = auth()->user()->notifications()->whereKey($notificationId)->firstOrFail();
        $notification->markAsRead();

        return redirect()->to($notification->data['url'] ?? route('notifications.index'));
    }

    public function markAllRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
