@extends('Mindigo-dashboard::layouts')

@section('title', __('student-dashboard::app.meta_title'))
@section('meta_description', __('student-dashboard::app.meta_description'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
@php
    $statCards = [
        ['label' => __('student-dashboard::app.stat_classrooms'),  'value' => $stats['classrooms'],          'suffix' => '', 'route' => 'student.classrooms.index',  'color' => 'bg-green-100 text-green-700',   'icon' => 'M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6'],
        ['label' => __('student-dashboard::app.stat_pending'),     'value' => $stats['pending_assignments'], 'suffix' => '', 'route' => 'student.assignments.index', 'color' => 'bg-amber-100 text-amber-700',   'icon' => 'M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11'],
        ['label' => __('student-dashboard::app.stat_exams'),       'value' => $stats['exams_taken'],          'suffix' => '', 'route' => 'student.exams.index',       'color' => 'bg-sky-100 text-sky-700',       'icon' => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M8 13h8M8 17h5'],
        ['label' => __('student-dashboard::app.stat_avg_score'),   'value' => $stats['avg_score'],            'suffix' => '%','route' => 'student.progress.index',    'color' => 'bg-violet-100 text-violet-700', 'icon' => 'M3 3v18h18M7 15l4-4 3 3 5-7'],
    ];
@endphp

<div class="flex flex-col gap-6 p-6 max-md:p-4">

    {{-- Header band --}}
    <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-green-600 to-emerald-500 px-7 py-7 text-white shadow-lg shadow-green-600/20 max-md:px-5">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="min-w-0">
                <p class="text-xs font-black uppercase tracking-wider text-green-100">{{ now()->translatedFormat('l, d/m/Y') }}</p>
                <h1 class="mt-1 text-2xl font-black tracking-tight max-md:text-xl">
                    {{ __('student-dashboard::app.greeting', ['name' => $student->name]) }}
                </h1>
                <p class="mt-1 text-sm font-semibold text-green-50">{{ __('student-dashboard::app.subtitle') }}</p>
            </div>
            <span class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl bg-white/15 text-2xl font-black backdrop-blur">
                {{ mb_substr($student->name ?? 'S', 0, 1) }}
            </span>
        </div>
    </section>

    {{-- Stat cards --}}
    <section class="grid grid-cols-4 gap-4 max-lg:grid-cols-2 max-sm:grid-cols-1">
        @foreach($statCards as $card)
            @php $href = Route::has($card['route']) ? route($card['route']) : null; @endphp
            <a @if($href) href="{{ $href }}" @endif
               class="group flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-green-200 hover:shadow-md">
                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl {{ $card['color'] }}">
                    <svg viewBox="0 0 24 24" class="h-6 w-6 fill-none stroke-current stroke-2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $card['icon'] }}"/></svg>
                </span>
                <span class="min-w-0">
                    <span class="block text-2xl font-black text-slate-900">{{ $card['value'] }}{{ $card['suffix'] }}</span>
                    <span class="block truncate text-xs font-bold text-slate-500">{{ $card['label'] }}</span>
                </span>
            </a>
        @endforeach
    </section>

    <div class="grid grid-cols-3 gap-6 max-lg:grid-cols-1">

        {{-- Left: assignments + exams --}}
        <div class="col-span-2 flex flex-col gap-6 max-lg:col-span-1">

            {{-- Upcoming assignments --}}
            <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base font-black text-slate-900">{{ __('student-dashboard::app.upcoming_assignments') }}</h2>
                    @if(Route::has('student.assignments.index'))
                        <a href="{{ route('student.assignments.index') }}" class="text-xs font-extrabold text-green-600 no-underline hover:text-green-700">{{ __('student-dashboard::app.view_all') }}</a>
                    @endif
                </div>
                @forelse($upcomingAssignments as $a)
                    <div class="flex items-center gap-3 border-b border-slate-50 py-3 last:border-0">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-amber-50 text-amber-600">
                            <svg viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current stroke-2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-extrabold text-slate-800">{{ $a->title }}</p>
                            <p class="truncate text-xs font-semibold text-slate-400">{{ $a->classroom?->name }}</p>
                        </div>
                        <span class="shrink-0 rounded-lg bg-slate-50 px-2.5 py-1 text-xs font-black text-slate-500">
                            {{ $a->due_date?->format('d/m H:i') }}
                        </span>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm font-semibold text-slate-400">{{ __('student-dashboard::app.empty_assignments') }}</p>
                @endforelse
            </section>

            {{-- Open exams --}}
            <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base font-black text-slate-900">{{ __('student-dashboard::app.open_exams') }}</h2>
                    @if(Route::has('student.exams.index'))
                        <a href="{{ route('student.exams.index') }}" class="text-xs font-extrabold text-green-600 no-underline hover:text-green-700">{{ __('student-dashboard::app.view_all') }}</a>
                    @endif
                </div>
                @forelse($openExams as $exam)
                    <div class="flex items-center gap-3 border-b border-slate-50 py-3 last:border-0">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-sky-50 text-sky-600">
                            <svg viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current stroke-2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-extrabold text-slate-800">{{ $exam->title }}</p>
                            <p class="truncate text-xs font-semibold text-slate-400">{{ $exam->subject ?? '—' }} · {{ $exam->duration_minutes ?? '?' }}'</p>
                        </div>
                        @if($exam->ends_at)
                            <span class="shrink-0 rounded-lg bg-slate-50 px-2.5 py-1 text-xs font-black text-slate-500">{{ $exam->ends_at->format('d/m H:i') }}</span>
                        @endif
                    </div>
                @empty
                    <p class="py-6 text-center text-sm font-semibold text-slate-400">{{ __('student-dashboard::app.empty_exams') }}</p>
                @endforelse
            </section>
        </div>

        {{-- Right: classrooms + recent results --}}
        <div class="flex flex-col gap-6">

            {{-- My classrooms --}}
            <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base font-black text-slate-900">{{ __('student-dashboard::app.my_classrooms') }}</h2>
                    @if(Route::has('student.classrooms.index'))
                        <a href="{{ route('student.classrooms.index') }}" class="text-xs font-extrabold text-green-600 no-underline hover:text-green-700">{{ __('student-dashboard::app.view_all') }}</a>
                    @endif
                </div>
                @forelse($myClassrooms as $classroom)
                    <div class="flex items-center gap-3 border-b border-slate-50 py-3 last:border-0">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-green-50 text-sm font-black text-green-700">
                            {{ mb_substr($classroom->name, 0, 1) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-extrabold text-slate-800">{{ $classroom->name }}</p>
                            <p class="truncate text-xs font-semibold text-slate-400">{{ $classroom->teacher?->name ?? __('student-dashboard::app.no_teacher') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm font-semibold text-slate-400">{{ __('student-dashboard::app.empty_classrooms') }}</p>
                @endforelse
            </section>

            {{-- Recent results --}}
            <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base font-black text-slate-900">{{ __('student-dashboard::app.recent_results') }}</h2>
                    @if(Route::has('student.history.index'))
                        <a href="{{ route('student.history.index') }}" class="text-xs font-extrabold text-green-600 no-underline hover:text-green-700">{{ __('student-dashboard::app.view_all') }}</a>
                    @endif
                </div>
                @forelse($recentResults as $attempt)
                    <div class="flex items-center gap-3 border-b border-slate-50 py-3 last:border-0">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-extrabold text-slate-800">{{ $attempt->exam?->title ?? __('student-dashboard::app.deleted_exam') }}</p>
                            <p class="truncate text-xs font-semibold text-slate-400">{{ $attempt->submitted_at?->format('d/m/Y H:i') }}</p>
                        </div>
                        <span class="shrink-0 rounded-lg px-2.5 py-1 text-xs font-black {{ ($attempt->passed ?? false) ? 'bg-green-50 text-green-700' : 'bg-rose-50 text-rose-600' }}">
                            {{ $attempt->percentage !== null ? round((float) $attempt->percentage, 1) . '%' : '—' }}
                        </span>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm font-semibold text-slate-400">{{ __('student-dashboard::app.empty_results') }}</p>
                @endforelse
            </section>
        </div>
    </div>
</div>
@endsection
