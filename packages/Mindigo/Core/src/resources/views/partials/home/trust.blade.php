{{-- New feature section --}}
<section id="features" class="relative -mt-0.5 overflow-hidden bg-[#d9f4e9] px-5 pb-20 pt-10 sm:px-8 lg:px-12 lg:pb-28 lg:pt-14">
    <span class="pointer-events-none absolute right-8 top-12 rotate-12 text-5xl font-black text-blue-500">〰</span>
    <svg class="pointer-events-none absolute right-0 top-0 z-10 h-24 w-28" viewBox="0 0 112 96" fill="none" aria-hidden="true">
        <rect width="112" height="96" fill="#d9f4e9"/>
        <path d="M107 3c-15 6-7 23-16 29-9 7-18-8-11-15 7-8 17 6 11 18-7 15-23 21-33 12-9-8-3-19 5-14 8 6-1 19-14 25-12 6-24 3-29-7" stroke="#4f83e5" stroke-width="4" stroke-linecap="round"/>
    </svg>
    <span class="pointer-events-none absolute left-9 top-16 h-4 w-4 rounded-full border-4 border-orange-400"></span>
    <div class="mx-auto max-w-7xl">
        <div class="grid items-center gap-10 lg:grid-cols-[0.9fr_1.1fr]">
            <div class="relative max-w-[440px]">
                <svg class="absolute left-[15.25rem] -top-3 hidden h-[4.75rem] w-14 lg:block" viewBox="0 0 70 100" fill="none" aria-hidden="true">
                    <path d="M35 18C20 18 10 29 11 44c.5 10 5 17 12 23 4 3 5 7 5 11h14c0-4 1-8 5-11 7-6 11.5-13 12-23 1-15-9-26-24-26Z" fill="#f4ce6a" stroke="#172033" stroke-width="2.7" stroke-linejoin="round"/>
                    <path d="M28 77h14v5H28zM29 82h12v5H29zM32 87h6l-1.5 5h-3L32 87Z" fill="#e7b94b" stroke="#172033" stroke-width="2.3" stroke-linejoin="round"/>
                    <path d="M27 47c2 5 5 8 8 8s6-3 8-8M35 55v19" stroke="#75501b" stroke-width="2.1" stroke-linecap="round"/>
                    <path d="M35 2v9M11 12l6 7M59 12l-6 7M1 39h9M60 39h9" stroke="#75501b" stroke-width="2.3" stroke-linecap="round"/>
                </svg>
                <h2 class="max-w-[400px] text-[3.25rem] font-black leading-[0.94] tracking-[-0.045em] text-slate-950 sm:text-[3.75rem]">@lang('core::app.home_redesign.features_title')</h2>
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
