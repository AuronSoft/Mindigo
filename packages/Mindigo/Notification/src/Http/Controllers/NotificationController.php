<?php

namespace Mindigo\Notification\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $filter = $request->input('filter'); // null | 'unread'

        $query = $user->notifications();
        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate(15)->withQueryString();
        $unreadCount = $user->unreadNotifications()->count();

        return view('notification::index', compact('notifications', 'unreadCount', 'filter'));
    }

    // Đánh dấu đã đọc 1 thông báo (rồi điều hướng tới url đính kèm nếu có)

    public function read(Request $request, string $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        if ($url) {
            return redirect()->to($url);
        }

        return redirect()->route('notifications.index');
    }

    // Đánh dấu tất cả đã đọc

    public function readAll()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->route('notifications.index')
            ->with('success', __('notification::app.marked_all_read'));
    }
}
