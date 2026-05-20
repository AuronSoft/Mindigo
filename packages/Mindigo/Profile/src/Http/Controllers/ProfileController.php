<?php

namespace Mindigo\Profile\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Mindigo\Profile\Http\Requests\UpdateNotificationsRequest;
use Mindigo\Profile\Http\Requests\UpdatePasswordRequest;
use Mindigo\Profile\Http\Requests\UpdateProfileRequest;
use Mindigo\Profile\Services\ProfileService;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $service
    ) {}

    public function index(): View
    {
        /** @var \Mindigo\Auth\Models\User $user */
        $user = Auth::user();
        $user->load('notificationPreference');

        $roleProfile = $this->roleProfile($user->role);
        $dashboardUrl = $user->isAdmin() && Route::has('dashboard') ? route('dashboard') : url('/');

        return view('profile::profile', compact('user', 'roleProfile', 'dashboardUrl'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->service->update($request, Auth::user());

        return redirect()
            ->route('profile.index')
            ->with('success', 'Hồ sơ đã được cập nhật thành công.');
    }

    public function updateNotifications(UpdateNotificationsRequest $request): RedirectResponse
    {
        $this->service->updateNotifications($request, Auth::user());

        return redirect()
            ->to(route('profile.index') . '#email')
            ->with('success', 'Cài đặt thông báo đã được cập nhật.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $this->service->updatePassword($request, Auth::user());

        return redirect()
            ->to(route('profile.index') . '#bao-mat')
            ->with('success', 'Mật khẩu đã được cập nhật thành công.');
    }

    public function suspend(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $this->service->suspend($user);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('warning', 'Tài khoản của bạn đã bị đình chỉ.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $this->service->destroy($user);

        return redirect()
            ->route('login')
            ->with('info', 'Tài khoản của bạn đã được xoá vĩnh viễn.');
    }

    private function roleProfile(?string $role): array
    {
        return match ($role) {
            'admin' => [
                'label' => 'Quản trị viên',
                'summary' => 'Quản lý vận hành hệ thống, tài khoản, nội dung ôn thi và cấu hình bảo mật.',
                'badge' => 'bg-green-50 text-green-700 ring-green-100',
                'icon' => 'shield',
                'items' => ['Quản lý người dùng', 'Theo dõi vận hành', 'Cấu hình hệ thống'],
            ],
            'teacher' => [
                'label' => 'Giáo viên',
                'summary' => 'Quản lý học liệu, ngân hàng câu hỏi, đề thi và theo dõi kết quả học viên.',
                'badge' => 'bg-sky-50 text-sky-700 ring-sky-100',
                'icon' => 'book',
                'items' => ['Ngân hàng câu hỏi', 'Đề thi phụ trách', 'Kết quả học viên'],
            ],
            default => [
                'label' => 'Học viên',
                'summary' => 'Theo dõi hồ sơ học tập, lịch ôn luyện, kết quả làm bài và thông báo mới.',
                'badge' => 'bg-amber-50 text-amber-700 ring-amber-100',
                'icon' => 'user',
                'items' => ['Lộ trình ôn luyện', 'Lịch sử làm bài', 'Thông báo học tập'],
            ],
        };
    }
}
