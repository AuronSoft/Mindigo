@extends('Mindigo-dashboard::layouts')
@section('title', __('Mindigo-exam-management::app.grading.review_title'))
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/ExamManagement/src/resources/css/app.css'])
@endsection
@section('content')
<main class="exam-foundation-shell"><div class="exam-foundation-container max-w-5xl">
    <x-exam::page-header :eyebrow="__('Mindigo-exam-management::app.grading.workspace')" :title="__('Mindigo-exam-management::app.grading.review_title')" :description="__('Mindigo-exam-management::app.grading.review_description', ['candidate' => $attempt->candidate?->name ?? $attempt->user?->name, 'session' => $attempt->session->title])"><x-slot:actions><x-exam::button variant="secondary" :href="route('teacher.exam-sessions.grading.index', $attempt->session)">@lang('Mindigo-exam-management::app.grading.back_queue')</x-exam::button></x-slot:actions></x-exam::page-header>
    @if(session('success'))<div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-bold text-green-700">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-700">{{ $errors->first() }}</div>@endif
    <div class="grid gap-4 sm:grid-cols-4"><x-exam::stat-card :label="__('Mindigo-exam-management::app.grading.current_score')" :value="$attempt->score.'/'.$attempt->max_score" /><x-exam::stat-card :label="__('Mindigo-exam-management::app.grading.percentage')" :value="$attempt->percentage.'%'" tone="green" /><x-exam::stat-card :label="__('Mindigo-exam-management::app.grading.security_events')" :value="$attempt->proctorEvents->count()" tone="amber" /><x-exam::stat-card :label="__('Mindigo-exam-management::app.proctoring.risk_level')" :value="__('Mindigo-exam-management::app.proctoring.risk_'.$attempt->risk_level)" tone="amber" /></div>
    <x-exam::panel :title="__('Mindigo-exam-management::app.proctoring.review_title')" :description="__('Mindigo-exam-management::app.proctoring.review_description')">
        <form method="POST" action="{{ route('teacher.exam-sessions.proctor.note', [$attempt->session, $attempt]) }}" class="flex gap-3">@csrf<input name="note" required maxlength="2000" class="exam-input" placeholder="@lang('Mindigo-exam-management::app.proctoring.note_placeholder')"><x-exam::button type="submit">@lang('Mindigo-exam-management::app.proctoring.add_note')</x-exam::button></form>
        <div class="mt-4 grid gap-2">@forelse($attempt->proctorEvents as $event)<div class="flex items-start justify-between gap-4 rounded-xl bg-slate-50 p-3 text-sm"><div><strong class="text-slate-800">{{ __('Mindigo-exam-management::app.proctoring.events.'.$event->type) }}</strong>@if(data_get($event->metadata, 'note'))<p class="mt-1 text-slate-600">{{ data_get($event->metadata, 'note') }}</p>@endif</div><time class="shrink-0 text-xs font-bold text-slate-400">{{ $event->occurred_at->format('d/m H:i:s') }}</time></div>@empty<p class="text-sm font-semibold text-slate-500">@lang('Mindigo-exam-management::app.proctoring.no_events')</p>@endforelse</div>
    </x-exam::panel>
    <div class="space-y-4">@foreach($attempt->answers as $answer)
        <x-exam::panel :title="__('Mindigo-exam-management::app.grading.question', ['number' => $loop->iteration])" :description="$answer->question->content">
            <div class="rounded-xl bg-slate-50 p-4 text-sm font-semibold leading-6 text-slate-700">{{ collect($answer->answer ?? [])->join(', ') ?: __('Mindigo-exam-management::app.grading.no_answer') }}</div>
            @if($answer->needs_review)
                <form method="POST" action="{{ route('teacher.exam-sessions.grading.answers.update', [$attempt->session, $attempt, $answer]) }}" class="mt-4 grid gap-4 sm:grid-cols-[12rem_1fr_auto] sm:items-end">@csrf @method('PUT')
                    <label class="grid gap-1.5 text-xs font-black text-slate-600">@lang('Mindigo-exam-management::app.grading.points_awarded')<input type="number" name="points_awarded" min="0" max="{{ $answer->question->points }}" step="0.01" required class="h-11 rounded-xl border border-slate-200 px-3 text-sm font-bold" placeholder="0 / {{ $answer->question->points }}"></label>
                    <label class="grid gap-1.5 text-xs font-black text-slate-600">@lang('Mindigo-exam-management::app.grading.feedback')<input type="text" name="feedback" maxlength="3000" class="h-11 rounded-xl border border-slate-200 px-3 text-sm font-semibold" placeholder="@lang('Mindigo-exam-management::app.grading.feedback_placeholder')"></label>
                    <x-exam::button type="submit">@lang('Mindigo-exam-management::app.grading.save_grade')</x-exam::button>
                </form>
            @else
                <div class="mt-4 flex flex-wrap items-center gap-3 text-sm"><span class="rounded-full bg-green-50 px-3 py-1.5 font-black text-green-700">{{ $answer->points_awarded }}/{{ $answer->question->points }}</span>@if($answer->feedback)<span class="font-semibold text-slate-500">{{ $answer->feedback }}</span>@endif</div>
            @endif
        </x-exam::panel>
    @endforeach</div>
    <x-exam::panel :title="__('Mindigo-exam-management::app.grading.release_title')" :description="__('Mindigo-exam-management::app.grading.release_description')">
        @if($attempt->released_at)<p class="text-sm font-black text-green-700">{{ __('Mindigo-exam-management::app.grading.released_at', ['time' => $attempt->released_at->format('d/m/Y H:i')]) }}</p>
        @elseif($attempt->needs_review)<p class="text-sm font-bold text-amber-700">@lang('Mindigo-exam-management::app.grading.release_blocked')</p>
        @else<form method="POST" action="{{ route('teacher.exam-sessions.grading.release', [$attempt->session, $attempt]) }}">@csrf<x-exam::button type="submit">@lang('Mindigo-exam-management::app.grading.release_action')</x-exam::button></form>@endif
    </x-exam::panel>
</div></main>
@endsection
