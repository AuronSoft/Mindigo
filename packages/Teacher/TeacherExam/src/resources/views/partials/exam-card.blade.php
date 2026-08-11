@php
    $statusStyles = [
        'published' => 'border-green-200 bg-green-50 text-green-700',
        'reviewing' => 'border-amber-200 bg-amber-50 text-amber-700',
        'closed' => 'border-slate-200 bg-slate-100 text-slate-600',
        'draft' => 'border-blue-200 bg-blue-50 text-blue-700',
    ];
    $covers = [
        ['surface' => 'bg-green-100', 'ink' => 'text-green-700', 'shape' => 'bg-green-200'],
        ['surface' => 'bg-blue-100', 'ink' => 'text-blue-700', 'shape' => 'bg-blue-200'],
        ['surface' => 'bg-violet-100', 'ink' => 'text-violet-700', 'shape' => 'bg-violet-200'],
        ['surface' => 'bg-amber-100', 'ink' => 'text-amber-700', 'shape' => 'bg-amber-200'],
        ['surface' => 'bg-rose-100', 'ink' => 'text-rose-700', 'shape' => 'bg-rose-200'],
    ];
    $cover = $covers[$exam->id % count($covers)];
    $statusClass = $statusStyles[$exam->status] ?? $statusStyles['draft'];
@endphp

<article class="group flex min-h-86 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md">
    <a href="{{ route('teacher.exams.show', $exam) }}" class="relative block h-36 overflow-hidden {{ $cover['surface'] }} no-underline" aria-label="{{ __('teacher-exam::app.open_exam', ['title' => $exam->title]) }}">
        <span class="absolute -right-5 -top-7 h-24 w-24 rounded-full border-[14px] border-white/50"></span>
        <span class="absolute bottom-4 left-5 h-8 w-20 rounded-lg {{ $cover['shape'] }}"></span>
        <span class="absolute bottom-7 right-7 h-12 w-12 rotate-12 rounded-xl border-4 border-white/70 {{ $cover['shape'] }}"></span>
        <span class="absolute inset-0 grid place-items-center {{ $cover['ink'] }}">
            <span class="grid h-18 w-18 place-items-center rounded-2xl border border-white/80 bg-white/75 shadow-sm">
                <x-heroicon-o-clipboard-document-check class="h-9 w-9" />
            </span>
        </span>
        <span class="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-full border border-white/80 bg-white/85 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide {{ $cover['ink'] }}">
            <x-heroicon-o-academic-cap class="h-3.5 w-3.5" />{{ $exam->subject ?: __('teacher-exam::app.general_subject') }}
        </span>
    </a>

    <div class="flex flex-1 flex-col p-4">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <a href="{{ route('teacher.exams.show', $exam) }}" class="line-clamp-2 text-sm font-black leading-5 text-slate-900 no-underline transition hover:text-green-700">{{ $exam->title }}</a>
                <p class="mt-1 truncate text-xs font-semibold text-slate-400">{{ $exam->topic ?: __('teacher-exam::app.no_topic') }}</p>
            </div>
            <span class="shrink-0 rounded-full border px-2 py-1 text-[10px] font-black {{ $statusClass }}">@lang('teacher-exam::app.'.$exam->status)</span>
        </div>

        <dl class="mt-4 grid grid-cols-3 divide-x divide-slate-100 rounded-xl border border-slate-100 bg-slate-50 py-2.5 text-center">
            <div class="px-2"><dt class="text-[9px] font-black uppercase tracking-wide text-slate-400">@lang('teacher-exam::app.col_questions')</dt><dd class="mt-1 text-xs font-black text-slate-800">{{ $exam->total_questions ?? 0 }}</dd></div>
            <div class="px-2"><dt class="text-[9px] font-black uppercase tracking-wide text-slate-400">@lang('teacher-exam::app.col_attempts')</dt><dd class="mt-1 text-xs font-black text-slate-800">{{ number_format($exam->attempts_count) }}</dd></div>
            <div class="px-2"><dt class="text-[9px] font-black uppercase tracking-wide text-slate-400">@lang('teacher-exam::app.col_avg')</dt><dd class="mt-1 text-xs font-black text-slate-800">{{ $exam->attempts_count ? number_format((float) $exam->attempts_avg_percentage, 1).'%' : '—' }}</dd></div>
        </dl>

        <div class="mt-3 flex items-center gap-2 text-[11px] font-semibold text-slate-400">
            <x-heroicon-o-calendar-days class="h-3.5 w-3.5" />
            {{ __('teacher-exam::app.updated_date', ['date' => $exam->updated_at->format('d/m/Y')]) }}
        </div>

        <div class="mt-auto flex items-center justify-between border-t border-slate-100 pt-3">
            <a href="{{ route('teacher.exams.show', $exam) }}" class="inline-flex h-9 flex-1 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 no-underline transition hover:border-green-300 hover:bg-green-50 hover:text-green-700">@lang('teacher-exam::app.view_exam')</a>
            <div class="ml-2 flex items-center gap-1">
                @if(in_array($exam->status, ['draft', 'reviewing']))
                    <a href="{{ route('teacher.exams.edit', $exam) }}" aria-label="{{ __('teacher-exam::app.edit_exam_label', ['title' => $exam->title]) }}" class="grid h-9 w-9 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"><x-heroicon-o-pencil-square class="h-4 w-4" /></a>
                @endif
                <form method="POST" action="{{ route('teacher.exams.destroy', $exam) }}" data-mindigo-confirm-title="@lang('teacher-exam::app.delete_title')" data-mindigo-confirm-message="@lang('teacher-exam::app.delete_confirm')" data-mindigo-confirm-text="@lang('teacher-exam::app.delete')" data-mindigo-confirm-cancel="{{ __('teacher-exam::app.cancel') }}" data-mindigo-confirm-type="danger">
                    @csrf @method('DELETE')
                    <button type="submit" aria-label="{{ __('teacher-exam::app.delete_exam_label', ['title' => $exam->title]) }}" class="grid h-9 w-9 place-items-center rounded-xl text-slate-400 transition hover:bg-red-50 hover:text-red-600"><x-heroicon-o-trash class="h-4 w-4" /></button>
                </form>
            </div>
        </div>
    </div>
</article>
