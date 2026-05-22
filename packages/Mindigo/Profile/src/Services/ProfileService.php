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

    public function roleProfile(?string $role): array
    {
        return match ($role) {
            'admin' => [
                'label' => 'Quáº£n trá»‹ viÃªn',
                'summary' => 'Quáº£n lÃ½ váº­n hÃ nh há»‡ thá»‘ng, tÃ i khoáº£n, ná»™i dung Ã´n thi vÃ  cáº¥u hÃ¬nh báº£o máº­t.',
                'badge' => 'bg-green-50 text-green-700 ring-green-100',
                'icon' => 'shield',
                'items' => ['Quáº£n lÃ½ ngÆ°á»i dÃ¹ng', 'Theo dÃµi váº­n hÃ nh', 'Cáº¥u hÃ¬nh há»‡ thá»‘ng'],
            ],
            'teacher' => [
                'label' => 'GiÃ¡o viÃªn',
                'summary' => 'Quáº£n lÃ½ há»c liá»‡u, ngÃ¢n hÃ ng cÃ¢u há»i, Ä‘á» thi vÃ  theo dÃµi káº¿t quáº£ há»c viÃªn.',
                'badge' => 'bg-sky-50 text-sky-700 ring-sky-100',
                'icon' => 'book',
                'items' => ['NgÃ¢n hÃ ng cÃ¢u há»i', 'Äá» thi phá»¥ trÃ¡ch', 'Káº¿t quáº£ há»c viÃªn'],
            ],
            default => [
                'label' => 'Há»c viÃªn',
                'summary' => 'Theo dÃµi há»“ sÆ¡ há»c táº­p, lá»‹ch Ã´n luyá»‡n, káº¿t quáº£ lÃ m bÃ i vÃ  thÃ´ng bÃ¡o má»›i.',
                'badge' => 'bg-amber-50 text-amber-700 ring-amber-100',
                'icon' => 'user',
                'items' => ['Lá»™ trÃ¬nh Ã´n luyá»‡n', 'Lá»‹ch sá»­ lÃ m bÃ i', 'ThÃ´ng bÃ¡o há»c táº­p'],
            ],
        };
    }
}
