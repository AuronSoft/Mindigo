@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-dashboard::app.title'))

@section('styles')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Teacher/TeacherDashboard/src/resources/css/app.css',
        'packages/Teacher/TeacherDashboard/src/resources/js/app.js',
    ])
@endsection

@section('scripts')
    <script>
        window.__teacherTrend = {
            labels: {{ Illuminate\Support\Js::from(array_column($trend, 'label')) }},
            counts: {{ Illuminate\Support\Js::from(array_column($trend, 'count')) }},
        };
    </script>
@endsection

@section('content')
<div class="min-h-screen bg-slate-50">

    {{-- Header --}}
    <header class="flex min-h-17 items-center justify-between gap-4 border-b border-slate-200 bg-white px-6 py-3">
        <div>
            <p class="text-xs font-black uppercase tracking-wider text-slate-400">@lang('teacher-dashboard::app.welcome'), {{ $teacher->name }}</p>
            <h1 class="mt-0.5 text-xl font-black text-slate-950">@lang('teacher-dashboard::app.title')</h1>
        </div>
        <div class="flex items-center gap-2">
            @if(Route::has('exams.create'))
            <a href="{{ route('exams.create') }}" class="inline-flex h-9 items-center gap-2 rounded-full bg-green-600 px-4 text-xs font-black text-white no-underline transition hover:bg-green-500">
                <x-heroicon-o-plus class="h-4 w-4" />
                @lang('teacher-dashboard::app.create_exam')
            </a>
            @endif
            @if(Route::has('classrooms.create'))
            <a href="{{ route('classrooms.create') }}" class="inline-flex h-9 items-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 no-underline transition hover:bg-slate-50">
                <x-heroicon-o-user-group class="h-4 w-4" />
                @lang('teacher-dashboard::app.create_class')
            </a>
            @endif
        </div>
    </header>

    <div class="grid gap-4 p-6">

        {{-- ── Stat cards ── --}}
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['icon' => 'heroicon-o-user-group',    'label' => __('teacher-dashboard::app.total_classrooms'), 'value' => number_format($stats['totalClassrooms']),  'sub' => number_format($stats['totalStudents']) . ' ' . __('teacher-dashboard::app.students'), 'tone' => 'bg-sky-600'],
                ['icon' => 'heroicon-o-document-text', 'label' => __('teacher-dashboard::app.total_exams'),      'value' => number_format($stats['totalExams']),       'sub' => $stats['publishedExams'] . ' đã xuất bản',  'tone' => 'bg-green-600'],
                ['icon' => 'heroicon-o-pencil-square',  'label' => __('teacher-dashboard::app.total_attempts'),  'value' => number_format($stats['totalAttempts']),    'sub' => __('teacher-dashboard::app.pass_rate') . ': ' . ($stats['totalAttempts'] > 0 ? round($stats['passedAttempts'] / $stats['totalAttempts'] * 100) : 0) . '%', 'tone' => 'bg-amber-500'],
                ['icon' => 'heroicon-o-circle-stack',  'label' => __('teacher-dashboard::app.my_questions_stat'),'value' => number_format($stats['totalQuestions']),  'sub' => $stats['pendingQuestions'] . ' ' . __('teacher-dashboard::app.pending_review'), 'tone' => 'bg-violet-600'],
            ] as $card)
                <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl {{ $card['tone'] }} text-white">
                        <x-dynamic-component :component="$card['icon']" class="h-6 w-6" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">{{ $card['label'] }}</p>
                        <strong class="mt-0.5 block text-2xl font-black text-slate-950">{{ $card['value'] }}</strong>
                        <span class="block text-xs font-bold text-slate-400">{{ $card['sub'] }}</span>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- ── Main grid ── --}}
        <div class="grid gap-4 xl:grid-cols-[minmax(0,1.3fr)_minmax(0,0.7fr)]">

            {{-- Left column --}}
            <div class="space-y-4">

                {{-- Weekly trend --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-sm font-black text-slate-950">@lang('teacher-dashboard::app.weekly_trend')</p>
                        <span class="rounded-full bg-green-50 px-2.5 py-1 text-xs font-black text-green-700">7 ngày</span>
                    </div>
                    <div class="h-40"><canvas id="teacherTrendChart"></canvas></div>
                </div>

                {{-- Recent submissions --}}
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                        <p class="text-sm font-black text-slate-950">@lang('teacher-dashboard::app.recent_submissions')</p>
                        @if(Route::has('teacher.results.index'))
                        <a href="{{ route('teacher.results.index') }}" class="text-xs font-black text-green-700 no-underline hover:underline">@lang('teacher-dashboard::app.view_all')</a>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[500px] text-left">
                            <thead class="bg-slate-50 text-[11px] font-black uppercase text-slate-400">
                                <tr>
                                    <th class="px-5 py-3">@lang('teacher-dashboard::app.student')</th>
                                    <th class="px-5 py-3">@lang('teacher-dashboard::app.exam')</th>
                                    <th class="px-5 py-3">@lang('teacher-dashboard::app.score')</th>
                                    <th class="px-5 py-3">@lang('teacher-dashboard::app.result')</th>
                                    <th class="px-5 py-3">@lang('teacher-dashboard::app.submitted_at')</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm font-bold text-slate-700">
                                @forelse($recentAttempts as $attempt)
                                    <tr class="bg-white hover:bg-slate-50 transition">
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-2">
                                                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-sky-100 text-xs font-black text-sky-700">{{ mb_substr($attempt->user?->name ?? '?', 0, 1) }}</span>
                                                {{ $attempt->user?->name ?? '—' }}
                                            </div>
                                        </td>
                                        <td class="px-5 py-3 max-w-[180px] truncate text-slate-500">{{ \Illuminate\Support\Str::limit($attempt->exam?->title ?? '—', 28) }}</td>
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-2">
                                                <div class="h-1.5 w-16 rounded-full bg-slate-100">
                                                    <div class="h-1.5 rounded-full bg-green-500" style="width:{{ min(100, $attempt->percentage) }}%"></div>
                                                </div>
                                                {{ round($attempt->percentage, 1) }}%
                                            </div>
                                        </td>
                                        <td class="px-5 py-3">
                                            @if($attempt->passed)
                                                <span class="rounded-full bg-green-100 px-2.5 py-1 text-[11px] font-black text-green-800">@lang('teacher-dashboard::app.passed')</span>
                                            @else
                                                <span class="rounded-full bg-red-100 px-2.5 py-1 text-[11px] font-black text-red-700">@lang('teacher-dashboard::app.failed')</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-slate-400 text-xs">{{ $attempt->submitted_at?->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="h-44 text-center align-middle">
                                            <div class="flex flex-col items-center justify-center gap-3">
                                                <x-heroicon-o-inbox class="h-12 w-12 text-slate-200" />
                                                <span class="text-sm font-bold text-slate-400">@lang('teacher-dashboard::app.no_submissions')</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            {{-- Right column --}}
            <div class="space-y-4">

                {{-- Top students --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="mb-4 text-sm font-black text-slate-950">@lang('teacher-dashboard::app.top_students')</p>
                    <div class="space-y-3">
                        @forelse($topStudents as $i => $s)
                            <div class="flex items-center gap-3">
                                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full {{ $i === 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }} text-xs font-black">{{ $i + 1 }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-black text-slate-900">{{ $s->name }}</span>
                                    <span class="text-xs font-bold text-slate-400">{{ $s->attempt_count }} @lang('teacher-dashboard::app.attempts_unit')</span>
                                </span>
                                <span class="rounded-full bg-green-50 px-2.5 py-1 text-[11px] font-black text-green-700">{{ $s->avg_score }}%</span>
                            </div>
                        @empty
                            <div class="flex flex-col items-center gap-2 py-6">
                                <x-heroicon-o-trophy class="h-10 w-10 text-slate-200" />
                                <p class="text-sm font-bold text-slate-400">@lang('teacher-dashboard::app.no_data')</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- My classrooms --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-sm font-black text-slate-950">@lang('teacher-dashboard::app.my_classrooms_overview')</p>
                        @if(Route::has('teacher.classrooms.index'))
                        <a href="{{ route('teacher.classrooms.index') }}" class="text-xs font-black text-green-700 no-underline hover:underline">@lang('teacher-dashboard::app.view_all')</a>
                        @endif
                    </div>
                    <div class="space-y-2">
                        @forelse($myClassrooms as $classroom)
                            <div class="flex items-center gap-3 rounded-2xl bg-slate-50 px-3 py-2.5">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-sky-100 text-sky-700 text-xs font-black">
                                    <x-heroicon-o-user-group class="h-5 w-5" />
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-black text-slate-900">{{ $classroom->name }}</span>
                                    <span class="text-xs font-bold text-slate-400">{{ $classroom->students_count }} @lang('teacher-dashboard::app.students_unit') · {{ $classroom->school_year }}</span>
                                </span>
                                <span class="shrink-0 rounded-full {{ $classroom->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }} px-2 py-0.5 text-[10px] font-black">
                                    @lang('teacher-dashboard::app.' . $classroom->status)
                                </span>
                            </div>
                        @empty
                            <div class="flex flex-col items-center gap-2 py-6">
                                <x-heroicon-o-user-group class="h-10 w-10 text-slate-200" />
                                <p class="text-sm font-bold text-slate-400">@lang('teacher-dashboard::app.no_classrooms')</p>
                                @if(Route::has('classrooms.create'))
                                <a href="{{ route('classrooms.create') }}" class="mt-1 text-xs font-black text-green-700 no-underline hover:underline">@lang('teacher-dashboard::app.create_first_class')</a>
                                @endif
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- My recent exams --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-sm font-black text-slate-950">@lang('teacher-dashboard::app.my_recent_exams')</p>
                        @if(Route::has('exams.index'))
                        <a href="{{ route('exams.index') }}" class="text-xs font-black text-green-700 no-underline hover:underline">@lang('teacher-dashboard::app.view_all')</a>
                        @endif
                    </div>
                    <div class="space-y-2">
                        @forelse($recentExams as $exam)
                            @php
                                $statusTone = match($exam->status) {
                                    'published' => 'bg-green-100 text-green-700',
                                    'reviewing' => 'bg-amber-100 text-amber-700',
                                    'closed'    => 'bg-slate-100 text-slate-500',
                                    default     => 'bg-slate-100 text-slate-400',
                                };
                            @endphp
                            <div class="flex items-center gap-3 rounded-2xl bg-slate-50 px-3 py-2.5">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-green-100 text-green-700">
                                    <x-heroicon-o-document-text class="h-5 w-5" />
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-black text-slate-900">{{ \Illuminate\Support\Str::limit($exam->title, 26) }}</span>
                                    <span class="text-xs font-bold text-slate-400">{{ $exam->attempts_count }} @lang('teacher-dashboard::app.attempts_abbr') · @lang('teacher-dashboard::app.avg_label') {{ $exam->attempts_avg_percentage ? round($exam->attempts_avg_percentage, 1) . '%' : '—' }}</span>
                                </span>
                                <span class="shrink-0 rounded-full {{ $statusTone }} px-2 py-0.5 text-[10px] font-black">
                                    @lang('teacher-dashboard::app.' . $exam->status)
                                </span>
                            </div>
                        @empty
                            <div class="flex flex-col items-center gap-2 py-6">
                                <x-heroicon-o-document-text class="h-10 w-10 text-slate-200" />
                                <p class="text-sm font-bold text-slate-400">@lang('teacher-dashboard::app.no_exams')</p>
                                @if(Route::has('exams.create'))
                                <a href="{{ route('exams.create') }}" class="mt-1 text-xs font-black text-green-700 no-underline hover:underline">@lang('teacher-dashboard::app.create_first_exam')</a>
                                @endif
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
