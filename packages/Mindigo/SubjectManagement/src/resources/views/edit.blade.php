@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-subject-management::app.edit_subject'))

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css','packages/Mindigo/Dashboard/src/resources/js/app.js','packages/Mindigo/SubjectManagement/src/resources/css/app.css','packages/Mindigo/SubjectManagement/src/resources/js/app.js'])
@endsection

@section('content')
<div class="subject-page mx-auto flex max-w-5xl flex-col gap-6">
    <header class="subject-hero"><div><div class="subject-breadcrumb"><a href="{{ route('dashboard') }}">@lang('Mindigo-dashboard::app.dashboard')</a><span>/</span><a href="{{ route('subjects.index') }}">@lang('Mindigo-subject-management::app.breadcrumb')</a><span>/</span><strong>{{ $subject->name }}</strong></div><h1>@lang('Mindigo-subject-management::app.edit_subject')</h1><p>@lang('Mindigo-subject-management::app.edit_desc')</p></div></header>
    <form method="POST" action="{{ route('subjects.update', $subject) }}" class="flex flex-col gap-5" data-mindigo-confirm-title="@lang('Mindigo-subject-management::app.confirm_save_title')" data-mindigo-confirm-message="@lang('Mindigo-subject-management::app.confirm_save_message')" data-mindigo-confirm-text="@lang('Mindigo-subject-management::app.save')" data-mindigo-confirm-cancel="@lang('Mindigo-subject-management::app.cancel')">
        @csrf @method('PUT')
        @include('Mindigo-subject-management::partials.form')
        <div class="flex justify-end gap-3"><a href="{{ route('subjects.show', $subject) }}" class="subject-secondary-button">@lang('Mindigo-subject-management::app.cancel')</a><button class="subject-primary-button">@lang('Mindigo-subject-management::app.save')</button></div>
    </form>
</div>
@endsection
