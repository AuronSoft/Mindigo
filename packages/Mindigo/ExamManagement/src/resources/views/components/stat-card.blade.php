@props(['label', 'value', 'hint' => null, 'tone' => 'green'])

<article {{ $attributes->class(['exam-foundation-stat-card']) }} data-tone="{{ $tone }}">
    <div class="flex items-start justify-between gap-4">
        <div><p class="exam-stat-label">{{ $label }}</p><strong class="exam-stat-value">{{ $value }}</strong></div>
        @isset($icon)<span class="exam-stat-icon">{{ $icon }}</span>@endisset
    </div>
    @if($hint)<p class="exam-stat-hint">{{ $hint }}</p>@endif
</article>
