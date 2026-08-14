{{-- Anytime Anywhere section --}}
<section class="py-20 px-10 bg-white border-t border-gray-100">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col lg:flex-row items-center gap-20">

            {{-- LEFT: Desktop + Phone mockups --}}
            <div class="flex-1 relative flex items-center justify-center min-h-120">

                {{-- Dashed orbit circle --}}
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="w-95 h-95 rounded-full border-2 border-dashed border-green-200 opacity-60"></div>
                </div>

                {{-- Clock decoration --}}
                <div class="absolute top-2 right-16 z-30" style="animation:floatStar 3s ease-in-out infinite">
                    <div class="w-16 h-16 bg-white rounded-full shadow-xl border-4 border-gray-100 flex items-center justify-center relative">
                        <div class="w-12 h-12 rounded-full border-4 border-pink-300 bg-pink-50 flex items-center justify-center">
                            <div class="relative w-6 h-6">
                                <div class="absolute bottom-1/2 left-1/2 w-0.5 h-2.5 bg-gray-700 rounded-full origin-bottom" style="transform: translateX(-50%) rotate(-30deg)"></div>
                                <div class="absolute bottom-1/2 left-1/2 w-0.5 h-3 bg-gray-500 rounded-full origin-bottom" style="transform: translateX(-50%) rotate(120deg)"></div>
                                <div class="absolute top-1/2 left-1/2 w-1.5 h-1.5 bg-gray-700 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
                            </div>
                        </div>
                        <div class="absolute -top-2 -left-1 w-3 h-3 bg-pink-300 rounded-full border-2 border-white"></div>
                        <div class="absolute -top-2 -right-1 w-3 h-3 bg-pink-300 rounded-full border-2 border-white"></div>
                    </div>
                </div>

                {{-- Decorative dots --}}
                <div class="absolute top-12 left-12 w-3 h-3 bg-green-300 rounded-full opacity-60 pointer-events-none" style="animation:floatStar 3s ease-in-out infinite"></div>
                <div class="absolute bottom-10 left-16 w-2 h-2 bg-green-400 rounded-full opacity-50 pointer-events-none" style="animation:floatStar 4s .5s ease-in-out infinite"></div>
                <div class="absolute bottom-6 right-10 text-green-400 text-2xl pointer-events-none" style="animation:floatStar 4s ease-in-out infinite">✦</div>

                {{-- Desktop browser mockup --}}
                <div class="relative z-10 w-100">
                    <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
                        <div class="bg-gray-50 border-b border-gray-100 px-4 py-2.5 flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full bg-red-400"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-yellow-400"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-green-400"></div>
                            <div class="flex items-center gap-1.5 ml-2">
                                <div class="w-5 h-5 bg-green-500 rounded-md flex items-center justify-center">
                                    <span class="text-white text-[7px] font-black">M</span>
                                </div>
                                <span class="text-[9px] font-black text-gray-600">Auronsoft</span>
                            </div>
                            <div class="flex-1 bg-white rounded-md h-5 mx-2 border border-gray-200 flex items-center px-2">
                                <span class="text-[8px] text-gray-400">@lang('core::app.nav.url')</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg width="12" height="12" fill="none" viewBox="0 0 12 12"><circle cx="6" cy="4" r="2.5" stroke="#16a34a" stroke-width="1"/><path d="M2 11c0-2.209 1.79-4 4-4s4 1.791 4 4" stroke="#16a34a" stroke-width="1" stroke-linecap="round"/></svg>
                                </div>
                                <span class="text-[8px] font-bold text-gray-600 hidden sm:block">@lang('core::app.nav.username')</span>
                            </div>
                        </div>
                        <div class="flex" style="height: 290px;">
                            {{-- Left sidebar --}}
                            <div class="w-48 border-r border-gray-100 p-3 flex flex-col gap-3 bg-gray-50/50 overflow-hidden">
                                <div>
                                    <p class="text-[8px] font-black text-gray-700 mb-0.5">@lang('core::app.exam.subject')</p>
                                    <p class="text-[7px] text-gray-400">@lang('core::app.exam.topic')</p>
                                    <p class="text-[7px] text-gray-400 mb-2">@lang('core::app.exam.time_remaining')</p>
                                    <div class="text-green-600 font-black text-sm">24:47</div>
                                </div>
                                <div class="flex gap-1">
                                    <button class="flex-1 bg-green-500 text-white text-[7px] font-black py-1 rounded-md shadow-[0_1px_0_#15803d]">@lang('core::app.exam.submit')</button>
                                    <button class="flex-1 bg-blue-500 text-white text-[7px] font-black py-1 rounded-md">@lang('core::app.exam.calculator')</button>
                                </div>
                                <div>
                                    <p class="text-[7px] font-black text-gray-500 mb-1.5">@lang('core::app.exam.section_list')</p>
                                    <div class="space-y-1.5">
                                        <div class="bg-white rounded-lg p-2 border border-green-100 shadow-sm">
                                            <div class="flex items-center gap-1.5 mb-0.5">
                                                <div class="w-3.5 h-3.5 bg-green-500 rounded-full flex items-center justify-center"><svg width="7" height="7" fill="none" viewBox="0 0 7 7"><path d="M1.5 3.5l1.5 1.5 2.5-3" stroke="white" stroke-width="1" stroke-linecap="round"/></svg></div>
                                                <p class="text-[7px] font-black text-gray-700 leading-tight">@lang('core::app.exam.section_1_title')</p>
                                            </div>
                                            <p class="text-[6px] text-gray-400 ml-5">@lang('core::app.exam.section_1_progress')</p>
                                        </div>
                                        <div class="bg-white rounded-lg p-2 border border-gray-100">
                                            <div class="flex items-center gap-1.5 mb-0.5">
                                                <div class="w-3.5 h-3.5 bg-gray-200 rounded-full"></div>
                                                <p class="text-[7px] font-bold text-gray-500 leading-tight">@lang('core::app.exam.section_2_title')</p>
                                            </div>
                                            <p class="text-[6px] text-gray-400 ml-5">@lang('core::app.exam.section_2_progress')</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- Main content --}}
                            <div class="flex-1 overflow-hidden">
                                <div class="flex h-full">
                                    {{-- Questions --}}
                                    <div class="flex-1 p-3 overflow-hidden space-y-3">
                                        {{-- Question 1 --}}
                                        <div class="bg-gray-50 rounded-xl p-2.5 border border-gray-100">
                                            <div class="flex justify-between items-center mb-1.5">
                                                <p class="text-[8px] font-black text-gray-700">@lang('core::app.question.label', ['number' => 1])</p>
                                                <span class="text-[7px] text-gray-400">@lang('core::app.question.single_choice')</span>
                                            </div>
                                            <p class="text-[8px] text-gray-600 italic mb-2">She ___ to the store yesterday.</p>
                                            <div class="space-y-1">
                                                @foreach(['a. goes', 'b. going', 'c. went', 'd. go'] as $i => $opt)
                                                <div class="flex items-center gap-1.5 py-0.5 px-1.5 rounded-md {{ $i === 2 ? 'bg-green-50 border border-green-200' : '' }}">
                                                    <div class="w-3 h-3 rounded-full border {{ $i === 2 ? 'border-green-500 bg-green-500' : 'border-gray-300' }} flex items-center justify-center shrink-0">
                                                        @if($i === 2)<div class="w-1.5 h-1.5 bg-white rounded-full"></div>@endif
                                                    </div>
                                                    <span class="text-[7px] {{ $i === 2 ? 'text-green-700 font-bold' : 'text-gray-500' }}">{{ $opt }}</span>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        {{-- Question 2 --}}
                                        <div class="bg-gray-50 rounded-xl p-2.5 border border-gray-100">
                                            <div class="flex justify-between items-center mb-1.5">
                                                <p class="text-[8px] font-black text-gray-700">@lang('core::app.question.label', ['number' => 2])</p>
                                                <span class="text-[7px] text-gray-400">@lang('core::app.question.single_choice')</span>
                                            </div>
                                            <p class="text-[8px] text-gray-600 italic mb-2">They ___ very happy about the news.</p>
                                            <div class="space-y-1">
                                                @foreach(['a. was', 'b. were', 'c. is', 'd. been'] as $i => $opt)
                                                <div class="flex items-center gap-1.5 py-0.5 px-1.5 rounded-md {{ $i === 1 ? 'bg-green-50 border border-green-200' : '' }}">
                                                    <div class="w-3 h-3 rounded-full border {{ $i === 1 ? 'border-green-500 bg-green-500' : 'border-gray-300' }} flex items-center justify-center shrink-0">
                                                        @if($i === 1)<div class="w-1.5 h-1.5 bg-white rounded-full"></div>@endif
                                                    </div>
                                                    <span class="text-[7px] {{ $i === 1 ? 'text-green-700 font-bold' : 'text-gray-500' }}">{{ $opt }}</span>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        {{-- Question 3 preview --}}
                                        <div class="bg-gray-50 rounded-xl p-2.5 border border-gray-100">
                                            <p class="text-[8px] font-black text-gray-700 mb-1">@lang('core::app.question.label', ['number' => 3])</p>
                                            <p class="text-[8px] text-gray-600 italic">The cat is ___ the table.</p>
                                        </div>
                                    </div>
                                    {{-- Question navigator --}}
                                    <div class="w-24 border-l border-gray-100 p-2 bg-gray-50/50">
                                        <p class="text-[7px] font-black text-gray-400 mb-1.5">@lang('core::app.question.question_index')</p>
                                        <div class="grid grid-cols-4 gap-1 mb-2">
                                            @foreach(range(1,8) as $n)
                                            <div class="w-4.5 h-4.5 rounded text-[6px] font-bold flex items-center justify-center
                                                {{ $n <= 2 ? 'bg-green-500 text-white shadow-[0_1px_0_#15803d]' : 'bg-white border border-gray-200 text-gray-400' }}">{{ $n }}</div>
                                            @endforeach
                                        </div>
                                        <div class="bg-white rounded-lg p-1.5 border border-green-100">
                                            <p class="text-[6px] font-black text-green-700 mb-1">@lang('core::app.question.question_index')</p>
                                            <div class="flex gap-1 flex-wrap">
                                                @foreach(['1','2','3','4','5','6','7','8'] as $i => $n)
                                                <div class="w-4 h-4 rounded text-[6px] font-bold flex items-center justify-center
                                                    {{ $i < 2 ? 'bg-green-500 text-white' : ($i === 4 ? 'bg-orange-400 text-white' : 'bg-gray-100 text-gray-400') }}">{{ $n }}</div>
                                                @endforeach
                                            </div>
                                            <div class="mt-1.5 space-y-0.5">
                                                <div class="flex items-center gap-1"><div class="w-2 h-2 bg-green-500 rounded-sm"></div><span class="text-[6px] text-gray-500">@lang('core::app.question.answered')</span></div>
                                                <div class="flex items-center gap-1"><div class="w-2 h-2 bg-orange-400 rounded-sm"></div><span class="text-[6px] text-gray-500">@lang('core::app.question.flagged')</span></div>
                                                <div class="flex items-center gap-1"><div class="w-2 h-2 bg-gray-100 border border-gray-200 rounded-sm"></div><span class="text-[6px] text-gray-500">@lang('core::app.question.unanswered')</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Phone mockup (overlapping) --}}
                <div class="absolute bottom-0 right-4 z-20" style="transform: rotate(4deg); width: 120px;">
                    <div class="relative bg-white shadow-2xl" style="border: 2px solid #34d399; border-radius: 2rem;">
                        <div class="absolute top-10 h-5 rounded-l-full" style="left:-3px; width:3px; background:#86efac;"></div>
                        <div class="absolute top-18 h-7 rounded-l-full" style="left:-3px; width:3px; background:#86efac;"></div>
                        <div class="absolute top-14 h-8 rounded-r-full" style="right:-3px; width:3px; background:#86efac;"></div>
                        <div class="overflow-hidden bg-white" style="border-radius: calc(2rem - 2px);">
                            <div class="flex justify-center pt-2 pb-0">
                                <div class="bg-gray-900 rounded-full" style="height:12px; width:40px;"></div>
                            </div>
                            <div class="flex justify-between items-center px-3 py-0.5">
                                <span class="text-[7px] font-black text-gray-700">17:30</span>
                                <svg width="8" height="6" fill="#374151" viewBox="0 0 10 8"><rect x="0" y="4" width="2" height="4" rx="0.5"/><rect x="2.5" y="2.5" width="2" height="5.5" rx="0.5"/><rect x="5" y="1" width="2" height="7" rx="0.5"/><rect x="7.5" y="0" width="2" height="8" rx="0.5"/></svg>
                            </div>
                            <div class="bg-green-600 px-2 py-1.5">
                                <p class="text-white text-[7px] font-black">@lang('core::app.mobile.section_label')</p>
                                <div class="flex items-center gap-1 mt-0.5">
                                    <div class="flex-1 bg-green-700 rounded-full h-1">
                                        <div class="bg-white h-1 rounded-full" style="width: 40%"></div>
                                    </div>
                                    <span class="text-green-200 text-[6px]">@lang('core::app.mobile.progress')</span>
                                </div>
                            </div>
                            <div class="p-2 space-y-2">
                                <div>
                                    <p class="text-[7px] font-black text-gray-600 mb-0.5">@lang('core::app.mobile.question_label')</p>
                                    <p class="text-[6px] text-gray-500 italic mb-1.5">@lang('core::app.mobile.instruction')</p>
                                    <div class="space-y-1">
                                        <div class="bg-green-50 border border-green-300 rounded-md px-1.5 py-1 flex items-center gap-1">
                                            <div class="w-2.5 h-2.5 rounded-full bg-green-500 flex items-center justify-center"><div class="w-1 h-1 bg-white rounded-full"></div></div>
                                            <span class="text-[6px] text-green-700 font-bold">a. were</span>
                                        </div>
                                        <div class="bg-red-50 border border-red-200 rounded-md px-1.5 py-1 flex items-center gap-1">
                                            <div class="w-2.5 h-2.5 rounded-full border border-red-300"></div>
                                            <span class="text-[6px] text-gray-500">@lang('core::app.mobile.see_more')</span>
                                        </div>
                                    </div>
                                    <div class="mt-1.5 bg-green-50 border border-green-100 rounded-md p-1.5">
                                        <p class="text-[6px] text-green-700 font-bold leading-tight">@lang('core::app.mobile.explanation')</p>
                                    </div>
                                </div>
                                <div class="flex gap-1">
                                    <button class="flex-1 bg-gray-100 text-gray-500 text-[6px] font-bold py-1 rounded-md">@lang('core::app.mobile.prev')</button>
                                    <button class="flex-1 bg-green-500 text-white text-[6px] font-black py-1 rounded-md shadow-[0_1px_0_#15803d]">@lang('core::app.mobile.next')</button>
                                </div>
                            </div>
                            <div class="flex justify-center py-1.5">
                                <div class="w-8 h-0.5 bg-gray-300 rounded-full"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Text content --}}
            <div class="flex-1 flex flex-col gap-6">
                <span class="bg-green-500 text-white text-xs font-black px-3 py-1 rounded-lg w-fit">@lang('core::app.anytime_anywhere.badge')</span>
                <h2 class="text-4xl font-black text-gray-900 leading-tight">
                    @lang('core::app.anytime_anywhere.heading')
                </h2>
                <div class="flex flex-col gap-5">
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                            <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><path d="M8 1C4.134 1 1 4.134 1 8s3.134 7 7 7 7-3.134 7-7-3.134-7-7-7z" stroke="#16a34a" stroke-width="1.3"/><path d="M1 8h14M8 1a10.5 10.5 0 010 14M8 1a10.5 10.5 0 000 14" stroke="#16a34a" stroke-width="1.3" stroke-linecap="round"/></svg>
                        </div>
                        <p class="text-gray-500 text-sm leading-relaxed">@lang('core::app.anytime_anywhere.feature_1')</p>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                            <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><rect x="1" y="3" width="10" height="8" rx="1.5" stroke="#16a34a" stroke-width="1.3"/><path d="M3 11v1.5M8 11v1.5M1.5 12.5h8" stroke="#16a34a" stroke-width="1.3" stroke-linecap="round"/><rect x="11" y="6" width="4" height="6" rx="1" stroke="#16a34a" stroke-width="1.3"/><path d="M13 13v1" stroke="#16a34a" stroke-width="1.3" stroke-linecap="round"/></svg>
                        </div>
                        <p class="text-gray-500 text-sm leading-relaxed">@lang('core::app.anytime_anywhere.feature_2')</p>
                    </div>
                </div>
                <a href="#" class="bg-green-500 hover:bg-green-400 text-white font-black text-sm px-7 py-3.5 rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 transition-all w-fit">
                    @lang('core::app.anytime_anywhere.cta')
                </a>
            </div>
        </div>
    </div>
</section>
