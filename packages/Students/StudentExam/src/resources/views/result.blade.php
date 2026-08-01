@extends('Mindigo-dashboard::layouts')

@section('title', __('student-exam::app.your_result') . ' - Mindigo LMS')

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
@php
    $exam = $result['exam'];
    $pendingReview = $result['pending_review'];
    $duration = $attempt->started_at && $attempt->submitted_at ? $attempt->started_at->diffInMinutes($attempt->submitted_at) : null;
@endphp

<div class="min-h-screen bg-slate-50">
    <header class="flex items-center gap-3 border-b border-slate-200 bg-white px-6 py-4">
        <a href="{{ route('student.exams.index') }}" class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 text-slate-600 no-underline hover:text-green-700"><x-heroicon-o-arrow-left class="h-4 w-4" /></a>
        <div><p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('student-exam::app.your_result')</p><h1 class="text-lg font-black text-slate-950">{{ $exam->title }}</h1></div>
    </header>

    <main class="mx-auto max-w-5xl space-y-5 p-6">
        <section class="border border-slate-200 bg-white p-6">
            @if($pendingReview)
                <div class="flex items-start gap-4"><x-heroicon-o-clock class="h-8 w-8 shrink-0 text-amber-500" /><div><h2 class="text-lg font-black text-slate-950">@lang('student-exam::app.pending_review')</h2><p class="mt-1 text-sm font-semibold text-slate-500">@lang('student-exam::app.essay_pending')</p></div></div>
            @else
                <div class="flex flex-wrap items-center justify-between gap-5"><div><p class="text-xs font-black uppercase tracking-wide {{ $result['passed'] ? 'text-green-700' : 'text-red-600' }}">{{ $result['passed'] ? __('student-exam::app.passed') : __('student-exam::app.failed') }}</p><div class="mt-2 flex items-end gap-2"><strong class="text-4xl font-black text-slate-950">{{ number_format((float) $result['percentage'], 1) }}%</strong><span class="mb-1 text-sm font-bold text-slate-400">{{ $result['score'] }}/{{ $result['max_score'] }} @lang('student-exam::app.score_label')</span></div></div><x-heroicon-o-check-badge class="h-12 w-12 {{ $result['passed'] ? 'text-green-600' : 'text-slate-300' }}" /></div>
            @endif
            <div class="mt-5 flex flex-wrap gap-x-6 gap-y-2 border-t border-slate-100 pt-4 text-xs font-semibold text-slate-500"><span>@lang('student-exam::app.submitted_at'): {{ $attempt->submitted_at?->format('d/m/Y H:i') }}</span>@if($duration !== null)<span>@lang('student-exam::app.duration_label'): {{ $duration }} @lang('student-exam::app.minutes')</span>@endif<span>@lang('student-exam::app.passing_score'): {{ $exam->passing_score }}/{{ $exam->total_points }}</span></div>
        </section>

        @if($result['show_review'])
            <section class="border border-slate-200 bg-white"><div class="border-b border-slate-200 px-5 py-4"><h2 class="text-sm font-black text-slate-950">@lang('student-exam::app.review_answers')</h2></div><div class="divide-y divide-slate-100">
                @foreach($result['questions'] as $index => $question)
                    @php $answer = $result['answers']->get($question->id); @endphp
                    <article class="p-5"><div class="flex items-start justify-between gap-4"><div class="text-sm font-bold leading-6 text-slate-900"><span class="mr-2 text-slate-400">{{ $index + 1 }}.</span>{!! $question->content !!}</div><span class="shrink-0 text-xs font-black {{ $answer?->is_correct ? 'text-green-700' : 'text-red-600' }}">{{ $answer?->is_correct ? __('student-exam::app.correct') : __('student-exam::app.incorrect') }} · {{ $answer?->points_awarded ?? 0 }}/{{ $question->points }}</span></div><div class="mt-3 bg-slate-50 px-4 py-3 text-sm text-slate-600"><strong class="text-xs text-slate-400">@lang('student-exam::app.your_answer')</strong><p class="mt-1 whitespace-pre-wrap">{{ implode(', ', $answer?->answer ?? []) ?: __('student-exam::app.no_answer') }}</p>@if($answer?->feedback)<p class="mt-3 border-t border-slate-200 pt-3 text-green-700"><strong>@lang('student-exam::app.teacher_feedback'):</strong> {{ $answer->feedback }}</p>@endif</div></article>
                @endforeach
            </div></section>
        @elseif(!$pendingReview)
            <div class="border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-sm font-semibold text-slate-400">@lang('student-exam::app.no_review_available')</div>
        @endif
    </main>
</div>
@endsection
