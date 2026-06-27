@extends('Mindigo-dashboard::layouts')

@section('title', __('student-dashboard::app.meta_title'))
@section('meta_description', __('student-dashboard::app.meta_description'))

@section('styles')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
@php
    $statCards = [
        [
            'label'   => __('student-dashboard::app.card_assignments'),
            'percent' => $stats['assignments']['percent'],
            'sub'     => __('student-dashboard::app.of_count', ['done' => $stats['assignments']['done'], 'total' => $stats['assignments']['total'], 'unit' => __('student-dashboard::app.unit_assignments')]),
            'icon'    => 'M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11',
        ],
        [
            'label'   => __('student-dashboard::app.card_exams'),
            'percent' => $stats['exams']['percent'],
            'sub'     => __('student-dashboard::app.of_count', ['done' => $stats['exams']['done'], 'total' => $stats['exams']['total'], 'unit' => __('student-dashboard::app.unit_exams')]),
            'icon'    => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M8 13h8M8 17h5',
        ],
        [
            'label'   => __('student-dashboard::app.card_weekly'),
            'percent' => $stats['weekly']['percent'],
            'sub'     => __('student-dashboard::app.this_week'),
            'icon'    => 'M3 3v18h18M7 15l4-4 3 3 5-7',
        ],
        [
            'label'   => __('student-dashboard::app.card_avg'),
            'percent' => $stats['avg_score']['percent'],
            'sub'     => __('student-dashboard::app.scale_100'),
            'icon'    => 'M12 2a10 10 0 1 0 10 10h-10z M12 2v10l8.66 5',
        ],
    ];
@endphp

<div class="flex flex-col gap-5 p-6 max-md:p-4">

    {{-- ===== ROW 1 — stat cards ===== --}}
    <section class="grid grid-cols-4 gap-5 max-lg:grid-cols-2 max-sm:grid-cols-1">
        @foreach($statCards as $card)
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400">{{ $card['label'] }}</p>
                        <p class="mt-1 text-3xl font-black text-slate-800">{{ $card['percent'] }}%</p>
                    </div>
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-green-50 text-green-600">
                        <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $card['icon'] }}"/></svg>
                    </span>
                </div>
                <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-green-500" style="width: {{ $card['percent'] }}%"></div>
                </div>
                <p class="mt-2 text-[11px] font-bold text-slate-400">{{ $card['sub'] }}</p>
            </div>
        @endforeach
    </section>

    {{-- ===== ROW 2 — schedule | active tasks | donut ===== --}}
    <section class="grid grid-cols-3 gap-5 max-xl:grid-cols-1">

        {{-- Week strip + nearest events --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-base font-black text-slate-800">{{ now()->translatedFormat('F Y') }}</h2>
            </div>
            <div class="flex items-center justify-between gap-1">
                @foreach($weekStrip as $d)
                    <div class="grid flex-1 place-items-center gap-1 rounded-xl py-2 {{ $d['is_today'] ? 'bg-green-500 text-white' : 'text-slate-600' }}">
                        <span class="text-base font-black">{{ $d['day'] }}</span>
                        <span class="text-[10px] font-bold uppercase {{ $d['is_today'] ? 'text-green-50' : 'text-slate-400' }}">{{ $d['label'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex flex-col gap-3">
                @forelse($activeTasks->take(2) as $task)
                    <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-3">
                        <span class="text-sm font-black text-slate-700">{{ $task->at?->format('H:i') }}</span>
                        <div class="min-w-0 flex-1 border-l border-slate-100 pl-3">
                            <p class="truncate text-sm font-extrabold text-slate-800">{{ $task->title }}</p>
                            <p class="truncate text-xs font-semibold text-slate-400">{{ $task->status }}</p>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm font-semibold text-slate-400">{{ __('student-dashboard::app.empty_tasks') }}</p>
                @endforelse
            </div>
        </div>

        {{-- Active tasks --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-base font-black text-slate-800">{{ __('student-dashboard::app.active_tasks') }}</h2>
                @if(Route::has('student.assignments.index'))
                    <a href="{{ route('student.assignments.index') }}" class="text-xs font-extrabold text-green-600 no-underline hover:text-green-700">{{ __('student-dashboard::app.see_all') }}</a>
                @endif
            </div>
            <div class="flex flex-col gap-3">
                @forelse($activeTasks as $task)
                    @php $isExam = $task->type === 'exam'; @endphp
                    <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-white text-green-600 ring-1 ring-slate-100">
                            @if($isExam)
                                <svg viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current stroke-2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6"/></svg>
                            @else
                                <svg viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current stroke-2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-extrabold text-slate-800">{{ $task->title }}</p>
                            <p class="truncate text-xs font-semibold text-slate-400">{{ $task->status }}</p>
                        </div>
                        <span class="shrink-0 text-xs font-black text-green-600">{{ $task->time_left }}</span>
                    </div>
                @empty
                    <p class="py-10 text-center text-sm font-semibold text-slate-400">{{ __('student-dashboard::app.empty_tasks') }}</p>
                @endforelse
            </div>
        </div>

        {{-- Course statistics donut --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
            <h2 class="mb-4 text-base font-black text-slate-800">{{ __('student-dashboard::app.course_stats') }}</h2>
            @if($courseStats['total'] > 0)
                <div class="relative mx-auto h-40 w-40">
                    <canvas id="courseDonut"></canvas>
                </div>
                <div class="mt-4 flex flex-col gap-2 text-xs font-bold">
                    <div class="flex items-center justify-between"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>{{ __('student-dashboard::app.legend_completed') }}</span><span class="text-slate-500">{{ $courseStats['pct_completed'] }}%</span></div>
                    <div class="flex items-center justify-between"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>{{ __('student-dashboard::app.legend_in_progress') }}</span><span class="text-slate-500">{{ $courseStats['pct_in_progress'] }}%</span></div>
                    <div class="flex items-center justify-between"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>{{ __('student-dashboard::app.legend_incomplete') }}</span><span class="text-slate-500">{{ $courseStats['pct_incomplete'] }}%</span></div>
                </div>
            @else
                <p class="py-16 text-center text-sm font-semibold text-slate-400">{{ __('student-dashboard::app.empty_course_stats') }}</p>
            @endif
        </div>
    </section>

    {{-- ===== ROW 3 — weekly activity | recent activity ===== --}}
    <section class="grid grid-cols-[minmax(0,1fr)_22rem] gap-5 max-xl:grid-cols-1">

        {{-- Weekly activity bar chart --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
            <h2 class="mb-4 text-base font-black text-slate-800">{{ __('student-dashboard::app.weekly_activity') }}</h2>
            <div class="h-56">
                <canvas id="activityBar"></canvas>
            </div>
        </div>

        {{-- Recent activity --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-base font-black text-slate-800">{{ __('student-dashboard::app.recent_activity') }}</h2>
                @if(Route::has('student.history.index'))
                    <a href="{{ route('student.history.index') }}" class="text-xs font-extrabold text-green-600 no-underline hover:text-green-700">{{ __('student-dashboard::app.see_all') }}</a>
                @endif
            </div>
            <div class="flex flex-col gap-3">
                @forelse($recentActivity as $item)
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-green-50 text-green-600">
                            <svg viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current stroke-2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-extrabold text-slate-800">{{ $item->action }}</p>
                            <p class="truncate text-xs font-semibold text-slate-400">{{ $item->text }}</p>
                        </div>
                        <span class="shrink-0 text-[11px] font-bold text-slate-400">{{ $item->at?->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="py-10 text-center text-sm font-semibold text-slate-400">{{ __('student-dashboard::app.empty_activity') }}</p>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
    window.__studentCharts = {
        course: {
            labels: [
                @json(__('student-dashboard::app.legend_completed')),
                @json(__('student-dashboard::app.legend_in_progress')),
                @json(__('student-dashboard::app.legend_incomplete')),
            ],
            data: [{{ $courseStats['completed'] }}, {{ $courseStats['in_progress'] }}, {{ $courseStats['incomplete'] }}],
        },
        weekly: {
            labels: {{ Illuminate\Support\Js::from([__('student-dashboard::app.d_mon'),__('student-dashboard::app.d_tue'),__('student-dashboard::app.d_wed'),__('student-dashboard::app.d_thu'),__('student-dashboard::app.d_fri'),__('student-dashboard::app.d_sat'),__('student-dashboard::app.d_sun')]) }},
            data: {{ Illuminate\Support\Js::from($weeklyActivity) }},
        },
    };

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') return;
        const C = window.__studentCharts;

        const donut = document.getElementById('courseDonut');
        if (donut) {
            new Chart(donut, {
                type: 'doughnut',
                data: {
                    labels: C.course.labels,
                    datasets: [{
                        data: C.course.data,
                        backgroundColor: ['#22c55e', '#fbbf24', '#e2e8f0'],
                        borderWidth: 0,
                    }],
                },
                options: {
                    cutout: '70%',
                    plugins: { legend: { display: false } },
                    responsive: true,
                    maintainAspectRatio: false,
                },
            });
        }

        const bar = document.getElementById('activityBar');
        if (bar) {
            new Chart(bar, {
                type: 'bar',
                data: {
                    labels: C.weekly.labels,
                    datasets: [{
                        data: C.weekly.data,
                        backgroundColor: '#22c55e',
                        borderRadius: 8,
                        maxBarThickness: 28,
                    }],
                },
                options: {
                    plugins: { legend: { display: false } },
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                        x: { grid: { display: false } },
                    },
                },
            });
        }
    });
</script>
@endsection
