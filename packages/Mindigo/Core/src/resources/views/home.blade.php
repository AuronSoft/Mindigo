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

    {{-- Section news --}}
    @php
        $newsArticlesAll = \Mindigo\BlogManagement\Models\NewsArticle::orderBy('published_at', 'desc')->get();
        $newsfeatured = $newsArticlesAll->first();
        $newsArticles = $newsArticlesAll->skip(1)->take(11);
    @endphp
    @include('blog::news')

    {{-- Section pricing --}}
    @include('core::partials.home.pricing')

    {{-- CTA + Footer luôn hiển thị --}}
    @include('core::partials.home.cta-banner')
</div>

<script>
    // Contact section toggle
    document.getElementById('btn-contact').addEventListener('click', function(e) {
        e.preventDefault();

        const homeSections = document.getElementById('home-sections');
        const contactSection = document.getElementById('section-contact');
        const newsSection = document.getElementById('section-news');
        const pricingSection = document.getElementById('section-pricing'); 
        const btnNews = document.getElementById('btn-news');
        const btnPricing = document.getElementById('btn-pricing'); 
        const isContact = !contactSection.classList.contains('hidden');

        if (isContact) {
            contactSection.classList.add('hidden');
            homeSections.classList.remove('hidden');
            this.classList.remove('bg-green-50', 'text-green-600');
        } else {
            homeSections.classList.add('hidden');
            newsSection.classList.add('hidden');
            pricingSection.classList.add('hidden');
            contactSection.classList.remove('hidden');
            this.classList.add('bg-green-50', 'text-green-600');
            if (btnNews) btnNews.classList.remove('bg-green-50', 'text-green-600');
            if (btnPricing) btnPricing.classList.remove('bg-green-50', 'text-green-600'); 
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    // News section toggle
    document.getElementById('btn-news').addEventListener('click', function(e) {
        e.preventDefault();

        const homeSections = document.getElementById('home-sections');
        const newsSection = document.getElementById('section-news');
        const contactSection = document.getElementById('section-contact');
        const pricingSection = document.getElementById('section-pricing'); 
        const btnContact = document.getElementById('btn-contact');
        const btnPricing = document.getElementById('btn-pricing'); 
        const isNews = !newsSection.classList.contains('hidden');

        if (isNews) {
            newsSection.classList.add('hidden');
            homeSections.classList.remove('hidden');
            this.classList.remove('bg-green-50', 'text-green-600');
        } else {
            homeSections.classList.add('hidden');
            contactSection.classList.add('hidden');
            pricingSection.classList.add('hidden'); 
            newsSection.classList.remove('hidden');
            this.classList.add('bg-green-50', 'text-green-600');
            if (btnContact) btnContact.classList.remove('bg-green-50', 'text-green-600');
            if (btnPricing) btnPricing.classList.remove('bg-green-50', 'text-green-600'); 
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    // Pricing section toggle
    document.getElementById('btn-pricing').addEventListener('click', function(e) {
        e.preventDefault();

        const homeSections = document.getElementById('home-sections');
        const pricingSection = document.getElementById('section-pricing');
        const contactSection = document.getElementById('section-contact');
        const newsSection = document.getElementById('section-news');
        const btnContact = document.getElementById('btn-contact');
        const btnNews = document.getElementById('btn-news');
        const isPricing = !pricingSection.classList.contains('hidden');

        if (isPricing) {
            pricingSection.classList.add('hidden');
            homeSections.classList.remove('hidden');
            this.classList.remove('bg-green-50', 'text-green-600');
        } else {
            homeSections.classList.add('hidden');
            contactSection.classList.add('hidden');
            newsSection.classList.add('hidden');
            pricingSection.classList.remove('hidden');
            this.classList.add('bg-green-50', 'text-green-600');
            if (btnContact) btnContact.classList.remove('bg-green-50', 'text-green-600');
            if (btnNews) btnNews.classList.remove('bg-green-50', 'text-green-600');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    // Filter news theo source
    function filterNews(source) {
        document.querySelectorAll('.news-filter-btn').forEach(btn => {
            if (btn.dataset.source === source) {
                btn.className = 'news-filter-btn text-sm font-bold px-4 py-2 rounded-xl transition bg-green-500 text-white shadow-[0_3px_0_#15803d]';
            } else {
                btn.className = 'news-filter-btn text-sm font-bold px-4 py-2 rounded-xl transition bg-gray-100 text-gray-500 hover:bg-green-50 hover:text-green-600';
            }
        });

        fetch(`/news/partial?source=${source}`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('news-articles').innerHTML = html;
            });
    }

    // Typewriter
    (function() {
        const words = ['study and review', 'create exams', 'practice smarter'];
        let wordIndex = 0, charIndex = 0, isDeleting = false;
        const el = document.getElementById('typewriter');
        if (!el) return;

        function type() {
            const current = words[wordIndex];
            el.textContent = isDeleting
                ? current.substring(0, charIndex - 1)
                : current.substring(0, charIndex + 1);
            isDeleting ? charIndex-- : charIndex++;

            let speed = isDeleting ? 60 : 100;
            if (!isDeleting && charIndex === current.length) {
                speed = 1500; isDeleting = true;
            } else if (isDeleting && charIndex === 0) {
                isDeleting = false;
                wordIndex = (wordIndex + 1) % words.length;
                speed = 400;
            }
            setTimeout(type, speed);
        }
        type();
    })();
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