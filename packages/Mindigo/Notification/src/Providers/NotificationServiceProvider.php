<?php

namespace Mindigo\Notification\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'notification');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'notification');

        // Chia sẻ thông báo cho mọi view (chuông + badge + dropdown xem nhanh trên sidebar).
        // Cache trong 1 request để không query lặp ở mỗi view.
        View::composer('*', function ($view) {
            if (! auth()->check()) {
                return;
            }

            [$count, $recent] = once(function () {
                $user = auth()->user();

                return [
                    $user->unreadNotifications()->count(),
                    $user->notifications()->latest()->limit(6)->get(),
                ];
            });

            $view->with('globalUnreadNotifications', $count);
            $view->with('globalRecentNotifications', $recent);
        });
    }
}
