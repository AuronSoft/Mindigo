@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-dashboard::app.title'))

@section('styles')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
    <header class="mb-6 flex items-start justify-between gap-5 max-xl:grid max-xl:grid-cols-1">
        <div>
            <p class="mb-1.5 text-[11px] font-black uppercase tracking-wider text-green-600">@lang('Mindigo-dashboard::app.admin_area')</p>
            <h1 class="m-0 text-3xl font-black leading-tight text-slate-900 max-sm:text-2xl">@lang('Mindigo-dashboard::app.dashboard_heading')</h1>
            <p class="mt-2 max-w-3xl text-sm font-semibold leading-6 text-slate-500">@lang('Mindigo-dashboard::app.dashboard_subheading')</p>
        </div>

        <div class="flex items-center gap-2.5 max-sm:grid max-sm:grid-cols-1">
            <div class="flex h-11 w-72 items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-3 max-sm:w-full">
                <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-2 text-slate-400" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" placeholder="@lang('Mindigo-dashboard::app.global_search')" class="min-w-0 flex-1 border-0 bg-transparent text-sm font-bold outline-none placeholder:text-slate-400">
            </div>
            <div class="relative">
                <button id="dashboard-notification-btn" class="relative grid h-11 w-11 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm shadow-slate-900/[0.03] transition hover:border-green-200 hover:bg-green-50 hover:text-green-700" type="button" title="@lang('Mindigo-dashboard::app.notifications')" aria-expanded="false">
                    <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <span class="absolute right-2.5 top-2.5 h-2 w-2 rounded-full bg-green-500 ring-2 ring-white"></span>
                </button>

                <div id="dashboard-notification-menu" class="absolute right-0 top-[52px] z-40 hidden w-80 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/10 max-sm:right-auto max-sm:left-0 max-sm:w-[calc(100vw-2rem)]">
                    <div class="flex items-center justify-between gap-3 rounded-xl bg-green-50 px-3 py-2.5">
                        <div>
                            <p class="m-0 text-sm font-black text-slate-900">@lang('Mindigo-dashboard::app.notification_title')</p>
                            <p class="m-0 mt-0.5 text-[11px] font-bold text-slate-500">@lang('Mindigo-dashboard::app.notification_subtitle')</p>
                        </div>
                        <span class="rounded-full bg-white px-2 py-1 text-[10px] font-black text-green-700 ring-1 ring-green-100">3</span>
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
                        <a href="#reports" class="flex gap-3 rounded-xl px-3 py-2.5 text-slate-700 no-underline transition hover:bg-slate-50">
                            <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-slate-300"></span>
                            <span class="min-w-0">
                                <span class="block text-sm font-black">@lang('Mindigo-dashboard::app.notification_system_ok')</span>
                                <span class="mt-0.5 block text-xs font-semibold leading-5 text-slate-500">@lang('Mindigo-dashboard::app.notification_system_ok_desc')</span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
            <a href="#exams" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-green-500 px-4 text-sm font-black text-white no-underline shadow-[0_4px_0_#15803d] transition hover:-translate-y-0.5 hover:bg-green-400 hover:shadow-[0_2px_0_#15803d] active:translate-y-1 active:shadow-none max-sm:w-full">
                <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                @lang('Mindigo-dashboard::app.create_exam')
            </a>
        </div>
    </header>

    <section class="grid gap-5">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-900/[0.03]">
            <div class="grid grid-cols-[minmax(0,1fr)_420px] gap-6 max-2xl:grid-cols-[minmax(0,1fr)_360px] max-xl:grid-cols-1">
                <div class="flex min-w-0 gap-4">
                    <div class="hidden w-1.5 rounded-full bg-green-500 sm:block"></div>
                    <div class="min-w-0">
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-2 rounded-full border border-green-200 bg-green-50 px-3 py-1.5 text-[11px] font-black text-green-700">
                                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                @lang('Mindigo-dashboard::app.system_health')
                            </span>
                            <span class="rounded-full border border-slate-200 px-3 py-1.5 text-[11px] font-black text-slate-500">@lang('Mindigo-dashboard::app.last_7_days')</span>
                        </div>
                        <h2 class="m-0 max-w-3xl text-2xl font-black leading-snug text-slate-950 max-sm:text-xl">@lang('Mindigo-dashboard::app.hero_title')</h2>
                        <p class="mt-3 max-w-3xl text-sm font-semibold leading-7 text-slate-500">@lang('Mindigo-dashboard::app.hero_desc')</p>
                        <div class="mt-5 flex flex-wrap gap-2">
                            <a href="#exams" class="inline-flex h-10 items-center justify-center rounded-xl bg-green-500 px-4 text-sm font-black text-white no-underline shadow-[0_3px_0_#15803d] transition hover:bg-green-400 active:translate-y-1 active:shadow-none">@lang('Mindigo-dashboard::app.create_exam')</a>
                            <a href="#reports" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 no-underline transition hover:border-green-200 hover:bg-green-50 hover:text-green-700">@lang('Mindigo-dashboard::app.view_report')</a>
                        </div>
                    </div>
                </div>

                <div class="self-stretch rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div>
                            <p class="m-0 text-[11px] font-black uppercase tracking-wider text-slate-400">@lang('Mindigo-dashboard::app.system_health')</p>
                            <h3 class="mt-1 text-sm font-black text-slate-900">Live operations</h3>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-1 text-[10px] font-black text-green-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                            Online
                        </span>
                    </div>

                    <div class="divide-y divide-slate-200 rounded-xl bg-white ring-1 ring-slate-200">
                        <div class="flex items-center justify-between gap-4 px-4 py-3">
                            <div class="min-w-0">
                                <span class="block truncate text-xs font-black text-slate-500">@lang('Mindigo-dashboard::app.completion_rate')</span>
                                <span class="mt-1 block h-1.5 w-32 overflow-hidden rounded-full bg-slate-100"><span class="block h-full w-[98%] rounded-full bg-green-500"></span></span>
                            </div>
                            <strong class="text-2xl font-black tracking-tight text-slate-950">98%</strong>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-4 py-3">
                            <div class="min-w-0">
                                <span class="block truncate text-xs font-black text-slate-500">@lang('Mindigo-dashboard::app.system_uptime')</span>
                                <span class="mt-1 block text-[11px] font-bold text-slate-400">Monitoring enabled</span>
                            </div>
                            <strong class="text-2xl font-black tracking-tight text-slate-950">24/7</strong>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-4 py-3">
                            <div class="min-w-0">
                                <span class="block truncate text-xs font-black text-slate-500">@lang('Mindigo-dashboard::app.active_accounts')</span>
                                <span class="mt-1 block text-[11px] font-bold text-slate-400">Admin, teacher, student</span>
                            </div>
                            <strong class="text-2xl font-black tracking-tight text-slate-950">{{ number_format($stats['active_users']) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-4 max-xl:grid-cols-2 max-sm:grid-cols-1">
            <article class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-900/[0.03] transition hover:-translate-y-0.5 hover:border-green-200 hover:shadow-md hover:shadow-slate-900/[0.05]">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="block text-xs font-black text-slate-500">@lang('Mindigo-dashboard::app.total_students')</span>
                        <strong class="mt-2 block text-4xl font-black tracking-tight text-slate-950">{{ number_format($stats['students']) }}</strong>
                    </div>
                    <div class="grid h-11 w-11 place-items-center rounded-xl bg-green-50 text-green-600 ring-1 ring-green-100"><svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg></div>
                </div>
                <div class="mt-4 flex items-center justify-between gap-3">
                    <small class="text-xs font-bold leading-5 text-slate-400">@lang('Mindigo-dashboard::app.students_hint')</small>
                    <span class="h-1.5 w-16 rounded-full bg-green-100"><span class="block h-full w-3/4 rounded-full bg-green-500"></span></span>
                </div>
            </article>
            <article class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-900/[0.03] transition hover:-translate-y-0.5 hover:border-green-200 hover:shadow-md hover:shadow-slate-900/[0.05]">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="block text-xs font-black text-slate-500">@lang('Mindigo-dashboard::app.total_teachers')</span>
                        <strong class="mt-2 block text-4xl font-black tracking-tight text-slate-950">{{ number_format($stats['teachers']) }}</strong>
                    </div>
                    <div class="grid h-11 w-11 place-items-center rounded-xl bg-green-50 text-green-600 ring-1 ring-green-100"><svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10l-10-5-10 5 10 5 10-5z"/><path d="M6 12v5c3 2 9 2 12 0v-5"/></svg></div>
                </div>
                <div class="mt-4 flex items-center justify-between gap-3">
                    <small class="text-xs font-bold leading-5 text-slate-400">@lang('Mindigo-dashboard::app.teachers_hint')</small>
                    <span class="h-1.5 w-16 rounded-full bg-green-100"><span class="block h-full w-2/3 rounded-full bg-green-500"></span></span>
                </div>
            </article>
            <article class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-900/[0.03] transition hover:-translate-y-0.5 hover:border-green-200 hover:shadow-md hover:shadow-slate-900/[0.05]">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="block text-xs font-black text-slate-500">@lang('Mindigo-dashboard::app.mock_exams')</span>
                        <strong class="mt-2 block text-4xl font-black tracking-tight text-slate-950">128</strong>
                    </div>
                    <div class="grid h-11 w-11 place-items-center rounded-xl bg-green-50 text-green-600 ring-1 ring-green-100"><svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div>
                </div>
                <div class="mt-4 flex items-center justify-between gap-3">
                    <small class="text-xs font-bold leading-5 text-slate-400">@lang('Mindigo-dashboard::app.mock_exams_hint')</small>
                    <span class="h-1.5 w-16 rounded-full bg-green-100"><span class="block h-full w-4/5 rounded-full bg-green-500"></span></span>
                </div>
            </article>
            <article class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-900/[0.03] transition hover:-translate-y-0.5 hover:border-green-200 hover:shadow-md hover:shadow-slate-900/[0.05]">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="block text-xs font-black text-slate-500">@lang('Mindigo-dashboard::app.questions')</span>
                        <strong class="mt-2 block text-4xl font-black tracking-tight text-slate-950">18,420</strong>
                    </div>
                    <div class="grid h-11 w-11 place-items-center rounded-xl bg-green-50 text-green-600 ring-1 ring-green-100"><svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-6"/><path d="M4 20h16"/></svg></div>
                </div>
                <div class="mt-4 flex items-center justify-between gap-3">
                    <small class="text-xs font-bold leading-5 text-slate-400">@lang('Mindigo-dashboard::app.questions_hint')</small>
                    <span class="h-1.5 w-16 rounded-full bg-green-100"><span class="block h-full w-[88%] rounded-full bg-green-500"></span></span>
                </div>
            </article>
        </div>

        <div class="grid grid-cols-[minmax(0,1fr)_360px] gap-5 max-xl:grid-cols-1">
            <section class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-lg shadow-slate-900/[0.03]">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <p class="mb-1.5 text-[11px] font-black uppercase tracking-wider text-green-600">@lang('Mindigo-dashboard::app.performance')</p>
                        <h3 class="m-0 text-lg font-black leading-snug text-slate-900">@lang('Mindigo-dashboard::app.performance_title')</h3>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-[11px] font-black text-slate-600">@lang('Mindigo-dashboard::app.last_7_days')</span>
                </div>
                <div class="h-72"><canvas id="examTrendChart"></canvas></div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-lg shadow-slate-900/[0.03]">
                <div class="mb-4">
                    <p class="mb-1.5 text-[11px] font-black uppercase tracking-wider text-green-600">@lang('Mindigo-dashboard::app.quality')</p>
                    <h3 class="m-0 text-lg font-black leading-snug text-slate-900">@lang('Mindigo-dashboard::app.question_quality')</h3>
                </div>
                <div class="grid h-48 place-items-center"><canvas id="qualityChart"></canvas></div>
                <div class="grid gap-2.5">
                    <span class="flex items-center justify-between gap-2 text-xs font-extrabold text-slate-600"><span><i class="mr-2 inline-block h-2.5 w-2.5 rounded-full bg-green-500"></i>@lang('Mindigo-dashboard::app.approved_questions')</span><b class="text-slate-900">72%</b></span>
                    <span class="flex items-center justify-between gap-2 text-xs font-extrabold text-slate-600"><span><i class="mr-2 inline-block h-2.5 w-2.5 rounded-full bg-amber-500"></i>@lang('Mindigo-dashboard::app.review_questions')</span><b class="text-slate-900">18%</b></span>
                    <span class="flex items-center justify-between gap-2 text-xs font-extrabold text-slate-600"><span><i class="mr-2 inline-block h-2.5 w-2.5 rounded-full bg-red-500"></i>@lang('Mindigo-dashboard::app.rejected_questions')</span><b class="text-slate-900">10%</b></span>
                </div>
            </section>
        </div>

        <div class="grid grid-cols-[360px_minmax(0,1fr)] gap-5 max-xl:grid-cols-1">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-lg shadow-slate-900/[0.03]" id="question-bank">
                <div class="mb-4">
                    <p class="mb-1.5 text-[11px] font-black uppercase tracking-wider text-green-600">@lang('Mindigo-dashboard::app.workflow')</p>
                    <h3 class="m-0 text-lg font-black leading-snug text-slate-900">@lang('Mindigo-dashboard::app.today_tasks')</h3>
                </div>
                <div class="grid gap-2.5">
                    <div class="grid min-h-16 grid-cols-[10px_minmax(0,1fr)_auto] items-center gap-3 rounded-2xl bg-slate-50 p-3"><span class="h-10 rounded-full bg-red-500"></span><div><b class="block text-sm font-black text-slate-900">@lang('Mindigo-dashboard::app.task_review_questions')</b><small class="mt-1 block text-xs font-bold leading-5 text-slate-500">@lang('Mindigo-dashboard::app.task_review_questions_desc')</small></div><strong class="text-lg font-black text-slate-900">42</strong></div>
                    <div class="grid min-h-16 grid-cols-[10px_minmax(0,1fr)_auto] items-center gap-3 rounded-2xl bg-slate-50 p-3"><span class="h-10 rounded-full bg-amber-500"></span><div><b class="block text-sm font-black text-slate-900">@lang('Mindigo-dashboard::app.task_publish_exam')</b><small class="mt-1 block text-xs font-bold leading-5 text-slate-500">@lang('Mindigo-dashboard::app.task_publish_exam_desc')</small></div><strong class="text-lg font-black text-slate-900">8</strong></div>
                    <div class="grid min-h-16 grid-cols-[10px_minmax(0,1fr)_auto] items-center gap-3 rounded-2xl bg-slate-50 p-3"><span class="h-10 rounded-full bg-green-500"></span><div><b class="block text-sm font-black text-slate-900">@lang('Mindigo-dashboard::app.task_support')</b><small class="mt-1 block text-xs font-bold leading-5 text-slate-500">@lang('Mindigo-dashboard::app.task_support_desc')</small></div><strong class="text-lg font-black text-slate-900">15</strong></div>
                </div>
            </section>

            <section class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-lg shadow-slate-900/[0.03]" id="exams">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <p class="mb-1.5 text-[11px] font-black uppercase tracking-wider text-green-600">@lang('Mindigo-dashboard::app.operations')</p>
                        <h3 class="m-0 text-lg font-black leading-snug text-slate-900">@lang('Mindigo-dashboard::app.latest_exams')</h3>
                    </div>
                    <a href="#reports" class="text-xs font-black text-green-600 no-underline hover:text-green-700">@lang('Mindigo-dashboard::app.view_report')</a>
                </div>
                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="w-full min-w-[760px] border-collapse bg-white">
                        <thead>
                            <tr>
                                <th class="border-b border-slate-200 bg-slate-50 px-4 py-3.5 text-left text-[11px] font-black uppercase text-slate-500">@lang('Mindigo-dashboard::app.exam_name')</th>
                                <th class="border-b border-slate-200 bg-slate-50 px-4 py-3.5 text-left text-[11px] font-black uppercase text-slate-500">@lang('Mindigo-dashboard::app.subject')</th>
                                <th class="border-b border-slate-200 bg-slate-50 px-4 py-3.5 text-left text-[11px] font-black uppercase text-slate-500">@lang('Mindigo-dashboard::app.candidates')</th>
                                <th class="border-b border-slate-200 bg-slate-50 px-4 py-3.5 text-left text-[11px] font-black uppercase text-slate-500">@lang('Mindigo-dashboard::app.avg_score')</th>
                                <th class="border-b border-slate-200 bg-slate-50 px-4 py-3.5 text-left text-[11px] font-black uppercase text-slate-500">@lang('Mindigo-dashboard::app.status')</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td class="border-b border-slate-200 px-4 py-3.5 text-sm font-extrabold text-slate-700">De luyen thi THPT 2026 - Toan 01</td><td class="border-b border-slate-200 px-4 py-3.5 text-sm font-extrabold text-slate-700">Toan hoc</td><td class="border-b border-slate-200 px-4 py-3.5 text-sm font-extrabold text-slate-700">1,248</td><td class="border-b border-slate-200 px-4 py-3.5 text-sm font-extrabold text-slate-700">7.4</td><td class="border-b border-slate-200 px-4 py-3.5"><span class="inline-flex min-h-6 items-center rounded-full bg-green-100 px-2.5 text-[11px] font-black text-green-800">@lang('Mindigo-dashboard::app.published')</span></td></tr>
                            <tr><td class="border-b border-slate-200 px-4 py-3.5 text-sm font-extrabold text-slate-700">Kiem tra Sinh hoc - Di truyen</td><td class="border-b border-slate-200 px-4 py-3.5 text-sm font-extrabold text-slate-700">Sinh hoc</td><td class="border-b border-slate-200 px-4 py-3.5 text-sm font-extrabold text-slate-700">684</td><td class="border-b border-slate-200 px-4 py-3.5 text-sm font-extrabold text-slate-700">6.9</td><td class="border-b border-slate-200 px-4 py-3.5"><span class="inline-flex min-h-6 items-center rounded-full bg-amber-100 px-2.5 text-[11px] font-black text-amber-800">@lang('Mindigo-dashboard::app.reviewing')</span></td></tr>
                            <tr><td class="border-b border-slate-200 px-4 py-3.5 text-sm font-extrabold text-slate-700">Ngan hang Anh van B1 - Reading</td><td class="border-b border-slate-200 px-4 py-3.5 text-sm font-extrabold text-slate-700">Tieng Anh</td><td class="border-b border-slate-200 px-4 py-3.5 text-sm font-extrabold text-slate-700">932</td><td class="border-b border-slate-200 px-4 py-3.5 text-sm font-extrabold text-slate-700">8.1</td><td class="border-b border-slate-200 px-4 py-3.5"><span class="inline-flex min-h-6 items-center rounded-full bg-green-100 px-2.5 text-[11px] font-black text-green-800">@lang('Mindigo-dashboard::app.published')</span></td></tr>
                            <tr><td class="px-4 py-3.5 text-sm font-extrabold text-slate-700">De danh gia nang luc - Logic</td><td class="px-4 py-3.5 text-sm font-extrabold text-slate-700">Tu duy</td><td class="px-4 py-3.5 text-sm font-extrabold text-slate-700">510</td><td class="px-4 py-3.5 text-sm font-extrabold text-slate-700">7.7</td><td class="px-4 py-3.5"><span class="inline-flex min-h-6 items-center rounded-full bg-sky-100 px-2.5 text-[11px] font-black text-sky-800">@lang('Mindigo-dashboard::app.scheduled')</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </section>
@endsection
