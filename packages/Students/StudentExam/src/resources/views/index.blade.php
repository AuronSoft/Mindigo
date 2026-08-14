@extends('Mindigo-dashboard::layouts')

@section('title', __('student-exam::app.my_exams') . ' - Auronsoft LMS')
@section('meta_description', __('student-exam::app.subtitle'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Students/StudentExam/src/resources/css/app.css',
        'packages/Students/StudentExam/src/resources/js/app.js',
    ])
@endsection

@section('content')
@php
    $recommendedExams = $ongoing->concat($upcoming)->take(4);
    $examGroups = [
        ['status' => 'ongoing', 'label' => __('student-exam::app.status_open'), 'items' => $ongoing],
        ['status' => 'upcoming', 'label' => __('student-exam::app.status_upcoming'), 'items' => $upcoming],
        ['status' => 'completed', 'label' => __('student-exam::app.completed_exams'), 'items' => $completed],
    ];
@endphp

<div class="min-h-screen bg-slate-50">
    <header class="border-b border-slate-200 bg-white px-6 py-4">
        <p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('student-exam::app.area')</p>
        <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('student-exam::app.my_exams')</h1>
        <p class="text-xs font-semibold text-slate-400">@lang('student-exam::app.subtitle')</p>
    </header>

    <main class="p-6">
        @if(session('warning'))
            <div class="mb-5 flex items-center gap-3 border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
                <x-heroicon-o-exclamation-triangle class="h-5 w-5 shrink-0" />{{ session('warning') }}
            </div>
        @endif

        <div class="relative w-full max-w-sm">
            <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
            <input type="search" data-exam-search placeholder="@lang('student-exam::app.search_placeholder')" class="h-11 w-full rounded-lg border border-slate-300 bg-white pl-11 pr-4 text-sm font-semibold text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-100">
        </div>

        <section class="mt-6" aria-labelledby="recommended-exams-heading">
            <div class="mb-4 flex items-center justify-between">
                <h2 id="recommended-exams-heading" class="text-lg font-black text-slate-950">@lang('student-exam::app.recommended')</h2>
                <span class="text-xs font-bold text-slate-400">{{ $recommendedExams->count() }} @lang('student-exam::app.exams_unit')</span>
            </div>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @forelse($recommendedExams as $exam)
                    @php
                        $isOpen = $ongoing->contains('id', $exam->id);
                        $attemptCount = $exam->attempts->count();
                    @endphp
                    <article class="flex min-h-28 items-center gap-4 rounded-xl border border-slate-200 bg-white p-4 transition hover:border-green-300" data-exam-search-item data-exam-name="{{ str($exam->title)->lower() }}">
                        <x-heroicon-o-document-check class="h-9 w-9 shrink-0 {{ $isOpen ? 'text-green-600' : 'text-amber-500' }}" />
                        <div class="min-w-0 flex-1">
                            <h3 class="truncate text-sm font-black text-slate-900">{{ $exam->title }}</h3>
                            <p class="mt-1 text-[11px] font-semibold text-slate-500">{{ $isOpen ? __('student-exam::app.status_open') : __('student-exam::app.opens_at').' '.$exam->starts_at?->format('d/m/Y H:i') }}</p>
                            <p class="mt-1 text-[11px] font-semibold text-slate-500">{{ __('student-exam::app.duration_minutes', ['min' => $exam->duration_minutes ?? 0]) }}</p>
                            <p class="mt-1 text-[11px] font-semibold text-slate-500">@lang('student-exam::app.attempt_label') <span class="inline-grid h-5 min-w-5 place-items-center rounded-full bg-green-500 px-1.5 font-black text-white">{{ $attemptCount }}</span></p>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full border border-dashed border-slate-300 bg-white px-6 py-8 text-center text-sm font-semibold text-slate-400">@lang('student-exam::app.no_recommended')</div>
                @endforelse
            </div>
        </section>

        <section class="mt-7" aria-labelledby="all-exams-heading">
            <h2 id="all-exams-heading" class="mb-4 text-lg font-black text-slate-950">@lang('student-exam::app.all_exams')</h2>
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-230 border-collapse text-left">
                        <thead><tr class="border-b border-slate-200 text-xs font-bold text-slate-500"><th class="px-5 py-4">@lang('student-exam::app.column_name')</th><th class="px-5 py-4">@lang('student-exam::app.column_status')</th><th class="px-5 py-4">@lang('student-exam::app.column_duration')</th><th class="px-5 py-4">@lang('student-exam::app.column_schedule')</th><th class="px-5 py-4">@lang('student-exam::app.column_attempts')</th><th class="px-5 py-4 text-right">@lang('student-exam::app.column_action')</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($examGroups as $examGroup)
                                @foreach($examGroup['items'] as $exam)
                                    @php $lastAttempt = $exam->attempts->last(); @endphp
                                    <tr class="text-sm text-slate-600 transition hover:bg-slate-50" data-exam-search-item data-exam-name="{{ str($exam->title)->lower() }}">
                                        <td class="px-5 py-4"><div class="flex items-center gap-3"><x-heroicon-o-document-check class="h-6 w-6 shrink-0 {{ $examGroup['status'] === 'ongoing' ? 'text-green-600' : ($examGroup['status'] === 'upcoming' ? 'text-amber-500' : 'text-slate-400') }}" /><div><strong class="block font-bold text-slate-900">{{ $exam->title }}</strong>@if($exam->subject?->name)<span class="text-[11px] font-semibold text-slate-400">{{ $exam->subject->name }}</span>@endif</div></div></td>
                                        <td class="px-5 py-4"><span class="inline-flex items-center gap-1.5 text-xs font-bold"><span class="h-2 w-2 rounded-full {{ $examGroup['status'] === 'ongoing' ? 'bg-green-500' : ($examGroup['status'] === 'upcoming' ? 'bg-amber-400' : 'bg-slate-400') }}"></span>{{ $examGroup['label'] }}</span></td>
                                        <td class="px-5 py-4 text-xs font-semibold">{{ __('student-exam::app.duration_minutes', ['min' => $exam->duration_minutes ?? 0]) }}</td>
                                        <td class="px-5 py-4 text-xs font-semibold"><span class="block">{{ $exam->starts_at?->format('d/m/Y H:i') ?? '—' }}</span><span class="mt-0.5 block text-slate-400">{{ $exam->ends_at?->format('d/m/Y H:i') ?? '—' }}</span></td>
                                        <td class="px-5 py-4 text-xs font-semibold">{{ __('student-exam::app.attempts_used', ['used' => $exam->attempts->count(), 'max' => $exam->max_attempts ?? 1]) }}</td>
                                        <td class="px-5 py-4 text-right">
                                            @if($examGroup['status'] === 'ongoing')<form class="inline" action="{{ route('student.exams.start', $exam) }}" method="POST">@csrf<button class="rounded-lg bg-green-600 px-4 py-2 text-xs font-black text-white hover:bg-green-700">@lang('student-exam::app.start_exam')</button></form>
                                            @elseif($examGroup['status'] === 'completed' && $lastAttempt)<a href="{{ route('student.exams.result', $lastAttempt) }}" class="inline-flex rounded-lg border border-slate-300 px-4 py-2 text-xs font-black text-slate-700 no-underline hover:border-green-300 hover:text-green-700">@lang('student-exam::app.view_result')</a>
                                            @else<span class="text-xs font-semibold text-slate-400">@lang('student-exam::app.not_yet_open')</span>@endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($ongoing->isEmpty() && $upcoming->isEmpty() && $completed->isEmpty())<p class="border-t border-slate-100 px-6 py-10 text-center text-sm font-semibold text-slate-400">@lang('student-exam::app.no_exams')</p>@endif
                <p class="hidden px-6 py-10 text-center text-sm font-semibold text-slate-400" data-exam-search-empty>@lang('student-exam::app.no_search_results')</p>
            </div>
        </section>
    </main>
</div>
@endsection
