@extends('core::layouts.home')

@section('content')
<div class="min-h-screen bg-white text-gray-900">
    <header class="sticky top-0 z-40 border-b border-gray-100 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-10">
            <a href="{{ route('home', [], false) }}" class="flex items-center gap-2 no-underline">
                <svg width="36" height="36" viewBox="0 0 200 220" fill="none" aria-hidden="true">
                    <path d="M48 160 L22 148 L38 158 L16 152 L35 164" fill="#15803d" stroke="#14532d" stroke-width="1"/>
                    <circle cx="105" cy="145" r="90" fill="#22c55e" stroke="#14532d" stroke-width="3"/>
                    <ellipse cx="115" cy="185" rx="55" ry="38" fill="#86efac" stroke="#14532d" stroke-width="2"/>
                    <path d="M95 58 Q85 20 105 8 Q118 22 112 58" fill="#16a34a" stroke="#14532d" stroke-width="2.5" stroke-linejoin="round"/>
                    <path d="M108 55 Q100 18 118 10 Q128 26 120 56" fill="#22c55e" stroke="#14532d" stroke-width="2" stroke-linejoin="round"/>
                    <path d="M52 118 L95 108 L88 128 Z" fill="#14532d"/>
                    <path d="M148 118 L108 108 L114 128 Z" fill="#14532d"/>
                    <circle cx="82" cy="135" r="20" fill="white" stroke="#14532d" stroke-width="2"/>
                    <circle cx="86" cy="138" r="12" fill="#14532d"/>
                    <circle cx="128" cy="135" r="20" fill="white" stroke="#14532d" stroke-width="2"/>
                    <circle cx="132" cy="138" r="12" fill="#14532d"/>
                    <path d="M85 158 Q105 148 130 158 L118 175 Q105 180 92 175 Z" fill="#f59e0b" stroke="#14532d" stroke-width="2"/>
                </svg>
                <span class="text-xl font-black tracking-tight text-green-600">Auronsoft</span>
            </a>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-1 rounded-xl bg-gray-100 p-1">
                    <a href="{{ route('lang.switch', ['locale' => 'vi'], false) }}"
                       class="rounded-lg px-3 py-1.5 text-xs font-black transition {{ app()->getLocale() === 'vi' ? 'bg-white text-green-600 shadow-sm' : 'text-gray-400 hover:text-gray-600' }}">
                        VI
                    </a>
                    <a href="{{ route('lang.switch', ['locale' => 'en'], false) }}"
                       class="rounded-lg px-3 py-1.5 text-xs font-black transition {{ app()->getLocale() === 'en' ? 'bg-white text-green-600 shadow-sm' : 'text-gray-400 hover:text-gray-600' }}">
                        EN
                    </a>
                </div>
                <a href="{{ route('login', [], false) }}" class="rounded-xl bg-green-500 px-5 py-2.5 text-sm font-black text-white shadow-[0_4px_0_#15803d] transition hover:-translate-y-0.5 hover:bg-green-400">
                    @lang('core::app.navbar.login')
                </a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-6 py-16 lg:px-10">
        <section class="mb-10">
            <span class="mb-4 inline-block rounded-full bg-green-50 px-4 py-1.5 text-xs font-bold text-green-700">@lang('blog::app.news.section_title')</span>
            <h1 class="mb-3 text-4xl font-black text-gray-900">@lang('blog::app.news.heading') <span class="text-green-500">@lang('blog::app.news.heading_highlight')</span></h1>
            <p class="text-sm text-gray-500">@lang('blog::app.news.description')</p>
        </section>

        <div class="mb-10 flex flex-wrap gap-2" id="news-filters">
            @foreach(['all' => __('blog::app.news.filters.all'), 'VnExpress' => __('blog::app.news.filters.vnexpress'), 'thanhnien' => __('blog::app.news.filters.thanhnien'), 'tuoitre' => __('blog::app.news.filters.tuoitre')] as $val => $label)
                <button data-news-filter="{{ $val }}"
                    class="news-filter-btn rounded-xl px-4 py-2 text-sm font-bold transition {{ ($source ?? 'all') === $val ? 'bg-green-500 text-white shadow-[0_3px_0_#15803d]' : 'bg-gray-100 text-gray-500 hover:bg-green-50 hover:text-green-600' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div id="news-articles">
            @include('blog::news-partial', ['newsfeatured' => $newsfeatured, 'newsArticles' => $newsArticles, 'source' => $source ?? 'all'])
        </div>
    </main>
</div>

<script>
    function filterNews(source) {
        document.querySelectorAll('.news-filter-btn').forEach(btn => {
            if (btn.dataset.newsFilter === source) {
                btn.className = 'news-filter-btn rounded-xl px-4 py-2 text-sm font-bold transition bg-green-500 text-white shadow-[0_3px_0_#15803d]';
            } else {
                btn.className = 'news-filter-btn rounded-xl px-4 py-2 text-sm font-bold transition bg-gray-100 text-gray-500 hover:bg-green-50 hover:text-green-600';
            }
        });

        fetch(`{{ route('news.partial', [], false) }}?source=${encodeURIComponent(source)}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('news-articles').innerHTML = html;
            });
    }

    document.querySelectorAll('[data-news-filter]').forEach(button => {
        button.addEventListener('click', () => filterNews(button.dataset.newsFilter));
    });
</script>
@endsection
