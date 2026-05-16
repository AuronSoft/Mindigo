<?php

namespace Mindigo\Profile\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\Profile\Http\Requests\UpdatePasswordRequest;
use Mindigo\Profile\Http\Requests\UpdateNotificationsRequest;
use Mindigo\Profile\Http\Requests\UpdateProfileRequest;
use Mindigo\Profile\Models\NotificationPreference;

class ProfileService
{
    /**
     * Cập nhật mật khẩu người dùng.
     */
    public function updatePassword(UpdatePasswordRequest $request, User $user): void
    {
        if (!Hash::check($request->current_password, $user->getAuthPassword())) {
            throw ValidationException::withMessages([
                'current_password' => 'Mật khẩu hiện tại không đúng.',
            ]);
        }

        if (Hash::check($request->password, $user->getAuthPassword())) {
            throw ValidationException::withMessages([
                'password' => 'Mật khẩu mới không được trùng với mật khẩu hiện tại.',
            ]);
        }

        // Tự động Hash mật khẩu khi lưu (hoặc dùng thuộc tính hashed trong Model Laravel 10+)
        $user->password = Hash::needsRehash($request->password) ? Hash::make($request->password) : $request->password;
        $user->save();
    }

    /**
     * Tạm khóa tài khoản người dùng.
     */
    public function suspend(User $user): void
    {
        $user->is_active = false;
        $user->save();
    }

    /**
     * Xóa vĩnh viễn tài khoản người dùng.
     */
    public function destroy(User $user): void
    {
        // Xóa avatar cũ của user nếu có trước khi xóa tài khoản
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        
        $user->delete();
    }

    /**
     * Cập nhật cấu hình nhận thông báo của người dùng.
     */
    public function updateNotifications(UpdateNotificationsRequest $request, User $user): void
    {
        NotificationPreference::updateOrCreate(
            ['user_id' => $user->id], 
            [
                'notif_new_quiz'     => $request->notif_new_quiz,     
                'notif_system_news'  => $request->notif_system_news,  
            ]
        );
    }

    /**
     * Cập nhật thông tin hồ sơ cá nhân (Profile).
     */
    public function update(UpdateProfileRequest $request, User $user): void
    {
        // Lọc các trường dữ liệu cá nhân sạch sẽ của Mindigo ID
        $data = $request->only([
            'first_name', 'last_name', 'phone', 'date_of_birth',
            'gender', 'address', 'language', 'bio'
        ]);

        // Xử lý Upload Avatar
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->fill($data);
        $user->save();
    }
}