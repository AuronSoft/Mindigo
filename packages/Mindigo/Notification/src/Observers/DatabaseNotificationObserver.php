<?php

declare(strict_types=1);

namespace Mindigo\Notification\Observers;

use Illuminate\Notifications\DatabaseNotification;
use Mindigo\Notification\Events\NotificationBroadcast;

class DatabaseNotificationObserver
{
    public function created(DatabaseNotification $notification): void
    {
        broadcast(new NotificationBroadcast($notification));
    }
}
