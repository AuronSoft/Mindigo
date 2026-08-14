@props(['href' => null, 'variant' => 'primary', 'type' => 'button'])
@php
    $classes = match ($variant) {
        'secondary' => 'exam-button exam-button-secondary',
        'danger' => 'exam-button exam-button-danger',
        'ghost' => 'exam-button exam-button-ghost',
        default => 'exam-button exam-button-primary',
    };
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
