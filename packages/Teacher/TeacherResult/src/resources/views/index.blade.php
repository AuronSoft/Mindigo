@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-result::app.title'))

@section('styles')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('scripts')
    @php
        $trendLabels = $overview['trend']->pluck('label')->toArray();
        $trendCounts = $overview['trend']->pluck('count')->toArray();
        $trendScores = $overview['trend']->pluck('avg_score')->toArray();
    @endphp
    <script>
        (function () {
            const labels = {{ Illuminate\Support\Js::from($trendLabels) }};
            const counts = {{ Illuminate\Support\Js::from($trendCounts) }};
            const scores = {{ Illuminate\Support\Js::from($trendScores) }};
            const common = {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { backgroundColor: '#0f172a', padding: 8, titleFont: { family: 'Be Vietnam Pro', weight: '800' }, bodyFont: { family: 'Be Vietnam Pro', weight: '700' } } },
                animation: { duration: 500 },
            };
            const elA = document.getElementById('trendAttempts');
            if (elA) new Chart(elA, {
                type: 'bar',
                data: { labels, datasets: [{ data: counts, backgroundColor: '#22c55e', borderRadius: 6, borderSkipped: false }] },
                options: { ...common, scales: { x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { weight: 700, size: 10 } } }, y: { border: { display: false }, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { weight: 700 }, precision: 0 }, beginAtZero: true } } },
            });
            const elS = document.getElementById('trendScore');
            if (elS) new Chart(elS, {
                type: 'line',
                data: { labels, datasets: [{ data: scores, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,.08)', borderWidth: 2, pointRadius: 3, pointBackgroundColor: '#3b82f6', tension: .38, fill: true, spanGaps: true }] },
                options: { ...common, scales: { x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { weight: 700, size: 10 } } }, y: { border: { display: false }, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { weight: 700 } }, min: 0, max: 100 } } },
            });

            // Tab switching
            const tabs = document.querySelectorAll('[data-result-tab]');
            const panes = document.querySelectorAll('[data-result-pane]');
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    tabs.forEach(t => {
                        t.classList.toggle('bg-white', t === tab);
                        t.classList.toggle('text-slate-900', t === tab);
                        t.classList.toggle('shadow-sm', t === tab);
                        t.classList.toggle('text-slate-500', t !== tab);
                    });
                    panes.forEach(p => p.classList.toggle('hidden', p.dataset.resultPane !== tab.dataset.resultTab));
                });
            });
        })();
    </script>
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">

    {{-- Compact header --}}
    <header class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white/95 px-6 py-3 backdrop-blur">
        <div>
            <h1 class="text-base font-black text-slate-950">@lang('teacher-result::app.title')</h1>
            <p class="text-xs font-bold text-slate-400">@lang('teacher-result::app.subtitle')</p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 focus-within:border-green-300 focus-within:bg-white focus-within:ring-1 focus-within:ring-green-100">
                <x-heroicon-o-magnifying-glass class="h-4 w-4 text-slate-400" />
                <input type="text" name="q" value="{{ $keyword }}"
                       placeholder="@lang('teacher-result::app.search_exams')"
                       class="w-44 bg-transparent text-sm font-bold text-slate-700 outline-none placeholder:text-slate-400">
            </div>
        </form>
    </header>

    <div class="grid flex-1 gap-0 xl:grid-cols-[14rem_minmax(0,1fr)]">

        {{-- Left sidebar: classrooms --}}
        <aside class="hidden border-r border-slate-200 bg-white xl:block">
            <div class="sticky top-[57px] max-h-[calc(100vh-57px)] overflow-y-auto p-4">
                <p class="mb-2 px-2 text-[10px] font-black uppercase tracking-widest text-slate-400">
                    @lang('teacher-classroom::app.title')
                </p>
                @forelse($classrooms as $cls)
                    <div class="mb-1 flex items-center justify-between gap-2 rounded-xl px-3 py-2.5 hover:bg-slate-50 transition cursor-default">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-black text-slate-800">{{ $cls->name }}</p>
                            <p class="text-[11px] font-bold text-slate-400">@lang('teacher-classroom::app.students_count_badge', ['count' => $cls->students_count]){{ $cls->school_year ? ' · ' . $cls->school_year : '' }}</p>
                        </div>
                        <span class="shrink-0 rounded-full {{ $cls->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-400' }} px-1.5 py-0.5 text-[9px] font-black">
                            @lang('teacher-classroom::app.' . $cls->status)
                        </span>
                    </div>
                @empty
                    <p class="px-2 py-4 text-xs font-bold text-slate-400">@lang('teacher-classroom::app.no_classrooms')</p>
                @endforelse
            </div>
        </aside>

        {{-- Main content --}}
        <main class="flex flex-col gap-5 p-5">

            {{-- 4 stat cards --}}
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['label' => __('teacher-result::app.total_attempts'),  'value' => number_format($overview['total_attempts']),              'color' => 'text-green-600',  'bg' => 'bg-green-50',  'icon' => 'heroicon-o-pencil-square'],
                    ['label' => __('teacher-result::app.pass_rate'),       'value' => $overview['pass_rate'] . '%',                            'color' => 'text-emerald-600','bg' => 'bg-emerald-50','icon' => 'heroicon-o-check-badge'],
                    ['label' => __('teacher-result::app.avg_score'),       'value' => $overview['avg_score'] . '%',                            'color' => 'text-sky-600',    'bg' => 'bg-sky-50',    'icon' => 'heroicon-o-chart-bar'],
                    ['label' => __('teacher-result::app.total_students'),  'value' => number_format($overview['total_students']),              'color' => 'text-violet-600', 'bg' => 'bg-violet-50', 'icon' => 'heroicon-o-academic-cap'],
                ] as $card)
                    <article class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-white px-4 py-3 shadow-sm">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $card['bg'] }} {{ $card['color'] }}">
                            <x-dynamic-component :component="$card['icon']" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $card['label'] }}</p>
                            <strong class="mt-0.5 block text-2xl font-black text-slate-950">{{ $card['value'] }}</strong>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Dual trend charts --}}
            <div class="grid gap-3 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <p class="text-xs font-black text-slate-700">@lang('teacher-result::app.trend_title')</p>
                        <span class="rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-black text-green-700">@lang('teacher-result::app.trend_attempts')</span>
                    </div>
                    <div class="h-36"><canvas id="trendAttempts"></canvas></div>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <p class="text-xs font-black text-slate-700">@lang('teacher-result::app.trend_title')</p>
                        <span class="rounded-full bg-sky-50 px-2 py-0.5 text-[10px] font-black text-sky-700">@lang('teacher-result::app.avg_score')</span>
                    </div>
                    <div class="h-36"><canvas id="trendScore"></canvas></div>
                </div>
            </div>

            {{-- Tabbed table --}}
            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                {{-- Tab bar --}}
                <div class="flex items-center gap-1 border-b border-slate-100 bg-slate-50 px-4 py-2">
                    <button data-result-tab="exams"
                            class="rounded-lg bg-white px-4 py-1.5 text-xs font-black text-slate-900 shadow-sm transition">
                        @lang('teacher-result::app.tab_exams')
                    </button>
                    <button data-result-tab="students"
                            class="rounded-lg px-4 py-1.5 text-xs font-black text-slate-500 transition hover:text-slate-700">
                        @lang('teacher-result::app.tab_students')
                    </button>
                </div>

                {{-- Pane: exams --}}
                <div data-result-pane="exams" class="overflow-x-auto">
                    @if($examResults->isEmpty())
                        <div class="flex flex-col items-center justify-center gap-3 py-16">
                            <x-heroicon-o-document-text class="h-12 w-12 text-slate-200" />
                            <p class="text-sm font-bold text-slate-400">@lang('teacher-result::app.no_exams')</p>
                        </div>
                    @else
                        <table class="w-full text-left">
                            <thead class="text-[11px] font-black uppercase tracking-wide text-slate-400">
                                <tr class="border-b border-slate-100">
                                    <th class="px-5 py-3">@lang('teacher-result::app.col_exam')</th>
                                    <th class="px-5 py-3">@lang('teacher-result::app.col_subject')</th>
                                    <th class="px-5 py-3">@lang('teacher-result::app.col_attempts')</th>
                                    <th class="px-5 py-3">@lang('teacher-result::app.col_avg')</th>
                                    <th class="px-5 py-3">@lang('teacher-result::app.col_pass_rate')</th>
                                    <th class="px-5 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($examResults as $r)
                                    <tr class="transition hover:bg-slate-50/80">
                                        <td class="px-5 py-3">
                                            <p class="text-sm font-black text-slate-900">{{ \Illuminate\Support\Str::limit($r['exam']->title, 36) }}</p>
                                        </td>
                                        <td class="px-5 py-3 text-sm font-bold text-slate-400">{{ $r['exam']->subject ?: '—' }}</td>
                                        <td class="px-5 py-3 text-sm font-black text-slate-700">{{ $r['attempts'] }}</td>
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-2">
                                                <div class="h-1.5 w-16 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-1.5 rounded-full bg-sky-500" style="width:{{ min(100,$r['avg_score']) }}%"></div>
                                                </div>
                                                <span class="text-sm font-black text-slate-700">{{ $r['avg_score'] }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3">
                                            @php $pr = $r['pass_rate']; @endphp
                                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-black {{ $pr >= 70 ? 'bg-green-100 text-green-700' : ($pr >= 40 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-600') }}">
                                                {{ $pr }}%
                                            </span>
                                        </td>
                                        <td class="px-5 py-3">
                                            <a href="{{ route('teacher.results.by_exam', $r['exam']) }}"
                                               class="text-xs font-black text-slate-400 no-underline hover:text-green-700">
                                                @lang('teacher-result::app.view_detail') →
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                {{-- Pane: students (hidden initially) --}}
                <div data-result-pane="students" class="hidden overflow-x-auto">
                    @if($studentResults->isEmpty())
                        <div class="flex flex-col items-center justify-center gap-3 py-16">
                            <x-heroicon-o-academic-cap class="h-12 w-12 text-slate-200" />
                            <p class="text-sm font-bold text-slate-400">@lang('teacher-result::app.no_students')</p>
                        </div>
                    @else
                        <table class="w-full text-left">
                            <thead class="text-[11px] font-black uppercase tracking-wide text-slate-400">
                                <tr class="border-b border-slate-100">
                                    <th class="px-5 py-3">#</th>
                                    <th class="px-5 py-3">@lang('teacher-result::app.col_student')</th>
                                    <th class="px-5 py-3">@lang('teacher-result::app.col_total')</th>
                                    <th class="px-5 py-3">@lang('teacher-result::app.col_avg')</th>
                                    <th class="px-5 py-3">@lang('teacher-result::app.pass_rate')</th>
                                    <th class="px-5 py-3">@lang('teacher-result::app.col_last_at')</th>
                                    <th class="px-5 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($studentResults as $i => $r)
                                    <tr class="transition hover:bg-slate-50/80">
                                        <td class="px-5 py-3 text-sm font-black text-slate-400">{{ $i + 1 }}</td>
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-2.5">
                                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-sky-100 text-xs font-black text-sky-700">
                                                    {{ mb_substr($r['student']->name, 0, 1) }}
                                                </span>
                                                <div>
                                                    <p class="text-sm font-black text-slate-900">{{ $r['student']->name }}</p>
                                                    <p class="text-xs font-bold text-slate-400">{{ $r['student']->email }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3 text-sm font-black text-slate-700">{{ $r['total'] }}</td>
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-2">
                                                <div class="h-1.5 w-16 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-1.5 rounded-full bg-green-500" style="width:{{ min(100,$r['avg_score']) }}%"></div>
                                                </div>
                                                <span class="text-sm font-black text-slate-700">{{ $r['avg_score'] }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3">
                                            <span class="rounded-full px-2.5 py-1 text-[11px] font-black {{ $r['pass_rate'] >= 60 ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                                {{ $r['pass_rate'] }}%
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-xs font-bold text-slate-400">
                                            {{ $r['last_at'] ? \Carbon\Carbon::parse($r['last_at'])->diffForHumans() : '—' }}
                                        </td>
                                        <td class="px-5 py-3">
                                            <a href="{{ route('teacher.results.by_student', $r['student']) }}"
                                               class="text-xs font-black text-slate-400 no-underline hover:text-green-700">
                                                @lang('teacher-result::app.view_detail') →
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

        </main>
    </div>
</div>
@endsection
