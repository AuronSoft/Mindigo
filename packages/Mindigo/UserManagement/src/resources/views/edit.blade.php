@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-user-management::app.edit_user'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Mindigo/UserManagement/src/resources/css/app.css',
        'packages/Mindigo/UserManagement/src/resources/js/app.js',
    ])
@endsection

@section('content')
    <div class="user-page mx-auto flex max-w-5xl flex-col gap-6">
        <header class="user-hero">
            <div>
                <div class="user-breadcrumb">
                    <a href="{{ route('dashboard') }}">@lang('Mindigo-dashboard::app.dashboard')</a>
                    <span>/</span>
                    <a href="{{ route('users.index') }}">@lang('Mindigo-user-management::app.breadcrumb')</a>
                    <span>/</span>
                    <strong>{{ $managedUser->name }}</strong>
                </div>
                <h1>@lang('Mindigo-user-management::app.edit_user')</h1>
                <p>@lang('Mindigo-user-management::app.edit_description')</p>
            </div>
        </header>

        <form
            method="POST"
            action="{{ route('users.update', $managedUser) }}"
            class="user-card p-5"
            data-mindigo-confirm-title="@lang('Mindigo-user-management::app.confirm_update_title')"
            data-mindigo-confirm-message="@lang('Mindigo-user-management::app.confirm_update_message')"
            data-mindigo-confirm-text="@lang('Mindigo-user-management::app.save')"
            data-mindigo-confirm-cancel="@lang('Mindigo-user-management::app.cancel')"
        >
            @csrf
            @method('PUT')
            @include('Mindigo-user-management::partials.form')
            <div class="mt-5 flex flex-wrap justify-end gap-3">
                <a href="{{ route('users.show', $managedUser) }}" class="user-secondary-button">@lang('Mindigo-user-management::app.cancel')</a>
                <button type="submit" class="user-primary-button">@lang('Mindigo-user-management::app.save')</button>
            </div>
        </form>
    </div>
@endsection
