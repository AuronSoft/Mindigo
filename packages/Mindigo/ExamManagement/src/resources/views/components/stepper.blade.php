@props(['steps' => [], 'current' => 1])

<ol {{ $attributes->class(['exam-stepper']) }} aria-label="Tiến trình">
    @foreach($steps as $index => $step)
        @php($number = $index + 1)
        <li class="exam-step" data-state="{{ $number < $current ? 'complete' : ($number === $current ? 'current' : 'upcoming') }}" @if($number === $current) aria-current="step" @endif>
            <span class="exam-step-number">{{ $number < $current ? '✓' : $number }}</span>
            <span class="exam-step-label">{{ $step }}</span>
        </li>
    @endforeach
</ol>
