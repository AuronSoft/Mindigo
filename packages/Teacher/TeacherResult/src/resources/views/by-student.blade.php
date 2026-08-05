@extends('Mindigo-dashboard::layouts')

@section('title', $user->name . ' — ' . __('teacher-result::app.student_detail'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    <header class="sticky top-0 z-10 flex items-center gap-3 border-b border-slate-200 bg-white/95 px-6 py-3 backdrop-blur">
        <a href="{{ route('teacher.results.index', array_filter(['classroom_id' => $selectedClassroom?->id])) }}" class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline transition hover:bg-green-50 hover:text-green-700">
            <x-heroicon-o-arrow-left class="h-4 w-4" />
        </a>
        <div class="flex items-center gap-3">
            <span class="grid h-10 w-10 place-items-center rounded-2xl bg-linear-to-br from-sky-400 to-sky-600 text-base font-black text-white shadow-sm">
                {{ mb_substr($user->name, 0, 1) }}
            </span>
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">@lang('teacher-result::app.student_detail')</p>
                <h1 class="text-base font-black text-slate-950">{{ $user->name }}</h1>
            </div>
        </div>
    </header>

    <div class="grid gap-4 p-5 xl:grid-cols-[minmax(0,0.38fr)_minmax(0,0.62fr)]">

        {{-- Left: summary + subject performance --}}
        <div class="space-y-4">
            {{-- 3 stat chips --}}
            <div class="grid grid-cols-3 gap-2">
                @foreach([
                    [__('teacher-result::app.total_attempts'), $detail['total'],         'text-slate-900'],
                    [__('teacher-result::app.avg_score'),      $detail['avg_score'].'/10', 'text-sky-700'],
                    [__('teacher-result::app.pass_rate'),      $detail['pass_rate'].'%', 'text-green-700'],
                ] as $s)
                    <div class="rounded-2xl border border-slate-200 bg-white p-3 text-center shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $s[0] }}</p>
                        <strong class="mt-1 block text-xl font-black {{ $s[2] }}">{{ $s[1] }}</strong>
                    </div>
                @endforeach
            </div>

            {{-- Subject performance bars --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="mb-3 text-xs font-black text-slate-700">@lang('teacher-result::app.subject_perf')</p>
                @forelse($detail['by_subject'] as $subj)
                    <div class="mb-3 last:mb-0">
                        <div class="mb-1 flex items-center justify-between gap-2">
                            <span class="text-sm font-black text-slate-800">{{ $subj['subject'] }}</span>
                            <span class="text-xs font-black text-slate-500">{{ $subj['avg_score'] }}/10 · {{ $subj['count'] }} @lang('teacher-result::app.attempts_unit')</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-2 rounded-full bg-green-500 transition-all duration-500" style="width:{{ min(100, $subj['avg_score'] * 10) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-4 text-center text-sm font-bold text-slate-400">@lang('teacher-result::app.no_history')</p>
                @endforelse
            </div>
        </div>

        {{-- Right: exam history --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-3.5">
                <p class="text-sm font-black text-slate-950">@lang('teacher-result::app.exam_history')</p>
                <p class="mt-0.5 text-xs font-bold text-slate-400">{{ $detail['total'] }} @lang('teacher-result::app.attempts_unit') @lang('teacher-result::app.trend_attempts')</p>
            </div>
            @if($detail['history']->isEmpty())
                <div class="flex flex-col items-center justify-center gap-3 py-16">
                    <x-heroicon-o-document-text class="h-12 w-12 text-slate-200" />
                    <p class="text-sm font-bold text-slate-400">@lang('teacher-result::app.no_history')</p>
                </div>
            @else
                <div class="max-h-112 overflow-y-auto">
                    <table class="w-full text-left">
                        <thead class="sticky top-0 bg-slate-50 text-[11px] font-black uppercase tracking-wide text-slate-400">
                            <tr>
                                <th class="px-5 py-3">@lang('teacher-exam::app.col_subject')</th>
                                <th class="px-5 py-3">@lang('teacher-result::app.col_score_pct')</th>
                                <th class="px-5 py-3">@lang('teacher-result::app.col_result')</th>
                                <th class="px-5 py-3">@lang('teacher-result::app.col_submitted')</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($detail['history'] as $a)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-5 py-3">
                                        <p class="text-sm font-black text-slate-900">{{ \Illuminate\Support\Str::limit($a->exam?->title ?? '—', 30) }}</p>
                                        @if($a->exam?->subject)
                                            <p class="text-xs font-bold text-slate-400">{{ $a->exam->subject }}</p>
                                        @endif
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
                                        @if($a->exam)
                                            <a href="{{ route('teacher.results.by_exam', array_filter(['exam' => $a->exam, 'classroom_id' => $selectedClassroom?->id])) }}"
                                               class="text-xs font-black text-slate-400 no-underline hover:text-green-700">→</a>
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
