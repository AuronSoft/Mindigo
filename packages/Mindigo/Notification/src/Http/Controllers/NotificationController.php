<?php

namespace Mindigo\Notification\Http\Controllers;

use App\Support\Notification\NotificationCategorization;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\Auth\Models\User;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $filter = $request->input('filter'); // null | 'unread'
        $category = $request->input('category'); // null | 'announcement' | 'system'

        $query = $user->notifications();
        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }
        if (in_array($category, [NotificationCategorization::CATEGORY_ANNOUNCEMENT, NotificationCategorization::CATEGORY_SYSTEM], true)) {
            NotificationCategorization::scopeCategory($query, $category);
        }

        $notifications = $query->paginate(15)->withQueryString();

        $unreadCount = $user->unreadNotifications()->count();
        $unreadAnnouncementCount = $user->unreadNotifications()
            ->where('data->category', 'announcement')
            ->count();

        return view('notification::index', compact('notifications', 'unreadCount', 'unreadAnnouncementCount', 'filter', 'category'));
    }

    // Đánh dấu đã đọc 1 thông báo (rồi điều hướng tới url đính kèm nếu có)
    public function read(Request $request, string $id)
    {
        /** @var User $user */
        $user = Auth::user();

        $notification = $user->notifications()->findOrFail($id);
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
        /** @var User $user */
        $user = Auth::user();

        $user->unreadNotifications->markAsRead();

        return redirect()->route('notifications.index')
            ->with('success', __('notification::app.marked_all_read'));
    }
}
