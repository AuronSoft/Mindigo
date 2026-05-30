<?php

namespace Mindigo\UserManagement\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\Auth\Models\User;
use Mindigo\UserManagement\Http\Requests\UserRequest;
use Mindigo\UserManagement\Services\UserManagementService;
use Symfony\Component\HttpFoundation\Response;

class UserManagementController extends Controller
{
    public function __construct(private UserManagementService $users)
    {
    }

    public function index(Request $request)
    {
        $this->authorizePermission($request->user(), 'users.view');

        return view('Mindigo-user-management::index', [
            'users' => $this->users->filteredList($request->only(['keyword', 'role', 'status'])),
            'roles' => User::ROLES,
            'statuses' => $this->users->statuses(),
            'filters' => $request->only(['keyword', 'role', 'status']),
            'stats' => $this->users->stats(),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizePermission($request->user(), 'users.create');

        return view('Mindigo-user-management::create', [
            'roles' => User::ROLES,
            'genders' => User::GENDERS,
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = $this->users->create($request->userData());

        return redirect()
            ->route('users.show', $user)
            ->with('success', __('Mindigo-user-management::app.messages.created'));
    }

    public function show(Request $request, User $user)
    {
        $this->authorizePermission($request->user(), 'users.view');

        return view('Mindigo-user-management::show', [
            'managedUser' => $user,
        ]);
    }

    public function edit(Request $request, User $user)
    {
        $this->authorizePermission($request->user(), 'users.update');

        return view('Mindigo-user-management::edit', [
            'managedUser' => $user,
            'roles' => User::ROLES,
            'genders' => User::GENDERS,
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $this->users->update($user, $request->userData());

        return redirect()
            ->route('users.show', $user)
            ->with('success', __('Mindigo-user-management::app.messages.updated'));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'users.delete');

        if ((int) $request->user()->getAuthIdentifier() === (int) $user->getKey()) {
            return back()->with('error', __('Mindigo-user-management::app.messages.self_delete_blocked'));
        }

        $this->users->delete($user);

        return redirect()
            ->route('users.index')
            ->with('success', __('Mindigo-user-management::app.messages.deleted'));
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'users.restore');
        $user = $this->users->restore($id);

        return redirect()
            ->route('users.show', $user)
            ->with('success', __('Mindigo-user-management::app.messages.restored'));
    }

    private function authorizePermission(User $user, string $permission): void
    {
        if ($this->users->can($user, $permission)) {
            return;
        }

        abort(Response::HTTP_FORBIDDEN);
    }
}
