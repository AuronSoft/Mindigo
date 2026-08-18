@extends('core::layouts.home')

@section('content')

<div class="min-h-screen bg-white flex flex-col">
    @include('core::partials.home.navbar')

    {{-- Các section trang chủ --}}
    <div id="home-sections">
        @include('core::partials.home.hero')
        @include('core::partials.home.trust')
        @include('core::partials.home.feature-ai')
        @if(isset($featuredCourses) && $featuredCourses->isNotEmpty())
            <section class="border-y border-slate-100 bg-slate-50 px-5 py-12 sm:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="mb-5 flex items-end justify-between gap-4"><div><p class="text-xs font-black uppercase tracking-widest text-green-700">@lang('teacher-course::catalog.eyebrow')</p><h2 class="mt-1 text-2xl font-black text-slate-950">@lang('teacher-course::discovery.featured')</h2><p class="mt-1 text-sm font-semibold text-slate-500">@lang('teacher-course::discovery.featured_description')</p></div><a href="{{ route('courses.index') }}" class="shrink-0 text-sm font-black text-green-700 no-underline">@lang('core::app.home.cta_search')</a></div>
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">@foreach($featuredCourses->take(4) as $course) @include('teacher-course::catalog.partials.course-card', ['course' => $course]) @endforeach</div>
                </div>
            </section>
        @endif
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
            this.classList.remove('bg-blue-50', 'text-blue-600');
        } else {
            homeSections.classList.add('hidden');
            newsSection.classList.add('hidden');
            pricingSection.classList.add('hidden');
            contactSection.classList.remove('hidden');
            this.classList.add('bg-blue-50', 'text-blue-600');
            if (btnNews) btnNews.classList.remove('bg-blue-50', 'text-blue-600');
            if (btnPricing) btnPricing.classList.remove('bg-blue-50', 'text-blue-600');
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
            this.classList.remove('bg-blue-50', 'text-blue-600');
        } else {
            homeSections.classList.add('hidden');
            contactSection.classList.add('hidden');
            pricingSection.classList.add('hidden'); 
            newsSection.classList.remove('hidden');
            this.classList.add('bg-blue-50', 'text-blue-600');
            if (btnContact) btnContact.classList.remove('bg-blue-50', 'text-blue-600');
            if (btnPricing) btnPricing.classList.remove('bg-blue-50', 'text-blue-600');
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
            this.classList.remove('bg-blue-50', 'text-blue-600');
        } else {
            homeSections.classList.add('hidden');
            contactSection.classList.add('hidden');
            newsSection.classList.add('hidden');
            pricingSection.classList.remove('hidden');
            this.classList.add('bg-blue-50', 'text-blue-600');
            if (btnContact) btnContact.classList.remove('bg-blue-50', 'text-blue-600');
            if (btnNews) btnNews.classList.remove('bg-blue-50', 'text-blue-600');
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

    document.querySelectorAll('[data-news-filter]').forEach(button => {
        button.addEventListener('click', () => filterNews(button.dataset.newsFilter));
    });

    // Typewriter
    (function() {
        const words = {{ Illuminate\Support\Js::from([
            __('core::app.hero.heading_2'),
            __('core::app.hero.heading_4'),
            __('core::app.hero.heading_5'),
        ]) }};
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
@keyframes heroDemoCursor {
    0%, 8% { opacity: 0; transform: translate(110px, 78px); }
    12%, 25% { opacity: 1; transform: translate(110px, 78px); }
    34%, 47% { opacity: 1; transform: translate(535px, 78px); }
    58%, 70% { opacity: 1; transform: translate(455px, 335px); }
    80%, 92% { opacity: 1; transform: translate(130px, 128px); }
    100% { opacity: 0; transform: translate(130px, 128px); }
}
@keyframes heroDemoClick {
    0%, 10%, 25%, 32%, 47%, 56%, 70%, 78%, 92%, 100% { opacity: 0; transform: scale(.4); }
    14%, 36%, 60%, 82% { opacity: .75; transform: scale(1); }
}
@keyframes heroUploadState {
    0%, 10%, 100% { border-color: #86efac; background: #f0fdf4; transform: scale(1); }
    14%, 30% { border-color: #60a5fa; background: #eff6ff; transform: scale(1.01); }
}
@keyframes heroProcessingState {
    0%, 30%, 52%, 100% { opacity: 0; transform: translate(-50%, 8px) scale(.96); }
    34%, 48% { opacity: 1; transform: translate(-50%, 0) scale(1); }
}
@keyframes heroLessonUpdate {
    0%, 48%, 100% { opacity: .55; transform: translateY(3px); border-color: #f3f4f6; }
    56%, 90% { opacity: 1; transform: translateY(0); border-color: #fde68a; background: #fffbeb; }
}
@keyframes heroPublishState {
    0%, 48%, 100% { color: #64748b; border-color: #e2e8f0; background: #f8fafc; }
    56%, 90% { color: #15803d; border-color: #bbf7d0; background: #dcfce7; }
}
@keyframes heroSaveAction {
    0%, 77%, 100% { transform: translateY(0); filter: brightness(1); }
    82%, 92% { transform: translateY(2px); filter: brightness(.95); box-shadow: 0 1px 0 #15803d; }
}
@keyframes heroSaveToast {
    0%, 80%, 100% { opacity: 0; transform: translate(-50%, 10px) scale(.96); }
    84%, 96% { opacity: 1; transform: translate(-50%, 0) scale(1); }
}
@keyframes heroPhoneTyping {
    0%, 8% { width: 0; border-right-color: #16a34a; }
    42%, 78% { width: 100%; border-right-color: #16a34a; }
    88%, 100% { width: 100%; border-right-color: transparent; }
}
@keyframes heroPhoneTap {
    0%, 42%, 100% { opacity: 0; transform: translate(78px, 176px) scale(.5); }
    48%, 58% { opacity: .8; transform: translate(78px, 176px) scale(1); }
    68%, 78% { opacity: .8; transform: translate(78px, 199px) scale(1); }
    84% { opacity: 0; transform: translate(78px, 199px) scale(1.35); }
}
@keyframes heroPhoneOption {
    0%, 58%, 100% { background: #fff; color: #4b5563; }
    68%, 86% { background: #f0fdf4; color: #16a34a; }
}
@keyframes heroPhoneOptionInitial {
    0%, 58%, 100% { background: #f0fdf4; color: #16a34a; }
    68%, 86% { background: #fff; color: #4b5563; }
}
@keyframes heroPhoneCheckIn {
    0%, 58%, 100% { opacity: 0; transform: scale(.4); }
    68%, 86% { opacity: 1; transform: scale(1); }
}
@keyframes heroPhoneCheckOut {
    0%, 58%, 100% { opacity: 1; transform: scale(1); }
    68%, 86% { opacity: 0; transform: scale(.4); }
}
.hero-demo-cursor { animation: heroDemoCursor 10s ease-in-out infinite; }
.hero-demo-click { animation: heroDemoClick 10s ease-out infinite; }
.hero-upload-zone { animation: heroUploadState 10s ease-in-out infinite; }
.hero-processing { opacity: 0; animation: heroProcessingState 10s ease-in-out infinite; }
.hero-lesson-two { animation: heroLessonUpdate 10s ease-in-out infinite; }
.hero-publish-state { animation: heroPublishState 10s ease-in-out infinite; }
.hero-save-action { animation: heroSaveAction 10s ease-in-out infinite; }
.hero-save-toast { opacity: 0; animation: heroSaveToast 10s ease-in-out infinite; }
.hero-phone-typing { border-right: 1px solid #16a34a; animation: heroPhoneTyping 8s steps(30, end) infinite; }
.hero-phone-tap { left: 0; top: 0; animation: heroPhoneTap 8s ease-in-out infinite; }
.hero-phone-option-0 { animation: heroPhoneOptionInitial 8s ease-in-out infinite; }
.hero-phone-option-1 { animation: heroPhoneOption 8s ease-in-out infinite; }
.hero-phone-option-0 .hero-phone-option-label { animation: heroPhoneOptionInitial 8s ease-in-out infinite; }
.hero-phone-option-1 .hero-phone-option-label { animation: heroPhoneOption 8s ease-in-out infinite; }
.hero-phone-option-0 .hero-phone-option-check { animation: heroPhoneCheckOut 8s ease-in-out infinite; }
.hero-phone-option-1 .hero-phone-option-check { animation: heroPhoneCheckIn 8s ease-in-out infinite; }
@keyframes marquee {
    0%   { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
.animate-marquee {
    display: flex;
    width: max-content;
    animation: marquee 30s linear infinite;
}
@media (prefers-reduced-motion: reduce) {
    .hero-demo-cursor, .hero-demo-click, .hero-upload-zone, .hero-processing, .hero-lesson-two, .hero-publish-state, .hero-save-action, .hero-save-toast, .hero-phone-typing, .hero-phone-tap, .hero-phone-option-0, .hero-phone-option-1, .hero-phone-option-label, .hero-phone-option-check { animation: none !important; }
    .hero-demo-cursor, .hero-processing, .hero-save-toast, .hero-phone-tap { display: none !important; }
    .hero-phone-typing { width: 100%; border-right-color: transparent; }
    .hero-lesson-two { opacity: 1; transform: none; }
    .hero-publish-state { color: #15803d; border-color: #bbf7d0; background: #dcfce7; }
}
</style>

@endsection
