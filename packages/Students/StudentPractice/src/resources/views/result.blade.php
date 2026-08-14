@extends('Mindigo-dashboard::layouts')

@section('title', __('student-practice::app.result').' - Auronsoft LMS')
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="min-h-screen bg-slate-50">
    <header class="border-b border-slate-200 bg-white px-6 py-4"><p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('student-practice::app.area')</p><h1 class="text-lg font-black text-slate-950">@lang('student-practice::app.result')</h1></header>
    <main class="mx-auto max-w-5xl p-6">
        <section class="grid overflow-hidden rounded-xl border border-slate-200 bg-white sm:grid-cols-4">
            @foreach([
                __('student-practice::app.score') => number_format($details['summary']['score'], 1).'%',
                __('student-practice::app.correct') => $details['summary']['correct_answers'].'/'.$details['summary']['total_questions'],
                __('student-practice::app.duration') => __('student-practice::app.minutes', ['count' => $details['summary']['duration']]),
                __('student-practice::app.questions') => $details['summary']['total_questions'],
            ] as $label => $value)<div class="border-b border-slate-100 px-5 py-4 sm:border-b-0 sm:border-r"><p class="text-xs font-bold text-slate-400">{{ $label }}</p><p class="mt-1 text-xl font-black text-slate-950">{{ $value }}</p></div>@endforeach
        </section>
        <div class="mt-6 space-y-4">
            @foreach($details['answers'] as $index => $answer)
                <article class="border-l-4 {{ $answer->is_correct ? 'border-green-500' : 'border-red-400' }} bg-white px-5 py-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3"><p class="text-sm font-bold text-slate-900">{{ $index + 1 }}. {{ strip_tags($answer->display_content) }}</p><span class="shrink-0 text-xs font-black {{ $answer->is_correct ? 'text-green-700' : 'text-red-600' }}">{{ $answer->is_correct ? __('student-practice::app.correct') : __('student-practice::app.incorrect') }}</span></div>
                    @if($answer->display_explanation)<div class="mt-3 border-t border-slate-100 pt-3 text-sm leading-6 text-slate-600"><strong>@lang('student-practice::app.explanation'):</strong> {!! $answer->display_explanation !!}</div>@endif
                </article>
            @endforeach
        </div>
        <div class="mt-6 flex gap-3"><a href="{{ route('student.practice.index') }}" class="rounded-lg bg-green-600 px-5 py-2.5 text-sm font-black text-white no-underline">@lang('student-practice::app.start_new')</a><a href="{{ route('student.practice.history') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-black text-slate-700 no-underline">@lang('student-practice::app.history')</a></div>
    </main>
</div>
@endsection
