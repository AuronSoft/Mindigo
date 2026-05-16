@extends('core::layouts.home')

@section('content')

<div class="min-h-screen bg-white flex flex-col">
    @include('core::partials.home.navbar')
    @include('core::partials.home.hero')
    @include('core::partials.home.trust')
    @include('core::partials.home.feature-ai')
    @include('core::partials.home.feature-personalize')
    @include('core::partials.home.feature-virtual-exam')
    @include('core::partials.home.feature-anytime')
    @include('core::partials.home.testimonials')
    @include('core::partials.home.cta-banner')
</div>

<style>
@keyframes floatStar {
    0%,100% { transform: translateY(0) rotate(0deg); }
    50%      { transform: translateY(-12px) rotate(20deg); }
}

@keyframes floatBadge {
    0%, 100% { transform: translateY(0px); }
    50%       { transform: translateY(-6px); }
}

@keyframes marquee {
    0%   { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
.animate-marquee {
    display: flex;
    width: max-content;
}
</style>

@php
    $typewriterWords = [
        __('core::app.hero.heading_2'),
        __('core::app.hero.heading_3'),
        __('core::app.hero.heading_4'),
        __('core::app.hero.heading_5'),
    ];
@endphp

<script>
(function(){
    const words = @json($typewriterWords);
    let wordIndex = 0;
    let charIndex = 0;
    let deleting = false;
    const el = document.getElementById('typewriter');
    if (!el) return;

    function type() {
        const current = words[wordIndex];
        if (!deleting) {
            el.textContent = current.substring(0, charIndex + 1);
            charIndex++;
            if (charIndex === current.length) {
                setTimeout(() => { deleting = true; type(); }, 1500);
                return;
            }
            setTimeout(type, 80);
        } else {
            el.textContent = current.substring(0, charIndex - 1);
            charIndex--;
            if (charIndex === 0) {
                deleting = false;
                wordIndex = (wordIndex + 1) % words.length;
                setTimeout(type, 300);
                return;
            }
            setTimeout(type, 40);
        }
    }
    type();
})();
</script>

@endsection