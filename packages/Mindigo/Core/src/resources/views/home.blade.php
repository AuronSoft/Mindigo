@extends('core::layouts.home')

@section('content')

<div class="min-h-screen bg-white flex flex-col">
    @include('core::partials.home.navbar')

    {{-- Các section trang chủ --}}
    <div id="home-sections">
        @include('core::partials.home.hero')
        @include('core::partials.home.trust')
        @include('core::partials.home.feature-ai')
        @include('core::partials.home.feature-personalize')
        @include('core::partials.home.feature-virtual-exam')
        @include('core::partials.home.feature-anytime')
        @include('core::partials.home.testimonials')
    </div>

    {{-- Section contact --}}
    @include('core::partials.home.contact')

    {{-- CTA + Footer luôn hiển thị --}}
    @include('core::partials.home.cta-banner')
</div>

<script>
    document.getElementById('btn-contact').addEventListener('click', function(e) {
        e.preventDefault();

        const homeSections = document.getElementById('home-sections');
        const contactSection = document.getElementById('section-contact');
        const isContact = !contactSection.classList.contains('hidden');

        if (isContact) {
            // Đang ở contact → về trang chủ
            contactSection.classList.add('hidden');
            homeSections.classList.remove('hidden');
            this.classList.remove('bg-green-50', 'text-green-600');
        } else {
            // Vào contact
            homeSections.classList.add('hidden');
            contactSection.classList.remove('hidden');
            this.classList.add('bg-green-50', 'text-green-600');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
</script>

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
    animation: marquee 30s linear infinite;
}
</style>

@endsection