{{-- Trust section --}}
<section class="py-20 px-10 border-t border-gray-100 bg-white">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-14">
            <p class="text-green-600 font-black text-3xl leading-snug">@lang('core::app.trust.heading_1')</p>
            <p class="text-green-600 font-black text-3xl leading-snug">@lang('core::app.trust.heading_2')</p>
        </div>
        <div class="overflow-hidden w-full mb-20">
            <div class="animate-marquee">
                @foreach([
                    ['AJC', asset('image/AJC.png')],
                    ['ĐHQGHN', asset('image/ĐHQGHN.png')],
                    ['FTU', asset('image/FTU.png')],
                    ['HCMUT', asset('image/HCMUT.png')],
                    ['HNUE', asset('image/HNUE.png')],
                    ['HUCE', asset('image/HUCE.png')],
                    ['HUST', asset('image/HUST.png')],
                    ['NEU', asset('image/NEU.png')],
                    ['PTIT', asset('image/PTIT.png')],
                    ['UEH', asset('image/UEH.png')],
                    ['UTT', asset('image/UTT.png')],
                    ['VNU-HCM', asset('image/VNU-HCM.png')],
                    ['AJC', asset('image/AJC.png')],
                    ['ĐHQGHN', asset('image/ĐHQGHN.png')],
                    ['FTU', asset('image/FTU.png')],
                    ['HCMUT', asset('image/HCMUT.png')],
                    ['HNUE', asset('image/HNUE.png')],
                    ['HUCE', asset('image/HUCE.png')],
                    ['HUST', asset('image/HUST.png')],
                    ['NEU', asset('image/NEU.png')],
                    ['PTIT', asset('image/PTIT.png')],
                    ['UEH', asset('image/UEH.png')],
                    ['UTT', asset('image/UTT.png')],
                    ['VNU-HCM', asset('image/VNU-HCM.png')],
                ] as [$name, $logo])
                <div class="flex flex-col items-center gap-2 group mx-6 shrink-0">
                    <div class="w-20 h-20 rounded-2xl bg-white border border-gray-100 shadow-sm flex items-center justify-center p-2 group-hover:shadow-md group-hover:border-green-200 transition-all">
                        <img src="{{ $logo }}" alt="{{ $name }}" class="w-full h-full object-contain" onerror="this.parentElement.innerHTML='<span class=\'text-xs font-black text-gray-600 text-center leading-tight\'>{{ $name }}</span>'">
                    </div>
                    <span class="text-sm font-black text-gray-600">{{ $name }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @php
            $trustCards = [
                [
                    'icon' => asset('images/home/certificate.svg'),
                    'title' => __('core::app.trust.student.title'),
                    'description' => __('core::app.trust.student.desc'),
                ],
                [
                    'icon' => asset('images/home/headphones.svg'),
                    'title' => __('core::app.trust.lecturer.title'),
                    'description' => __('core::app.trust.lecturer.desc'),
                ],
                [
                    'icon' => asset('images/home/training.svg'),
                    'title' => __('core::app.trust.training.title'),
                    'description' => __('core::app.trust.training.desc'),
                ],
            ];
        @endphp

        <div class="grid grid-cols-1 gap-7 md:grid-cols-3 lg:gap-10">
            @foreach($trustCards as $card)
                <article class="group relative min-h-72 overflow-hidden rounded-sm bg-[#fff8e8] px-7 pb-8 pt-7 transition duration-300 hover:-translate-y-1 hover:shadow-[0_18px_38px_rgba(72,57,29,0.10)] sm:px-8 sm:pb-9 sm:pt-8">
                    <svg class="pointer-events-none absolute -right-1 -top-1 h-16 w-16 text-[#efb94e] transition-transform duration-300 group-hover:rotate-6" viewBox="0 0 64 64" fill="none" aria-hidden="true">
                        <path d="M23 2C9 13 5 28 8 43M38 1C22 17 17 34 20 54" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                    </svg>

                    <img src="{{ $card['icon'] }}" alt="" class="h-16 w-16 object-contain" aria-hidden="true">

                    <h3 class="mt-5 max-w-xs text-xl font-black leading-[1.15] tracking-[-0.025em] text-slate-950 sm:text-[1.35rem]">
                        {{ $card['title'] }}
                    </h3>

                    <p class="mt-4 max-w-sm text-sm font-medium leading-6 text-slate-600">
                        {{ $card['description'] }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
