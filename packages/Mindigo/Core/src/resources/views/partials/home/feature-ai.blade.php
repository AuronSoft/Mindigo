{{-- Private instructor section --}}
<section id="features" class="relative scroll-mt-20 overflow-hidden bg-[#fbfdf9] px-5 sm:px-8 lg:px-12">
    <svg class="pointer-events-none absolute left-[3.5%] top-12 h-20 w-10 -rotate-6 text-amber-400" viewBox="0 0 40 80" fill="none" aria-hidden="true">
        <path d="M24.5 7.5c-7.8-2.2-13.4 2.7-13 10.1.5 8.8 7.2 18.6 8.2 31.1.2 2.8 3.9 3.6 5.1.9 5-11.7 9.8-26.4 8.2-34.2-.8-4-3.6-6.8-8.5-7.9Z" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M20.8 64.3c1.8-1.8 5.6-1.8 7.2.4 1.7 2.4.3 6.6-3 7.1-3.2.5-6.5-4.9-4.2-7.5Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
    </svg>
    <span class="pointer-events-none absolute bottom-20 left-[6%] h-3 w-3 rounded-full bg-orange-500" aria-hidden="true"></span>
    <svg class="pointer-events-none absolute right-[4%] top-14 h-20 w-20 text-blue-400" viewBox="0 0 80 80" fill="none" aria-hidden="true">
        <path d="M16 56c10-21 21-27 31-17 8 8 1 17-8 12-9-5-3-18 14-19 7 0 12 2 16 6" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"/>
    </svg>

    <div class="mx-auto grid min-h-[610px] max-w-7xl items-center gap-10 py-16 lg:grid-cols-[0.88fr_1.12fr] lg:gap-4 lg:py-0">
        <div class="relative z-10 max-w-[570px] lg:pl-4">
            <h2 class="text-[3.1rem] font-black leading-[1.01] tracking-[-0.045em] text-slate-950 sm:text-[3.85rem] lg:text-[4.25rem]">
                <span class="block">@lang('core::app.home_redesign.private_title_1')</span>
                <span class="block">@lang('core::app.home_redesign.private_title_2')</span>
                <span class="relative mt-2 inline-flex -rotate-[1.5deg] rounded-[2.7rem_2.15rem_2.55rem_2rem] bg-[#e6bd8f] p-2 pr-3.5">
                    <span class="absolute -top-2 left-12 h-5 w-24 rounded-[50%] bg-[#e6bd8f]" aria-hidden="true"></span>
                    <span class="absolute -bottom-1 right-14 h-5 w-32 rounded-[50%] bg-[#e6bd8f]" aria-hidden="true"></span>
                    <span class="relative z-10 inline-flex skew-x-[-2deg] rounded-[2.2rem_1.75rem_2.05rem_1.65rem] bg-[#4f8f72] px-6 pb-2.5 pt-1.5 text-white shadow-[inset_0_-4px_0_rgba(15,23,42,0.16)]">@lang('core::app.home_redesign.private_highlight')</span>
                </span>
            </h2>

            <p class="mt-7 max-w-[500px] text-base font-semibold leading-7 text-slate-700">
                @lang('core::app.home_redesign.private_description')
            </p>

            <a href="{{ route('courses.index') }}" class="mt-7 inline-flex rounded-full bg-orange-500 px-8 py-3.5 text-base font-black text-white shadow-[0_5px_0_#c2410c] transition hover:-translate-y-1 hover:bg-orange-400 hover:shadow-[0_8px_0_#c2410c] active:translate-y-1 active:shadow-none">
                @lang('core::app.home_redesign.enroll')
            </a>
        </div>

        <div class="relative flex min-h-[390px] items-end justify-center self-end sm:min-h-[500px] lg:min-h-[610px]">
            <span class="pointer-events-none absolute bottom-7 left-[3%] h-32 w-[86%] rounded-[50%] bg-[#d9f4e9] opacity-80 blur-[1px]" aria-hidden="true"></span>
            <img
                src="{{ asset('image/home/private-class-instructors.png') }}"
                alt="@lang('core::app.home_redesign.private_illustration_alt')"
                class="private-class-illustration relative z-10 block w-full max-w-[760px] object-contain object-bottom"
            >
        </div>
    </div>
</section>

<style>
@keyframes privateClassFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

.private-class-illustration {
    animation: privateClassFloat 5s ease-in-out infinite;
}

@media (prefers-reduced-motion: reduce) {
    .private-class-illustration { animation: none; }
}
</style>
