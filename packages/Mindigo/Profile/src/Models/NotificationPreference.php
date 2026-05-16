<?php

namespace Mindigo\Profile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;

class NotificationPreference extends Model
{
    protected $table = 'notification_preferences';

    protected $fillable = [
        'user_id',            
        'notif_new_quiz',     // Thông báo khi có đề thi trắc nghiệm mới
        'notif_system_news',  // Thông báo tin tức/cập nhật hệ thống
    ];

    /**
     * Ép kiểu dữ liệu (Casting) về dạng boolean để khi lấy dữ liệu ra 
     * nó tự trả về true/false, giúp Frontend dễ xử lý checkbox.
     */
    protected $casts = [
        'notif_new_quiz'    => 'boolean',
        'notif_system_news' => 'boolean',
    ];

    /**
     * Mối quan hệ: Cấu hình thông báo này thuộc về một User nhất định.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}