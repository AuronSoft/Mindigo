@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-classroom-management::app.create_classroom'))

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css','packages/Mindigo/Dashboard/src/resources/js/app.js','packages/Mindigo/ClassroomManagement/src/resources/css/app.css','packages/Mindigo/ClassroomManagement/src/resources/js/app.js'])
@endsection

@section('content')
<div class="classroom-page mx-auto flex max-w-5xl flex-col gap-6">
    <header class="classroom-hero"><div><div class="classroom-breadcrumb"><a href="{{ route('dashboard') }}">@lang('Mindigo-dashboard::app.dashboard')</a><span>/</span><a href="{{ route('classrooms.index') }}">@lang('Mindigo-classroom-management::app.breadcrumb')</a><span>/</span><strong>@lang('Mindigo-classroom-management::app.create_classroom')</strong></div><h1>@lang('Mindigo-classroom-management::app.create_classroom')</h1><p>@lang('Mindigo-classroom-management::app.create_desc')</p></div></header>
    <form method="POST" action="{{ route('classrooms.store') }}" class="flex flex-col gap-5" data-mindigo-confirm-title="@lang('Mindigo-classroom-management::app.confirm_save_title')" data-mindigo-confirm-message="@lang('Mindigo-classroom-management::app.confirm_save_message')" data-mindigo-confirm-text="@lang('Mindigo-classroom-management::app.save')" data-mindigo-confirm-cancel="@lang('Mindigo-classroom-management::app.cancel')">
        @csrf
        @include('Mindigo-classroom-management::partials.form')
        <div class="flex justify-end gap-3"><a href="{{ route('classrooms.index') }}" class="classroom-secondary-button">@lang('Mindigo-classroom-management::app.cancel')</a><button class="classroom-primary-button">@lang('Mindigo-classroom-management::app.save')</button></div>
    </form>
</div>
@endsection
