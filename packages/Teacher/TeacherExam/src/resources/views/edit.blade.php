@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-exam::app.edit') . ' — ' . $exam->title)

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Mindigo/ExamManagement/src/resources/css/app.css',
        'packages/Mindigo/ExamManagement/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    <header class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white/90 px-6 py-4 backdrop-blur">
        <div class="flex items-center gap-3">
            <a href="{{ route('teacher.exams.show', $exam) }}"
               class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline transition hover:bg-green-50 hover:text-green-700">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
            </a>
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">{{ $exam->title }}</p>
                <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('teacher-exam::app.edit')</h1>
            </div>
        </div>
    </header>

    <div class="p-6">
        <form method="POST" action="{{ route('teacher.exams.update', $exam) }}" class="w-full space-y-4">
            @csrf @method('PUT')
            @include('Mindigo-exam-management::partials.form')
        </form>
    </div>
</div>
@endsection
