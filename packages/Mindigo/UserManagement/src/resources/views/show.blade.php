@extends('Mindigo-dashboard::layouts')

@section('title', $managedUser->name)

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Mindigo/UserManagement/src/resources/css/app.css',
        'packages/Mindigo/UserManagement/src/resources/js/app.js',
    ])
@endsection

@section('content')
    <div class="user-page mx-auto flex max-w-6xl flex-col gap-6">
        <header class="user-hero">
            <div class="flex min-w-0 items-center gap-4">
                <img src="{{ $managedUser->avatar_url }}" alt="" class="h-16 w-16 rounded-2xl object-cover ring-1 ring-green-100">
                <div class="min-w-0">
                    <div class="user-breadcrumb">
                        <a href="{{ route('dashboard') }}">@lang('Mindigo-dashboard::app.dashboard')</a>
                        <span>/</span>
                        <a href="{{ route('users.index') }}">@lang('Mindigo-user-management::app.breadcrumb')</a>
                        <span>/</span>
                        <strong>{{ $managedUser->name }}</strong>
                    </div>
                    <h1>{{ $managedUser->name }}</h1>
                    <p>{{ $managedUser->email }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('users.edit', $managedUser) }}" class="user-primary-button">@lang('Mindigo-user-management::app.edit')</a>
                <a href="{{ route('users.index') }}" class="user-secondary-button">@lang('Mindigo-user-management::app.back')</a>
            </div>
        </header>

        <section class="grid gap-5 lg:grid-cols-[1fr_1.2fr]">
            <article class="user-card p-5">
                <h2 class="text-lg font-black text-slate-950">@lang('Mindigo-user-management::app.account_status')</h2>
                <dl class="mt-4 grid gap-3">
                    <div class="user-detail-row"><dt>@lang('Mindigo-user-management::app.role')</dt><dd>@lang('Mindigo-user-management::app.roles.' . $managedUser->role)</dd></div>
                    <div class="user-detail-row"><dt>@lang('Mindigo-user-management::app.status')</dt><dd>{{ $managedUser->is_active ? __('Mindigo-user-management::app.statuses.active') : __('Mindigo-user-management::app.statuses.inactive') }}</dd></div>
                    <div class="user-detail-row"><dt>@lang('Mindigo-user-management::app.created_at')</dt><dd>{{ $managedUser->created_at?->format('d/m/Y H:i') }}</dd></div>
                    <div class="user-detail-row"><dt>@lang('Mindigo-user-management::app.updated_at')</dt><dd>{{ $managedUser->updated_at?->format('d/m/Y H:i') }}</dd></div>
                </dl>
            </article>

            <article class="user-card p-5">
                <h2 class="text-lg font-black text-slate-950">@lang('Mindigo-user-management::app.profile')</h2>
                <dl class="mt-4 grid gap-3 md:grid-cols-2">
                    <div class="user-detail-row"><dt>@lang('Mindigo-user-management::app.phone')</dt><dd>{{ $managedUser->phone ?: __('Mindigo-user-management::app.not_set') }}</dd></div>
                    <div class="user-detail-row"><dt>@lang('Mindigo-user-management::app.gender')</dt><dd>{{ $managedUser->gender ? __('Mindigo-user-management::app.genders.' . $managedUser->gender) : __('Mindigo-user-management::app.not_set') }}</dd></div>
                    <div class="user-detail-row"><dt>@lang('Mindigo-user-management::app.date_of_birth')</dt><dd>{{ $managedUser->date_of_birth?->format('d/m/Y') ?: __('Mindigo-user-management::app.not_set') }}</dd></div>
                    <div class="user-detail-row"><dt>@lang('Mindigo-user-management::app.address')</dt><dd>{{ $managedUser->address ?: __('Mindigo-user-management::app.not_set') }}</dd></div>
                </dl>
                @if($managedUser->bio)
                    <div class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm font-semibold leading-6 text-slate-600">{{ $managedUser->bio }}</div>
                @endif
            </article>
        </section>
    </div>
@endsection
