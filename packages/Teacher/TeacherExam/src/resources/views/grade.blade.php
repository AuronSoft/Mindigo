@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-exam::app.grade_attempt') . ' - ' . $attempt->user?->name)

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="min-h-screen bg-slate-50">
    <header class="flex items-center gap-3 border-b border-slate-200 bg-white px-6 py-4">
        <a href="{{ route('teacher.exams.show', $attempt->exam) }}" class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 text-slate-600 no-underline hover:text-green-700"><x-heroicon-o-arrow-left class="h-4 w-4" /></a>
        <div><p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('teacher-exam::app.manual_grading')</p><h1 class="text-lg font-black text-slate-950">{{ $attempt->user?->name }} · {{ $attempt->exam->title }}</h1><p class="text-xs font-semibold text-slate-400">{{ $attempt->user?->email }}</p></div>
    </header>

    <main class="p-6">
        @if($manualAnswers->isEmpty())
            <div class="border border-slate-200 bg-white px-6 py-12 text-center text-sm font-semibold text-slate-500">@lang('teacher-exam::app.no_manual_answers')</div>
        @else
            <form method="POST" action="{{ route('teacher.exams.attempts.grade.update', [$attempt->exam, $attempt]) }}" class="space-y-4">@csrf @method('PUT')
                <input type="hidden" name="grading_version" value="{{ $attempt->grading_version }}">
                @foreach($manualAnswers as $index => $answer)
                    <article class="border border-slate-200 bg-white p-5">
                        <div class="flex items-start justify-between gap-4"><div><p class="text-xs font-black text-green-700">{{ __('teacher-exam::app.question_number', ['number' => $index + 1]) }}</p><div class="mt-2 text-sm font-bold leading-6 text-slate-900">{!! $answer->question->content !!}</div></div><span class="shrink-0 text-xs font-black text-slate-500">{{ $answer->question->points }} @lang('teacher-exam::app.points')</span></div>
                        <div class="mt-4 border-l-2 border-slate-200 pl-4"><p class="text-xs font-black uppercase tracking-wide text-slate-400">@lang('teacher-exam::app.student_answer')</p><p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-slate-700">{{ implode(', ', $answer->answer ?? []) ?: __('teacher-exam::app.no_answer') }}</p></div>
                        <div class="mt-5 grid gap-4 md:grid-cols-3"><label class="block"><span class="text-xs font-black text-slate-700">@lang('teacher-exam::app.points_awarded')</span><input type="number" name="grades[{{ $answer->id }}][points]" value="{{ old('grades.'.$answer->id.'.points', $answer->points_awarded) }}" min="0" max="{{ $answer->question->points }}" step="0.25" class="mt-2 h-10 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-green-500" required></label><label class="block md:col-span-2"><span class="text-xs font-black text-slate-700">@lang('teacher-exam::app.feedback')</span><textarea name="grades[{{ $answer->id }}][feedback]" rows="3" class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-green-500">{{ old('grades.'.$answer->id.'.feedback', $answer->feedback) }}</textarea></label></div>
                    </article>
                @endforeach
                <div class="flex justify-end"><button class="rounded-lg bg-green-600 px-5 py-2.5 text-sm font-black text-white hover:bg-green-700">@lang('teacher-exam::app.complete_grading')</button></div>
            </form>
        @endif
    </main>
</div>
@endsection
