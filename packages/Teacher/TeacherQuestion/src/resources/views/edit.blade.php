@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-question::app.edit'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Mindigo/QuestionBank/src/resources/css/app.css',
        'packages/Mindigo/QuestionBank/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    <header class="sticky top-0 z-10 flex items-center gap-3 border-b border-slate-200 bg-white/95 px-6 py-3 backdrop-blur">
        <a href="{{ route('teacher.questions.show', $question) }}"
           class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline transition hover:bg-green-50 hover:text-green-700">
            <x-heroicon-o-arrow-left class="h-4 w-4" />
        </a>
        <div>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">@lang('teacher-question::app.title')</p>
            <h1 class="text-base font-black text-slate-950">@lang('teacher-question::app.edit')</h1>
        </div>
    </header>
 
     <div class="question-page mx-auto w-full max-w-5xl p-6">
    <form method="POST" action="{{ route('teacher.questions.update', $question) }}" class="w-full">
        @csrf @method('PUT')
        @include('Mindigo-question-bank::partials.form')
    </form>
</div>

    <div class="mx-auto w-full max-w-5xl p-6">
    <form method="POST" action="{{ route('teacher.questions.update', $question) }}" class="w-full space-y-4">
            @csrf @method('PUT')
            @include('Mindigo-question-bank::partials.form')
        </form>
    </div>
</div>
@endsection
