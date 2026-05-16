{{-- Feature section --}}
<section class="py-20 px-10 bg-green-50 border-t border-green-100">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
            <p class="text-green-600 font-black text-3xl">@lang('core::app.feature.section_title')</p>
        </div>
        <div class="flex flex-col lg:flex-row items-center gap-20">
            <div class="flex-1 flex flex-col gap-6">
                <span class="bg-green-500 text-white text-xs font-black px-3 py-1 rounded-lg w-fit">@lang('core::app.feature.badge')</span>
                <h2 class="text-4xl font-black text-gray-900 leading-tight">
                    <span class="text-green-600">@lang('core::app.feature.heading_highlight')</span> @lang('core::app.feature.heading_rest')
                </h2>
                <div class="flex flex-col gap-5">
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                            <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><rect x="1" y="2" width="14" height="12" rx="2" stroke="#16a34a" stroke-width="1.3"/><path d="M4 7h8M4 10h5" stroke="#16a34a" stroke-width="1.3" stroke-linecap="round"/></svg>
                        </div>
                        <p class="text-gray-500 text-sm leading-relaxed">@lang('core::app.feature.feature_1')</p>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                            <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><path d="M8 1v5M8 10v5M1 8h5M10 8h5" stroke="#16a34a" stroke-width="1.3" stroke-linecap="round"/></svg>
                        </div>
                        <p class="text-gray-500 text-sm leading-relaxed">@lang('core::app.feature.feature_2')</p>
                    </div>
                </div>
                <a href="#" class="bg-green-500 hover:bg-green-400 text-white font-black text-sm px-7 py-3.5 rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 transition-all w-fit">
                    @lang('core::app.feature.cta')
                </a>
            </div>

            <div class="flex-1 relative min-h-105 flex items-center justify-center">
                {{-- Card sau --}}
                <div class="absolute top-0 right-0 bg-white rounded-2xl shadow-xl border border-gray-100 w-96 p-5 opacity-80 rotate-1 z-0">
                    <div class="bg-gray-50 border-b border-gray-100 flex items-center gap-1.5 pb-2 mb-4">
                        <div class="w-2.5 h-2.5 rounded-full bg-red-400"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-yellow-400"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-green-400"></div>
                        <span class="text-[10px] text-gray-400 ml-2 font-medium">@lang('core::app.ai_card.tab_input')</span>
                        <span class="text-[10px] text-gray-400 ml-auto font-medium">@lang('core::app.ai_card.tab_review')</span>
                    </div>
                    <div class="bg-gray-50 rounded-lg h-24 mb-4 border border-dashed border-gray-200 flex items-center justify-center">
                        <span class="text-xs text-gray-300">@lang('core::app.ai_card.placeholder')</span>
                    </div>
                    <div class="flex gap-2">
                        <div class="flex-1 bg-gray-50 border border-gray-200 rounded-lg px-2 py-2">
                            <p class="text-[9px] text-gray-400 mb-0.5">@lang('core::app.ai_card.question_count_label')</p>
                            <p class="text-xs font-bold text-gray-700">@lang('core::app.ai_card.question_count_value')</p>
                        </div>
                        <div class="flex-1 bg-gray-50 border border-gray-200 rounded-lg px-2 py-2">
                            <p class="text-[9px] text-gray-400 mb-0.5">@lang('core::app.ai_card.type_label')</p>
                            <p class="text-xs font-bold text-gray-700">@lang('core::app.ai_card.type_value')</p>
                        </div>
                        <div class="flex-1 bg-gray-50 border border-gray-200 rounded-lg px-2 py-2">
                            <p class="text-[9px] text-gray-400 mb-0.5">@lang('core::app.ai_card.level_label')</p>
                            <p class="text-xs font-bold text-gray-700">@lang('core::app.ai_card.level_value')</p>
                        </div>
                    </div>
                </div>

                {{-- AI chip --}}
                <div class="absolute left-0 top-1/3 z-30 w-16 h-16 bg-green-500 rounded-2xl flex items-center justify-center shadow-xl" style="animation:floatStar 3s ease-in-out infinite">
                    <span class="text-white font-black text-lg">AI</span>
                </div>

                {{-- Card trước --}}
                <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 w-96 mt-28 z-10">
                    <div class="bg-gray-50 border-b border-gray-100 px-4 py-2.5 flex items-center gap-1.5 rounded-t-2xl">
                        <div class="w-2.5 h-2.5 rounded-full bg-red-400"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-yellow-400"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-green-400"></div>
                        <span class="text-[10px] text-gray-400 ml-2 font-medium">@lang('core::app.ai_card.tab_input')</span>
                        <span class="text-[10px] text-gray-400 ml-auto font-medium">@lang('core::app.ai_card.tab_review')</span>
                    </div>
                    <div class="p-4">
                        <div class="flex gap-2 mb-3">
                            <span class="bg-red-100 text-red-400 text-xs font-black px-2 py-0.5 rounded">@lang('core::app.ai_card.btn_return')</span>
                            <span class="bg-green-500 text-white text-xs font-black px-2 py-0.5 rounded">@lang('core::app.ai_card.btn_save')</span>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-28 shrink-0">
                                <p class="text-[9px] font-black text-gray-400 mb-1">@lang('core::app.ai_card.section_list_label')</p>
                                <div class="bg-green-500 text-white text-[9px] font-black px-2 py-1 rounded text-center mb-1">@lang('core::app.ai_card.section_1_name')</div>
                                <p class="text-[9px] font-black text-gray-400 mt-2 mb-1">@lang('core::app.ai_card.question_index_label')</p>
                                <div class="grid grid-cols-5 gap-0.5">
                                    @foreach(range(1,10) as $n)
                                    <div class="w-4 h-4 rounded text-[7px] font-bold flex items-center justify-center {{ $n <= 3 ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-400' }}">{{ $n }}</div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="flex-1 space-y-2">
                                <p class="text-[9px] font-black text-gray-400">@lang('core::app.ai_card.question_list_label')</p>
                                <div class="bg-gray-50 rounded-lg p-2">
                                    <p class="text-[9px] font-black text-gray-700 mb-1">@lang('core::app.ai_card.question_1_label')</p>
                                    <p class="text-[9px] text-gray-500 italic mb-1">What is the plural form of "child"?</p>
                                    <div class="flex items-center gap-1 text-[9px] text-gray-500"><span class="text-red-400">✗</span> Childs</div>
                                    <div class="flex items-center gap-1 text-[9px] text-green-600 font-bold"><span class="text-green-500">✓</span> Children</div>
                                    <div class="mt-1 bg-green-50 rounded px-1.5 py-1 text-[8px] text-green-700 font-bold">The plural form of "child" is "children"</div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-2">
                                    <p class="text-[9px] font-black text-gray-700 mb-1">@lang('core::app.ai_card.question_2_label')</p>
                                    <p class="text-[9px] text-gray-500 italic">Choose the correct word: "I ___ to school by bus."</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="absolute bottom-4 left-8 text-green-400 text-2xl" style="animation:floatStar 4s ease-in-out infinite">✦</div>
                <div class="absolute top-4 right-4 text-green-300 text-lg" style="animation:floatStar 3s .5s ease-in-out infinite">✦</div>
            </div>
        </div>
    </div>
</section>