<?php

use Illuminate\Support\Facades\Broadcast;
use Mindigo\TeacherDiscussion\Models\DiscussionThread;

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

// Kênh hội thoại (thread). Chỉ thành viên trong bảng participants mới được lắng nghe.
Broadcast::channel('private-discussion.{threadId}', function ($user, $threadId) {
    return DiscussionThread::query()
        ->whereKey($threadId)
        ->whereHas('participants', fn ($query) => $query->where('user_id', $user->id))
        ->exists();
});
