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

        $roleProfile = $this->service->roleProfile($user->role);
        $dashboardUrl = $user->isAdmin() && Route::has('dashboard') ? route('dashboard') : url('/');

        return view('profile::profile', compact('user', 'roleProfile', 'dashboardUrl'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->service->update($request, Auth::user());

        return redirect()
            ->route('profile.index')
            ->with('success', 'Há»“ sÆ¡ Ä‘Ã£ Ä‘Æ°á»£c cáº­p nháº­t thÃ nh cÃ´ng.');
    }

    public function updateNotifications(UpdateNotificationsRequest $request): RedirectResponse
    {
        $this->service->updateNotifications($request, Auth::user());

        return redirect()
            ->to(route('profile.index') . '#email')
            ->with('success', 'CÃ i Ä‘áº·t thÃ´ng bÃ¡o Ä‘Ã£ Ä‘Æ°á»£c cáº­p nháº­t.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $this->service->updatePassword($request, Auth::user());

        return redirect()
            ->to(route('profile.index') . '#bao-mat')
            ->with('success', 'Máº­t kháº©u Ä‘Ã£ Ä‘Æ°á»£c cáº­p nháº­t thÃ nh cÃ´ng.');
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
            ->with('warning', 'TÃ i khoáº£n cá»§a báº¡n Ä‘Ã£ bá»‹ Ä‘Ã¬nh chá»‰.');
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
            ->with('info', 'TÃ i khoáº£n cá»§a báº¡n Ä‘Ã£ Ä‘Æ°á»£c xoÃ¡ vÄ©nh viá»…n.');
    }
}
