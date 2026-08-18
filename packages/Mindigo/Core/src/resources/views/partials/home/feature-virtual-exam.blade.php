{{-- Instant test results section --}}
<section class="relative -mt-px overflow-hidden bg-[#fbfdf9] px-5 sm:px-8 lg:px-12">
    <span class="pointer-events-none absolute bottom-16 left-[4%] h-4 w-4 rounded-full border-[3px] border-orange-400" aria-hidden="true"></span>

    <div class="mx-auto grid min-h-[620px] w-full max-w-7xl items-center gap-10 py-16 lg:min-h-screen lg:grid-cols-[0.86fr_1.14fr] lg:gap-12 lg:py-12 2xl:max-w-[1480px]">
        <div class="relative z-10 mx-auto w-full max-w-[520px] text-center lg:mx-0 lg:text-left">
            <svg class="pointer-events-none absolute -right-1 -top-14 h-20 w-20 text-amber-400 sm:right-5 lg:-right-5" viewBox="0 0 80 80" fill="none" aria-hidden="true">
                <path d="M18 42c-7.8-7.2-12.8-13.8-13.3-18.1-.2-2 1.1-2.7 2.8-1.4 3.8 2.9 8.3 9.5 12.6 17.9" stroke="currentColor" stroke-width="3.3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M35.5 33.5c-2.1-10.4-2.1-18.7.1-22.5 1-1.7 2.5-1.5 3.2.5 1.7 4.5 1.2 12.4-.1 21.5" stroke="currentColor" stroke-width="3.3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M51 39.5c5.6-9 11.2-15.2 15.3-16.8 1.9-.7 2.8.4 1.9 2.3-2 4.3-7.8 9.8-14.9 15.7" stroke="currentColor" stroke-width="3.3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>

            <h2 class="text-[2.85rem] font-black leading-[0.96] tracking-[-0.045em] text-slate-950 sm:text-[3.45rem] lg:text-[3.75rem]">
                <span class="block">@lang('core::app.home_redesign.test_title_1')</span>
                <span class="block">@lang('core::app.home_redesign.test_title_2')</span>
                <span class="relative mt-1 inline-flex -rotate-[1.5deg] p-1.5 sm:mt-2 sm:p-2">
                    <span class="absolute inset-x-0 bottom-0 top-1 rotate-2 rounded-[2.2rem_1.7rem_2.4rem_1.8rem] bg-[#f4d8b2]" aria-hidden="true"></span>
                    <span class="absolute -inset-x-1 inset-y-0 -rotate-1 rounded-[1.8rem_2.35rem_1.9rem_2.25rem] bg-[#e2b987]" aria-hidden="true"></span>
                    <span class="relative rounded-[1.35rem_1.8rem_1.45rem_1.7rem] bg-[#4f8f72] px-6 pb-2.5 pt-1.5 text-[0.82em] leading-none tracking-[-0.035em] text-white shadow-[inset_0_-5px_0_rgba(30,74,56,0.2),0_3px_0_rgba(111,73,37,0.12)] sm:px-8">
                        <span class="inline-block -rotate-1">@lang('core::app.home_redesign.test_highlight')</span>
                    </span>
                </span>
            </h2>

            <p class="mx-auto mt-7 max-w-lg text-base font-medium leading-7 text-slate-700 sm:text-lg sm:leading-8 lg:mx-0">
                @lang('core::app.home_redesign.test_description')
            </p>

            <a
                href="{{ route('login') }}"
                class="mt-8 inline-flex items-center justify-center rounded-full bg-orange-500 px-8 py-4 text-sm font-black text-white shadow-[0_6px_0_#c2410c] transition duration-200 hover:-translate-y-0.5 hover:bg-orange-400 hover:shadow-[0_8px_0_#c2410c] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-orange-200"
            >
                @lang('core::app.home_redesign.enroll')
            </a>
        </div>

        <div class="relative flex min-h-[360px] items-center justify-center sm:min-h-[460px] lg:min-h-[540px] lg:justify-end">
            <img
                src="{{ asset('image/home/instant-test-results.png') }}"
                alt="@lang('core::app.home_redesign.test_illustration_alt')"
                class="relative z-10 h-auto w-full max-w-[500px] object-contain sm:max-w-[570px] lg:max-w-[610px]"
                loading="lazy"
                decoding="async"
            >
        </div>
    </div>
</section>
