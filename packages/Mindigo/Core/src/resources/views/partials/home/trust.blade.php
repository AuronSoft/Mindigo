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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                        <svg width="20" height="20" fill="none" viewBox="0 0 20 20"><circle cx="10" cy="7" r="4" stroke="#16a34a" stroke-width="1.5"/><path d="M2 17c0-3.866 3.582-7 8-7s8 3.134 8 7" stroke="#16a34a" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </div>
                    <span class="font-black text-gray-800 text-lg">@lang('core::app.trust.student.title')</span>
                </div>
                <p class="text-gray-500 text-sm leading-relaxed mb-5">@lang('core::app.trust.student.desc')</p>
                <a href="#" class="text-green-600 font-black text-sm flex items-center gap-1 hover:gap-2 transition-all">@lang('core::app.trust.student.cta')<svg width="14" height="14" fill="none" viewBox="0 0 14 14"><path d="M3 7h8M8 4l3 3-3 3" stroke="#16a34a" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
            </div>
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                        <svg width="20" height="20" fill="none" viewBox="0 0 20 20"><rect x="2" y="3" width="16" height="12" rx="2" stroke="#16a34a" stroke-width="1.5"/><path d="M7 18h6M10 15v3" stroke="#16a34a" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </div>
                    <span class="font-black text-gray-800 text-lg">@lang('core::app.trust.lecturer.title')</span>
                </div>
                <p class="text-gray-500 text-sm leading-relaxed mb-5">@lang('core::app.trust.lecturer.desc')</p>
                <a href="#" class="text-green-600 font-black text-sm flex items-center gap-1 hover:gap-2 transition-all">@lang('core::app.trust.lecturer.cta')<svg width="14" height="14" fill="none" viewBox="0 0 14 14"><path d="M3 7h8M8 4l3 3-3 3" stroke="#16a34a" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
            </div>
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                        <svg width="20" height="20" fill="none" viewBox="0 0 20 20"><path d="M3 17V8l7-5 7 5v9" stroke="#16a34a" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><rect x="7" y="11" width="6" height="6" rx="1" stroke="#16a34a" stroke-width="1.5"/></svg>
                    </div>
                    <span class="font-black text-gray-800 text-lg">@lang('core::app.trust.training.title')</span>
                </div>
                <p class="text-gray-500 text-sm leading-relaxed mb-5">@lang('core::app.trust.training.desc')</p>
                <a href="#" class="text-green-600 font-black text-sm flex items-center gap-1 hover:gap-2 transition-all">@lang('core::app.trust.training.cta')<svg width="14" height="14" fill="none" viewBox="0 0 14 14"><path d="M3 7h8M8 4l3 3-3 3" stroke="#16a34a" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
            </div>
        </div>
    </div>
</section>