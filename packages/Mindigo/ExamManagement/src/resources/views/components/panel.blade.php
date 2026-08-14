@props(['title' => null, 'description' => null])

<section {{ $attributes->class(['exam-panel']) }}>
    @if($title || $description || isset($actions))
        <header class="exam-panel-header">
            <div>
                @if($title)<h2 class="exam-panel-title">{{ $title }}</h2>@endif
                @if($description)<p class="exam-panel-description">{{ $description }}</p>@endif
            </div>
            @isset($actions)<div class="exam-actions">{{ $actions }}</div>@endisset
        </header>
    @endif
    <div class="exam-panel-body">{{ $slot }}</div>
</section>
