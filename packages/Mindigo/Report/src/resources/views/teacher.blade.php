@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-report::app.teacher_reports'))

@section('styles')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('scripts')
    <script>
        (function () {
            if (!window.Chart) return;

            const trend = {{ Illuminate\Support\Js::from($report['trend']) }};
            const distribution = {{ Illuminate\Support\Js::from($report['score_distribution']) }};
            const common = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 10,
                        titleFont: { family: 'Be Vietnam Pro', weight: '800' },
                        bodyFont: { family: 'Be Vietnam Pro', weight: '700' }
                    }
                },
                animation: { duration: 450 }
            };

            const trendEl = document.getElementById('teacherReportTrend');
            if (trendEl) {
                new Chart(trendEl, {
                    type: 'line',
                    data: {
                        labels: trend.labels,
                        datasets: [
                            {
                                type: 'bar',
                                data: trend.counts,
                                backgroundColor: '#22c55e',
                                borderRadius: 7,
                                borderSkipped: false,
                                yAxisID: 'y'
                            },
                            {
                                type: 'line',
                                data: trend.scores,
                                borderColor: '#2563eb',
                                backgroundColor: 'rgba(37, 99, 235, .08)',
                                borderWidth: 2,
                                pointRadius: 3,
                                pointBackgroundColor: '#2563eb',
                                tension: .35,
                                spanGaps: true,
                                yAxisID: 'score'
                            }
                        ]
                    },
                    options: {
                        ...common,
                        scales: {
                            x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10, weight: 700 } } },
                            y: { beginAtZero: true, border: { display: false }, grid: { color: '#f1f5f9' }, ticks: { precision: 0, color: '#94a3b8', font: { weight: 700 } } },
                            score: { position: 'right', min: 0, max: 10, border: { display: false }, grid: { display: false }, ticks: { color: '#94a3b8', font: { weight: 700 } } }
                        }
                    }
                });
            }

            const distEl = document.getElementById('teacherScoreDistribution');
            if (distEl) {
                new Chart(distEl, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(distribution),
                        datasets: [{ data: Object.values(distribution), backgroundColor: ['#ef4444', '#f59e0b', '#0ea5e9', '#22c55e', '#16a34a'], borderRadius: 8, borderSkipped: false }]
                    },
                    options: {
                        ...common,
                        scales: {
                            x: { grid: { display: false }, ticks: { color: '#64748b', font: { weight: 800 } } },
                            y: { beginAtZero: true, border: { display: false }, grid: { color: '#f1f5f9' }, ticks: { precision: 0, color: '#94a3b8', font: { weight: 700 } } }
                        }
                    }
                });
            }
        })();
    </script>
@endsection

@section('content')
@php
    $summary = $report['summary'];
    $queryBase = array_filter(['classroom_id' => $selectedClassroom?->id]);
    $statCards = [
        ['label' => __('Mindigo-report::app.total_students'), 'value' => number_format($summary['total_students']), 'hint' => __('Mindigo-report::app.managed_students'), 'icon' => 'heroicon-o-user-group', 'class' => 'bg-green-50 text-green-600'],
        ['label' => __('Mindigo-report::app.total_exams'), 'value' => number_format($summary['total_exams']), 'hint' => __('Mindigo-report::app.teacher_exam_scope'), 'icon' => 'heroicon-o-document-text', 'class' => 'bg-sky-50 text-sky-600'],
        ['label' => __('Mindigo-report::app.avg_score'), 'value' => number_format($summary['avg_score'], 1), 'hint' => __('Mindigo-report::app.score_scale'), 'icon' => 'heroicon-o-chart-bar', 'class' => 'bg-violet-50 text-violet-600'],
        ['label' => __('Mindigo-report::app.pass_rate'), 'value' => number_format($summary['pass_rate'], 1) . '%', 'hint' => number_format($summary['total_attempts']) . ' ' . __('Mindigo-report::app.attempts'), 'icon' => 'heroicon-o-check-badge', 'class' => 'bg-emerald-50 text-emerald-600'],
        ['label' => __('Mindigo-report::app.total_assignments'), 'value' => number_format($summary['total_assignments']), 'hint' => __('Mindigo-report::app.assignment_scope'), 'icon' => 'heroicon-o-clipboard-document-list', 'class' => 'bg-amber-50 text-amber-600'],
        ['label' => __('Mindigo-report::app.submission_rate'), 'value' => number_format($summary['submission_rate'], 1) . '%', 'hint' => number_format($summary['graded_submissions']) . ' ' . __('Mindigo-report::app.graded_submissions'), 'icon' => 'heroicon-o-inbox-stack', 'class' => 'bg-slate-100 text-slate-600'],
    ];
@endphp

<div class="min-h-screen bg-slate-50">
    <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 px-6 py-3 backdrop-blur max-md:px-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-[11px] font-black uppercase tracking-wider text-green-600">@lang('Mindigo-report::app.teacher_report_scope')</p>
                <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('Mindigo-report::app.teacher_reports')</h1>
                <p class="text-xs font-bold text-slate-400">@lang('Mindigo-report::app.teacher_reports_desc')</p>
            </div>

            <form method="GET" class="flex flex-wrap items-center gap-2">
                <select name="classroom_id" class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-xs font-extrabold text-slate-700 outline-none transition focus:border-green-300 focus:ring-2 focus:ring-green-100" onchange="this.form.submit()">
                    <option value="">@lang('Mindigo-report::app.all_classrooms')</option>
                    @foreach($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" @selected($selectedClassroom?->id === $classroom->id)>{{ $classroom->name }}</option>
                    @endforeach
                </select>

                <div class="flex h-10 items-center rounded-xl border border-slate-200 bg-slate-100 p-1">
                    @foreach([7, 30, 90] as $days)
                        <a href="{{ route('teacher.reports.index', array_merge($queryBase, ['period' => $days])) }}"
                           class="inline-flex h-8 items-center rounded-lg px-3 text-xs font-black no-underline transition {{ $period === $days ? 'bg-white text-green-700 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                            {{ $days }} @lang('Mindigo-report::app.days')
                        </a>
                    @endforeach
                </div>
            </form>
        </div>
    </header>

    <main class="space-y-5 px-6 py-5 max-md:px-4">
        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
            @foreach($statCards as $card)
                <article class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-[11px] font-black uppercase tracking-wide text-slate-400">{{ $card['label'] }}</p>
                            <p class="mt-2 text-2xl font-black text-slate-950">{{ $card['value'] }}</p>
                            <p class="mt-1 truncate text-xs font-bold text-slate-400">{{ $card['hint'] }}</p>
                        </div>
                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl {{ $card['class'] }}">
                            <x-dynamic-component :component="$card['icon']" class="h-5 w-5" />
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="grid gap-5 xl:grid-cols-[minmax(0,1.45fr)_minmax(22rem,.55fr)]">
            <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-black text-slate-950">@lang('Mindigo-report::app.learning_trend')</h2>
                        <p class="text-xs font-bold text-slate-400">@lang('Mindigo-report::app.learning_trend_desc')</p>
                    </div>
                    <a href="{{ route('teacher.results.index', array_filter(['classroom_id' => $selectedClassroom?->id])) }}" class="inline-flex h-9 items-center gap-2 rounded-xl bg-green-600 px-4 text-xs font-black text-white no-underline shadow-sm transition hover:bg-green-500">
                        <x-heroicon-o-check-badge class="h-4 w-4" />
                        @lang('Mindigo-report::app.open_results')
                    </a>
                </div>
                <div class="h-72">
                    <canvas id="teacherReportTrend"></canvas>
                </div>
            </article>

            <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-black text-slate-950">@lang('Mindigo-report::app.score_distribution')</h2>
                <p class="text-xs font-bold text-slate-400">@lang('Mindigo-report::app.score_distribution_desc')</p>
                <div class="mt-4 h-72">
                    <canvas id="teacherScoreDistribution"></canvas>
                </div>
            </article>
        </section>

        <section class="grid gap-5 xl:grid-cols-2">
            <article class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-sm font-black text-slate-950">@lang('Mindigo-report::app.classroom_performance')</h2>
                    <p class="text-xs font-bold text-slate-400">@lang('Mindigo-report::app.classroom_performance_desc')</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wide text-slate-400">
                            <tr>
                                <th class="px-5 py-3">@lang('Mindigo-report::app.classroom')</th>
                                <th class="px-5 py-3 text-right">@lang('Mindigo-report::app.students')</th>
                                <th class="px-5 py-3 text-right">@lang('Mindigo-report::app.attempts')</th>
                                <th class="px-5 py-3 text-right">@lang('Mindigo-report::app.avg_score_short')</th>
                                <th class="px-5 py-3 text-right">@lang('Mindigo-report::app.pass_rate')</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($report['classrooms'] as $row)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-5 py-4">
                                        <a href="{{ route('teacher.reports.index', ['classroom_id' => $row['classroom']->id, 'period' => $period]) }}" class="font-black text-slate-900 no-underline hover:text-green-700">{{ $row['classroom']->name }}</a>
                                        <p class="mt-0.5 text-xs font-bold text-slate-400">{{ number_format($row['assignments']) }} @lang('Mindigo-report::app.assignments') · {{ number_format($row['submissions']) }} @lang('Mindigo-report::app.submissions')</p>
                                    </td>
                                    <td class="px-5 py-4 text-right font-extrabold text-slate-700">{{ number_format($row['students']) }}</td>
                                    <td class="px-5 py-4 text-right font-extrabold text-slate-700">{{ number_format($row['attempts']) }}</td>
                                    <td class="px-5 py-4 text-right font-black text-slate-950">{{ number_format($row['avg_score'], 1) }}</td>
                                    <td class="px-5 py-4 text-right font-black text-green-700">{{ number_format($row['pass_rate'], 1) }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-10 text-center text-sm font-bold text-slate-400">@lang('Mindigo-report::app.no_data')</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-sm font-black text-slate-950">@lang('Mindigo-report::app.exam_performance')</h2>
                    <p class="text-xs font-bold text-slate-400">@lang('Mindigo-report::app.exam_performance_desc')</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wide text-slate-400">
                            <tr>
                                <th class="px-5 py-3">@lang('Mindigo-report::app.exam_name')</th>
                                <th class="px-5 py-3 text-right">@lang('Mindigo-report::app.attempts')</th>
                                <th class="px-5 py-3 text-right">@lang('Mindigo-report::app.avg_score_short')</th>
                                <th class="px-5 py-3 text-right">@lang('Mindigo-report::app.pass_rate')</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($report['exams'] as $row)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-5 py-4">
                                        <a href="{{ route('teacher.results.by_exam', array_filter(['exam' => $row['exam'], 'classroom_id' => $selectedClassroom?->id])) }}" class="font-black text-slate-900 no-underline hover:text-green-700">{{ $row['exam']->title }}</a>
                                        <p class="mt-0.5 text-xs font-bold text-slate-400">{{ $row['exam']->subject ?: __('Mindigo-report::app.no_subject') }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-right font-extrabold text-slate-700">{{ number_format($row['attempts']) }}</td>
                                    <td class="px-5 py-4 text-right font-black text-slate-950">{{ number_format($row['avg_score'], 1) }}</td>
                                    <td class="px-5 py-4 text-right font-black text-green-700">{{ number_format($row['pass_rate'], 1) }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-10 text-center text-sm font-bold text-slate-400">@lang('Mindigo-report::app.no_data')</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-black text-slate-950">@lang('Mindigo-report::app.students_need_attention')</h2>
                <p class="text-xs font-bold text-slate-400">@lang('Mindigo-report::app.students_need_attention_desc')</p>
            </div>
            <div class="grid divide-y divide-slate-100 md:grid-cols-2 md:divide-x md:divide-y-0 xl:grid-cols-4">
                @forelse($report['students'] as $row)
                    <a href="{{ route('teacher.results.by_student', array_filter(['user' => $row['student'], 'classroom_id' => $selectedClassroom?->id])) }}" class="block p-5 no-underline transition hover:bg-slate-50">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-black text-slate-950">{{ $row['student']->name }}</p>
                                <p class="truncate text-xs font-bold text-slate-400">{{ $row['student']->email }}</p>
                            </div>
                            <span class="rounded-full {{ $row['attempts'] === 0 ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600' }} px-2.5 py-1 text-[11px] font-black">
                                {{ $row['attempts'] === 0 ? __('Mindigo-report::app.no_attempts') : number_format($row['avg_score'], 1) }}
                            </span>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-[11px] font-black uppercase text-slate-400">@lang('Mindigo-report::app.attempts')</p>
                                <p class="mt-1 text-lg font-black text-slate-950">{{ number_format($row['attempts']) }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-[11px] font-black uppercase text-slate-400">@lang('Mindigo-report::app.pass_rate')</p>
                                <p class="mt-1 text-lg font-black text-slate-950">{{ number_format($row['pass_rate'], 1) }}%</p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full px-5 py-10 text-center text-sm font-bold text-slate-400">@lang('Mindigo-report::app.no_data')</div>
                @endforelse
            </div>
        </section>
    </main>
</div>
@endsection
