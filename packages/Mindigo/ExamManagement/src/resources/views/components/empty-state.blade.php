@props(['title', 'description' => null])

<div {{ $attributes->class(['exam-empty-state']) }}>
    @isset($icon)<span class="exam-empty-icon">{{ $icon }}</span>@endisset
    <div><h3 class="exam-empty-title">{{ $title }}</h3>@if($description)<p class="exam-empty-description">{{ $description }}</p>@endif</div>
    @isset($actions)<div class="exam-actions">{{ $actions }}</div>@endisset
</div>
