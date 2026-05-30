@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-report::app.title'))

@section('styles')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    @vite([
        'packages/Mindigo/Report/src/resources/css/app.css',
        'packages/Mindigo/Report/src/resources/js/app.js',
    ])
@endsection

@section('scripts')
    @php
        $trendLabels = $trend['labels'] ?? [];
        $trendCounts = $trend['counts'] ?? [];
        $distLabels = array_keys($scoreDistribution);
        $distData = array_values($scoreDistribution);
        $subjectLabels = $subjectBreakdown->pluck('subject')->toArray();
        $subjectData = $subjectBreakdown->pluck('attempt_count')->toArray();
    @endphp
    <script>
        window.__reportTrend = {
            labels: {{ Illuminate\Support\Js::from($trendLabels) }},
            counts: {{ Illuminate\Support\Js::from($trendCounts) }},
        };
        window.__reportScoreDist = {
            labels: {{ Illuminate\Support\Js::from($distLabels) }},
            data: {{ Illuminate\Support\Js::from($distData) }},
        };
        window.__reportSubjects = {
            labels: {{ Illuminate\Support\Js::from($subjectLabels) }},
            data: {{ Illuminate\Support\Js::from($subjectData) }},
        };
    </script>
@endsection

@section('content')
    <section class="min-h-screen bg-[#f7faf7]">
        <header class="flex min-h-[4.25rem] items-center justify-between gap-4 bg-[#f7faf7] px-5 py-3 max-md:px-4">
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">@lang('Mindigo-report::app.title')</p>
                <h1 class="mt-0.5 text-xl font-black text-slate-950">@lang('Mindigo-report::app.overview')</h1>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('reports.exams') }}" class="inline-flex h-9 items-center gap-2 rounded-full bg-white px-4 text-xs font-black text-slate-700 ring-1 ring-slate-200 transition hover:bg-green-50 hover:text-green-700 no-underline">
                    @lang('Mindigo-report::app.exam_reports')
                </a>
                <a href="{{ route('reports.students') }}" class="inline-flex h-9 items-center gap-2 rounded-full bg-green-600 px-4 text-xs font-black text-white no-underline transition hover:bg-green-500">
                    @lang('Mindigo-report::app.student_reports')
                </a>
            </div>
        </header>

        <div class="grid gap-4 px-5 pb-8 max-md:px-4">

            {{-- Overview stat cards --}}
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['label' => __('Mindigo-report::app.total_attempts'), 'value' => number_format($overview['total_attempts']), 'sub' => ($overview['growth'] >= 0 ? '+' : '') . $overview['growth'] . '% ' . __('Mindigo-report::app.growth'), 'tone' => 'bg-green-600', 'icon' => 'heroicon-o-document-text'],
                    ['label' => __('Mindigo-report::app.pass_rate'), 'value' => $overview['pass_rate'] . '%', 'sub' => number_format($overview['passed_attempts']) . ' / ' . number_format($overview['total_attempts']), 'tone' => 'bg-emerald-600', 'icon' => 'heroicon-o-check-badge'],
                    ['label' => __('Mindigo-report::app.avg_score'), 'value' => $overview['avg_score'] . '%', 'sub' => number_format($overview['total_exams']) . ' ' . __('Mindigo-report::app.total_exams'), 'tone' => 'bg-sky-600', 'icon' => 'heroicon-o-chart-bar'],
                    ['label' => __('Mindigo-report::app.total_students'), 'value' => number_format($overview['total_students']), 'sub' => number_format($overview['active_students']) . ' ' . __('Mindigo-report::app.active_students'), 'tone' => 'bg-amber-500', 'icon' => 'heroicon-o-academic-cap'],
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

            {{-- Trend + Score Distribution --}}
            <div class="grid gap-4 xl:grid-cols-[minmax(0,1.4fr)_minmax(0,0.6fr)]">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-slate-400">@lang('Mindigo-report::app.attempt_trend')</p>
                        </div>
                        <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-black text-green-700 ring-1 ring-green-100">30 ngày</span>
                    </div>
                    <div class="h-56"><canvas id="trendChart"></canvas></div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="mb-4 text-xs font-black uppercase tracking-wider text-slate-400">@lang('Mindigo-report::app.score_distribution')</p>
                    <div class="h-56"><canvas id="scoreDistChart"></canvas></div>
                </div>
            </div>

            {{-- Subject Breakdown + Top Exams --}}
            <div class="grid gap-4 xl:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="mb-4 text-xs font-black uppercase tracking-wider text-slate-400">@lang('Mindigo-report::app.subject_breakdown')</p>
                    <div class="h-64"><canvas id="subjectChart"></canvas></div>
                    @if($subjectBreakdown->isEmpty())
                        <p class="py-4 text-center text-sm font-bold text-slate-400">@lang('Mindigo-report::app.no_data')</p>
                    @endif
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-xs font-black uppercase tracking-wider text-slate-400">@lang('Mindigo-report::app.top_exams')</p>
                        <a href="{{ route('reports.exams') }}" class="text-xs font-black text-green-700 no-underline hover:underline">@lang('Mindigo-report::app.view_all')</a>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($topExams as $i => $exam)
                            <div class="flex items-center gap-3 py-2.5">
                                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-slate-100 text-xs font-black text-slate-500">{{ $i + 1 }}</span>
                                <span class="min-w-0 flex-1 truncate text-sm font-black text-slate-800">{{ \Illuminate\Support\Str::limit($exam->title, 32) }}</span>
                                <span class="shrink-0 text-xs font-bold text-slate-400">{{ number_format($exam->attempts_count) }} lượt</span>
                                <span class="shrink-0 rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-black text-green-700">{{ round($exam->attempts_avg_percentage ?? 0, 1) }}%</span>
                                <a href="{{ route('reports.exam.detail', $exam) }}" class="shrink-0 text-slate-400 no-underline hover:text-green-600">
                                    <x-heroicon-o-chevron-right class="h-4 w-4" />
                                </a>
                            </div>
                        @empty
                            <p class="py-6 text-center text-sm font-bold text-slate-400">@lang('Mindigo-report::app.no_data')</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Top Students --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">@lang('Mindigo-report::app.top_students')</p>
                    <a href="{{ route('reports.students') }}" class="text-xs font-black text-green-700 no-underline hover:underline">@lang('Mindigo-report::app.view_all')</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[600px] text-left">
                        <thead class="text-[11px] font-black uppercase text-slate-400">
                            <tr>
                                <th class="pb-3 pr-4">#</th>
                                <th class="pb-3 pr-4">@lang('Mindigo-report::app.name')</th>
                                <th class="pb-3 pr-4">@lang('Mindigo-report::app.email')</th>
                                <th class="pb-3 pr-4">@lang('Mindigo-report::app.attempts')</th>
                                <th class="pb-3 pr-4">@lang('Mindigo-report::app.avg_score')</th>
                                <th class="pb-3 pr-4">@lang('Mindigo-report::app.pass_rate')</th>
                                <th class="pb-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse($topStudents as $i => $student)
                                <tr>
                                    <td class="py-3 pr-4 font-black text-slate-400">{{ $i + 1 }}</td>
                                    <td class="py-3 pr-4 font-black text-slate-900">{{ $student->name }}</td>
                                    <td class="py-3 pr-4 font-bold text-slate-500">{{ $student->email }}</td>
                                    <td class="py-3 pr-4 font-black text-slate-700">{{ number_format($student->attempt_count) }}</td>
                                    <td class="py-3 pr-4">
                                        <span class="rounded-full bg-green-50 px-2.5 py-1 text-[11px] font-black text-green-700">{{ $student->avg_score }}%</span>
                                    </td>
                                    <td class="py-3 pr-4">
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-black text-emerald-700">{{ $student->pass_rate }}%</span>
                                    </td>
                                    <td class="py-3">
                                        <a href="{{ route('reports.student.detail', $student->id) }}" class="text-xs font-black text-slate-400 no-underline hover:text-green-600">@lang('Mindigo-report::app.view_detail')</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="py-8 text-center font-bold text-slate-400">@lang('Mindigo-report::app.no_data')</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>
@endsection
