<?php

namespace Mindigo\UserManagement\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Mindigo\Auth\Models\User;
use Symfony\Component\HttpFoundation\Response;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizePermission($request->user(), 'users.view');

        $query = User::query()->latest('updated_at');

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->input('keyword'));
            $query->where(function ($builder) use ($keyword) {
                $builder->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->input('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->input('status') === 'inactive') {
            $query->where('is_active', false);
        } elseif ($request->input('status') === 'deleted') {
            $query->onlyTrashed();
        }

        return view('Mindigo-user-management::index', [
            'users' => $query->paginate(12)->withQueryString(),
            'roles' => User::ROLES,
            'statuses' => $this->statuses(),
            'filters' => $request->only(['keyword', 'role', 'status']),
            'stats' => $this->stats(),
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

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'users.create');

        $validated = $request->validate($this->rules());
        $user = User::query()->create($validated);

        $this->audit('create', [], $user->only($this->auditFields()), $user);

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

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'users.update');

        $validated = $request->validate($this->rules($user));
        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $oldValues = $user->only($this->auditFields());
        $user->fill($validated)->save();

        $this->audit('update', $oldValues, $user->only($this->auditFields()), $user);

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

        $oldValues = $user->only($this->auditFields());
        $user->delete();

        $this->audit('delete', $oldValues, [], $user);

        return redirect()
            ->route('users.index')
            ->with('success', __('Mindigo-user-management::app.messages.deleted'));
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'users.restore');

        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        $this->audit('restore', [], $user->only($this->auditFields()), $user);

        return redirect()
            ->route('users.show', $user)
            ->with('success', __('Mindigo-user-management::app.messages.restored'));
    }

    private function rules(?User $user = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'max:255'],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'phone' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', Rule::in(array_keys(User::GENDERS))],
            'date_of_birth' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:1000'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    private function stats(): array
    {
        return [
            'total' => User::query()->count(),
            'active' => User::query()->where('is_active', true)->count(),
            'teachers' => User::query()->where('role', 'teacher')->count(),
            'students' => User::query()->where('role', 'student')->count(),
        ];
    }

    private function statuses(): array
    {
        return [
            'active' => __('Mindigo-user-management::app.statuses.active'),
            'inactive' => __('Mindigo-user-management::app.statuses.inactive'),
            'deleted' => __('Mindigo-user-management::app.statuses.deleted'),
        ];
    }

    private function auditFields(): array
    {
        return ['id', 'name', 'email', 'role', 'phone', 'gender', 'date_of_birth', 'is_active'];
    }

    private function authorizePermission(User $user, string $permission): void
    {
        if (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo($permission)) {
            return;
        }

        abort(Response::HTTP_FORBIDDEN);
    }

    private function audit(string $action, array $oldValues, array $newValues, User $user): void
    {
        if (!class_exists(\Mindigo\AuditLog\Services\AuditLogService::class)) {
            return;
        }

        app(\Mindigo\AuditLog\Services\AuditLogService::class)->record(
            $action,
            'users',
            $oldValues,
            $newValues,
            ['user_id' => $user->id, 'email' => $user->email],
            $user
        );
    }
}
