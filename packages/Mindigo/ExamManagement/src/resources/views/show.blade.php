@extends('Mindigo-dashboard::layouts')

@section('title', $exam->title)

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css','packages/Mindigo/Dashboard/src/resources/js/app.js','packages/Mindigo/ExamManagement/src/resources/css/app.css','packages/Mindigo/ExamManagement/src/resources/js/app.js'])
@endsection

@section('content')
<div class="exam-page mx-auto flex max-w-7xl flex-col gap-6">
    <header class="exam-hero">
        <div><div class="exam-breadcrumb"><a href="{{ route('dashboard') }}">@lang('Mindigo-dashboard::app.dashboard')</a><span>/</span><a href="{{ route('exams.index') }}">@lang('Mindigo-exam-management::app.breadcrumb')</a><span>/</span><strong>{{ $exam->title }}</strong></div><h1>{{ $exam->title }}</h1><p>{{ $exam->description ?: __('Mindigo-exam-management::app.no_description') }}</p></div>
        <div class="flex flex-wrap gap-2">
            @if(auth()->user()?->hasPermissionTo('exams.publish') && $exam->status !== 'published')<form method="POST" action="{{ route('exams.publish', $exam) }}" data-mindigo-confirm-title="@lang('Mindigo-exam-management::app.confirm_publish_title')" data-mindigo-confirm-message="@lang('Mindigo-exam-management::app.confirm_publish_message')" data-mindigo-confirm-text="@lang('Mindigo-exam-management::app.publish')" data-mindigo-confirm-cancel="@lang('Mindigo-exam-management::app.cancel')">@csrf<button class="exam-primary-button">@lang('Mindigo-exam-management::app.publish')</button></form>@endif
            @if(auth()->user()?->hasPermissionTo('exams.publish') && $exam->status === 'published')<form method="POST" action="{{ route('exams.close', $exam) }}">@csrf<button class="exam-secondary-button">@lang('Mindigo-exam-management::app.close_exam')</button></form>@endif
            @if(auth()->user()?->hasPermissionTo('exams.attempt') && $exam->isOpen())<form method="POST" action="{{ route('exams.start', $exam) }}">@csrf<button class="exam-primary-button">@lang('Mindigo-exam-management::app.start_exam')</button></form>@endif
            @if(auth()->user()?->hasPermissionTo('exams.update'))<a href="{{ route('exams.edit', $exam) }}" class="exam-secondary-button">@lang('Mindigo-exam-management::app.edit')</a>@endif
        </div>
    </header>

    <section class="grid gap-4 md:grid-cols-4">
        <article class="exam-stat-card"><span>@lang('Mindigo-exam-management::app.status')</span><strong class="text-xl">@lang('Mindigo-exam-management::app.statuses.' . $exam->status)</strong></article>
        <article class="exam-stat-card"><span>@lang('Mindigo-exam-management::app.duration')</span><strong>{{ $exam->duration_minutes }}</strong></article>
        <article class="exam-stat-card"><span>@lang('Mindigo-exam-management::app.questions')</span><strong>{{ $exam->total_questions }}</strong></article>
        <article class="exam-stat-card"><span>@lang('Mindigo-exam-management::app.total_points')</span><strong>{{ number_format((float) $exam->total_points, 2) }}</strong></article>
    </section>

    <section class="grid gap-5 lg:grid-cols-[1fr_0.85fr]">
        <article class="exam-card overflow-hidden">
            <div class="exam-card-head"><h2>@lang('Mindigo-exam-management::app.question_snapshot')</h2><p>@lang('Mindigo-exam-management::app.question_snapshot_desc')</p></div>
            <div class="divide-y divide-slate-100">
                @foreach($exam->questions as $question)
                    <div class="p-5"><div class="flex items-start justify-between gap-4"><div class="min-w-0"><span class="exam-badge exam-type">@lang('Mindigo-exam-management::app.question_types.' . $question->type)</span><p class="mt-2 text-sm font-black leading-6 text-slate-900">{{ $question->content }}</p></div><strong class="rounded-xl bg-green-50 px-3 py-2 text-sm text-green-700">{{ number_format((float) $question->points, 2) }}</strong></div></div>
                @endforeach
            </div>
        </article>
        <aside class="exam-card overflow-hidden">
            <div class="exam-card-head"><h2>@lang('Mindigo-exam-management::app.attempts')</h2><p>@lang('Mindigo-exam-management::app.attempts_desc')</p></div>
            <div class="divide-y divide-slate-100">
                @forelse($exam->attempts as $attempt)
                    <a href="{{ route('exams.attempts.result', $attempt) }}" class="flex items-center justify-between gap-3 px-5 py-4 text-slate-700 no-underline hover:bg-slate-50"><span><strong class="block text-sm font-black">{{ $attempt->user?->name }}</strong><small class="text-xs font-bold text-slate-400">{{ $attempt->status }} / {{ $attempt->submitted_at?->format('d/m/Y H:i') ?: '-' }}</small></span><strong class="text-sm">{{ number_format((float) $attempt->score, 2) }}</strong></a>
                @empty
                    <div class="p-5 text-sm font-bold text-slate-400">@lang('Mindigo-exam-management::app.no_attempts')</div>
                @endforelse
            </div>
        </aside>
    </section>
</div>
@endsection
