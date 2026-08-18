{{-- New feature section --}}
<section id="features" class="relative -mt-0.5 overflow-hidden bg-[#d9f4e9] px-5 pb-20 pt-10 sm:px-8 lg:px-12 lg:pb-28 lg:pt-14">
    <span class="pointer-events-none absolute right-8 top-12 rotate-12 text-5xl font-black text-blue-500">〰</span>
    <svg class="pointer-events-none absolute right-0 top-0 z-10 h-24 w-28" viewBox="0 0 112 96" fill="none" aria-hidden="true">
        <rect width="112" height="96" fill="#d9f4e9"/>
        <path d="M107 3c-15 6-7 23-16 29-9 7-18-8-11-15 7-8 17 6 11 18-7 15-23 21-33 12-9-8-3-19 5-14 8 6-1 19-14 25-12 6-24 3-29-7" stroke="#4f83e5" stroke-width="4" stroke-linecap="round"/>
    </svg>
    <span class="pointer-events-none absolute left-9 top-16 h-4 w-4 rounded-full border-4 border-orange-400"></span>
    <div class="mx-auto max-w-7xl">
        <div class="grid items-center gap-10 lg:grid-cols-[1.05fr_0.95fr] lg:gap-14">
            <div class="relative max-w-[650px]">
                @php
                    $featureTitleLines = __('core::app.home_redesign.features_title_lines');
                @endphp
                <h2 class="text-[3.1rem] font-black leading-[1.01] tracking-[-0.045em] text-slate-950 sm:text-[3.85rem] lg:text-[4.25rem]">
                    <span class="relative block w-fit pr-12 sm:inline-flex sm:items-start sm:gap-1 sm:whitespace-nowrap sm:pr-0">
                        {{ $featureTitleLines[0] }}
                        <svg class="absolute right-0 top-0 h-14 w-10 sm:static sm:-mt-4 sm:h-[4.75rem] sm:w-14 sm:shrink-0" viewBox="0 0 64 92" fill="none" aria-hidden="true">
                            <path d="M32 17C18.6 17 9.7 27.1 10.5 40.7c.5 9.7 4.8 16.4 11.1 21.8 3.7 3.1 4.8 6.7 4.8 10.8h11.2c0-4.1 1.1-7.7 4.8-10.8 6.3-5.4 10.6-12.1 11.1-21.8C54.3 27.1 45.4 17 32 17Z" fill="#f4ce6a" stroke="#172033" stroke-width="2.5" stroke-linejoin="round"/>
                            <path d="M26.5 72.8h11v4.7h-11zM27.4 77.5h9.2v4.5h-9.2zM29.2 82h5.6l-1.3 4.4h-3L29.2 82Z" fill="#e7b94b" stroke="#172033" stroke-width="2" stroke-linejoin="round"/>
                            <path d="M23.9 40.8c1.8 4.7 4.7 7.4 8.1 7.4s6.3-2.7 8.1-7.4M32 48.2v21.5" stroke="#75501b" stroke-width="1.9" stroke-linecap="round"/>
                            <path d="M32 2.5v8M10.2 10.7l5.3 6M53.8 10.7l-5.3 6M1.5 35.5h7.8M54.7 35.5h7.8" stroke="#8a5b17" stroke-width="2.2" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span class="block">{{ $featureTitleLines[1] }}</span>
                    <span class="block">{{ $featureTitleLines[2] }}</span>
                </h2>
            </div>
            <div class="max-w-lg lg:justify-self-end">
                <p class="text-sm font-semibold leading-7 text-slate-700">@lang('core::app.home_redesign.features_description')</p>
                <a href="{{ route('courses.index') }}" class="mt-6 inline-flex rounded-full bg-blue-500 px-7 py-3.5 text-sm font-black text-white shadow-[0_5px_0_#9a3412] transition hover:-translate-y-1 hover:bg-blue-400 hover:shadow-[0_8px_0_#9a3412] active:translate-y-1 active:shadow-none">@lang('core::app.home_redesign.view_courses')</a>
            </div>
        </div>

        @php
            $featureIconFiles = [
                'Certificate.svg',
                'Headphones.svg',
                'Video.svg',
            ];
            $features = __('core::app.home_redesign.feature_cards');
        @endphp
        <div class="mt-16 grid w-full gap-7 md:grid-cols-3">
            @foreach($features as $index => $feature)
                <article class="group relative min-h-[280px] overflow-hidden rounded bg-[#fff9e8] p-5 transition duration-300 hover:-translate-y-2 hover:shadow-[0_20px_45px_rgba(15,23,42,0.14)]">
                    <svg class="pointer-events-none absolute right-0 top-0 z-10 h-20 w-24" viewBox="0 0 96 80" fill="none" aria-hidden="true">
                        <rect width="96" height="80" fill="#fff9e8"/>
                        <path d="M95 7C77 15 68 31 67 53M95 23c-10 6-16 16-17 30" stroke="#f4c767" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>
                    <span class="absolute right-4 top-3 text-5xl font-light text-orange-300">〽</span>
                    <span class="grid h-14 w-14 place-items-center">
                        <img src="{{ asset('images/home/'.$featureIconFiles[$index]) }}" alt="" class="h-[3.25rem] w-[3.25rem] object-contain" aria-hidden="true">
                    </span>
                    <h3 class="mt-5 max-w-[15rem] text-base font-black leading-[1.18] text-slate-950">{{ $feature['title'] }}</h3>
                    <p class="mt-3 text-xs font-medium leading-5 text-slate-600">{{ $feature['description'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
