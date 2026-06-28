@props(['expiresAt'])

@if($expiresAt)
<div id="timer"
     data-student-exam-timer
     data-expires-at="{{ $expiresAt->getTimestamp() * 1000 }}"
     data-result-url="{{ route('student.exams.result', $attempt) }}"
     data-expired-message="@lang('student-exam::app.time_expired')"
     class="flex items-center gap-2 bg-red-50 text-red-600 px-5 py-2 rounded-3xl font-mono font-bold text-lg">
    <x-heroicon-o-clock class="h-5 w-5" />
    <span id="time-remaining" data-student-exam-time-remaining>00:00</span>
</div>
@endif
