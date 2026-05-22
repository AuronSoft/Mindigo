@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-exam-management::app.edit_exam'))

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css','packages/Mindigo/Dashboard/src/resources/js/app.js','packages/Mindigo/ExamManagement/src/resources/css/app.css','packages/Mindigo/ExamManagement/src/resources/js/app.js'])
@endsection

@section('content')
<div class="exam-page mx-auto flex max-w-6xl flex-col gap-6">
    <header class="exam-hero"><div><div class="exam-breadcrumb"><a href="{{ route('dashboard') }}">@lang('Mindigo-dashboard::app.dashboard')</a><span>/</span><a href="{{ route('exams.index') }}">@lang('Mindigo-exam-management::app.breadcrumb')</a><span>/</span><strong>{{ $exam->title }}</strong></div><h1>@lang('Mindigo-exam-management::app.edit_exam')</h1><p>@lang('Mindigo-exam-management::app.edit_desc')</p></div></header>
    <form method="POST" action="{{ route('exams.update', $exam) }}" class="flex flex-col gap-5" data-mindigo-confirm-title="@lang('Mindigo-exam-management::app.confirm_save_title')" data-mindigo-confirm-message="@lang('Mindigo-exam-management::app.confirm_save_message')" data-mindigo-confirm-text="@lang('Mindigo-exam-management::app.save')" data-mindigo-confirm-cancel="@lang('Mindigo-exam-management::app.cancel')">
        @csrf
        @method('PUT')
        @include('Mindigo-exam-management::partials.form')
        <div class="flex justify-end gap-3"><a href="{{ route('exams.show', $exam) }}" class="exam-secondary-button">@lang('Mindigo-exam-management::app.cancel')</a><button class="exam-primary-button">@lang('Mindigo-exam-management::app.save')</button></div>
    </form>
</div>
@endsection
