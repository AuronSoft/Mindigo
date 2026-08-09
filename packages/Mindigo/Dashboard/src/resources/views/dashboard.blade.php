@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-dashboard::app.title'))

@section('styles')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('scripts')
    @php
        $dashboardMessages = [
            'tool_applied' => __('Mindigo-dashboard::app.dashboard_tool_applied'),
            'filters' => __('Mindigo-dashboard::app.filters'),
            'timeframe' => __('Mindigo-dashboard::app.timeframe'),
            'date_filter_off' => __('Mindigo-dashboard::app.date_filter_off'),
            'logout_title' => __('Mindigo-dashboard::app.logout_title'),
            'logout_message' => __('Mindigo-dashboard::app.logout_message'),
            'logout_confirm' => __('Mindigo-dashboard::app.logout_confirm'),
            'logout_cancel' => __('Mindigo-dashboard::app.logout_cancel'),
            'logging_out' => __('Mindigo-dashboard::app.logging_out'),
            'notif_unread_count' => __('notification::app.unread_count'),
            'notif_all_read' => __('notification::app.all_read'),
            'notif_announcement' => __('notification::app.cat_announcement'),
            'notif_title' => __('notification::app.title'),
        ];

        $dashboardChartLabels = [
            'ranking' => [
                __('Mindigo-dashboard::app.demo_admin'),
                __('Mindigo-dashboard::app.demo_teacher'),
                __('Mindigo-dashboard::app.demo_student'),
                __('Mindigo-dashboard::app.demo_class'),
                __('Mindigo-dashboard::app.demo_exam'),
            ],
        ];
    @endphp

    @php
        $rankingLabels = $topPerformers->map(fn($p) => mb_strlen($p->name) > 14 ? mb_substr($p->name, 0, 14) . '…' : $p->name)->values()->toArray();
        $rankingData = $topPerformers->pluck('avg_score')->map(fn($v) => (float) $v)->values()->toArray();
    @endphp
    <script>
        window.__dashboardMessages = {{ Illuminate\Support\Js::from($dashboardMessages) }};
        window.__dashboardChartLabels = {{ Illuminate\Support\Js::from($dashboardChartLabels) }};
        window.__dashboardRuntime = {{ Illuminate\Support\Js::from([
            'timezone' => config('app.timezone'),
            'server_now' => now()->toIso8601String(),
        ]) }};
        window.__questionStats = {{ Illuminate\Support\Js::from($questionStats) }};
        window.__searchConfig = {
            url: {{ Illuminate\Support\Js::from(route('dashboard.search')) }},
            labels: {
                exam:     {{ Illuminate\Support\Js::from(__('Mindigo-dashboard::app.search_type_exam')) }},
                user:     {{ Illuminate\Support\Js::from(__('Mindigo-dashboard::app.search_type_user')) }},
                question: {{ Illuminate\Support\Js::from(__('Mindigo-dashboard::app.search_type_question')) }},
                ticket:   {{ Illuminate\Support\Js::from(__('Mindigo-dashboard::app.search_type_ticket')) }},
                empty:    {{ Illuminate\Support\Js::from(__('Mindigo-dashboard::app.search_no_results')) }},
                hint:     {{ Illuminate\Support\Js::from(__('Mindigo-dashboard::app.search_hint')) }},
            },
        };
        window.__dashboardRanking = {
            labels: {{ Illuminate\Support\Js::from($rankingLabels) }},
            data: {{ Illuminate\Support\Js::from($rankingData) }},
        };
    </script>
@endsection

@section('content')

    <section class="min-h-screen bg-[#f7faf7]">
        <header class="flex min-h-17 flex-wrap items-center justify-between gap-4 bg-[#f7faf7] px-5 py-3 backdrop-blur max-md:px-4">
            <div class="relative min-w-[16rem] max-w-xl flex-1" id="global-search-wrap">
                <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 transition focus-within:border-green-300 focus-within:bg-white focus-within:shadow-sm">
                    <x-heroicon-o-magnifying-glass class="h-5 w-5 shrink-0 text-slate-400" id="global-search-icon" />
                    <svg id="global-search-spinner" class="hidden h-5 w-5 shrink-0 animate-spin text-green-500" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16v-4l-3 3 3 3v-4a8 8 0 01-8-8z"/></svg>
                    <input
                        id="global-search-input"
                        type="text"
                        placeholder="@lang('Mindigo-dashboard::app.global_search')"
                        autocomplete="off"
                        data-search-url="{{ route('dashboard.search') }}"
                        class="min-w-0 flex-1 bg-transparent text-sm font-bold text-slate-700 outline-none placeholder:text-slate-400"
                    >
                    <button id="global-search-clear" class="hidden text-slate-400 hover:text-slate-600">
                        <x-heroicon-m-x-mark class="h-4 w-4" />
                    </button>
                </div>
                {{-- Results dropdown --}}
                <div id="global-search-results" class="absolute left-0 top-[calc(100%+6px)] z-50 hidden w-full min-w-90 rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10">
                    <div id="global-search-list" class="max-h-105 overflow-y-auto py-2"></div>
                    <div id="global-search-empty" class="hidden flex-col items-center gap-2 py-8 text-center">
                        <x-heroicon-o-magnifying-glass class="h-10 w-10 text-slate-200" />
                        <p class="text-sm font-bold text-slate-400">@lang('Mindigo-dashboard::app.search_no_results')</p>
                    </div>
                    <div class="border-t border-slate-100 px-4 py-2.5">
                        <p class="text-[11px] font-bold text-slate-400" id="global-search-hint">@lang('Mindigo-dashboard::app.search_hint')</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 rounded-full bg-white/80 p-1.5 shadow-sm ring-1 ring-slate-200">
                <button type="button" class="grid h-10 w-10 place-items-center rounded-full bg-white text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-green-50 hover:text-green-700">
                    <x-heroicon-o-bars-3 class="h-5 w-5" />
                </button>
                <span class="grid h-10 w-10 place-items-center rounded-full bg-linear-to-br from-amber-200 via-green-100 to-emerald-300 text-xs font-black text-slate-800 shadow-sm ring-2 ring-white">{{ mb_substr($dashboardUser?->name ?? 'A', 0, 1) }}</span>

                {{-- Quick create button --}}
                <div class="relative" id="quick-create-wrap">
                    <button type="button" id="quick-create-btn" class="grid h-10 w-10 place-items-center rounded-full bg-green-600 text-white shadow-sm transition hover:bg-green-500" aria-expanded="false">
                        <x-heroicon-o-plus class="h-5 w-5" id="quick-create-icon" />
                        <x-heroicon-m-x-mark class="h-5 w-5 hidden" id="quick-create-close-icon" />
                    </button>

                    <div id="quick-create-dropdown" class="absolute right-0 top-[calc(100%+10px)] z-50 hidden w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/10">
                        <p class="px-2 pb-1.5 pt-0.5 text-[10px] font-black uppercase tracking-wider text-slate-400">@lang('Mindigo-dashboard::app.quick_create')</p>

                        @foreach([
                            ['route' => 'exams.create',        'label' => __('Mindigo-dashboard::app.quick_exam'),     'icon' => 'heroicon-o-document-text', 'tone' => 'bg-green-100 text-green-700'],
                            ['route' => 'question-bank.create','label' => __('Mindigo-dashboard::app.quick_question'), 'icon' => 'heroicon-o-circle-stack',  'tone' => 'bg-amber-100 text-amber-700'],
                            ['route' => 'users.create',        'label' => __('Mindigo-dashboard::app.quick_user'),     'icon' => 'heroicon-o-user-plus',     'tone' => 'bg-sky-100 text-sky-700'],
                            ['route' => 'support-tickets.create','label' => __('Mindigo-dashboard::app.quick_ticket'), 'icon' => 'heroicon-o-chat-bubble-left-ellipsis', 'tone' => 'bg-rose-100 text-rose-700'],
                        ] as $item)
                            @if(Route::has($item['route']))
                            <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 no-underline transition hover:bg-slate-50">
                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-xl {{ $item['tone'] }}">
                                    <x-dynamic-component :component="$item['icon']" class="h-4 w-4" />
                                </span>
                                <span class="text-sm font-black text-slate-800">{{ $item['label'] }}</span>
                            </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <div id="dashboard-notification-menu" class="absolute right-8 top-20 z-40 hidden w-80 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/10">
                <div class="rounded-xl bg-green-50 px-3 py-2.5">
                    <p class="m-0 text-sm font-black text-slate-900">@lang('Mindigo-dashboard::app.notification_title')</p>
                    <p class="m-0 mt-0.5 text-[11px] font-bold text-slate-500">@lang('Mindigo-dashboard::app.notification_subtitle')</p>
                </div>
                <div class="mt-2 grid gap-1">
                    <a href="#question-bank" class="flex gap-3 rounded-xl px-3 py-2.5 text-slate-700 no-underline transition hover:bg-slate-50">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-green-500"></span>
                        <span class="min-w-0">
                            <span class="block text-sm font-black">@lang('Mindigo-dashboard::app.notification_new_questions')</span>
                            <span class="mt-0.5 block text-xs font-semibold leading-5 text-slate-500">@lang('Mindigo-dashboard::app.notification_new_questions_desc')</span>
                        </span>
                    </a>
                    <a href="#exams" class="flex gap-3 rounded-xl px-3 py-2.5 text-slate-700 no-underline transition hover:bg-slate-50">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-amber-500"></span>
                        <span class="min-w-0">
                            <span class="block text-sm font-black">@lang('Mindigo-dashboard::app.notification_exam_ready')</span>
                            <span class="mt-0.5 block text-xs font-semibold leading-5 text-slate-500">@lang('Mindigo-dashboard::app.notification_exam_ready_desc')</span>
                        </span>
                    </a>
                </div>
            </div>
        </header>

        <div class="grid -mt-3 items-start gap-4 px-5 pb-5 pt-0 2xl:grid-cols-[minmax(0,1fr)_23rem] max-md:-mt-2 max-md:px-4 max-md:pb-4">
            <main class="min-w-0 space-y-4">
                <section class="min-h-75 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm" id="overview">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex flex-wrap items-center gap-2">
                            @if(Route::has('users.create'))
                            <a href="{{ route('users.create') }}" class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline transition hover:border-green-200 hover:bg-green-50 hover:text-green-700" title="@lang('Mindigo-dashboard::app.create_user')">
                                <x-heroicon-o-plus class="h-4 w-4" />
                            </a>
                            @else
                            <button type="button" class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-600">
                                <x-heroicon-o-plus class="h-4 w-4" />
                            </button>
                            @endif
                            @php
                                $badgeTones = ['bg-green-100 text-green-700', 'bg-amber-100 text-amber-700', 'bg-sky-100 text-sky-700'];
                            @endphp
                            @foreach($headerUsers as $i => $person)
                                <span class="inline-flex h-9 items-center gap-2 rounded-full border border-slate-200 bg-white px-2.5 text-xs font-black text-slate-700">
                                    <span class="grid h-6 w-6 place-items-center rounded-full text-[10px] {{ $badgeTones[$i] ?? 'bg-slate-100 text-slate-600' }}">{{ mb_substr($person->name, 0, 1) }}</span>
                                    {{ \Illuminate\Support\Str::limit($person->name, 14) }}
                                </span>
                            @endforeach
                            <span class="grid h-9 w-9 place-items-center rounded-full bg-green-700 text-xs font-black text-white" title="{{ $dashboardUser?->name }}">{{ mb_substr($dashboardUser?->name ?? 'A', 0, 1) }}</span>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" class="inline-flex h-9 items-center gap-2 rounded-full bg-green-50 px-3 text-xs font-black text-green-700 ring-1 ring-green-100 transition hover:bg-green-100" data-dashboard-timeframe-toggle aria-pressed="true">
                                <span class="h-4 w-7 rounded-full bg-green-600 p-0.5 transition" data-dashboard-timeframe-track>
                                    <span class="block h-3 w-3 translate-x-3 rounded-full bg-white transition" data-dashboard-timeframe-knob></span>
                                </span>
                                <span data-dashboard-timeframe-status>@lang('Mindigo-dashboard::app.timeframe')</span>
                            </button>
                            <button type="button" class="inline-flex h-9 items-center gap-2 rounded-full bg-white px-3 text-xs font-black text-slate-600 ring-1 ring-slate-200 transition hover:border-green-200 hover:bg-green-50 hover:text-green-700" data-dashboard-date-button>
                                <x-heroicon-o-calendar-days class="h-4 w-4" />
                                <span data-dashboard-date-label>{{ now()->format('d/m/Y H:i:s') }}</span>
                            </button>
                            <input type="date" value="{{ now()->toDateString() }}" class="sr-only" data-dashboard-date-input>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 xl:grid-cols-[minmax(0,1fr)_31rem]">
                        <div>
                            <p class="inline-flex rounded-full bg-green-50 px-3 py-1 text-xs font-black text-green-700 ring-1 ring-green-100">@lang('Mindigo-dashboard::app.exam_report')</p>
                            <p class="mt-5 text-sm font-black text-slate-700">@lang('Mindigo-dashboard::app.total_attempts')</p>
                            <div class="mt-1 flex flex-wrap items-end gap-2">
                                <h2 class="text-5xl font-black tracking-tight text-slate-950 max-sm:text-4xl">{{ number_format($totalAttempts) }}</h2>
                                <span class="mb-1 rounded-full bg-green-600 px-2.5 py-1 text-[11px] font-black text-white">{{ ($growth >= 0 ? '+' : '') . $growth . '%' }}</span>
                                <span class="mb-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-black text-emerald-700 ring-1 ring-emerald-100">@lang('Mindigo-dashboard::app.attempts_delta')</span>
                            </div>
                            <p class="mt-2 text-xs font-bold text-slate-400">@lang('Mindigo-dashboard::app.previous_period', ['count' => number_format($previousMonthAttempts)])</p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-5">
                            <article class="rounded-2xl border border-green-100 bg-linear-to-br from-white to-green-50 p-3 shadow-sm sm:col-span-1">
                                <p class="text-[11px] font-black text-green-700">@lang('Mindigo-dashboard::app.top_candidate')</p>
                                <strong class="mt-2 block text-2xl font-black text-slate-950">{{ $topPerformer ? round($topPerformer->avg_score) : '—' }}</strong>
                                <div class="mt-3 flex items-center justify-between gap-2 text-xs font-bold text-slate-600">
                                    <span class="inline-flex items-center gap-1"><span class="grid h-5 w-5 place-items-center rounded-full bg-sky-100 text-[10px] text-sky-700">{{ $topPerformer ? mb_substr($topPerformer->name, 0, 1) : '?' }}</span>{{ $topPerformer ? $topPerformer->name : __('Mindigo-dashboard::app.demo_candidate') }}</span>
                                    <x-heroicon-o-chevron-right class="h-4 w-4" />
                                </div>
                            </article>
                            <article class="rounded-2xl border border-emerald-200 bg-linear-to-br from-green-700 to-emerald-500 p-3 text-white shadow-sm sm:col-span-2">
                                <div class="flex items-center justify-between">
                                    <p class="text-[11px] font-black text-green-100">@lang('Mindigo-dashboard::app.best_exam')</p>
                                    <span class="grid h-7 w-7 place-items-center rounded-full bg-white/15">
                                        <x-heroicon-o-star class="h-4 w-4 text-green-100" />
                                    </span>
                                </div>
                                <strong class="mt-2 block text-2xl font-black">{{ $bestExam ? round($bestExam->attempts_avg_percentage ?? 0) . '%' : '—' }}</strong>
                                <p class="mt-2 text-xs font-bold text-green-50">{{ $bestExam ? \Illuminate\Support\Str::limit($bestExam->title, 28) : __('Mindigo-dashboard::app.best_exam_name') }}</p>
                            </article>
                            <article class="rounded-2xl border border-slate-200 bg-white p-3 text-center shadow-sm">
                                <p class="text-[11px] font-black text-slate-500">@lang('Mindigo-dashboard::app.exam_count')</p>
                                <strong class="mt-2 block text-xl font-black text-slate-950">{{ $totalExams }}</strong>
                                <span class="mt-2 inline-flex rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-black text-green-700 ring-1 ring-green-100">{{ $recentExams > 0 ? '+' . $recentExams : '0' }}</span>
                            </article>
                            <article class="rounded-2xl border border-emerald-200 bg-emerald-50 p-3 text-center shadow-sm">
                                <p class="text-[11px] font-black text-emerald-700">@lang('Mindigo-dashboard::app.pass_rate')</p>
                                <strong class="mt-2 block text-xl font-black text-emerald-800">{{ $passRate }}%</strong>
                                <span class="mt-2 inline-flex rounded-full bg-white px-2 py-0.5 text-[10px] font-black text-emerald-700 ring-1 ring-emerald-100">{{ number_format($passedAttempts) }}</span>
                            </article>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                            @foreach($userMetrics as $metric)
                                <div class="grid min-h-12 grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 text-xs font-black text-slate-500 shadow-sm">
                                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full text-[10px] ring-1 {{ $metric['tone'] }}">{{ $metric['initial'] }}</span>
                                    <strong class="min-w-0 truncate text-sm text-slate-700">{{ number_format($metric['value']) }}</strong>
                                    <b class="shrink-0 whitespace-nowrap text-[11px] text-slate-300">{{ round($metric['value'] / $totalUsers * 100, 1) }}%</b>
                                </div>
                            @endforeach
                        </div>
                        <a href="{{ Route::has('reports.index') ? route('reports.index') : '#reports' }}" class="inline-flex min-h-10 items-center justify-center rounded-full bg-green-700 px-5 text-xs font-black text-white no-underline transition hover:bg-green-600">@lang('Mindigo-dashboard::app.details')</a>
                    </div>
                </section>

                <section class="grid items-stretch gap-4 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
                    <div class="grid items-stretch gap-4 lg:grid-cols-2">
                        <div class="min-h-84 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm" data-dashboard-tool-panel="source-list">
                            <div class="mb-4 flex items-center justify-between gap-3">
                                <div class="relative" data-dashboard-dropdown>
                                    <button type="button" class="inline-flex h-9 items-center gap-2 rounded-full bg-slate-50 px-3 text-slate-600 ring-1 ring-slate-200 transition hover:bg-green-50 hover:text-green-700" data-dashboard-dropdown-trigger aria-expanded="false">
                                        <x-heroicon-o-list-bullet class="h-5 w-5" />
                                        <span class="sr-only" data-dashboard-dropdown-label>@lang('Mindigo-dashboard::app.source_view_list')</span>
                                        <x-heroicon-m-chevron-down class="h-3.5 w-3.5" />
                                    </button>
                                    <div class="absolute left-0 top-11 z-30 hidden min-w-44 rounded-2xl border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-900/10" data-dashboard-dropdown-menu>
                                        <button type="button" class="dashboard-tool-option" data-dashboard-option data-target="source-list" data-tool="view" data-value="list">@lang('Mindigo-dashboard::app.source_view_list')</button>
                                        <button type="button" class="dashboard-tool-option" data-dashboard-option data-target="source-list" data-tool="view" data-value="compact">@lang('Mindigo-dashboard::app.source_view_compact')</button>
                                    </div>
                                </div>
                                <div class="relative" data-dashboard-dropdown>
                                    <button type="button" class="inline-flex h-9 items-center gap-1 rounded-full bg-slate-50 px-3 text-xs font-black text-slate-600 ring-1 ring-slate-200 transition hover:bg-green-50 hover:text-green-700" data-dashboard-dropdown-trigger aria-expanded="false">
                                        <span data-dashboard-dropdown-label>@lang('Mindigo-dashboard::app.filters')</span>
                                        <x-heroicon-o-funnel class="h-4 w-4" />
                                    </button>
                                    <div class="absolute right-0 top-11 z-30 hidden min-w-44 rounded-2xl border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-900/10" data-dashboard-dropdown-menu>
                                        <button type="button" class="dashboard-tool-option" data-dashboard-option data-target="source-list" data-tool="filter" data-value="all">@lang('Mindigo-dashboard::app.filter_all')</button>
                                        <button type="button" class="dashboard-tool-option" data-dashboard-option data-target="source-list" data-tool="filter" data-value="core">@lang('Mindigo-dashboard::app.filter_core_exams')</button>
                                        <button type="button" class="dashboard-tool-option" data-dashboard-option data-target="source-list" data-tool="filter" data-value="learning">@lang('Mindigo-dashboard::app.filter_learning')</button>
                                    </div>
                                </div>
                            </div>

                            @php
                                $subjectIcons = ['heroicon-o-academic-cap', 'heroicon-o-bolt', 'heroicon-o-circle-stack', 'heroicon-o-users'];
                                $subjectTones = ['bg-green-50 text-green-700', 'bg-amber-50 text-amber-700', 'bg-sky-50 text-sky-700', 'bg-emerald-50 text-emerald-700'];
                                $subjectFilters = ['core', 'learning', 'core', 'learning'];
                            @endphp
                            <div class="grid gap-2">
                                @forelse($topSubjects as $i => $subject)
                                    <div class="flex min-h-14 items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50/80 px-3 transition" data-dashboard-source-row data-filter="{{ $subjectFilters[$i] ?? 'all' }}">
                                        <span class="grid h-9 w-9 place-items-center rounded-xl {{ $subjectTones[$i] ?? 'bg-slate-50 text-slate-600' }}">
                                            <x-dynamic-component :component="$subjectIcons[$i] ?? 'heroicon-o-document'" class="h-5 w-5" />
                                        </span>
                                        <span class="min-w-0 flex-1 truncate text-sm font-black text-slate-700">{{ $subject->subject }}</span>
                                        <strong class="text-sm font-black text-slate-950">{{ number_format($subject->attempt_count) }}</strong>
                                        <span class="text-xs font-black text-slate-400">{{ $totalSubjectAttempts > 0 ? round($subject->attempt_count / $totalSubjectAttempts * 100) : 0 }}%</span>
                                    </div>
                                @empty
                                    <div class="flex min-h-36 flex-col items-center justify-center gap-3">
                                        <x-heroicon-o-chart-pie class="h-12 w-12 text-slate-200" />
                                        <p class="text-sm font-bold text-slate-400">@lang('Mindigo-dashboard::app.no_data')</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="min-h-84 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm" data-dashboard-tool-panel="source-chart">
                            <div class="mb-4 flex items-center justify-between gap-3">
                                <div class="relative" data-dashboard-dropdown>
                                    <button type="button" class="inline-flex h-9 items-center gap-2 rounded-full bg-slate-50 px-3 text-slate-600 ring-1 ring-slate-200 transition hover:bg-green-50 hover:text-green-700" data-dashboard-dropdown-trigger aria-expanded="false">
                                        <x-heroicon-o-chart-bar class="h-5 w-5" />
                                        <span class="sr-only" data-dashboard-dropdown-label>@lang('Mindigo-dashboard::app.chart_view_bar')</span>
                                        <x-heroicon-m-chevron-down class="h-3.5 w-3.5" />
                                    </button>
                                    <div class="absolute left-0 top-11 z-30 hidden min-w-44 rounded-2xl border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-900/10" data-dashboard-dropdown-menu>
                                        <button type="button" class="dashboard-tool-option" data-dashboard-option data-target="source-chart" data-tool="view" data-value="bar">@lang('Mindigo-dashboard::app.chart_view_bar')</button>
                                        <button type="button" class="dashboard-tool-option" data-dashboard-option data-target="source-chart" data-tool="view" data-value="compare">@lang('Mindigo-dashboard::app.chart_view_compare')</button>
                                    </div>
                                </div>
                                <div class="relative" data-dashboard-dropdown>
                                    <button type="button" class="inline-flex h-9 items-center gap-1 rounded-full bg-slate-50 px-3 text-xs font-black text-slate-600 ring-1 ring-slate-200 transition hover:bg-green-50 hover:text-green-700" data-dashboard-dropdown-trigger aria-expanded="false">
                                        <span data-dashboard-dropdown-label>@lang('Mindigo-dashboard::app.filters')</span>
                                        <x-heroicon-o-funnel class="h-4 w-4" />
                                    </button>
                                    <div class="absolute right-0 top-11 z-30 hidden min-w-44 rounded-2xl border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-900/10" data-dashboard-dropdown-menu>
                                        <button type="button" class="dashboard-tool-option" data-dashboard-option data-target="source-chart" data-tool="filter" data-value="all">@lang('Mindigo-dashboard::app.filter_all')</button>
                                        <button type="button" class="dashboard-tool-option" data-dashboard-option data-target="source-chart" data-tool="filter" data-value="core">@lang('Mindigo-dashboard::app.filter_core_exams')</button>
                                        <button type="button" class="dashboard-tool-option" data-dashboard-option data-target="source-chart" data-tool="filter" data-value="learning">@lang('Mindigo-dashboard::app.filter_learning')</button>
                                    </div>
                                </div>
                            </div>

                            @php
                                $barTones = ['bg-green-600', 'bg-emerald-500', 'bg-sky-500', 'bg-amber-400'];
                                $barFilters = ['core', 'learning', 'core', 'learning'];
                                $maxSubjectCount = $topSubjects->max('attempt_count') ?: 1;
                            @endphp
                            <div class="grid h-56 items-end gap-2 rounded-2xl bg-slate-50 px-3 pb-4 pt-6" style="grid-template-columns: repeat({{ max(1, $topSubjects->count()) }}, minmax(0, 1fr))">
                                @forelse($topSubjects as $i => $subject)
                                    @php $height = max(10, round($subject->attempt_count / $maxSubjectCount * 88)); @endphp
                                    <div class="flex h-full min-w-0 flex-col items-center justify-end gap-2 transition" data-dashboard-chart-bar data-filter="{{ $barFilters[$i] ?? 'all' }}">
                                        <div class="grid w-full min-w-10 place-items-center rounded-2xl {{ $barTones[$i] ?? 'bg-slate-400' }} text-xs font-black text-white" style="height: {{ $height }}%">
                                            {{ mb_substr($subject->subject, 0, 2) }}
                                        </div>
                                        <span class="block h-4 w-full truncate text-center text-[10px] font-black leading-4 text-slate-400" title="{{ $subject->subject }}">{{ \Illuminate\Support\Str::limit($subject->subject, 6) }}</span>
                                    </div>
                                @empty
                                    <div class="col-span-4 flex min-h-44 flex-col items-center justify-center gap-3">
                                        <x-heroicon-o-chart-bar class="h-12 w-12 text-slate-200" />
                                        <span class="text-sm font-bold text-slate-400">@lang('Mindigo-dashboard::app.no_data')</span>
                                    </div>
                                @endforelse
                            </div>

                            <div class="mt-3">
                                <p class="text-sm font-black text-slate-900">@lang('Mindigo-dashboard::app.source_attempts_title')</p>
                                <p class="mt-0.5 text-xs font-semibold text-slate-400">@lang('Mindigo-dashboard::app.source_attempts_desc')</p>
                            </div>
                        </div>
                    </div>

                    <div class="min-h-84 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm" id="ranking">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-black uppercase tracking-wider text-slate-400">@lang('Mindigo-dashboard::app.ranking')</p>
                                <h3 class="mt-1 text-lg font-black text-slate-950">@lang('Mindigo-dashboard::app.top_performers')</h3>
                            </div>
                            <span class="rounded-full bg-green-50 px-2.5 py-1 text-xs font-black text-green-700">@lang('Mindigo-dashboard::app.live')</span>
                        </div>
                        <div class="h-72"><canvas id="rankingChart"></canvas></div>
                    </div>
                </section>

                <section class="grid gap-4 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
                    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm" id="question-bank">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="grid h-10 w-10 place-items-center rounded-full bg-green-50 text-green-700 ring-1 ring-green-100">
                                    <x-heroicon-o-chart-pie class="h-5 w-5" />
                                </span>
                                <div>
                                    <p class="text-xs font-black text-slate-400">@lang('Mindigo-dashboard::app.quality')</p>
                                    <h3 class="mt-0.5 text-sm font-black text-slate-950">@lang('Mindigo-dashboard::app.question_quality')</h3>
                                </div>
                            </div>
                            <div class="flex rounded-full bg-slate-100 p-1 text-xs font-black" id="qchart-tabs">
                                <button class="rounded-full bg-green-700 px-3 py-1.5 text-white shadow-sm transition" data-qchart-tab="difficulty">@lang('Mindigo-dashboard::app.qchart_difficulty')</button>
                                <button class="px-3 py-1.5 text-slate-500 transition hover:text-slate-700" data-qchart-tab="subject">@lang('Mindigo-dashboard::app.qchart_subject')</button>
                                <button class="px-3 py-1.5 text-slate-500 transition hover:text-slate-700" data-qchart-tab="status">@lang('Mindigo-dashboard::app.qchart_status')</button>
                            </div>
                        </div>
                        <div class="grid gap-4 lg:grid-cols-[14rem_minmax(0,1fr)]">
                            {{-- Left stat card --}}
                            <div class="rounded-3xl bg-linear-to-br from-green-700 to-emerald-500 p-5 text-white shadow-sm">
                                <p class="text-xs font-black text-green-100">@lang('Mindigo-dashboard::app.qbank_overview')</p>
                                <h4 class="mt-1 text-lg font-black">Mindigo LMS</h4>
                                <div class="mt-7 space-y-4">
                                    <div>
                                        <p class="text-xs font-bold text-green-100">@lang('Mindigo-dashboard::app.standard_questions')</p>
                                        <strong class="mt-0.5 block text-2xl font-black">{{ number_format($totalQuestions) }}</strong>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-green-100">@lang('Mindigo-dashboard::app.qbank_pending')</p>
                                        <strong class="mt-0.5 block text-xl font-black">{{ number_format($pendingQuestions) }} <span class="text-sm text-green-100">@lang('Mindigo-dashboard::app.questions_unit')</span></strong>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-green-100">@lang('Mindigo-dashboard::app.pass_rate')</p>
                                        <strong class="mt-0.5 block text-xl font-black">{{ $passRate }}% <span class="text-sm text-green-100">{{ number_format($passedAttempts) }}/{{ number_format($totalAttempts) }}</span></strong>
                                    </div>
                                </div>
                            </div>

                            {{-- Right dynamic chart --}}
                            <div class="relative min-h-64 rounded-3xl bg-slate-50 p-4" id="qchart-area">
                                {{-- Rendered by JS via window.__questionStats --}}
                                <div id="qchart-bars" class="flex h-full min-h-56 items-end gap-2"></div>
                                <div id="qchart-empty" class="hidden min-h-56 flex-col items-center justify-center gap-3">
                                    <x-heroicon-o-circle-stack class="h-12 w-12 text-slate-200" />
                                    <p class="text-sm font-bold text-slate-400">@lang('Mindigo-dashboard::app.no_data')</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm" id="exams">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-black uppercase tracking-wider text-slate-400">@lang('Mindigo-dashboard::app.operations')</p>
                                <h3 class="mt-1 text-lg font-black text-slate-950">@lang('Mindigo-dashboard::app.latest_exams')</h3>
                            </div>
                            <a href="{{ Route::has('reports.exams') ? route('reports.exams') : '#reports' }}" class="rounded-full bg-slate-950 px-4 py-2 text-xs font-black text-white no-underline">@lang('Mindigo-dashboard::app.view_report')</a>
                        </div>
                        <div class="overflow-hidden rounded-2xl border border-slate-200">
                            <table class="w-full min-w-155 text-left">
                                <thead class="bg-slate-50 text-[11px] font-black uppercase text-slate-400">
                                    <tr>
                                        <th class="px-4 py-3">@lang('Mindigo-dashboard::app.exam_name')</th>
                                        <th class="px-4 py-3">@lang('Mindigo-dashboard::app.candidates')</th>
                                        <th class="px-4 py-3">@lang('Mindigo-dashboard::app.avg_score')</th>
                                        <th class="px-4 py-3">@lang('Mindigo-dashboard::app.status')</th>
                                    </tr>
                                </thead>
                                @php
                                    $examStatusTones = [
                                        'published' => 'bg-green-100 text-green-800',
                                        'reviewing' => 'bg-amber-100 text-amber-800',
                                        'draft' => 'bg-slate-100 text-slate-700',
                                        'closed' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <tbody class="divide-y divide-slate-100 text-sm font-bold text-slate-700">
                                    @forelse($latestExams as $exam)
                                        <tr class="bg-white">
                                            <td class="max-w-50 truncate px-4 py-3.5">{{ \Illuminate\Support\Str::limit($exam->title, 36) }}</td>
                                            <td class="px-4 py-3.5">{{ number_format($exam->attempts_count) }}</td>
                                            <td class="px-4 py-3.5">{{ $exam->attempts_avg_score ? round($exam->attempts_avg_score, 1) : '—' }}</td>
                                            <td class="px-4 py-3.5"><span class="rounded-full px-2.5 py-1 text-[11px] font-black {{ $examStatusTones[$exam->status] ?? 'bg-slate-100 text-slate-700' }}">{{ __('Mindigo-dashboard::app.' . $exam->status) }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="h-44 text-center align-middle">
                                            <div class="flex flex-col items-center justify-center gap-3">
                                                <x-heroicon-o-document-text class="h-12 w-12 text-slate-200" />
                                                <span class="text-sm font-bold text-slate-400">@lang('Mindigo-dashboard::app.no_data')</span>
                                            </div>
                                        </td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </main>

            <aside class="space-y-4">
                <section class="min-h-75 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-slate-400">@lang('Mindigo-dashboard::app.top_sales')</p>
                            <h3 class="mt-1 text-lg font-black text-slate-950">@lang('Mindigo-dashboard::app.learner_flow')</h3>
                        </div>
                        <span class="rounded-full bg-green-50 px-2.5 py-1 text-xs font-black text-green-700">+3</span>
                    </div>
                    <div class="mt-5 grid gap-2">
                        @forelse($topPerformers as $performer)
                            <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-3">
                                <span class="grid h-9 w-9 place-items-center rounded-full bg-green-100 text-xs font-black text-green-700">{{ mb_substr($performer->name, 0, 1) }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-black text-slate-900">{{ $performer->name }}</span>
                                    <span class="block text-xs font-bold text-slate-400">{{ $performer->attempt_count }} @lang('Mindigo-dashboard::app.attempts_label')</span>
                                </span>
                                <b class="rounded-full bg-slate-950 px-2 py-1 text-xs font-black text-white">{{ round($performer->avg_score) }}%</b>
                            </div>
                        @empty
                            <div class="flex min-h-44 flex-col items-center justify-center gap-3">
                                <x-heroicon-o-trophy class="h-12 w-12 text-slate-200" />
                                <p class="text-sm font-bold text-slate-400">@lang('Mindigo-dashboard::app.no_data')</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="min-h-84 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm" id="reports">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-slate-400">@lang('Mindigo-dashboard::app.reports')</p>
                            <h3 class="mt-1 text-lg font-black text-slate-950">@lang('Mindigo-dashboard::app.today_tasks')</h3>
                        </div>
                        <x-heroicon-o-adjustments-horizontal class="h-5 w-5 text-slate-400" />
                    </div>
                    <div class="mt-4 space-y-3">
                        @foreach([
                            ['label' => __('Mindigo-dashboard::app.task_review_questions'), 'count' => $pendingReview, 'tone' => 'bg-green-600'],
                            ['label' => __('Mindigo-dashboard::app.task_publish_exam'), 'count' => $pendingPublish, 'tone' => 'bg-amber-500'],
                            ['label' => __('Mindigo-dashboard::app.task_support'), 'count' => $openSupport, 'tone' => 'bg-slate-950'],
                        ] as $task)
                            <div class="grid grid-cols-[8px_minmax(0,1fr)_auto] items-center gap-3 rounded-2xl bg-slate-50 p-3">
                                <span class="h-10 rounded-full {{ $task['tone'] }}"></span>
                                <span class="truncate text-sm font-black text-slate-800">{{ $task['label'] }}</span>
                                <strong class="text-lg font-black text-slate-950">{{ $task['count'] }}</strong>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="min-h-84 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="course-review-queue-title">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-black uppercase tracking-wider text-amber-500">@lang('teacher-course::admin-review.pending_dashboard')</p>
                            <h3 id="course-review-queue-title" class="mt-1 text-lg font-black text-slate-950">@lang('teacher-course::admin-review.review_queue')</h3>
                        </div>
                        <span class="grid h-9 min-w-9 place-items-center rounded-full bg-amber-50 px-2 text-sm font-black text-amber-700 ring-1 ring-amber-100">{{ number_format($courseReviewDashboard['pending']) }}</span>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <div class="rounded-2xl bg-amber-50 p-3 ring-1 ring-amber-100">
                            <p class="text-[10px] font-black uppercase tracking-wider text-amber-600">@lang('teacher-course::admin-review.pending_dashboard')</p>
                            <strong class="mt-1 block text-xl font-black text-slate-950">{{ number_format($courseReviewDashboard['pending']) }}</strong>
                        </div>
                        <div class="rounded-2xl bg-green-50 p-3 ring-1 ring-green-100">
                            <p class="text-[10px] font-black uppercase tracking-wider text-green-600">@lang('teacher-course::admin-review.approved_today')</p>
                            <strong class="mt-1 block text-xl font-black text-slate-950">{{ number_format($courseReviewDashboard['approved_today']) }}</strong>
                        </div>
                    </div>

                    <div class="mt-4 space-y-2">
                        @forelse($courseReviewDashboard['queue'] as $queuedCourse)
                            <a href="{{ route('admin.course-publication-reviews.show', $queuedCourse) }}" class="flex min-w-0 items-center gap-3 rounded-2xl bg-slate-50 p-3 text-sm font-black text-slate-800 no-underline transition hover:bg-green-50 hover:text-green-700">
                                <span class="h-10 w-1.5 shrink-0 rounded-full bg-amber-400"></span>
                                <span class="min-w-0 flex-1 truncate">{{ $queuedCourse->name }}</span>
                                <x-heroicon-o-chevron-right class="h-4 w-4 shrink-0 text-slate-400" />
                            </a>
                        @empty
                            <div class="flex min-h-40 flex-col items-center justify-center gap-3 rounded-2xl bg-slate-50">
                                <x-heroicon-o-clipboard-document-check class="h-10 w-10 text-slate-200" />
                                <p class="text-sm font-bold text-slate-400">@lang('teacher-course::admin-review.empty')</p>
                            </div>
                        @endforelse
                    </div>

                    <a href="{{ route('admin.course-publication-reviews.index') }}" class="mt-4 inline-flex h-10 w-full items-center justify-center rounded-2xl bg-green-600 px-4 text-xs font-black text-white no-underline transition hover:bg-green-500">
                        @lang('teacher-course::admin-review.view_queue')
                    </a>
                </section>
            </aside>
        </div>
    </section>
@endsection
