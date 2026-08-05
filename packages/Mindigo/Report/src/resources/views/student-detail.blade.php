@extends('Mindigo-dashboard::layouts')

@section('title', $user->name . ' — ' . __('Mindigo-report::app.student_detail'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Mindigo/Report/src/resources/css/app.css',
        'packages/Mindigo/Report/src/resources/js/app.js',
    ])
@endsection

@section('content')
    <section class="min-h-screen bg-[#f7faf7]">
        <header class="flex min-h-17 items-center gap-3 bg-[#f7faf7] px-5 py-3 max-md:px-4">
            <a href="{{ route('reports.students') }}" class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline transition hover:bg-green-50 hover:text-green-700">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
            </a>
            <div class="flex items-center gap-3">
                <span class="grid h-11 w-11 place-items-center rounded-2xl bg-linear-to-br from-green-200 to-emerald-300 text-lg font-black text-slate-800">{{ mb_substr($user->name, 0, 1) }}</span>
                <div>
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">@lang('Mindigo-report::app.student_detail')</p>
                    <h1 class="mt-0.5 text-xl font-black text-slate-950">{{ $user->name }}</h1>
                </div>
            </div>
        </header>

        <div class="grid gap-4 px-5 pb-8 max-md:px-4">

            {{-- Summary cards --}}
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['label' => __('Mindigo-report::app.total_attempts'), 'value' => number_format($report['total_attempts']), 'tone' => 'bg-slate-950 text-white'],
                    ['label' => __('Mindigo-report::app.avg_score'), 'value' => $report['avg_score'] . '%', 'tone' => 'bg-green-600 text-white'],
                    ['label' => __('Mindigo-report::app.pass_rate'), 'value' => $report['pass_rate'] . '%', 'tone' => 'bg-emerald-50 text-emerald-800'],
                    ['label' => __('Mindigo-report::app.passed'), 'value' => number_format($report['passed']) . ' / ' . number_format($report['total_attempts']), 'tone' => 'bg-white text-slate-900'],
                ] as $card)
                    <article class="rounded-2xl border border-slate-200 {{ $card['tone'] }} p-4 shadow-sm">
                        <p class="text-[11px] font-black uppercase tracking-wider opacity-60">{{ $card['label'] }}</p>
                        <strong class="mt-1 block text-2xl font-black">{{ $card['value'] }}</strong>
                    </article>
                @endforeach
            </div>

            <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)]">
                {{-- Subject performance --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="mb-4 text-xs font-black uppercase tracking-wider text-slate-400">@lang('Mindigo-report::app.subject_performance')</p>
                    @forelse($report['subject_performance'] as $subj)
                        <div class="mb-3">
                            <div class="flex items-center justify-between gap-3 mb-1">
                                <span class="text-sm font-black text-slate-800">{{ $subj->subject }}</span>
                                <span class="text-xs font-black text-slate-500">{{ $subj->avg_score }}% · {{ $subj->count }} lượt</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-slate-100">
                                <div class="h-2 rounded-full bg-green-500 transition-all" style="width: {{ min(100, $subj->avg_score) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="flex min-h-52 flex-col items-center justify-center gap-3">
                            <x-heroicon-o-academic-cap class="h-14 w-14 text-slate-200" />
                            <p class="text-sm font-bold text-slate-400">@lang('Mindigo-report::app.no_data')</p>
                        </div>
                    @endforelse
                </div>

                {{-- Exam history --}}
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100">
                        <p class="text-sm font-black text-slate-950">@lang('Mindigo-report::app.exam_history')</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-110 text-left">
                            <thead class="bg-slate-50 text-[11px] font-black uppercase text-slate-400">
                                <tr>
                                    <th class="px-4 py-3">@lang('Mindigo-report::app.exam_name')</th>
                                    <th class="px-4 py-3">@lang('Mindigo-report::app.percentage')</th>
                                    <th class="px-4 py-3">@lang('Mindigo-report::app.result')</th>
                                    <th class="px-4 py-3">@lang('Mindigo-report::app.submitted_at')</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm font-bold text-slate-700">
                                @forelse($report['exam_history'] as $attempt)
                                    <tr class="bg-white">
                                        <td class="max-w-xs truncate px-4 py-3">{{ $attempt->exam ? \Illuminate\Support\Str::limit($attempt->exam->title, 30) : '—' }}</td>
                                        <td class="px-4 py-3">{{ round($attempt->percentage, 1) }}%</td>
                                        <td class="px-4 py-3">
                                            @if($attempt->passed)
                                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-black text-green-800">@lang('Mindigo-report::app.status_passed')</span>
                                            @else
                                                <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-black text-red-800">@lang('Mindigo-report::app.status_failed')</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-500">{{ $attempt->submitted_at ? $attempt->submitted_at->format('d/m/Y') : '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="h-52 text-center align-middle">
                                        <div class="flex flex-col items-center justify-center gap-3">
                                            <x-heroicon-o-clock class="h-14 w-14 text-slate-200" />
                                            <span class="text-sm font-bold text-slate-400">@lang('Mindigo-report::app.no_data')</span>
                                        </div>
                                    </td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection
