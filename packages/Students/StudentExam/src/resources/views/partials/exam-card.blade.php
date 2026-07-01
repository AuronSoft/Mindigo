{{-- student-exam::partials.exam-card --}}
@php
    $lastAttempt  = $exam->attempts->last();
    $attemptCount = $exam->attempts->count();
    $maxAttempts  = $exam->max_attempts ?? 1;
@endphp

<div class="group relative flex flex-col rounded-2xl border bg-white p-5 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800
    {{ $group === 'ongoing'   ? 'border-emerald-100 dark:border-emerald-800/40' : '' }}
    {{ $group === 'upcoming'  ? 'border-blue-100 dark:border-blue-800/40' : '' }}
    {{ $group === 'completed' ? 'border-gray-200' : '' }}">

    {{-- Title + subject --}}
    <div class="mb-3">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">
            {{ $exam->subject->name ?? '' }}
        </p>
        <h3 class="mt-0.5 text-base font-semibold text-gray-900 dark:text-white leading-snug">
            {{ $exam->title }}
        </h3>
    </div>

    {{-- Meta row --}}
    <div class="mb-4 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
        @if($exam->duration_minutes)
            <span class="flex items-center gap-1">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"/></svg>
                {{ __('student-exam::app.duration_minutes', ['min' => $exam->duration_minutes]) }}
            </span>
        @endif

        @if($exam->starts_at)
            <span>
                {{ __('student-exam::app.opens_at') }}
                {{ $exam->starts_at->format('d/m H:i') }}
            </span>
        @endif

        @if($exam->ends_at)
            <span>
                {{ __('student-exam::app.closes_at') }}
                {{ $exam->ends_at->format('d/m H:i') }}
            </span>
        @endif

        <span>
            {{ __('student-exam::app.attempts_used', ['used' => $attemptCount, 'max' => $maxAttempts]) }}
        </span>
    </div>

    {{-- Score badge (completed) --}}
    @if($group === 'completed' && $lastAttempt)
        <div class="mb-4">
            @if($lastAttempt->status === 'graded')
                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold
                    {{ $lastAttempt->passed ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-300' }}">
                    {{ $lastAttempt->passed ? __('student-exam::app.passed') : __('student-exam::app.failed') }}
                    · {{ $lastAttempt->score }}/{{ $lastAttempt->max_score }}
                    ({{ $lastAttempt->percentage }}%)
                </span>
            @elseif($lastAttempt->status === 'submitted')
                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                    {{ __('student-exam::app.status_pending_review') }}
                </span>
            @endif
        </div>
    @endif

    {{-- CTA --}}
    <div class="mt-auto">
        @if($group === 'ongoing')
            <form action="{{ route('student.exams.start', $exam) }}" method="POST">
                @csrf
                <button type="submit"
                    class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 active:scale-95">
                    {{ __('student-exam::app.start_exam') }}
                </button>
            </form>
        @elseif($group === 'upcoming')
            <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-2.5 text-center text-sm text-blue-600 dark:border-blue-800/40 dark:bg-blue-900/20 dark:text-blue-400">
                {{ __('student-exam::app.opens_at') }}
                {{ $exam->starts_at?->format('d/m/Y H:i') ?? '—' }}
            </div>
        @elseif($group === 'completed' && $lastAttempt)
            <a href="{{ route('student.exams.result', $lastAttempt) }}"
               class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-center text-sm font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                {{ __('student-exam::app.view_result') }}
            </a>
        @endif
    </div>
</div>