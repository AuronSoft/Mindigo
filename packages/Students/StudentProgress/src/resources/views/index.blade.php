@extends('Mindigo-dashboard::layouts')
@section('title', __('student-progress::app.title') . ' · Mindigo LMS')
@section('meta_description', __('student-progress::app.subtitle'))

@section('styles')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
@php
    $cards = [
        ['label' => __('student-progress::app.card_assignments'), 'percent' => $progress['assignment']['rate'],
         'sub' => __('student-progress::app.of_count', ['done' => $progress['assignment']['done'], 'total' => $progress['assignment']['total']]),
         'icon' => 'heroicon-o-clipboard-document-check', 'tone' => 'bg-green-50 text-green-600', 'bar' => 'bg-green-500'],
        ['label' => __('student-progress::app.card_exams'), 'percent' => $progress['exam']['rate'],
         'sub' => __('student-progress::app.of_count', ['done' => $progress['exam']['done'], 'total' => $progress['exam']['total']]),
         'icon' => 'heroicon-o-document-text', 'tone' => 'bg-violet-50 text-violet-600', 'bar' => 'bg-violet-500'],
        ['label' => __('student-progress::app.card_avg'), 'percent' => $progress['avg_score'],
         'sub' => __('student-progress::app.scale_100'),
         'icon' => 'heroicon-o-academic-cap', 'tone' => 'bg-amber-50 text-amber-600', 'bar' => 'bg-amber-500'],
    ];
@endphp

<div class="flex min-h-screen flex-col bg-slate-50">

    {{-- Header --}}
    <header class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white/90 px-6 py-4 backdrop-blur">
        <div>
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">@lang('student-progress::app.area')</p>
            <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('student-progress::app.title')</h1>
            <p class="text-xs font-semibold text-slate-400">@lang('student-progress::app.subtitle')</p>
        </div>

        {{-- Filter --}}
        <form action="{{ route('student.progress.index') }}" method="GET" class="flex items-end gap-2">
            <select name="classroom_id" onchange="this.form.submit()"
                class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700 outline-none transition focus:border-green-300">
                <option value="">@lang('student-progress::app.all_classrooms')</option>
                @foreach($classrooms as $class)
                    <option value="{{ $class->id }}" {{ $classroomId == $class->id ? 'selected' : '' }}>{{ $class->name }} ({{ $class->code }})</option>
                @endforeach
            </select>
            @if($classroomId)
                <a href="{{ route('student.progress.index') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-500 no-underline hover:bg-slate-100" title="{{ __('student-progress::app.clear_filter') }}">
                    <x-heroicon-o-x-mark class="h-4 w-4" />
                </a>
            @endif
        </form>
    </header>

    <div class="flex flex-1 flex-col gap-5 p-6">

        {{-- Stat cards --}}
        <section class="grid grid-cols-3 gap-5 max-lg:grid-cols-1">
            @foreach($cards as $card)
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold text-slate-400">{{ $card['label'] }}</p>
                            <p class="mt-1 text-3xl font-black text-slate-800">{{ $card['percent'] }}%</p>
                        </div>
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $card['tone'] }}">
                            <x-dynamic-component :component="$card['icon']" class="h-5 w-5" />
                        </span>
                    </div>
                    <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full {{ $card['bar'] }}" style="width: {{ $card['percent'] }}%"></div>
                    </div>
                    <p class="mt-2 text-[11px] font-bold text-slate-400">{{ $card['sub'] }}</p>
                </div>
            @endforeach
        </section>

        <section class="grid grid-cols-[minmax(0,1fr)_22rem] gap-5 max-xl:grid-cols-1">

            {{-- Per classroom --}}
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                <h2 class="mb-4 text-base font-black text-slate-800">@lang('student-progress::app.per_classroom')</h2>
                @forelse($progress['per_classroom'] as $row)
                    <div class="border-t border-slate-100 py-4 first:border-t-0">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-black text-slate-700">{{ $row->name }}</p>
                                <p class="text-xs font-semibold text-slate-400">{{ $row->code }} · {{ $row->done }}/{{ $row->total }} @lang('student-progress::app.graded_unit')</p>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                @if(! is_null($row->avg_score))
                                    <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-black text-amber-600">{{ $row->avg_score }}/100</span>
                                @else
                                    <span class="text-xs font-bold text-slate-300">@lang('student-progress::app.no_score')</span>
                                @endif
                                <span class="w-10 text-right text-sm font-black text-slate-700">{{ $row->rate }}%</span>
                            </div>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-green-500" style="width: {{ $row->rate }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-10 text-center text-sm font-semibold text-slate-400">@lang('student-progress::app.empty_per_classroom')</p>
                @endforelse
            </div>

            {{-- Timeline --}}
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                <h2 class="text-base font-black text-slate-800">@lang('student-progress::app.timeline_title')</h2>
                <p class="mb-4 text-xs font-semibold text-slate-400">@lang('student-progress::app.timeline_desc')</p>
                <div class="h-56">
                    <canvas id="progressTimeline"></canvas>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.__progressTimeline = {
        labels: {{ Illuminate\Support\Js::from($progress['timeline']['labels']) }},
        data: {{ Illuminate\Support\Js::from($progress['timeline']['data']) }},
        legend: @json(__('student-progress::app.avg_score_legend')),
    };

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') return;
        const T = window.__progressTimeline;
        const el = document.getElementById('progressTimeline');
        if (!el) return;

        new Chart(el, {
            type: 'line',
            data: {
                labels: T.labels,
                datasets: [{
                    label: T.legend,
                    data: T.data,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34,197,94,0.12)',
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#22c55e',
                    pointRadius: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 100, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } },
                },
            },
        });
    });
</script>
@endsection
