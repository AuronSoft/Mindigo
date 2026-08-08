@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-exam::app.title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">

    <header class="sticky top-0 z-10 border-b border-slate-200 bg-white/95 px-6 py-4 backdrop-blur">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('teacher-exam::app.teaching_exam')</p>
                <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('teacher-exam::app.title')</h1>
                <p class="mt-1 text-xs font-semibold text-slate-400">@lang('teacher-exam::app.subtitle')</p>
            </div>
            <div class="flex flex-wrap gap-2"><a href="{{ route('teacher.exams.create') }}" class="inline-flex h-10 items-center gap-2 rounded-xl bg-green-600 px-4 text-sm font-black text-white no-underline shadow-sm transition hover:bg-green-500">
                <x-heroicon-o-plus class="h-4 w-4" />@lang('teacher-exam::app.create')
            </a></div>
        </div>
    </header>

    <div class="flex flex-1 flex-col gap-5 p-6">

        {{-- Stats --}}
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['label' => __('teacher-exam::app.stat_total'),     'value' => $stats['total'],     'bg' => 'bg-slate-900', 'icon' => 'heroicon-o-document-text'],
                ['label' => __('teacher-exam::app.stat_published'), 'value' => $stats['published'], 'bg' => 'bg-green-600', 'icon' => 'heroicon-o-check-circle'],
                ['label' => __('teacher-exam::app.stat_draft'),     'value' => $stats['draft'],     'bg' => 'bg-amber-500', 'icon' => 'heroicon-o-pencil'],
                ['label' => __('teacher-exam::app.stat_closed'),    'value' => $stats['closed'],    'bg' => 'bg-slate-400', 'icon' => 'heroicon-o-lock-closed'],
            ] as $card)
                <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl {{ $card['bg'] }} text-white shadow-sm">
                        <x-dynamic-component :component="$card['icon']" class="h-6 w-6" />
                    </span>
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">{{ $card['label'] }}</p>
                        <strong class="mt-0.5 block text-3xl font-black tracking-tight text-slate-950">{{ number_format($card['value']) }}</strong>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Filter --}}
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <div class="flex min-w-56 flex-1 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 shadow-sm focus-within:border-green-300 focus-within:ring-2 focus-within:ring-green-50">
                <x-heroicon-o-magnifying-glass class="h-4 w-4 shrink-0 text-slate-400" />
                <input type="text" name="keyword" value="{{ $filters['keyword'] ?? '' }}"
                       placeholder="@lang('teacher-exam::app.search')"
                       class="min-w-0 flex-1 bg-transparent text-sm font-bold text-slate-700 outline-none placeholder:text-slate-400">
            </div>
            <select name="status" data-mindigo-auto-submit
                    class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-600 shadow-sm outline-none">
                <option value="">@lang('teacher-exam::app.all_status')</option>
                @foreach(['published', 'draft', 'reviewing', 'closed'] as $st)
                    <option value="{{ $st }}" @selected(($filters['status'] ?? '') === $st)>@lang('teacher-exam::app.' . $st)</option>
                @endforeach
            </select>
        </form>

        {{-- List --}}
        @if($exams->isEmpty())
            <div class="flex flex-1 flex-col items-center justify-center gap-4 rounded-3xl border border-dashed border-slate-200 bg-white py-20">
                <span class="grid h-20 w-20 place-items-center rounded-full bg-slate-50 text-slate-300">
                    <x-heroicon-o-document-text class="h-10 w-10" />
                </span>
                <div class="text-center">
                    <p class="text-lg font-black text-slate-700">@lang('teacher-exam::app.empty_title')</p>
                    <p class="mt-1 max-w-xs text-sm font-semibold leading-relaxed text-slate-400">@lang('teacher-exam::app.empty_desc')</p>
                </div>
                <a href="{{ route('teacher.exams.create') }}"
                   class="mt-2 inline-flex h-10 items-center gap-2 rounded-full bg-green-600 px-6 text-sm font-black text-white no-underline shadow-sm transition hover:bg-green-500">
                    <x-heroicon-o-plus class="h-4 w-4" />@lang('teacher-exam::app.create')
                </a>
            </div>
        @else
            @php
                $statusConfig = [
                    'published' => ['bg' => 'bg-green-100 text-green-700', 'dot' => 'bg-green-500'],
                    'reviewing' => ['bg' => 'bg-amber-100 text-amber-700', 'dot' => 'bg-amber-400'],
                    'closed'    => ['bg' => 'bg-slate-100 text-slate-500', 'dot' => 'bg-slate-400'],
                    'draft'     => ['bg' => 'bg-slate-100 text-slate-500', 'dot' => 'bg-slate-300'],
                ];
            @endphp
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <table class="w-full text-left">
                    <thead class="border-b border-slate-100 bg-slate-50 text-[11px] font-black uppercase tracking-wide text-slate-400">
                        <tr>
                            <th class="px-5 py-3.5">Đề thi</th>
                            <th class="px-5 py-3.5">@lang('teacher-exam::app.col_subject')</th>
                            <th class="px-5 py-3.5">@lang('teacher-exam::app.col_questions')</th>
                            <th class="px-5 py-3.5">@lang('teacher-exam::app.col_attempts')</th>
                            <th class="px-5 py-3.5">@lang('teacher-exam::app.col_avg')</th>
                            <th class="px-5 py-3.5">@lang('teacher-exam::app.col_status')</th>
                            <th class="px-5 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($exams as $exam)
                            @php $sc = $statusConfig[$exam->status] ?? $statusConfig['draft']; @endphp
                            <tr class="bg-white transition hover:bg-slate-50/70">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('teacher.exams.show', $exam) }}" class="text-sm font-black text-slate-900 no-underline hover:text-green-700 hover:underline">
                                        {{ \Illuminate\Support\Str::limit($exam->title, 42) }}
                                    </a>
                                </td>
                                <td class="px-5 py-3.5 text-sm font-bold text-slate-500">{{ $exam->subject ?: '—' }}</td>
                                <td class="px-5 py-3.5 text-sm font-black text-slate-700">{{ $exam->total_questions ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-sm font-black text-slate-700">{{ number_format($exam->attempts_count) }}</td>
                                <td class="px-5 py-3.5">
                                    @if($exam->attempts_count > 0)
                                        <span class="rounded-full bg-green-50 px-2.5 py-1 text-[11px] font-black text-green-700">
                                            {{ round($exam->attempts_avg_percentage ?? 0, 1) }}%
                                        </span>
                                    @else
                                        <span class="text-sm text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-black {{ $sc['bg'] }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $sc['dot'] }}"></span>
                                        @lang('teacher-exam::app.' . $exam->status)
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-1">
                                        <a href="{{ route('teacher.exams.show', $exam) }}"
                                           class="grid h-8 w-8 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                                            <x-heroicon-o-eye class="h-4 w-4" />
                                        </a>
                                        @if(in_array($exam->status, ['draft', 'reviewing']))
                                        <a href="{{ route('teacher.exams.edit', $exam) }}"
                                           class="grid h-8 w-8 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                                            <x-heroicon-o-pencil-square class="h-4 w-4" />
                                        </a>
                                        @endif
                                        <form method="POST" action="{{ route('teacher.exams.destroy', $exam) }}"
                                              data-mindigo-confirm-title="@lang('teacher-exam::app.delete_title')"
                                              data-mindigo-confirm-message="@lang('teacher-exam::app.delete_confirm')"
                                              data-mindigo-confirm-text="@lang('teacher-exam::app.delete')"
                                              data-mindigo-confirm-cancel="{{ __('teacher-exam::app.cancel') }}"
                                              data-mindigo-confirm-type="danger">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="grid h-8 w-8 place-items-center rounded-xl text-slate-400 transition hover:bg-red-50 hover:text-red-600">
                                                <x-heroicon-o-trash class="h-4 w-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($exams->hasPages())
                <div class="flex justify-center">{{ $exams->links() }}</div>
            @endif
        @endif
    </div>
</div>
@endsection
