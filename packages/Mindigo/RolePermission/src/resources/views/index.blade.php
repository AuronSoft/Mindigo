@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-role-permission::app.title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Mindigo/RolePermission/src/resources/css/app.css',
        'packages/Mindigo/RolePermission/src/resources/js/app.js',
    ])
@endsection

@section('content')
    <div class="role-permission-page mx-auto flex max-w-7xl flex-col gap-6">
        <header class="role-permission-hero">
            <div class="min-w-0">
                <div class="flex items-center gap-1.5 text-xs font-black text-slate-400">
                    <a href="{{ route('dashboard') }}" class="text-slate-500 no-underline transition hover:text-green-700">@lang('Mindigo-dashboard::app.dashboard')</a>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    <span class="text-slate-700">@lang('Mindigo-role-permission::app.breadcrumb')</span>
                </div>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950">@lang('Mindigo-role-permission::app.heading')</h1>
                <p class="mt-1 max-w-3xl text-sm font-semibold leading-6 text-slate-500">@lang('Mindigo-role-permission::app.description')</p>
            </div>
            <div class="role-permission-summary">
                <span class="text-xs font-black uppercase tracking-wide text-green-700">@lang('Mindigo-role-permission::app.fixed_roles')</span>
                <strong>{{ count($roles) }}</strong>
                <span>@lang('Mindigo-role-permission::app.total_permissions', ['count' => $totalPermissions])</span>
            </div>
        </header>

        <section class="grid gap-4 lg:grid-cols-3">
            @foreach($roles as $role => $label)
                <article class="role-card">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="role-avatar role-avatar-{{ $role }}">{{ mb_substr($label, 0, 1) }}</span>
                            <div class="min-w-0">
                                <h2 class="truncate text-base font-black text-slate-950">{{ $label }}</h2>
                                <p class="mt-1 text-xs font-bold leading-5 text-slate-500">{{ $roleDescriptions[$role] }}</p>
                            </div>
                        </div>
                        <span class="role-count">{{ count($permissionMap[$role] ?? []) }}/{{ $totalPermissions }}</span>
                    </div>
                    <div class="mt-5 h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-green-500" style="width: {{ round((count($permissionMap[$role] ?? []) / max($totalPermissions, 1)) * 100) }}%"></div>
                    </div>
                </article>
            @endforeach
        </section>

        <form
            class="role-matrix"
            method="POST"
            action="{{ route('role-permissions.update') }}"
            data-mindigo-confirm-title="@lang('Mindigo-role-permission::app.confirm_title')"
            data-mindigo-confirm-message="@lang('Mindigo-role-permission::app.confirm_message')"
            data-mindigo-confirm-text="@lang('Mindigo-role-permission::app.save')"
            data-mindigo-confirm-cancel="@lang('Mindigo-role-permission::app.cancel')"
        >
            @csrf
            @method('PUT')

            <div class="role-matrix-header">
                <div>
                    <p class="text-xs font-black uppercase tracking-wide text-slate-400">@lang('Mindigo-role-permission::app.matrix_label')</p>
                    <h2 class="mt-1 text-lg font-black text-slate-950">@lang('Mindigo-role-permission::app.matrix_title')</h2>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="role-readonly-badge">@lang('Mindigo-role-permission::app.admin_locked')</span>
                    <button type="submit" class="role-save-button">
                        <svg viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current stroke-[2.5]" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        @lang('Mindigo-role-permission::app.save')
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="role-table">
                    <thead>
                        <tr>
                            <th>@lang('Mindigo-role-permission::app.permission')</th>
                            @foreach($roles as $role => $label)
                                <th>{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permissionGroups as $group)
                            <tr class="role-group-row">
                                <td colspan="{{ count($roles) + 1 }}">{{ $group['label'] }}</td>
                            </tr>
                            @foreach($group['permissions'] as $permission => $label)
                                <tr>
                                    <td>
                                        <span class="block text-sm font-black text-slate-900">{{ $label }}</span>
                                        <span class="mt-0.5 block text-xs font-semibold text-slate-400">{{ $permission }}</span>
                                    </td>
                                    @foreach($roles as $role => $roleLabel)
                                        @php($allowed = in_array($permission, $permissionMap[$role] ?? [], true))
                                        <td>
                                            <label class="permission-toggle" title="{{ $role === 'admin' ? __('Mindigo-role-permission::app.admin_locked') : ($allowed ? __('Mindigo-role-permission::app.allowed') : __('Mindigo-role-permission::app.denied')) }}">
                                                <input
                                                    type="checkbox"
                                                    name="permissions[{{ $role }}][{{ $permission }}]"
                                                    value="1"
                                                    {{ $allowed ? 'checked' : '' }}
                                                    {{ $role === 'admin' ? 'disabled' : '' }}
                                                >
                                                <span class="permission-state {{ $allowed ? 'permission-state-on' : 'permission-state-off' }}">
                                                    <svg viewBox="0 0 24 24" class="permission-check h-4 w-4 fill-none stroke-current stroke-[3]" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                    <svg viewBox="0 0 24 24" class="permission-x h-4 w-4 fill-none stroke-current stroke-[3]" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                </span>
                                            </label>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>
    </div>
@endsection
