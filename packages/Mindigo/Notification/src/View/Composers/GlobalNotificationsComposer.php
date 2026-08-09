<?php

declare(strict_types=1);

namespace Mindigo\Notification\View\Composers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;

class GlobalNotificationsComposer
{
    public const CATEGORY_ANNOUNCEMENT = 'announcement';

    private static array $resolved = [];

    public function __construct(private readonly AuthFactory $auth) {}

    public function compose(View $view): void
    {
        $guard = $this->auth->guard();

        if (! $guard->check()) {
            return;
        }

        /** @var Authenticatable&Model $user */
        $user = $guard->user();
        $cacheKey = (string) $user->getAuthIdentifier();

        [$count, $unreadAnnouncement, $recent] = self::$resolved[$cacheKey] ??= [
            $user->unreadNotifications()->count(),
            $user->unreadNotifications()
                ->where('data->category', self::CATEGORY_ANNOUNCEMENT)
                ->count(),
            $user->notifications()->latest()->limit(6)->get(),
        ];

        $view->with([
            'globalUnreadNotifications' => $count,
            'globalUnreadAnnouncementNotifications' => $unreadAnnouncement,
            'globalRecentNotifications' => $recent,
        ]);
    }
}
