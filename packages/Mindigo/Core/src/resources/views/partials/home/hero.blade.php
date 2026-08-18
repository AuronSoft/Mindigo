{{-- New Home Hero --}}
<section class="relative overflow-hidden bg-[#f7c86f] px-5 pb-0 sm:px-8 lg:px-12">
    <span class="pointer-events-none absolute left-[7%] top-[18%] h-3 w-3 rounded-full bg-orange-500"></span>
    <span class="pointer-events-none absolute right-[5%] top-[20%] rotate-12 text-4xl font-black text-white/70">〽</span>
    <svg class="pointer-events-none absolute right-[4.5%] top-[19%] z-10 h-10 w-24 rotate-12" viewBox="0 0 96 40" fill="none" aria-hidden="true">
        <rect width="96" height="40" fill="#f7c86f"/>
        <path d="M3 28 15 9l12 21L40 8l13 21L66 9l13 20 14-18" stroke="#ead5a4" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <span class="pointer-events-none absolute right-[2%] bottom-[16%] h-5 w-5 rounded-full border-4 border-blue-400"></span>

    <div class="mx-auto grid w-full max-w-7xl items-center gap-4 lg:min-h-[calc(100svh-4.75rem)] lg:grid-cols-[0.9fr_1.1fr] 2xl:max-w-[1480px] 2xl:grid-cols-[0.92fr_1.08fr]">
        <div class="relative z-10 max-w-[580px] self-start pb-12 pt-24 lg:translate-y-16 lg:pb-12 lg:pt-28 2xl:max-w-[650px] 2xl:translate-y-32">
            <h1 class="relative z-10 text-[3.1rem] font-black leading-[1.01] tracking-[-0.045em] text-slate-950 sm:text-[3.85rem] lg:-mt-10 lg:text-[4.25rem] 2xl:text-[4.75rem]">
                <span class="block whitespace-nowrap">
                    @lang('core::app.home_redesign.hero_line_1')
                    <span class="relative -ml-1 inline-block rounded-[2.5rem] bg-[#ffe3a6] px-4 pb-2 pt-0.5 text-orange-600">
                        <svg class="pointer-events-none absolute -top-[7.25rem] left-1/2 h-24 w-32 translate-x-[-92%] text-slate-900" viewBox="0 0 128 96" fill="none" aria-hidden="true">
                            <path d="M50 91c-16 3-30 0-30-13 0-12 13-20 24-14 11 6 7 18-2 20-12 3-24-8-20-21 5-16 30-20 49-32" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="5 5"/>
                            <path d="m66 27 36-18-11 38-11-17-14-3Z" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="m80 30 22-21" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                        </svg>
                        <span class="absolute -top-6 left-1/2 z-20 inline-flex -translate-x-1/2 whitespace-nowrap rounded-full bg-[#ffe3a6] px-7 py-2.5 text-sm font-black tracking-normal text-slate-900">
                            1K+ @lang('core::app.home_redesign.courses')
                        </span>
                        @lang('core::app.home_redesign.hero_highlight')
                    </span>
                </span>
                <span class="block">@lang('core::app.home_redesign.hero_line_2')</span>
                <span class="block">@lang('core::app.home_redesign.hero_line_3')</span>
            </h1>
            <p class="mt-8 max-w-[480px] text-base font-semibold leading-7 text-slate-700 2xl:max-w-[560px] 2xl:text-lg 2xl:leading-8">@lang('core::app.home_redesign.hero_description')</p>
            <a href="{{ route('courses.index') }}" class="mt-7 inline-flex rounded-full bg-orange-500 px-8 py-3.5 text-base font-black text-white shadow-[0_5px_0_#c2410c] transition hover:-translate-y-1 hover:bg-orange-400 hover:shadow-[0_8px_0_#c2410c] 2xl:px-9 2xl:py-4 2xl:text-lg">@lang('core::app.home_redesign.enroll')</a>
        </div>

        <div class="relative -ml-8 h-[390px] w-[calc(100%+2rem)] self-end sm:h-[520px] lg:-ml-10 lg:h-[600px] lg:w-[760px] 2xl:-ml-14 2xl:h-[680px] 2xl:w-[850px]">
            <img src="{{ asset('image/home/hero-learning-studio.png') }}"
                alt="@lang('core::app.home_redesign.illustration_alt')"
                class="block h-full w-full object-contain object-bottom"
                width="1291" height="998">
        </div>
    </div>
</section>
