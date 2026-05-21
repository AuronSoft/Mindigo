@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-user-management::app.title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Mindigo/UserManagement/src/resources/css/app.css',
        'packages/Mindigo/UserManagement/src/resources/js/app.js',
    ])
@endsection

@section('content')
    <div class="user-page mx-auto flex max-w-7xl flex-col gap-6">
        <header class="user-hero">
            <div class="min-w-0">
                <div class="user-breadcrumb">
                    <a href="{{ route('dashboard') }}">@lang('Mindigo-dashboard::app.dashboard')</a>
                    <span>/</span>
                    <strong>@lang('Mindigo-user-management::app.breadcrumb')</strong>
                </div>
                <h1>@lang('Mindigo-user-management::app.heading')</h1>
                <p>@lang('Mindigo-user-management::app.description')</p>
            </div>
            <a href="{{ route('users.create') }}" class="user-primary-button">
                <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-[2.5]" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                @lang('Mindigo-user-management::app.create_user')
            </a>
        </header>

        <section class="grid gap-4 md:grid-cols-4">
            @foreach(['total' => 'total_users', 'active' => 'active_users', 'teachers' => 'teachers', 'students' => 'students'] as $key => $label)
                <article class="user-stat-card">
                    <span>@lang('Mindigo-user-management::app.' . $label)</span>
                    <strong>{{ number_format($stats[$key] ?? 0) }}</strong>
                </article>
            @endforeach
        </section>

        <form method="GET" action="{{ route('users.index') }}" class="user-filter">
            <input name="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="@lang('Mindigo-user-management::app.search_placeholder')" class="user-input">
            <select name="role" class="user-select">
                <option value="">@lang('Mindigo-user-management::app.all_roles')</option>
                @foreach($roles as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['role'] ?? '') === $key)>@lang('Mindigo-user-management::app.roles.' . $key)</option>
                @endforeach
            </select>
            <select name="status" class="user-select">
                <option value="">@lang('Mindigo-user-management::app.all_statuses')</option>
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="user-filter-button">@lang('Mindigo-user-management::app.filter')</button>
            <a href="{{ route('users.index') }}" class="user-secondary-button">@lang('Mindigo-user-management::app.reset')</a>
        </form>

        <section class="user-card overflow-hidden">
            @if($users->count())
                <div class="overflow-x-auto">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>@lang('Mindigo-user-management::app.user')</th>
                                <th>@lang('Mindigo-user-management::app.role')</th>
                                <th>@lang('Mindigo-user-management::app.status')</th>
                                <th>@lang('Mindigo-user-management::app.phone')</th>
                                <th>@lang('Mindigo-user-management::app.updated_at')</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $managedUser)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $managedUser->avatar_url }}" alt="" class="h-10 w-10 rounded-xl object-cover ring-1 ring-slate-200">
                                            <span class="min-w-0">
                                                <a href="{{ $managedUser->trashed() ? '#' : route('users.show', $managedUser) }}" class="block truncate font-black text-slate-900 no-underline hover:text-green-700">{{ $managedUser->name }}</a>
                                                <span class="block truncate text-xs font-semibold text-slate-400">{{ $managedUser->email }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td><span class="user-badge user-role-{{ $managedUser->role }}">@lang('Mindigo-user-management::app.roles.' . $managedUser->role)</span></td>
                                    <td>
                                        @if($managedUser->trashed())
                                            <span class="user-badge user-status-deleted">@lang('Mindigo-user-management::app.statuses.deleted')</span>
                                        @elseif($managedUser->is_active)
                                            <span class="user-badge user-status-active">@lang('Mindigo-user-management::app.statuses.active')</span>
                                        @else
                                            <span class="user-badge user-status-inactive">@lang('Mindigo-user-management::app.statuses.inactive')</span>
                                        @endif
                                    </td>
                                    <td class="text-sm font-bold text-slate-500">{{ $managedUser->phone ?: __('Mindigo-user-management::app.not_set') }}</td>
                                    <td class="text-sm font-bold text-slate-500">{{ $managedUser->updated_at?->diffForHumans() }}</td>
                                    <td>
                                        <div class="flex justify-end gap-2">
                                            @if($managedUser->trashed())
                                                <form
                                                    method="POST"
                                                    action="{{ route('users.restore', $managedUser->id) }}"
                                                    data-mindigo-confirm-title="@lang('Mindigo-user-management::app.confirm_restore_title')"
                                                    data-mindigo-confirm-message="@lang('Mindigo-user-management::app.confirm_restore_message')"
                                                    data-mindigo-confirm-text="@lang('Mindigo-user-management::app.restore')"
                                                    data-mindigo-confirm-cancel="@lang('Mindigo-user-management::app.cancel')"
                                                >
                                                    @csrf
                                                    <button type="submit" class="user-secondary-button">@lang('Mindigo-user-management::app.restore')</button>
                                                </form>
                                            @else
                                                <a href="{{ route('users.edit', $managedUser) }}" class="user-secondary-button">@lang('Mindigo-user-management::app.edit')</a>
                                                <form
                                                    method="POST"
                                                    action="{{ route('users.destroy', $managedUser) }}"
                                                    data-mindigo-confirm-title="@lang('Mindigo-user-management::app.confirm_delete_title')"
                                                    data-mindigo-confirm-message="@lang('Mindigo-user-management::app.confirm_delete_message')"
                                                    data-mindigo-confirm-text="@lang('Mindigo-user-management::app.delete')"
                                                    data-mindigo-confirm-cancel="@lang('Mindigo-user-management::app.cancel')"
                                                    data-mindigo-confirm-type="danger"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="user-danger-button">@lang('Mindigo-user-management::app.delete')</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-4">{{ $users->links() }}</div>
            @else
                <div class="p-6">
                    @include('core::partials.empty-state', [
                        'title' => __('Mindigo-user-management::app.empty_title'),
                        'message' => __('Mindigo-user-management::app.empty_desc'),
                    ])
                </div>
            @endif
        </section>
    </div>
@endsection
