<?php

namespace Mindigo\Notification\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Mindigo\Notification\Notifications\AnnouncementPublished;

class NotificationCategorization
{
    public const CATEGORY_ANNOUNCEMENT = 'announcement';

    public const CATEGORY_SYSTEM = 'system';

    /**
     * Áp bộ lọc theo nhóm lên query notifications.
     *
     * @param  string  $category  'announcement' | 'system'
     */
    public static function scopeCategory(Relation|Builder $query, string $category): Relation|Builder
    {
        if ($category === self::CATEGORY_ANNOUNCEMENT) {
            return $query->where('data->category', AnnouncementPublished::CATEGORY);
        }

        // 'system' = mọi thứ không phải announcement
        return $query->where(function (Builder $q): void {
            $q->where('data->category', '!=', AnnouncementPublished::CATEGORY)
                ->orWhereNull('data->category');
        });
    }

    /**
     * Phân loại một notification model (từ data payload).
     */
    public static function categoryOf(Model $notification): string
    {
        $category = $notification->data['category'] ?? null;

        return $category === AnnouncementPublished::CATEGORY
            ? self::CATEGORY_ANNOUNCEMENT
            : self::CATEGORY_SYSTEM;
    }
}
