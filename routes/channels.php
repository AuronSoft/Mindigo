<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('private-user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Kênh hội thoại (thread). Authorization sẽ hoàn thiện ở Phase 1/3 khi
// bảng teacher_discussion_participants được tạo. Giữ chỗ để không lỗi boot.
Broadcast::channel('private-discussion.{threadId}', function ($user, $threadId) {
    return true;
});
