<?php

namespace Mindigo\Profile\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Mindigo\Profile\Http\Requests\UpdatePasswordRequest;
use Mindigo\Profile\Http\Requests\UpdateNotificationsRequest;
use Mindigo\Profile\Http\Requests\UpdateProfileRequest;
use Mindigo\Profile\Services\ProfileService;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $service
    ) {}

    /**
     * Hiển thị trang hồ sơ cá nhân.
     */
    public function index(): View
    {
        /** @var \Mindigo\Auth\Models\User $user */
        $user = Auth::user();
        $user->load('notificationPreference');

        return view('profile::profile', compact('user')); 
    }

    /**
     * Cập nhật mật khẩu người dùng.
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $this->service->updatePassword($request, Auth::user());

        return redirect()
            ->route('profile.index', ['#bao-mat'])
            ->with('success', 'Mật khẩu đã được cập nhật thành công.');
    }

    /**
     * Tạm khóa/Đình chỉ tài khoản.
     */
    public function suspend(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Gọi service xử lý cập nhật trạng thái trước
        $this->service->suspend($user);

        // Đăng xuất và xóa session an toàn
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('warning', 'Tài khoản của bạn đã bị đình chỉ.');
    }

    /**
     * Xóa vĩnh viễn tài khoản.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Đăng xuất và xóa session trước khi thực hiện xóa bản ghi trong DB
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Tiến hành xóa user thông qua service
        $this->service->destroy($user);

        return redirect()
            ->route('login')
            ->with('info', 'Tài khoản của bạn đã được xoá vĩnh viễn.');
    }

    /**
     * Cập nhật cấu hình nhận email thông báo.
     */
    public function updateNotifications(UpdateNotificationsRequest $request): RedirectResponse
    {
        $this->service->updateNotifications($request, Auth::user());

        return redirect()
            ->route('profile.index', ['#email'])
            ->with('success', 'Cài đặt thông báo đã được cập nhật.');
    }

    /**
     * Cập nhật thông tin chi tiết hồ sơ cá nhân.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->service->update($request, Auth::user());

        return redirect()
            ->route('profile.index')
            ->with('success', 'Hồ sơ đã được cập nhật thành công.');
    }
}