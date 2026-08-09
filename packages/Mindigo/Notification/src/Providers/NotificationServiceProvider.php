<?php

declare(strict_types=1);

namespace Mindigo\Notification\Providers;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Mindigo\Notification\Observers\DatabaseNotificationObserver;
use Mindigo\Notification\View\Composers\GlobalNotificationsComposer;

class NotificationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'notification');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'notification');

        // Broadcast realtime qua kênh private-user.{id} khi có notification mới được tạo
        // (announcement, chấm điểm, hệ thống...). Client dùng Echo cập nhật badge + dropdown.
        DatabaseNotification::observe(DatabaseNotificationObserver::class);

        // Chia sẻ thông báo cho mọi view (chuông + badge + dropdown xem nhanh trên sidebar).
        // Cache trong 1 request để không query lặp ở mỗi view.
        View::composer('*', GlobalNotificationsComposer::class);
    }
}
