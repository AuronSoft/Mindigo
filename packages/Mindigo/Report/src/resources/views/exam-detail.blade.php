@extends('Mindigo-dashboard::layouts')

@section('title', $exam->title . ' — ' . __('Mindigo-report::app.exam_detail'))

@section('styles')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Mindigo/Report/src/resources/css/app.css',
        'packages/Mindigo/Report/src/resources/js/app.js',
    ])
@endsection

@section('scripts')
    @php
        $distLabels = array_keys($report['score_distribution']);
        $distData = array_values($report['score_distribution']);
    @endphp
    <script>
        window.__reportExamDist = {
            labels: {{ Illuminate\Support\Js::from($distLabels) }},
            data: {{ Illuminate\Support\Js::from($distData) }},
        };
    </script>
@endsection

@section('content')
    <section class="min-h-screen bg-[#f7faf7]">
        <header class="flex min-h-[4.25rem] items-center gap-3 bg-[#f7faf7] px-5 py-3 max-md:px-4">
            <a href="{{ route('reports.exams') }}" class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline transition hover:bg-green-50 hover:text-green-700">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
            </a>
            <div class="min-w-0">
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">@lang('Mindigo-report::app.exam_detail')</p>
                <h1 class="mt-0.5 truncate text-xl font-black text-slate-950">{{ $exam->title }}</h1>
            </div>
        </header>

        <div class="grid gap-4 px-5 pb-8 max-md:px-4">

            {{-- Exam info + summary --}}
            <div class="grid gap-4 lg:grid-cols-[minmax(0,0.4fr)_minmax(0,0.6fr)]">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-3">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">Thông tin đề thi</p>
                    <div class="space-y-2 text-sm">
                        @foreach([
                            ['Môn học', $exam->subject ?: '—'],
                            ['Chủ đề', $exam->topic ?: '—'],
                            ['Thời gian', ($exam->duration_minutes ?? '—') . ' phút'],
                            ['Số câu hỏi', $exam->total_questions ?? '—'],
                            ['Điểm đạt', ($exam->passing_score ?? '—') . '/' . ($exam->total_points ?? '—')],
                            ['Trạng thái', $exam->status],
                        ] as [$key, $val])
                            <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2">
                                <span class="font-bold text-slate-500">{{ $key }}</span>
                                <span class="font-black text-slate-900">{{ $val }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 content-start">
                    @foreach([
                        ['label' => __('Mindigo-report::app.total_candidates'), 'value' => number_format($report['total']), 'tone' => 'bg-slate-950 text-white'],
                        ['label' => __('Mindigo-report::app.pass_rate'), 'value' => $report['pass_rate'] . '%', 'tone' => 'bg-green-600 text-white'],
                        ['label' => __('Mindigo-report::app.avg_score'), 'value' => $report['avg_score'] . '%', 'tone' => 'bg-white', 'text' => 'text-slate-950'],
                        ['label' => __('Mindigo-report::app.avg_duration'), 'value' => $report['avg_duration_mins'] . ' ' . __('Mindigo-report::app.minutes'), 'tone' => 'bg-white', 'text' => 'text-slate-950'],
                        ['label' => __('Mindigo-report::app.passed'), 'value' => number_format($report['passed']), 'tone' => 'bg-emerald-50', 'text' => 'text-emerald-800'],
                        ['label' => __('Mindigo-report::app.failed'), 'value' => number_format($report['failed']), 'tone' => 'bg-red-50', 'text' => 'text-red-700'],
                    ] as $card)
                        <article class="rounded-2xl border border-slate-200 {{ $card['tone'] }} p-4 shadow-sm">
                            <p class="text-[11px] font-black uppercase tracking-wider {{ isset($card['text']) ? 'text-slate-400' : 'opacity-70' }}">{{ $card['label'] }}</p>
                            <strong class="mt-1 block text-2xl font-black {{ $card['text'] ?? '' }}">{{ $card['value'] }}</strong>
                        </article>
                    @endforeach
                </div>
            </div>

            {{-- Score distribution --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="mb-4 text-xs font-black uppercase tracking-wider text-slate-400">@lang('Mindigo-report::app.score_dist_chart')</p>
                <div class="h-52"><canvas id="examScoreDistChart"></canvas></div>
            </div>

            {{-- Top candidates --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <p class="text-sm font-black text-slate-950">@lang('Mindigo-report::app.top_candidates')</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-125 text-left">
                        <thead class="bg-slate-50 text-[11px] font-black uppercase text-slate-400">
                            <tr>
                                <th class="px-4 py-3">@lang('Mindigo-report::app.rank')</th>
                                <th class="px-4 py-3">@lang('Mindigo-report::app.candidate')</th>
                                <th class="px-4 py-3">@lang('Mindigo-report::app.percentage')</th>
                                <th class="px-4 py-3">@lang('Mindigo-report::app.result')</th>
                                <th class="px-4 py-3">@lang('Mindigo-report::app.submitted_at')</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm font-bold text-slate-700">
                            @forelse($report['top_candidates'] as $i => $candidate)
                                <tr class="bg-white">
                                    <td class="px-4 py-3 font-black text-slate-400">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3 font-black text-slate-900">{{ $candidate->name }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="h-1.5 w-20 rounded-full bg-slate-100">
                                                <div class="h-1.5 rounded-full bg-green-500" style="width: {{ min(100, $candidate->percentage) }}%"></div>
                                            </div>
                                            <span>{{ round($candidate->percentage, 1) }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($candidate->passed)
                                            <span class="rounded-full bg-green-100 px-2.5 py-1 text-[11px] font-black text-green-800">@lang('Mindigo-report::app.status_passed')</span>
                                        @else
                                            <span class="rounded-full bg-red-100 px-2.5 py-1 text-[11px] font-black text-red-800">@lang('Mindigo-report::app.status_failed')</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-500">{{ $candidate->submitted_at ? \Carbon\Carbon::parse($candidate->submitted_at)->format('d/m/Y H:i') : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="h-52 text-center align-middle">
                                    <div class="flex flex-col items-center justify-center gap-3">
                                        <x-heroicon-o-user-group class="h-14 w-14 text-slate-200" />
                                        <span class="text-sm font-bold text-slate-400">@lang('Mindigo-report::app.no_data')</span>
                                    </div>
                                </td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>
@endsection
