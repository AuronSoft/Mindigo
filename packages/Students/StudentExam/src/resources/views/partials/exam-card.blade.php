@php
    $lastAttempt = $exam->attempts->last();
    $attemptCount = $exam->attempts->count();
    $maxAttempts = $exam->max_attempts ?? 1;
    $remainingAttempts = max(0, $maxAttempts - $attemptCount);
@endphp

<article class="grid gap-4 px-5 py-4 transition hover:bg-slate-50/70 lg:grid-cols-[minmax(0,1fr)_auto_auto] lg:items-center">
    <div class="flex min-w-0 items-start gap-3">
        <x-heroicon-o-document-text class="mt-1 h-5 w-5 shrink-0 {{ $group === 'ongoing' ? 'text-green-600' : ($group === 'upcoming' ? 'text-blue-500' : 'text-slate-400') }}" />
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h3 class="truncate text-sm font-black text-slate-900">{{ $exam->title }}</h3>
                @if($exam->subject?->name)<span class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">{{ $exam->subject->name }}</span>@endif
            </div>
            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-[11px] font-semibold text-slate-400">
                @if($exam->duration_minutes)<span class="inline-flex items-center gap-1"><x-heroicon-o-clock class="h-3.5 w-3.5" />{{ __('student-exam::app.duration_minutes', ['min' => $exam->duration_minutes]) }}</span>@endif
                @if($exam->starts_at)<span class="inline-flex items-center gap-1"><x-heroicon-o-calendar-days class="h-3.5 w-3.5" />{{ __('student-exam::app.opens_at') }} {{ $exam->starts_at->format('d/m/Y H:i') }}</span>@endif
                @if($exam->ends_at)<span class="inline-flex items-center gap-1"><x-heroicon-o-lock-closed class="h-3.5 w-3.5" />{{ __('student-exam::app.closes_at') }} {{ $exam->ends_at->format('d/m/Y H:i') }}</span>@endif
            </div>
        </div>
    </div>

    <div class="flex items-center gap-4 lg:min-w-48 lg:justify-end">
        @if($group === 'completed' && $lastAttempt)
            @if($lastAttempt->status === 'graded')
                <div class="text-left lg:text-right"><strong class="block text-base font-black {{ $lastAttempt->passed ? 'text-green-700' : 'text-red-600' }}">{{ $lastAttempt->percentage }}%</strong><span class="text-[10px] font-bold text-slate-400">{{ $lastAttempt->score }}/{{ $lastAttempt->max_score }} @lang('student-exam::app.points')</span></div>
                <span class="rounded-full px-2.5 py-1 text-[10px] font-black {{ $lastAttempt->passed ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $lastAttempt->passed ? __('student-exam::app.passed') : __('student-exam::app.failed') }}</span>
            @else
                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-black text-amber-700">@lang('student-exam::app.status_pending_review')</span>
            @endif
        @else
            <div class="text-left lg:text-right"><strong class="block text-xs font-black text-slate-700">{{ __('student-exam::app.attempts_remaining', ['n' => $remainingAttempts]) }}</strong><span class="mt-1 block text-[10px] font-semibold text-slate-400">{{ __('student-exam::app.attempts_used', ['used' => $attemptCount, 'max' => $maxAttempts]) }}</span></div>
        @endif
    </div>

    <div class="lg:w-40">
        @if($group === 'ongoing')
            <form action="{{ route('student.exams.start', $exam) }}" method="POST">@csrf<button type="submit" class="inline-flex h-9 w-full items-center justify-center gap-2 rounded-lg bg-green-600 px-4 text-xs font-black text-white transition hover:bg-green-700"><x-heroicon-o-play class="h-4 w-4" />@lang('student-exam::app.start_exam')</button></form>
        @elseif($group === 'upcoming')
            <span class="inline-flex h-9 w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-4 text-xs font-bold text-slate-500"><x-heroicon-o-lock-closed class="h-4 w-4" />@lang('student-exam::app.not_yet_open')</span>
        @elseif($lastAttempt)
            <a href="{{ route('student.exams.result', $lastAttempt) }}" class="inline-flex h-9 w-full items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 text-xs font-black text-slate-700 no-underline transition hover:border-green-300 hover:text-green-700"><x-heroicon-o-chart-bar class="h-4 w-4" />@lang('student-exam::app.view_result')</a>
        @endif
    </div>
</article>
