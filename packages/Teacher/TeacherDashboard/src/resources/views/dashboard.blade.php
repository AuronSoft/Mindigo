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
            labels: {{ Illuminate\Support\Js::from($trend['labels'] ?? []) }},
            counts: {{ Illuminate\Support\Js::from($trend['counts'] ?? []) }},
        };

        window.__teacherPerformance = {
            labels: {{ Illuminate\Support\Js::from($performance['labels'] ?? []) }},
            averages: {{ Illuminate\Support\Js::from($performance['averages'] ?? []) }},
            studentCounts: {{ Illuminate\Support\Js::from($performance['studentCounts'] ?? []) }},
            averageLabel: {{ Illuminate\Support\Js::from(__('teacher-dashboard::app.chart_avg_score')) }},
            studentCountLabel: {{ Illuminate\Support\Js::from(__('teacher-dashboard::app.chart_student_count')) }},
        };
    </script>
@endsection

@section('content')
@php
    $totalTodayClasses = $myClassrooms->where('status', 'active')->count();
    $pendingGrading = $assignmentStats['pendingSubmissions'] ?? 0;
    $passRate = ($stats['totalAttempts'] ?? 0) > 0
        ? round(($stats['passedAttempts'] / max(1, $stats['totalAttempts'])) * 100)
        : 0;

    $safeRoute = fn (string $route, array $params = []) => Route::has($route) ? route($route, $params) : '#';
    $teacherHeroImage = match ($teacher->gender) {
        'female' => asset('image/Teacher1.png'),
        'male' => asset('image/Man.png'),
        default => asset('image/Man.png'),
    };

    $quickLinks = [
        ['label' => __('teacher-dashboard::app.quick_classrooms'), 'icon' => 'heroicon-o-user-group', 'route' => 'teacher.classrooms.index', 'tone' => 'text-blue-600 bg-blue-50'],
        ['label' => __('teacher-dashboard::app.quick_courses'), 'icon' => 'heroicon-o-book-open', 'route' => 'teacher.courses.index', 'tone' => 'text-emerald-700 bg-emerald-50'],
        ['label' => __('teacher-dashboard::app.quick_exams'), 'icon' => 'heroicon-o-document-text', 'route' => 'teacher.exams.index', 'tone' => 'text-violet-600 bg-violet-50'],
        ['label' => __('teacher-dashboard::app.quick_assignments'), 'icon' => 'heroicon-o-clipboard-document-list', 'route' => 'teacher.assignments.index', 'tone' => 'text-amber-600 bg-amber-50'],
        ['label' => __('teacher-dashboard::app.quick_questions'), 'icon' => 'heroicon-o-circle-stack', 'route' => 'teacher.questions.index', 'tone' => 'text-cyan-600 bg-cyan-50'],
        ['label' => __('teacher-dashboard::app.quick_results'), 'icon' => 'heroicon-o-check-badge', 'route' => 'teacher.results.index', 'tone' => 'text-green-700 bg-green-50'],
        ['label' => __('teacher-dashboard::app.quick_reports'), 'icon' => 'heroicon-o-presentation-chart-line', 'route' => 'teacher.reports.index', 'tone' => 'text-slate-600 bg-slate-100'],
        ['label' => __('teacher-dashboard::app.quick_discussions'), 'icon' => 'heroicon-o-chat-bubble-left-right', 'route' => 'teacher.discussions.index', 'tone' => 'text-rose-600 bg-rose-50'],
    ];

    $statCards = [
        [
            'label' => __('teacher-dashboard::app.total_classrooms'),
            'value' => number_format($stats['totalClassrooms'] ?? 0),
            'sub' => __('teacher-dashboard::app.classrooms_sub', ['count' => number_format($stats['totalStudents'] ?? 0)]),
            'icon' => 'heroicon-o-calendar-days',
            'tone' => 'bg-blue-50 text-blue-600',
            'line' => 'bg-blue-500',
        ],
        [
            'label' => __('teacher-dashboard::app.total_students'),
            'value' => number_format($stats['totalStudents'] ?? 0),
            'sub' => __('teacher-dashboard::app.students_sub'),
            'icon' => 'heroicon-o-users',
            'tone' => 'bg-green-50 text-green-700',
            'line' => 'bg-green-500',
        ],
        [
            'label' => __('teacher-dashboard::app.pending_grading'),
            'value' => number_format($pendingGrading),
            'sub' => __('teacher-dashboard::app.pending_grading_sub'),
            'icon' => 'heroicon-o-clipboard-document-check',
            'tone' => 'bg-violet-50 text-violet-600',
            'line' => 'bg-violet-500',
        ],
        [
            'label' => __('teacher-dashboard::app.pass_rate'),
            'value' => $passRate . '%',
            'sub' => __('teacher-dashboard::app.attempts_sub', ['count' => number_format($stats['totalAttempts'] ?? 0)]),
            'icon' => 'heroicon-o-chart-bar',
            'tone' => 'bg-amber-50 text-amber-600',
            'line' => 'bg-amber-500',
        ],
    ];
@endphp

<div class="min-h-screen bg-[#f6f9f7] p-5 max-md:p-3">
    <div class="mx-auto grid max-w-[1560px] gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <section class="min-w-0 space-y-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase text-slate-400">@lang('teacher-dashboard::app.teacher_dashboard')</p>
                    <h1 class="text-xl font-black text-slate-950">@lang('teacher-dashboard::app.title')</h1>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-2">
                    @if(Route::has('teacher.exams.create'))
                        <a href="{{ route('teacher.exams.create') }}" class="inline-flex h-10 items-center gap-2 rounded-xl bg-green-600 px-4 text-xs font-black text-white no-underline shadow-lg shadow-green-600/20 transition hover:bg-green-700">
                            <x-heroicon-o-plus class="h-4 w-4" />
                            @lang('teacher-dashboard::app.create_exam')
                        </a>
                    @endif
                    @if(Route::has('teacher.assignments.create'))
                        <a href="{{ route('teacher.assignments.create') }}" class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 no-underline transition hover:border-green-200 hover:text-green-700">
                            <x-heroicon-o-clipboard-document-list class="h-4 w-4" />
                            @lang('teacher-dashboard::app.assign_homework')
                        </a>
                    @endif
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_18rem]">
                <article class="relative rounded-2xl border border-green-100 bg-white p-6 shadow-sm">
                    <div class="absolute inset-y-0 right-0 w-56 bg-gradient-to-l from-green-50 to-transparent"></div>
                    <div class="relative z-10 flex min-h-44 items-center gap-6">
                        <div class="hidden h-48 w-44 shrink-0 items-end justify-center overflow-visible sm:flex">
                            <img src="{{ $teacherHeroImage }}" alt="{{ $teacher->name }}" class="max-h-[13.5rem] w-auto object-contain object-bottom">
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-extrabold text-green-700">@lang('teacher-dashboard::app.hero_greeting', ['name' => $teacher->name])</p>
                            <h2 class="mt-2 text-3xl font-black leading-tight text-slate-950 max-md:text-2xl">@lang('teacher-dashboard::app.hero_title')</h2>
                            <p class="mt-3 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                                @lang('teacher-dashboard::app.hero_summary', ['classes' => $totalTodayClasses, 'pending' => number_format($pendingGrading), 'questions' => number_format($stats['pendingQuestions'] ?? 0)])
                            </p>
                            @if(Route::has('teacher.classrooms.index'))
                                <a href="{{ route('teacher.classrooms.index') }}" class="mt-5 inline-flex h-10 items-center gap-2 rounded-xl bg-blue-600 px-4 text-xs font-black text-white no-underline shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">
                                    @lang('teacher-dashboard::app.view_my_classes')
                                    <x-heroicon-o-arrow-right class="h-4 w-4" />
                                </a>
                            @endif
                        </div>
                    </div>
                </article>

                <div class="grid gap-4">
                    <a href="{{ $safeRoute('teacher.exams.create') }}" class="group rounded-2xl border border-green-100 bg-white p-4 no-underline shadow-sm transition hover:border-green-200 hover:shadow-md">
                        <div class="flex items-start gap-3">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-green-50 text-green-700">
                                <x-heroicon-o-sparkles class="h-6 w-6" />
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm font-black text-slate-950">@lang('teacher-dashboard::app.quick_create_exam')</span>
                                <span class="mt-1 block text-xs font-semibold leading-5 text-slate-500">@lang('teacher-dashboard::app.quick_create_exam_desc')</span>
                            </span>
                            <x-heroicon-o-chevron-right class="ml-auto h-4 w-4 shrink-0 text-slate-300 transition group-hover:text-green-600" />
                        </div>
                    </a>
                    <a href="{{ $safeRoute('teacher.questions.create') }}" class="group rounded-2xl border border-violet-100 bg-white p-4 no-underline shadow-sm transition hover:border-violet-200 hover:shadow-md">
                        <div class="flex items-start gap-3">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-violet-50 text-violet-600">
                                <x-heroicon-o-document-plus class="h-6 w-6" />
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm font-black text-slate-950">@lang('teacher-dashboard::app.quick_add_questions')</span>
                                <span class="mt-1 block text-xs font-semibold leading-5 text-slate-500">@lang('teacher-dashboard::app.quick_add_questions_desc')</span>
                            </span>
                            <x-heroicon-o-chevron-right class="ml-auto h-4 w-4 shrink-0 text-slate-300 transition group-hover:text-violet-600" />
                        </div>
                    </a>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach($statCards as $card)
                    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-black text-slate-500">{{ $card['label'] }}</p>
                                <strong class="mt-1 block text-3xl font-black text-slate-950">{{ $card['value'] }}</strong>
                            </div>
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl {{ $card['tone'] }}">
                                <x-dynamic-component :component="$card['icon']" class="h-6 w-6" />
                            </span>
                        </div>
                        <p class="text-xs font-bold text-slate-400">{{ $card['sub'] }}</p>
                        <div class="mt-4 h-1.5 rounded-full bg-slate-100">
                            <div class="h-1.5 w-2/3 rounded-full {{ $card['line'] }}"></div>
                        </div>
                    </article>
                @endforeach
            </div>

            <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-black text-slate-950">@lang('teacher-dashboard::app.quick_links')</h2>
                    <span class="text-xs font-bold text-slate-400">@lang('teacher-dashboard::app.teaching_tools')</span>
                </div>
                <div class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-8">
                    @foreach($quickLinks as $link)
                        @if(Route::has($link['route']))
                            <a href="{{ route($link['route']) }}" class="group flex min-h-24 flex-col items-center justify-center gap-2 rounded-xl border border-slate-100 bg-white px-2 text-center no-underline transition hover:border-green-200 hover:bg-green-50">
                                <span class="grid h-10 w-10 place-items-center rounded-xl {{ $link['tone'] }} transition group-hover:bg-white">
                                    <x-dynamic-component :component="$link['icon']" class="h-5 w-5" />
                                </span>
                                <span class="text-xs font-black text-slate-700">{{ $link['label'] }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>

            <div class="grid gap-5 lg:grid-cols-[20rem_minmax(0,1fr)]">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-black text-slate-950">@lang('teacher-dashboard::app.upcoming_activities')</h2>
                        @if(Route::has('teacher.assignments.index'))
                            <a href="{{ route('teacher.assignments.index') }}" class="text-xs font-black text-green-700 no-underline hover:underline">@lang('teacher-dashboard::app.view_all')</a>
                        @endif
                    </div>
                    <div class="space-y-3">
                        @forelse($upcomingActivities as $activity)
                            <a href="{{ $activity->route }}" class="flex gap-3 rounded-xl border border-slate-100 p-3 no-underline transition hover:border-green-200 hover:bg-green-50">
                                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl {{ $activity->type === 'exam' ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600' }}">
                                    <x-dynamic-component :component="$activity->type === 'exam' ? 'heroicon-o-document-text' : 'heroicon-o-clipboard-document-list'" class="h-5 w-5" />
                                </span>
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-black text-slate-900">{{ $activity->title }}</span>
                                    <span class="block text-xs font-bold text-slate-400">{{ $activity->subtitle }}</span>
                                    <span class="mt-1 block text-xs font-black text-green-700">{{ $activity->time?->format('d/m H:i') }}</span>
                                </span>
                            </a>
                        @empty
                            <div class="py-8 text-center">
                                <x-heroicon-o-calendar-days class="mx-auto h-10 w-10 text-slate-200" />
                                <p class="mt-2 text-sm font-bold text-slate-400">@lang('teacher-dashboard::app.no_upcoming_activities')</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="grid gap-5 xl:grid-cols-2">
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-sm font-black text-slate-950">@lang('teacher-dashboard::app.recent_assignment_submissions')</h2>
                            @if(Route::has('teacher.assignments.index'))
                                <a href="{{ route('teacher.assignments.index') }}" class="text-xs font-black text-green-700 no-underline hover:underline">@lang('teacher-dashboard::app.view_all')</a>
                            @endif
                        </div>
                        <div class="space-y-3">
                            @forelse($recentAssignmentSubmissions as $submission)
                                <div class="flex items-center gap-3">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-green-50 text-sm font-black text-green-700">{{ mb_substr($submission->student?->name ?? '?', 0, 1) }}</span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-black text-slate-900">{{ $submission->student?->name ?? __('teacher-dashboard::app.student_fallback') }}</span>
                                        <span class="block truncate text-xs font-bold text-slate-400">{{ $submission->assignment?->title ?? __('teacher-dashboard::app.assignment_fallback') }}</span>
                                    </span>
                                    <span class="rounded-full {{ $submission->isGraded() ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }} px-2.5 py-1 text-[11px] font-black">
                                        {{ $submission->isGraded() ? __('teacher-dashboard::app.graded') : __('teacher-dashboard::app.waiting_grading') }}
                                    </span>
                                </div>
                            @empty
                                <div class="py-8 text-center">
                                    <x-heroicon-o-inbox class="mx-auto h-10 w-10 text-slate-200" />
                                    <p class="mt-2 text-sm font-bold text-slate-400">@lang('teacher-dashboard::app.no_recent_submissions')</p>
                                </div>
                            @endforelse
                        </div>
                    </article>

                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-sm font-black text-slate-950">@lang('teacher-dashboard::app.performance_overview')</h2>
                            <span class="text-xs font-bold text-slate-400">@lang('teacher-dashboard::app.by_class')</span>
                        </div>
                        <div class="h-52">
                            <canvas id="teacherPerformanceChart"></canvas>
                        </div>
                    </article>
                </section>
            </div>

            <div class="grid gap-5 lg:grid-cols-2">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-black text-slate-950">@lang('teacher-dashboard::app.learning_alerts')</h2>
                        @if(Route::has('teacher.results.index'))
                            <a href="{{ route('teacher.results.index') }}" class="text-xs font-black text-green-700 no-underline hover:underline">@lang('teacher-dashboard::app.view_results')</a>
                        @endif
                    </div>
                    <div class="space-y-3">
                        @forelse($topStudents as $student)
                            <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-white text-sm font-black text-slate-700 ring-1 ring-slate-100">{{ mb_substr($student->name, 0, 1) }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-black text-slate-900">{{ $student->name }}</span>
                                    <span class="block text-xs font-bold text-slate-400">{{ $student->attempt_count }} @lang('teacher-dashboard::app.attempts_unit')</span>
                                </span>
                                <span class="rounded-full {{ $student->avg_score >= 80 ? 'bg-green-50 text-green-700' : ($student->avg_score >= 50 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-600') }} px-2.5 py-1 text-[11px] font-black">
                                    {{ $student->avg_score }}%
                                </span>
                            </div>
                        @empty
                            <p class="py-8 text-center text-sm font-bold text-slate-400">@lang('teacher-dashboard::app.no_student_data')</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-black text-slate-950">@lang('teacher-dashboard::app.activity_notifications')</h2>
                        <span class="text-xs font-bold text-slate-400">@lang('teacher-dashboard::app.seven_days')</span>
                    </div>
                    <div class="h-52">
                        <canvas id="teacherTrendChart"></canvas>
                    </div>
                </section>
            </div>
        </section>

        <aside class="space-y-5">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-sm font-black text-slate-950">@lang('teacher-dashboard::app.calendar')</h2>
                    <span class="text-xs font-black text-slate-400">{{ now()->locale(app()->getLocale())->translatedFormat('F Y') }}</span>
                </div>
                <div class="grid grid-cols-7 gap-2 text-center">
                    @foreach(__('teacher-dashboard::app.weekdays_short') as $day)
                        <span class="text-[10px] font-black uppercase text-slate-400">{{ $day }}</span>
                    @endforeach
                    @php
                        $start = now()->copy()->startOfMonth()->startOfWeek();
                        $today = now()->toDateString();
                    @endphp
                    @for($i = 0; $i < 35; $i++)
                        @php $date = $start->copy()->addDays($i); @endphp
                        <span class="grid h-9 place-items-center rounded-full text-xs font-black {{ $date->toDateString() === $today ? 'bg-blue-600 text-white' : ($date->month === now()->month ? 'text-slate-700' : 'text-slate-300') }}">
                            {{ $date->day }}
                        </span>
                    @endfor
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-black text-slate-950">@lang('teacher-dashboard::app.today_schedule')</h2>
                    @if(Route::has('teacher.classrooms.index'))
                        <a href="{{ route('teacher.classrooms.index') }}" class="text-xs font-black text-green-700 no-underline hover:underline">@lang('teacher-dashboard::app.view_class')</a>
                    @endif
                </div>
                <div class="space-y-3">
                    @forelse($myClassrooms->take(4) as $index => $classroom)
                        <a href="{{ $safeRoute('teacher.classrooms.show', [$classroom]) }}" class="flex gap-3 rounded-xl border-l-4 {{ $index === 0 ? 'border-blue-500 bg-blue-50' : 'border-green-400 bg-slate-50' }} p-3 no-underline">
                            <span class="w-16 shrink-0 text-xs font-black text-slate-500">{{ now()->copy()->setTime(8 + $index, 30)->format('H:i') }}</span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-black text-slate-900">{{ $classroom->name }}</span>
                                <span class="block text-xs font-bold text-slate-400">{{ $classroom->students_count }} @lang('teacher-dashboard::app.students_unit')</span>
                            </span>
                        </a>
                    @empty
                        <p class="py-6 text-center text-sm font-bold text-slate-400">@lang('teacher-dashboard::app.no_visible_classrooms')</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-black text-slate-950">@lang('teacher-dashboard::app.smart_reminders')</h2>
                    @if(Route::has('teacher.reports.index'))
                        <a href="{{ route('teacher.reports.index') }}" class="text-xs font-black text-green-700 no-underline hover:underline">@lang('teacher-dashboard::app.reports')</a>
                    @endif
                </div>
                <div class="space-y-3">
                    <a href="{{ $safeRoute('teacher.assignments.index') }}" class="flex items-center gap-3 rounded-xl p-3 no-underline transition hover:bg-slate-50">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-violet-50 text-violet-600">
                            <x-heroicon-o-clock class="h-5 w-5" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-black text-slate-900">@lang('teacher-dashboard::app.pending_grading_title')</span>
                            <span class="block text-xs font-bold text-slate-400">@lang('teacher-dashboard::app.pending_grading_desc', ['count' => number_format($pendingGrading)])</span>
                        </span>
                        <x-heroicon-o-chevron-right class="h-4 w-4 text-slate-300" />
                    </a>
                    <a href="{{ $safeRoute('teacher.questions.index') }}" class="flex items-center gap-3 rounded-xl p-3 no-underline transition hover:bg-slate-50">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-amber-50 text-amber-600">
                            <x-heroicon-o-circle-stack class="h-5 w-5" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-black text-slate-900">@lang('teacher-dashboard::app.pending_questions_title')</span>
                            <span class="block text-xs font-bold text-slate-400">@lang('teacher-dashboard::app.pending_questions_desc', ['count' => number_format($stats['pendingQuestions'] ?? 0)])</span>
                        </span>
                        <x-heroicon-o-chevron-right class="h-4 w-4 text-slate-300" />
                    </a>
                    <a href="{{ $safeRoute('teacher.exams.index') }}" class="flex items-center gap-3 rounded-xl p-3 no-underline transition hover:bg-slate-50">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-green-50 text-green-700">
                            <x-heroicon-o-document-check class="h-5 w-5" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-black text-slate-900">@lang('teacher-dashboard::app.published_exams_title')</span>
                            <span class="block text-xs font-bold text-slate-400">@lang('teacher-dashboard::app.published_exams_desc', ['count' => number_format($stats['publishedExams'] ?? 0)])</span>
                        </span>
                        <x-heroicon-o-chevron-right class="h-4 w-4 text-slate-300" />
                    </a>
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection