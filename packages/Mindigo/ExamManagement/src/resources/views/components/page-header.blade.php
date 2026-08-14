@props(['eyebrow' => null, 'title', 'description' => null])

<header {{ $attributes->class(['exam-page-header']) }}>
    <div class="min-w-0">
        @if($eyebrow)<p class="exam-eyebrow">{{ $eyebrow }}</p>@endif
        <h1 class="exam-page-title">{{ $title }}</h1>
        @if($description)<p class="exam-page-description">{{ $description }}</p>@endif
    </div>
    @isset($actions)<div class="exam-actions">{{ $actions }}</div>@endisset
</header>
