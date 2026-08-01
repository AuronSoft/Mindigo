@extends('Mindigo-dashboard::layouts')

@section('title', __('student-practice::app.history').' - Mindigo LMS')
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="min-h-screen bg-slate-50">
    <header class="flex items-center gap-3 border-b border-slate-200 bg-white px-6 py-4">
        <a href="{{ route('student.practice.index') }}" aria-label="@lang('student-practice::app.back')" class="grid h-9 w-9 shrink-0 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline transition hover:border-green-200 hover:bg-green-50 hover:text-green-700">
            <x-heroicon-o-arrow-left class="h-4 w-4" />
        </a>
        <div class="min-w-0">
            <p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('student-practice::app.area')</p>
            <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('student-practice::app.history')</h1>
            <p class="text-xs font-semibold text-slate-400">@lang('student-practice::app.history_subtitle')</p>
        </div>
    </header>
    <main class="p-6">
        <section class="grid overflow-hidden rounded-xl border border-slate-200 bg-white sm:grid-cols-4">
            @foreach([
                __('student-practice::app.attempts') => $stats['total_attempts'],
                __('student-practice::app.questions') => $stats['total_questions'],
                __('student-practice::app.average_score') => number_format($stats['average_score'], 1).'%',
                __('student-practice::app.accuracy') => number_format($stats['completion_rate'], 1).'%',
            ] as $label => $value)<div class="border-b border-slate-100 px-5 py-4 sm:border-b-0 sm:border-r"><p class="text-xs font-bold text-slate-400">{{ $label }}</p><p class="mt-1 text-xl font-black text-slate-950">{{ $value }}</p></div>@endforeach
        </section>
        <section class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="overflow-x-auto"><table class="w-full min-w-[720px] text-left"><thead><tr class="border-b border-slate-200 text-xs font-bold text-slate-500"><th class="px-5 py-4">@lang('student-practice::app.title')</th><th class="px-5 py-4">@lang('student-practice::app.questions')</th><th class="px-5 py-4">@lang('student-practice::app.score')</th><th class="px-5 py-4">@lang('student-practice::app.completed_at')</th><th class="px-5 py-4"></th></tr></thead><tbody class="divide-y divide-slate-100">
                @forelse($history as $attempt)<tr class="text-sm font-semibold text-slate-600"><td class="px-5 py-4 font-bold text-slate-900">{{ $attempt->practiceSet?->title ?? ($attempt->topic ?: ($attempt->subject ?: __('student-practice::app.modes.mixed'))) }}</td><td class="px-5 py-4">{{ $attempt->correct_answers }}/{{ $attempt->total_questions }}</td><td class="px-5 py-4">{{ number_format($attempt->score, 1) }}%</td><td class="px-5 py-4">{{ $attempt->completed_at?->format('d/m/Y H:i') }}</td><td class="px-5 py-4 text-right"><a href="{{ route('student.practice.result', $attempt) }}" class="text-xs font-black text-green-700 no-underline">@lang('student-practice::app.view_result')</a></td></tr>@empty<tr><td colspan="5" class="px-6 py-12 text-center text-sm font-semibold text-slate-400">@lang('student-practice::app.no_history')</td></tr>@endforelse
            </tbody></table></div>
        </section>
    </main>
</div>
@endsection
