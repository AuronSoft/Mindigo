@props(['expiresAt'])

@if($expiresAt)
<div id="timer" class="flex items-center gap-2 bg-red-50 text-red-600 px-5 py-2 rounded-3xl font-mono font-bold text-lg">
    <x-heroicon-o-clock class="h-5 w-5" />
    <span id="time-remaining">00:00</span>
</div>

<script>
    const expiresAt = new Date('{{ $expiresAt->toIso8601String() }}').getTime();

    function updateTimer() {
        const now = Date.now();
        const diff = Math.max(0, expiresAt - now);
        
        const minutes = Math.floor(diff / 60000);
        const seconds = Math.floor((diff % 60000) / 1000);
        
        document.getElementById('time-remaining').textContent = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

        if (diff <= 0) {
            clearInterval(timerInterval);
            alert('@lang('student-exam::app.time_expired')');
            window.location.href = "{{ route('student.exams.result', $attempt) }}";
        }
    }

    const timerInterval = setInterval(updateTimer, 1000);
    updateTimer();
</script>
@endif