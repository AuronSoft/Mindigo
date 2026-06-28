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

        // Chia sẻ số thông báo chưa đọc cho mọi view (chuông + badge trên sidebar)
        // Cache trong 1 request để không query lặp ở mỗi view.
        View::composer('*', function ($view) {
            if (! auth()->check()) {
                return;
            }

            $count = once(fn () => auth()->user()->unreadNotifications()->count());
            $view->with('globalUnreadNotifications', $count);
        });
    }
}
