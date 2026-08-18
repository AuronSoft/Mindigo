{{-- Learning community section --}}
<section class="relative -mt-px overflow-hidden bg-[#fbfdf9] px-5 py-16 sm:px-8 lg:flex lg:min-h-screen lg:items-center lg:px-12 lg:py-14">
    <span class="pointer-events-none absolute -right-8 top-36 h-24 w-36 rotate-12 rounded-[48%] bg-[#fff1d2]" aria-hidden="true"></span>
    <span class="pointer-events-none absolute left-[7%] top-[34%] h-3 w-12 -rotate-12 border-y-4 border-blue-400" aria-hidden="true"></span>
    <span class="pointer-events-none absolute bottom-[28%] right-[7%] text-3xl font-black text-orange-500" aria-hidden="true">✦</span>
    <span class="pointer-events-none absolute bottom-[20%] left-[8%] h-4 w-4 rounded-full border-[3px] border-fuchsia-400" aria-hidden="true"></span>

    <div class="mx-auto w-full max-w-7xl text-center 2xl:max-w-[1480px]">
        <div class="mx-auto max-w-4xl">
            <h2 class="text-[3rem] font-black leading-[1.01] tracking-[-0.045em] text-slate-950 sm:text-[3.8rem] lg:text-[4.35rem]">
                <span class="block">@lang('core::app.testimonials.community_title_prefix')</span>
                <span class="inline-flex flex-wrap justify-center gap-x-[.22em]">
                    <span class="relative inline-block">
                        @lang('core::app.testimonials.community_title_accent')
                        <svg class="absolute -bottom-4 left-0 h-5 w-full text-orange-500" viewBox="0 0 360 24" fill="none" preserveAspectRatio="none" aria-hidden="true">
                            <path d="M5 15c55-19 104 7 159-4 55-10 105 7 190-3" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span>@lang('core::app.testimonials.community_title_suffix')</span>
                </span>
            </h2>

            <p class="mx-auto mt-8 max-w-2xl text-sm font-medium leading-6 text-slate-600 sm:text-base sm:leading-7">
                @lang('core::app.testimonials.community_description')
            </p>
        </div>

        <div class="relative mx-auto mt-8 h-[310px] w-full max-w-6xl sm:h-[430px] lg:mt-5 lg:h-[500px]">
            <img
                src="{{ asset('image/learning-community-map.png') }}"
                alt="@lang('core::app.testimonials.community_map_alt')"
                class="absolute inset-0 h-full w-full object-contain"
                loading="lazy"
                decoding="async"
            >

            <div class="pointer-events-none absolute left-1/2 top-[45%] flex -translate-x-1/2 -translate-y-1/2 flex-col items-center text-center text-slate-950" aria-hidden="true">
                <strong class="text-[2.55rem] font-black leading-[.88] tracking-[-0.055em] drop-shadow-[0_2px_0_rgba(255,255,255,.28)] sm:text-[3.75rem] lg:text-[4.35rem]">
                    10K<span class="text-[.72em] align-[.12em]">+</span>
                </strong>
                <span class="mt-2 text-[1.55rem] font-black leading-none tracking-[-0.045em] drop-shadow-[0_2px_0_rgba(255,255,255,.28)] sm:mt-3 sm:text-[2.25rem] lg:text-[2.65rem]">
                    @lang('core::app.testimonials.learners')
                </span>
                <span class="mt-4 h-1 w-12 rounded-full bg-orange-500/90 sm:mt-5 sm:w-16"></span>
            </div>
        </div>

        <a href="{{ route('login', [], false) }}" class="relative z-20 -mt-2 inline-flex rounded-full bg-green-500 px-9 py-4 text-sm font-black text-white shadow-[0_6px_0_#15803d] transition hover:-translate-y-0.5 hover:bg-green-400 hover:shadow-[0_8px_0_#15803d] active:translate-y-0.5 active:bg-green-600 active:shadow-[0_3px_0_#15803d] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-green-200 sm:text-base">
            @lang('core::app.testimonials.join_now')
        </a>
    </div>
</section>
