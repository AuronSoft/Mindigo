@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-question-bank::app.create_question'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Mindigo/QuestionBank/src/resources/css/app.css',
        'packages/Mindigo/QuestionBank/src/resources/js/app.js',
    ])
@endsection

@section('content')
    <div class="question-page mx-auto flex max-w-5xl flex-col gap-6">
        <header class="question-hero">
            <div>
                <div class="question-breadcrumb">
                    <a href="{{ route('dashboard') }}">@lang('Mindigo-dashboard::app.dashboard')</a>
                    <span>/</span>
                    <a href="{{ route('question-bank.index') }}">@lang('Mindigo-question-bank::app.breadcrumb')</a>
                    <span>/</span>
                    <strong>@lang('Mindigo-question-bank::app.create_question')</strong>
                </div>
                <h1>@lang('Mindigo-question-bank::app.create_question')</h1>
                <p>@lang('Mindigo-question-bank::app.create_description')</p>
            </div>
        </header>

        <form method="POST" action="{{ route('question-bank.store') }}" class="flex flex-col gap-5" data-mindigo-confirm-title="@lang('Mindigo-question-bank::app.confirm_save_title')" data-mindigo-confirm-message="@lang('Mindigo-question-bank::app.confirm_save_message')" data-mindigo-confirm-text="@lang('Mindigo-question-bank::app.save')" data-mindigo-confirm-cancel="@lang('Mindigo-question-bank::app.cancel')">
            @csrf
            @include('Mindigo-question-bank::partials.form')
            <div class="flex flex-wrap justify-end gap-3">
                <a href="{{ route('question-bank.index') }}" class="question-secondary-button">@lang('Mindigo-question-bank::app.cancel')</a>
                <button type="submit" name="submit_for_review" value="1" class="question-secondary-button">@lang('Mindigo-question-bank::app.submit_for_review')</button>
                <button type="submit" class="question-primary-button">@lang('Mindigo-question-bank::app.save')</button>
            </div>
        </form>
    </div>
@endsection
