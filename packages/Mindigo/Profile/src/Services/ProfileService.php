<?php

namespace Mindigo\Profile\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\Profile\Http\Requests\UpdateNotificationsRequest;
use Mindigo\Profile\Http\Requests\UpdatePasswordRequest;
use Mindigo\Profile\Http\Requests\UpdateProfileRequest;
use Mindigo\Profile\Models\NotificationPreference;

class ProfileService
{
    public function update(UpdateProfileRequest $request, User $user): void
    {
        $data = $request->only([
            'name',
            'phone',
            'date_of_birth',
            'gender',
            'address',
            'bio',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->fill($data);
        $user->save();
    }

    public function updateNotifications(UpdateNotificationsRequest $request, User $user): void
    {
        NotificationPreference::updateOrCreate(
            ['user_id' => $user->id],
            [
                'notif_new_quiz' => $request->boolean('notif_new_quiz'),
                'notif_system_news' => $request->boolean('notif_system_news'),
            ]
        );
    }

    public function updatePassword(UpdatePasswordRequest $request, User $user): void
    {
        if (! Hash::check($request->current_password, $user->getAuthPassword())) {
            throw ValidationException::withMessages([
                'current_password' => 'Mật khẩu hiện tại không đúng.',
            ]);
        }

        if (Hash::check($request->password, $user->getAuthPassword())) {
            throw ValidationException::withMessages([
                'password' => 'Mật khẩu mới không được trùng với mật khẩu hiện tại.',
            ]);
        }

        $user->password = Hash::needsRehash($request->password)
            ? Hash::make($request->password)
            : $request->password;
        $user->save();
    }

    public function suspend(User $user): void
    {
        $user->is_active = false;
        $user->save();
    }

    public function destroy(User $user): void
    {
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();
    }
}
