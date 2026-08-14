@props(['status', 'label' => null])
@php
    $tone = match ($status) {
        'ready', 'live', 'completed' => 'success',
        'scheduled', 'grading' => 'info',
        'ended', 'archived' => 'neutral',
        'warning', 'needs_attention' => 'warning',
        'blocked', 'terminated' => 'danger',
        default => 'draft',
    };
@endphp

<span {{ $attributes->class(['exam-status']) }} data-tone="{{ $tone }}">
    <span class="exam-status-dot" aria-hidden="true"></span>{{ $label ?? str($status)->replace('_', ' ')->title() }}
</span>
