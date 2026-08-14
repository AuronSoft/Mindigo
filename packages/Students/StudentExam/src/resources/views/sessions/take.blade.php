@extends('Mindigo-dashboard::layouts')
@section('title', $attempt->session->title)
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Students/StudentExam/src/resources/css/app.css'])
@endsection
@section('content')
<main class="min-h-screen bg-slate-50 p-4 sm:p-6"><div class="mx-auto grid max-w-6xl gap-5 lg:grid-cols-[240px_minmax(0,1fr)]">
    <aside class="self-start rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs font-bold text-green-700">{{ __('student-exam::app.session_workspace.attempt', ['number' => $attempt->attempt_number]) }}</p><h1 class="mt-1 text-lg font-black text-slate-950">{{ $attempt->session->title }}</h1><p class="mt-4 text-xs font-bold text-slate-500">@lang('student-exam::app.session_workspace.time_left')</p><time class="mt-1 block text-xl font-black text-slate-900" datetime="{{ $attempt->expires_at->toIso8601String() }}" data-exam-deadline="{{ $attempt->expires_at->timestamp }}">--:--</time><div class="mt-5 grid grid-cols-5 gap-2">@foreach($questions as $number => $question)<a href="#question-{{ $question->id }}" class="flex aspect-square items-center justify-center rounded-lg bg-slate-100 text-xs font-black text-slate-600 no-underline">{{ $number + 1 }}</a>@endforeach</div></aside>
    <section class="space-y-4">@foreach($questions as $number => $question)
        @php
            $options = collect($question->options ?? [])->mapWithKeys(fn ($option, $key) => [(string) (is_array($option) ? ($option['key'] ?? $key) : $key) => is_array($option) ? ($option['text'] ?? $option['label'] ?? '') : $option]);
            $orderedKeys = data_get($attempt->answer_order, (string) $question->id, $options->keys()->all());
        @endphp
        <article id="question-{{ $question->id }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs font-black uppercase tracking-wider text-green-700">{{ __('student-exam::app.session_workspace.question_progress', ['current' => $number + 1, 'total' => $questions->count()]) }}</p><h2 class="mt-2 text-base font-black leading-7 text-slate-900">{{ $question->content }}</h2><div class="mt-5 grid gap-3">@foreach($orderedKeys as $key)<label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-4 hover:border-green-300"><input type="{{ $question->type === 'multiple_choice' ? 'checkbox' : 'radio' }}" name="answer[{{ $question->id }}][]" value="{{ $key }}" class="border-slate-300 text-green-600"><span class="text-sm font-semibold text-slate-700">{{ $options->get((string) $key) }}</span></label>@endforeach</div></article>
    @endforeach<div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-bold text-amber-800">@lang('student-exam::app.session_workspace.submit_later')</div></section>
</div></main>
@endsection
@section('scripts')
<script>
const timer = document.querySelector('[data-exam-deadline]');
if (timer) {
    const render = () => {
        const seconds = Math.max(0, Number(timer.dataset.examDeadline) - Math.floor(Date.now() / 1000));
        timer.textContent = `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
    };
    render(); window.setInterval(render, 1000);
}
</script>
@endsection
