@extends('Mindigo-dashboard::layouts')

@section('title', $exam->title . ' — ' . __('teacher-result::app.exam_detail'))

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
            const el = document.getElementById('distChart');
            if (!el || typeof Chart === 'undefined') return;
            const d = {{ Illuminate\Support\Js::from(array_values($result['distribution'])) }};
            const l = {{ Illuminate\Support\Js::from(array_keys($result['distribution'])) }};
            new Chart(el, {
                type: 'bar',
                data: { labels: l, datasets: [{ data: d, backgroundColor: ['#fca5a5','#fcd34d','#86efac','#34d399','#16a34a'], borderRadius: 8, borderSkipped: false }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { weight: 800 } } }, y: { border: { display: false }, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { weight: 800 }, precision: 0 }, beginAtZero: true } } },
            });
        })();
    </script>
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    <header class="sticky top-0 z-10 flex items-center gap-3 border-b border-slate-200 bg-white/95 px-6 py-3 backdrop-blur">
        <a href="{{ route('teacher.results.index', array_filter(['classroom_id' => $selectedClassroom?->id])) }}" class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline transition hover:bg-green-50 hover:text-green-700">
            <x-heroicon-o-arrow-left class="h-4 w-4" />
        </a>
        <div>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">@lang('teacher-result::app.exam_detail')</p>
            <h1 class="text-base font-black text-slate-950">{{ $exam->title }}</h1>
        </div>
    </header>

    <div class="grid gap-4 p-5 lg:grid-cols-[minmax(0,0.35fr)_minmax(0,0.65fr)]">

        {{-- Left: stats + distribution --}}
        <div class="space-y-4">
            {{-- Summary --}}
            <div class="grid grid-cols-2 gap-3">
                @foreach([
                    [__('teacher-result::app.total_candidates'), number_format($result['total']),   'bg-slate-900 text-white'],
                    [__('teacher-result::app.pass_rate'),        $result['pass_rate'] . '%',        'bg-green-600 text-white'],
                    [__('teacher-result::app.passed'),           number_format($result['passed']),  'bg-emerald-50 text-emerald-800'],
                    [__('teacher-result::app.avg_score'),        $result['avg_score'] . '/10',        'bg-sky-50 text-sky-800'],
                ] as $card)
                    <article class="rounded-2xl {{ $card[2] }} p-3 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-wider opacity-60">{{ $card[0] }}</p>
                        <strong class="mt-1 block text-2xl font-black">{{ $card[1] }}</strong>
                    </article>
                @endforeach
            </div>

            {{-- Score distribution chart --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="mb-3 text-xs font-black text-slate-700">@lang('teacher-result::app.score_dist')</p>
                <div class="h-44"><canvas id="distChart"></canvas></div>
            </div>
        </div>

        {{-- Right: candidate list --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-3.5">
                <p class="text-sm font-black text-slate-950">@lang('teacher-result::app.candidate_list')</p>
                <p class="mt-0.5 text-xs font-bold text-slate-400">{{ $result['total'] }} @lang('teacher-result::app.trend_attempts')</p>
            </div>
            @if($result['list']->isEmpty())
                <div class="flex flex-col items-center justify-center gap-3 py-16">
                    <x-heroicon-o-inbox class="h-12 w-12 text-slate-200" />
                    <p class="text-sm font-bold text-slate-400">@lang('teacher-result::app.no_attempts')</p>
                </div>
            @else
                <div class="max-h-[28rem] overflow-y-auto">
                    <table class="w-full text-left">
                        <thead class="sticky top-0 bg-slate-50 text-[11px] font-black uppercase tracking-wide text-slate-400">
                            <tr>
                                <th class="px-5 py-3">#</th>
                                <th class="px-5 py-3">@lang('teacher-result::app.col_student')</th>
                                <th class="px-5 py-3">@lang('teacher-result::app.col_score_pct')</th>
                                <th class="px-5 py-3">@lang('teacher-result::app.col_result')</th>
                                <th class="px-5 py-3">@lang('teacher-result::app.col_submitted')</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($result['list'] as $i => $a)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-5 py-3 text-sm font-black text-slate-400">{{ $i + 1 }}</td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-slate-100 text-[10px] font-black text-slate-600">{{ mb_substr($a->user?->name ?? '?', 0, 1) }}</span>
                                            <a href="{{ route('teacher.results.by_student', array_filter(['user' => $a->user_id, 'classroom_id' => $selectedClassroom?->id])) }}" class="text-sm font-black text-slate-900 no-underline hover:text-green-700">{{ $a->user?->name ?? '—' }}</a>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="h-1.5 w-14 overflow-hidden rounded-full bg-slate-100">
                                                <div class="h-1.5 rounded-full bg-green-500" style="width:{{ min(100,$a->percentage) }}%"></div>
                                            </div>
                                            <span class="text-sm font-black text-slate-700">{{ round($a->percentage / 10, 1) }}/10</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3">
                                        @if($a->passed)
                                            <span class="rounded-full bg-green-100 px-2 py-0.5 text-[11px] font-black text-green-800">@lang('teacher-result::app.passed')</span>
                                        @else
                                            <span class="rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-black text-red-700">@lang('teacher-result::app.failed')</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-xs font-bold text-slate-400">{{ $a->submitted_at?->diffForHumans() }}</td>
                                    <td class="px-5 py-3">
                                    @if($a->answers->where('needs_review', true)->count() > 0)
                                        <a href="{{ route('teacher.results.review_attempt', $a) }}"
                                        class="inline-flex items-center gap-1 rounded-xl bg-amber-50 px-3 py-1.5 text-xs font-black text-amber-700 no-underline transition hover:bg-amber-100">
                                            <x-heroicon-o-pencil-square class="h-3.5 w-3.5" />
                                            Chấm bài
                                        </a>
                                    @else
                                        <span class="text-xs font-bold text-slate-300">Đã chấm</span>
                                    @endif
                                </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
