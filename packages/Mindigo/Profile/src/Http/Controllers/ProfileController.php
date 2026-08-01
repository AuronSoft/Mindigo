<?php

namespace Mindigo\Profile\Http\Controllers;

use App\Support\RoleRedirector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Mindigo\Auth\Models\User;
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
        /** @var User $user */
        $user = Auth::user();
        $user->load('notificationPreference');

        $roleProfile = $this->service->roleProfile($user->role);

        $dashboardUrl = RoleRedirector::pathFor($user);

        return view('profile::profile', compact('user', 'roleProfile', 'dashboardUrl'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->service->update($request, Auth::user());

        return redirect()
            ->route('profile.index')
            ->with('success', __('Mindigo-profile::app.messages.profile_updated'));
    }

    public function updateNotifications(UpdateNotificationsRequest $request): RedirectResponse
    {
        $this->service->updateNotifications($request, Auth::user());

        return redirect()
            ->to(route('profile.index').'#email')
            ->with('success', __('Mindigo-profile::app.messages.notifications_updated'));
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $this->service->updatePassword($request, Auth::user());

        return redirect()
            ->to(route('profile.index').'#bao-mat')
            ->with('success', __('Mindigo-profile::app.messages.password_updated'));
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
            ->with('warning', __('Mindigo-profile::app.messages.account_suspended'));
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
            ->with('info', __('Mindigo-profile::app.messages.account_deleted'));
    }
}
