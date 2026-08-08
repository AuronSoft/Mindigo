@extends('Mindigo-dashboard::layouts')
@section('title', __('teacher-assignment::app.grading.title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
    <div class="flex min-h-screen flex-col bg-slate-50">
        <header
            class="sticky top-0 z-10 border-b border-slate-200 bg-white/95 px-6 py-4 backdrop-blur">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-widest text-green-700">
                        @lang('teacher-assignment::app.grading.eyebrow')
                    </p>
                    <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('teacher-assignment::app.grading.title')</h1>
                    <p class="mt-1 text-xs font-semibold text-slate-400">@lang('teacher-assignment::app.grading.subtitle')</p>
                </div>
                <div class="flex flex-wrap gap-2"><a href="{{ route('teacher.assignments.index') }}"
                    class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 no-underline transition hover:bg-slate-50">
                    <x-heroicon-o-clipboard-document-list class="h-4 w-4" />
                    @lang('teacher-assignment::app.assignment.title')
                </a></div>
            </div>
        </header>

        <main class="flex flex-1 flex-col gap-5 p-6">
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach([
                    ['label' => __('teacher-assignment::app.grading.stat_assignments'), 'value' => $summary['assignments'], 'icon' => 'heroicon-o-clipboard-document-list', 'tone' => 'bg-blue-50 text-blue-600', 'line' => 'bg-blue-500'],
                    ['label' => __('teacher-assignment::app.grading.stat_pending'), 'value' => $summary['pending'], 'icon' => 'heroicon-o-clock', 'tone' => 'bg-amber-50 text-amber-600', 'line' => 'bg-amber-500'],
                    ['label' => __('teacher-assignment::app.grading.stat_graded'), 'value' => $summary['graded'], 'icon' => 'heroicon-o-check-badge', 'tone' => 'bg-green-50 text-green-700', 'line' => 'bg-green-500'],
                    ['label' => __('teacher-assignment::app.grading.stat_submitted'), 'value' => $summary['submitted'], 'icon' => 'heroicon-o-inbox-arrow-down', 'tone' => 'bg-violet-50 text-violet-600', 'line' => 'bg-violet-500'],
                ] as $card)
                    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-black text-slate-500">{{ $card['label'] }}</p>
                                <strong class="mt-1 block text-3xl font-black text-slate-950">{{ number_format($card['value']) }}</strong>
                            </div>
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl {{ $card['tone'] }}">
                                <x-dynamic-component :component="$card['icon']" class="h-6 w-6" />
                            </span>
                        </div>
                        <div class="mt-4 h-1.5 rounded-full bg-slate-100">
                            <div class="h-1.5 w-2/3 rounded-full {{ $card['line'] }}"></div>
                        </div>
                    </article>
                @endforeach
            </section>

            @if($assignments->isEmpty())
                <section class="flex min-h-107.5 flex-col items-center justify-center gap-4 rounded-3xl border border-dashed border-slate-200 bg-white px-6 py-16">
                    <span class="grid h-20 w-20 place-items-center rounded-full bg-slate-50 text-slate-300">
                        <x-heroicon-o-check-badge class="h-10 w-10" />
                    </span>
                    <div class="text-center">
                        <p class="text-lg font-black text-slate-700">@lang('teacher-assignment::app.grading.empty_title')</p>
                        <p class="mt-1 max-w-xs text-sm font-semibold leading-relaxed text-slate-400">
                            @lang('teacher-assignment::app.grading.empty_desc')
                        </p>
                    </div>
                </section>
            @else
                <section class="grid gap-4 xl:grid-cols-2">
                    @foreach($assignments as $assignment)
                        @php
                            $pending = (int) $assignment->pending_submissions_count;
                            $submitted = max(1, (int) $assignment->submissions_count);
                            $progress = min(100, round(((int) $assignment->graded_submissions_count / $submitted) * 100));
                        @endphp
                        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-green-200 hover:shadow-md">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if($pending > 0)
                                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-black text-amber-700">
                                                {{ __('teacher-assignment::app.grading.pending_count', ['count' => $pending]) }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-black text-green-700">
                                                @lang('teacher-assignment::app.grading.all_graded')
                                            </span>
                                        @endif
                                        @if($assignment->late_submissions_count > 0)
                                            <span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-black text-red-600">
                                                {{ __('teacher-assignment::app.grading.late_count', ['count' => $assignment->late_submissions_count]) }}
                                            </span>
                                        @endif
                                    </div>
                                    <h2 class="mt-3 truncate text-base font-black text-slate-950">{{ $assignment->title }}</h2>
                                    <p class="mt-1 text-xs font-bold text-slate-400">
                                        {{ $assignment->classroom?->name ?? __('teacher-assignment::app.assignment.field_classroom') }}
                                        @if($assignment->due_date)
                                            <span class="mx-1">·</span>
                                            @lang('teacher-assignment::app.assignment.field_due_date'): {{ $assignment->due_date->format('d/m/Y H:i') }}
                                        @endif
                                    </p>
                                </div>
                                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-green-50 text-green-700">
                                    <x-heroicon-o-academic-cap class="h-6 w-6" />
                                </span>
                            </div>

                            <div class="mt-5 grid grid-cols-3 gap-3">
                                <div class="rounded-xl bg-slate-50 p-3">
                                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-assignment::app.grading.submitted')</p>
                                    <strong class="mt-1 block text-xl font-black text-slate-950">{{ $assignment->submissions_count }}</strong>
                                </div>
                                <div class="rounded-xl bg-amber-50 p-3">
                                    <p class="text-[10px] font-black uppercase tracking-wider text-amber-500">@lang('teacher-assignment::app.grading.pending')</p>
                                    <strong class="mt-1 block text-xl font-black text-amber-700">{{ $pending }}</strong>
                                </div>
                                <div class="rounded-xl bg-green-50 p-3">
                                    <p class="text-[10px] font-black uppercase tracking-wider text-green-600">@lang('teacher-assignment::app.grading.graded')</p>
                                    <strong class="mt-1 block text-xl font-black text-green-700">{{ $assignment->graded_submissions_count }}</strong>
                                </div>
                            </div>

                            <div class="mt-5">
                                <div class="mb-2 flex items-center justify-between text-xs font-black text-slate-500">
                                    <span>@lang('teacher-assignment::app.grading.progress')</span>
                                    <span>{{ $progress }}%</span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-100">
                                    <div class="h-2 rounded-full bg-green-500" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>

                            <div class="mt-5 flex justify-end">
                                <a href="{{ route('teacher.assignments.submissions.index', $assignment) }}"
                                    class="inline-flex h-10 items-center gap-2 rounded-full bg-green-600 px-4 text-xs font-black text-white no-underline shadow-sm shadow-green-200 transition hover:bg-green-500">
                                    <x-heroicon-o-check class="h-4 w-4" />
                                    @lang('teacher-assignment::app.grading.open_grading')
                                </a>
                            </div>
                        </article>
                    @endforeach
                </section>

                @if($assignments->hasPages())
                    <div class="flex justify-center">{{ $assignments->links() }}</div>
                @endif
            @endif
        </main>
    </div>
@endsection
