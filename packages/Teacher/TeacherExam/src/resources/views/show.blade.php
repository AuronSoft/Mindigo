@extends('Mindigo-dashboard::layouts')

@section('title', $exam->title)

@section('styles')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('scripts')
    @php
        $distLabels = array_keys($results['distribution']);
        $distData   = array_values($results['distribution']);
    @endphp
    <script>
        window.__examDistribution = {
            labels: {{ Illuminate\Support\Js::from($distLabels) }},
            data:   {{ Illuminate\Support\Js::from($distData) }},
        };
        (function () {
            const d = window.__examDistribution;
            const el = document.getElementById('distChart');
            if (!el || typeof Chart === 'undefined') return;
            new Chart(el, {
                type: 'bar',
                data: {
                    labels: d.labels,
                    datasets: [{ data: d.data, backgroundColor: ['#fca5a5','#fcd34d','#86efac','#34d399','#16a34a'], borderRadius: 8, borderSkipped: false }],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { backgroundColor: '#0f172a', padding: 8, titleFont: { family: 'Be Vietnam Pro', weight: '800' }, bodyFont: { family: 'Be Vietnam Pro', weight: '700' } } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { weight: 800 } } },
                        y: { border: { display: false }, grid: { color: '#e2e8f0' }, ticks: { color: '#94a3b8', font: { weight: 800 }, precision: 0 }, beginAtZero: true },
                    },
                },
            });
        })();
    </script>
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">

    <header class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white/90 px-6 py-4 backdrop-blur">
        <div class="flex items-center gap-3">
            <a href="{{ route('teacher.exams.index') }}"
               class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline transition hover:bg-green-50 hover:text-green-700">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
            </a>
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">{{ $exam->subject ?: __('teacher-exam::app.title') }}</p>
                <h1 class="mt-0.5 truncate text-lg font-black text-slate-950">{{ $exam->title }}</h1>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($exam->status === 'draft')
                <a href="{{ route('teacher.exams.edit', $exam) }}"
                   class="inline-flex h-9 items-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 no-underline transition hover:bg-slate-50">
                    <x-heroicon-o-pencil-square class="h-4 w-4" />@lang('teacher-exam::app.edit')
                </a>
                <form method="POST" action="{{ route('teacher.exams.publish', $exam) }}"
                      data-mindigo-confirm-title="@lang('teacher-exam::app.publish_title')"
                      data-mindigo-confirm-message="@lang('teacher-exam::app.publish_confirm')"
                      data-mindigo-confirm-text="@lang('teacher-exam::app.publish')"
                      data-mindigo-confirm-cancel="{{ __('teacher-exam::app.cancel') }}"
                      data-mindigo-confirm-type="info">
                    @csrf
                    <button type="submit"
                            class="inline-flex h-9 items-center gap-2 rounded-full bg-green-600 px-4 text-xs font-black text-white shadow-sm transition hover:bg-green-500">
                        <x-heroicon-o-arrow-up-tray class="h-4 w-4" />@lang('teacher-exam::app.publish')
                    </button>
                </form>
            @elseif($exam->status === 'published')
                <form method="POST" action="{{ route('teacher.exams.close', $exam) }}"
                      data-mindigo-confirm-title="@lang('teacher-exam::app.close_title')"
                      data-mindigo-confirm-message="@lang('teacher-exam::app.close_confirm')"
                      data-mindigo-confirm-text="@lang('teacher-exam::app.close')"
                      data-mindigo-confirm-cancel="{{ __('teacher-exam::app.cancel') }}"
                      data-mindigo-confirm-type="warning">
                    @csrf
                    <button type="submit"
                            class="inline-flex h-9 items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-4 text-xs font-black text-amber-700 transition hover:bg-amber-100">
                        <x-heroicon-o-lock-closed class="h-4 w-4" />@lang('teacher-exam::app.close')
                    </button>
                </form>
            @endif
             {{-- NÚT IN--}}
            <a href="{{ route('teacher.exams.print', $exam) }}"
               class="inline-flex h-9 items-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 no-underline transition hover:bg-slate-50">
                <x-heroicon-o-printer class="h-4 w-4" />@lang('teacher-exam::app.print')
            </a>
            <form method="POST" action="{{ route('teacher.exams.destroy', $exam) }}"
                  data-mindigo-confirm-title="@lang('teacher-exam::app.delete_title')"
                  data-mindigo-confirm-message="@lang('teacher-exam::app.delete_confirm')"
                  data-mindigo-confirm-text="@lang('teacher-exam::app.delete')"
                  data-mindigo-confirm-cancel="{{ __('teacher-exam::app.cancel') }}"
                  data-mindigo-confirm-type="danger">
                @csrf @method('DELETE')
                <button type="submit"
                        class="inline-flex h-9 items-center gap-2 rounded-full border border-red-200 bg-red-50 px-4 text-xs font-black text-red-600 transition hover:bg-red-100">
                    <x-heroicon-o-trash class="h-4 w-4" />@lang('teacher-exam::app.delete')
                </button>
            </form>
        </div>
    </header>

    <div class="flex flex-1 flex-col gap-5 p-6">

        {{-- Result summary cards --}}
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['label' => __('teacher-exam::app.total_candidates'), 'value' => number_format($results['total']),    'bg' => 'bg-slate-900'],
                ['label' => __('teacher-exam::app.pass_rate'),        'value' => $results['pass_rate'] . '%',         'bg' => 'bg-green-600'],
                ['label' => __('teacher-exam::app.avg_score'),        'value' => $results['avg_score'] . '%',         'bg' => 'bg-sky-600'],
                ['label' => __('teacher-exam::app.passed') . ' / ' . __('teacher-exam::app.failed'),
                 'value' => $results['passed'] . ' / ' . $results['failed'],                                          'bg' => 'bg-amber-500'],
            ] as $card)
                <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl {{ $card['bg'] }} text-white text-xl font-black">
                        {{ mb_substr($card['value'], 0, 3) }}
                    </span>
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">{{ $card['label'] }}</p>
                        <strong class="mt-0.5 block text-xl font-black text-slate-950">{{ $card['value'] }}</strong>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,0.38fr)_minmax(0,0.62fr)]">

            {{-- Exam info + score dist --}}
            <div class="space-y-4">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="mb-3 text-xs font-black uppercase tracking-wider text-slate-400">@lang('teacher-exam::app.exam_info')</p>
                    <div class="space-y-2">
                        @foreach([
                            [__('teacher-exam::app.info_subject'),   $exam->subject ?: '—'],
                            [__('teacher-exam::app.info_topic'),     $exam->topic ?: '—'],
                            [__('teacher-exam::app.info_duration'),  $exam->duration_minutes . ' ' . __('teacher-exam::app.minutes')],
                            [__('teacher-exam::app.info_questions'), ($exam->total_questions ?? '—') . ' ' . __('teacher-exam::app.questions')],
                            [__('teacher-exam::app.info_score'),     $exam->passing_score . '/' . $exam->total_points],
                            [__('teacher-exam::app.info_status'),    __('teacher-exam::app.' . $exam->status)],
                        ] as $row)
                            <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2.5 text-sm">
                                <span class="font-bold text-slate-500">{{ $row[0] }}</span>
                                <span class="font-black text-slate-900">{{ $row[1] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="mb-3 text-xs font-black uppercase tracking-wider text-slate-400">@lang('teacher-exam::app.score_dist')</p>
                    <div class="h-44"><canvas id="distChart"></canvas></div>
                </div>
            </div>

            {{-- Candidate list --}}
            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-4">
                    <p class="text-sm font-black text-slate-950">@lang('teacher-exam::app.candidate_list')</p>
                    <p class="mt-0.5 text-xs font-bold text-slate-400">{{ $results['total'] }} @lang('teacher-exam::app.attempts_label')</p>
                </div>

                @if($results['list']->isEmpty())
                    <div class="flex flex-col items-center justify-center gap-3 py-20">
                        <x-heroicon-o-inbox class="h-14 w-14 text-slate-200" />
                        <p class="text-base font-black text-slate-600">@lang('teacher-exam::app.no_attempts')</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wide text-slate-400">
                                <tr>
                                    <th class="w-10 px-5 py-3">#</th>
                                    <th class="px-5 py-3">@lang('teacher-exam::app.col_student')</th>
                                    <th class="px-5 py-3">@lang('teacher-exam::app.col_score')</th>
                                    <th class="px-5 py-3">@lang('teacher-exam::app.col_result')</th>
                                    <th class="px-5 py-3">@lang('teacher-exam::app.col_submitted')</th>
                                    <th class="px-5 py-3 text-right">@lang('teacher-exam::app.col_action')</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($results['list'] as $i => $attempt)
                                    <tr class="bg-white transition hover:bg-slate-50/80">
                                        <td class="px-5 py-3 text-sm font-black text-slate-400">{{ $i + 1 }}</td>
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-2.5">
                                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-slate-100 text-xs font-black text-slate-600">
                                                    {{ mb_substr($attempt->user?->name ?? '?', 0, 1) }}
                                                </span>
                                                <span class="text-sm font-black text-slate-900">{{ $attempt->user?->name ?? '—' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-2">
                                                <div class="h-1.5 w-20 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-1.5 rounded-full bg-green-500" style="width:{{ min(100,$attempt->percentage) }}%"></div>
                                                </div>
                                                <span class="text-sm font-black text-slate-700">{{ round($attempt->percentage, 1) }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3">
                                            @if($attempt->answers->contains(fn ($answer) => $answer->needs_review))
                                                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-black text-amber-700">@lang('teacher-exam::app.pending_grading')</span>
                                            @elseif($attempt->passed)
                                                <span class="rounded-full bg-green-100 px-2.5 py-1 text-[11px] font-black text-green-800">@lang('teacher-exam::app.status_passed')</span>
                                            @else
                                                <span class="rounded-full bg-red-100 px-2.5 py-1 text-[11px] font-black text-red-700">@lang('teacher-exam::app.status_failed')</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-xs font-bold text-slate-400">
                                            {{ $attempt->submitted_at?->diffForHumans() }}
                                        </td>
                                        <td class="px-5 py-3 text-right">
                                            @if($attempt->answers->contains(fn ($answer) => $answer->needs_review))
                                                <a href="{{ route('teacher.exams.attempts.grade', [$exam, $attempt]) }}" class="inline-flex rounded-lg bg-green-600 px-3 py-2 text-xs font-black text-white no-underline hover:bg-green-700">@lang('teacher-exam::app.grade_now')</a>
                                            @elseif($attempt->graded_at)
                                                <a href="{{ route('teacher.exams.attempts.grade', [$exam, $attempt]) }}" class="text-xs font-black text-green-700 no-underline">@lang('teacher-exam::app.review_grade')</a>
                                            @else
                                                <span class="text-xs font-bold text-slate-400">@lang('teacher-exam::app.auto_graded')</span>
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
</div>
@endsection
