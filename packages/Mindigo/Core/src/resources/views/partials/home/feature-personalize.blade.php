{{-- Personalization section --}}
<section class="border-t border-gray-100 bg-white px-6 py-16 lg:flex lg:min-h-[calc(100svh-5rem)] lg:items-center lg:px-10 lg:py-12">
    <div class="mx-auto w-full max-w-7xl">
        <div class="flex flex-col lg:flex-row items-center gap-20">

            {{-- LEFT: Phone mockups --}}
            <div class="flex-1 relative flex items-center justify-center min-h-130">
                {{-- Soft color accents behind the mockups --}}
                <div class="absolute left-16 top-16 w-40 h-40 rounded-full bg-blue-100/60 blur-3xl pointer-events-none"></div>
                <div class="absolute right-12 bottom-16 w-44 h-44 rounded-full bg-purple-100/60 blur-3xl pointer-events-none"></div>
                <div class="absolute left-1/2 top-1/2 w-56 h-56 -translate-x-1/2 -translate-y-1/2 rounded-full bg-amber-50/80 blur-3xl pointer-events-none"></div>

                {{-- Floating label: Dễ sử dụng --}}
                <div class="absolute left-0 top-1/2 -translate-y-1/2 z-30 bg-white border border-blue-100 rounded-2xl px-4 py-2.5 flex items-center gap-2.5 shadow-lg">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><circle cx="8" cy="5" r="3" stroke="#3b82f6" stroke-width="1.3"/><path d="M2 14c0-3 2.686-5 6-5s6 2 6 5" stroke="#3b82f6" stroke-width="1.3" stroke-linecap="round"/></svg>
                    </div>
                    <span class="text-blue-700 font-black text-xs">@lang('core::app.personalization.label_easy')</span>
                </div>

                {{-- Floating label: Thân thiện --}}
                <div class="absolute right-2 top-1/3 z-30 bg-white border border-amber-100 rounded-2xl px-4 py-2.5 flex items-center gap-2.5 shadow-lg">
                    <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center">
                        <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><path d="M8 2C4.686 2 2 4.686 2 8s2.686 6 6 6 6-2.686 6-6-2.686-6-6-6z" stroke="#f59e0b" stroke-width="1.3"/><path d="M5 9.5s.8 1.5 3 1.5 3-1.5 3-1.5" stroke="#f59e0b" stroke-width="1.3" stroke-linecap="round"/><circle cx="6" cy="7" r="0.8" fill="#f59e0b"/><circle cx="10" cy="7" r="0.8" fill="#f59e0b"/></svg>
                    </div>
                    <span class="text-amber-700 font-black text-xs">@lang('core::app.personalization.label_friendly')</span>
                </div>

                {{-- Decorative --}}
                <div class="absolute top-8 left-20 w-4 h-4 bg-blue-400 rounded-full opacity-60 pointer-events-none" style="animation:floatStar 3s ease-in-out infinite"></div>
                <div class="absolute bottom-12 left-10 w-3 h-3 bg-amber-300 rotate-45 opacity-70 pointer-events-none" style="animation:floatStar 4s .5s ease-in-out infinite"></div>
                <div class="absolute bottom-6 right-20 text-purple-400 text-3xl pointer-events-none" style="animation:floatStar 4s ease-in-out infinite">✦</div>
                <div class="absolute top-10 right-10 text-rose-300 text-xl pointer-events-none" style="animation:floatStar 3s .8s ease-in-out infinite">✦</div>
                <div class="absolute top-28 left-10 z-20 w-9 h-9 bg-purple-100 border border-purple-200 rounded-xl flex items-center justify-center shadow-md -rotate-12 pointer-events-none" style="animation:floatStar 4s .2s ease-in-out infinite">
                    <svg width="17" height="17" fill="none" viewBox="0 0 17 17"><path d="M3 13V9M8.5 13V4M14 13V7" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <div class="absolute bottom-24 right-6 z-20 w-9 h-9 bg-rose-100 border border-rose-200 rounded-full flex items-center justify-center shadow-md rotate-12 pointer-events-none" style="animation:floatStar 3.5s .6s ease-in-out infinite">
                    <svg width="17" height="17" fill="none" viewBox="0 0 17 17"><path d="M9.5 2.5L4.5 9h3l-1 5.5 6-7h-3v-5z" fill="#f43f5e"/></svg>
                </div>

                {{-- Phone 1 (Khám phá) --}}
                <div class="relative z-10 -mr-7 mt-8" style="transform: rotate(-5deg); width: 195px;">
                    <div class="relative rounded-[2.6rem] shadow-2xl" style="background: linear-gradient(160deg, #d1fae5 0%, #6ee7b7 50%, #34d399 100%); padding: 3px;">
                        <div class="absolute top-14 h-6 rounded-l-full" style="left:-4px; width:4px; background:#86efac;"></div>
                        <div class="absolute top-24 h-9 rounded-l-full" style="left:-4px; width:4px; background:#86efac;"></div>
                        <div class="absolute top-36 h-9 rounded-l-full" style="left:-4px; width:4px; background:#86efac;"></div>
                        <div class="absolute top-20 h-12 rounded-r-full" style="right:-4px; width:4px; background:#86efac;"></div>
                        <div class="bg-white overflow-hidden" style="border-radius: 2.3rem; max-height: 390px;">
                            <div class="flex justify-center pt-3 pb-0 bg-white">
                                <div class="bg-gray-900 rounded-full flex items-center gap-1.5 px-3" style="height:18px; width:62px;">
                                    <div class="w-1.5 h-1.5 rounded-full bg-gray-600"></div>
                                    <div class="w-3 h-3 rounded-full bg-gray-700"></div>
                                </div>
                            </div>
                            <div class="flex justify-between items-center px-4 py-1">
                                <span class="text-[9px] font-black text-gray-700">15:16</span>
                                <div class="flex items-center gap-1">
                                    <svg width="10" height="8" fill="#374151" viewBox="0 0 10 8"><rect x="0" y="4" width="2" height="4" rx="0.5"/><rect x="2.5" y="2.5" width="2" height="5.5" rx="0.5"/><rect x="5" y="1" width="2" height="7" rx="0.5"/><rect x="7.5" y="0" width="2" height="8" rx="0.5"/></svg>
                                    <svg width="14" height="8" fill="none" viewBox="0 0 14 8"><rect x="0.5" y="0.5" width="11" height="7" rx="1.5" stroke="#374151" stroke-width="1"/><rect x="1.5" y="1.5" width="8" height="5" rx="1" fill="#374151"/></svg>
                                </div>
                            </div>
                            <div style="background: linear-gradient(135deg, #16a34a, #15803d);" class="px-3 py-2.5">
                                <p class="text-white text-[10px] font-black text-center mb-1.5">@lang('core::app.personalization.phone1.title')</p>
                                <div class="flex gap-1 border-b border-green-700">
                                    <button class="text-white text-[8px] font-black border-b-2 border-white pb-1 px-2">@lang('core::app.personalization.phone1.tab_exam')</button>
                                    <button class="text-green-300 text-[8px] px-2 pb-1">@lang('core::app.personalization.phone1.tab_course')</button>
                                </div>
                            </div>
                            <div class="px-2.5 py-2 bg-green-50 flex-1">
                                <div class="bg-white rounded-lg px-2 py-1.5 flex items-center gap-1.5 border border-green-100 mb-2 shadow-sm">
                                    <svg width="9" height="9" fill="none" viewBox="0 0 9 9"><circle cx="4" cy="4" r="3" stroke="#16a34a" stroke-width="1"/><path d="M6.5 6.5l1.5 1.5" stroke="#16a34a" stroke-width="1" stroke-linecap="round"/></svg>
                                    <span class="text-[8px] text-gray-400">@lang('core::app.personalization.phone1.search')</span>
                                </div>
                                <div class="flex gap-1 mb-2">
                                    <span class="text-white text-[7px] font-black px-2 py-0.5 rounded-full shadow-sm" style="background:#16a34a;">@lang('core::app.personalization.phone1.filter_new')</span>
                                    <span class="bg-amber-50 text-amber-600 text-[7px] px-2 py-0.5 rounded-full border border-amber-200">@lang('core::app.personalization.phone1.filter_hot')</span>
                                </div>
                                <div class="bg-white rounded-xl overflow-hidden shadow-md mb-2 border border-green-50">
                                    <div class="h-16 flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);">
                                        <div class="absolute inset-0 opacity-10" style="background-image: repeating-linear-gradient(45deg, white 0, white 1px, transparent 0, transparent 50%); background-size: 8px 8px;"></div>
                                        <div class="text-center relative z-10">
                                            <p class="text-white text-[8px] font-black">@lang('core::app.personalization.phone1.card1_subject')</p>
                                            <p class="text-green-200 text-[7px] font-bold">@lang('core::app.personalization.phone1.card1_label')</p>
                                        </div>
                                    </div>
                                    <div class="p-2">
                                        <p class="text-[8px] font-black text-gray-700 leading-tight mb-1">@lang('core::app.personalization.phone1.card1_title')</p>
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-[7px] text-gray-400">@lang('core::app.personalization.phone1.card1_q')</span>
                                                <span class="text-gray-300">·</span>
                                                <span class="text-[7px] text-gray-400">@lang('core::app.personalization.phone1.card1_time')</span>
                                            </div>
                                            <div class="flex gap-0.5 text-yellow-400 text-[8px]">★★★★★</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-green-50">
                                    <div class="h-10 flex items-center px-2 justify-between" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                                        <p class="text-white text-[7px] font-black">@lang('core::app.personalization.phone1.card2_title')</p>
                                        <div class="w-5 h-5 bg-white/20 rounded-full flex items-center justify-center">
                                            <svg width="8" height="8" fill="white" viewBox="0 0 8 8"><path d="M3 1l3 3-3 3" stroke="white" stroke-width="1.2" stroke-linecap="round" fill="none"/></svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-around py-2.5 border-t border-gray-100 bg-white">
                                <div class="flex flex-col items-center gap-0.5">
                                    <div class="w-5 h-5 bg-green-100 rounded-md flex items-center justify-center">
                                        <svg width="10" height="10" fill="none" viewBox="0 0 10 10"><rect x="1" y="1" width="3.5" height="3.5" rx="0.7" fill="#16a34a"/><rect x="5.5" y="1" width="3.5" height="3.5" rx="0.7" fill="#16a34a" opacity=".4"/><rect x="1" y="5.5" width="3.5" height="3.5" rx="0.7" fill="#16a34a" opacity=".4"/><rect x="5.5" y="5.5" width="3.5" height="3.5" rx="0.7" fill="#16a34a" opacity=".4"/></svg>
                                    </div>
                                    <span class="text-[6px] text-green-600 font-black">@lang('core::app.personalization.phone1.nav_explore')</span>
                                </div>
                                <div class="flex flex-col items-center gap-0.5 opacity-40">
                                    <div class="w-5 h-5 bg-gray-100 rounded-md"></div>
                                    <span class="text-[6px] text-gray-400">@lang('core::app.personalization.phone1.nav_exam')</span>
                                </div>
                                <div class="flex flex-col items-center gap-0.5 opacity-40">
                                    <div class="w-5 h-5 bg-gray-100 rounded-md"></div>
                                    <span class="text-[6px] text-gray-400">@lang('core::app.personalization.phone1.nav_class')</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Phone 2 (Chi tiết đề thi) --}}
                <div class="relative z-20 -ml-3.75 -mt-7.5" style="transform: rotate(4deg); width: 195px;">
                    <div class="relative rounded-[2.6rem] shadow-2xl" style="background: linear-gradient(160deg, #d1fae5 0%, #6ee7b7 50%, #34d399 100%); padding: 3px;">
                        <div class="absolute top-14 h-6 rounded-l-full" style="left:-4px; width:4px; background:#86efac;"></div>
                        <div class="absolute top-24 h-9 rounded-l-full" style="left:-4px; width:4px; background:#86efac;"></div>
                        <div class="absolute top-20 h-12 rounded-r-full" style="right:-4px; width:4px; background:#86efac;"></div>
                        <div class="bg-white overflow-hidden" style="border-radius: 2.3rem; max-height: 390px;">
                            <div class="flex justify-center pt-3 pb-0 bg-white">
                                <div class="bg-gray-900 rounded-full flex items-center gap-1.5 px-3" style="height:18px; width:62px;">
                                    <div class="w-1.5 h-1.5 rounded-full bg-gray-600"></div>
                                    <div class="w-3 h-3 rounded-full bg-gray-700"></div>
                                </div>
                            </div>
                            <div class="flex justify-between items-center px-4 py-1">
                                <span class="text-[9px] font-black text-gray-700">15:24</span>
                                <div class="flex items-center gap-1">
                                    <svg width="10" height="8" fill="#374151" viewBox="0 0 10 8"><rect x="0" y="4" width="2" height="4" rx="0.5"/><rect x="2.5" y="2.5" width="2" height="5.5" rx="0.5"/><rect x="5" y="1" width="2" height="7" rx="0.5"/><rect x="7.5" y="0" width="2" height="8" rx="0.5"/></svg>
                                    <svg width="14" height="8" fill="none" viewBox="0 0 14 8"><rect x="0.5" y="0.5" width="11" height="7" rx="1.5" stroke="#374151" stroke-width="1"/><rect x="1.5" y="1.5" width="8" height="5" rx="1" fill="#374151"/></svg>
                                </div>
                            </div>
                            <div class="px-3 py-2 flex items-center gap-2 border-b border-gray-100">
                                <span class="text-green-500 text-sm font-black">‹</span>
                                <span class="text-[10px] font-black text-gray-700">@lang('core::app.personalization.phone2.back')</span>
                            </div>
                            <div class="mx-2.5 mt-2 rounded-xl overflow-hidden shadow-md">
                                <div class="p-3 flex items-center gap-2" style="background: linear-gradient(135deg, #16a34a, #15803d);">
                                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center shrink-0 border border-white/30">
                                        <svg width="20" height="20" fill="none" viewBox="0 0 20 20"><rect x="3" y="2" width="14" height="16" rx="2" stroke="white" stroke-width="1.5"/><path d="M6 7h8M6 10h6M6 13h4" stroke="white" stroke-width="1.2" stroke-linecap="round"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-white text-[9px] font-black leading-tight">@lang('core::app.personalization.phone2.exam_title')</p>
                                        <p class="text-green-200 text-[8px] font-bold">@lang('core::app.personalization.phone2.exam_label')</p>
                                    </div>
                                </div>
                                <div class="px-3 py-1.5 flex items-center gap-3" style="background:#15803d;">
                                    <span class="text-green-100 text-[10px] flex items-center gap-1">
                                        <i class="fa-solid fa-eye"></i> @lang('core::app.personalization.phone2.stat_views')
                                    </span>
                                    <span class="text-green-100 text-[10px] flex items-center gap-1">
                                        <i class="fa-solid fa-clock"></i> @lang('core::app.personalization.phone2.stat_time')
                                    </span>
                                    <span class="text-green-100 text-[10px] flex items-center gap-1">
                                        <i class="fa-solid fa-circle-question"></i> @lang('core::app.personalization.phone2.stat_q')
                                    </span>
                                </div>
                            </div>
                            <div class="px-2.5 py-2">
                                <div class="flex gap-1.5 mb-2">
                                    <button class="flex-1 text-white text-[8px] font-black py-1.5 rounded-lg shadow-[0_2px_0_#15803d]" style="background:#16a34a;">@lang('core::app.personalization.phone2.btn_start')</button>
                                    <button class="w-8 h-7 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-center">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 12 12"><path d="M6 2v8M2 6h8" stroke="#3b82f6" stroke-width="1.3" stroke-linecap="round"/></svg>
                                    </button>
                                    <button class="w-8 h-7 bg-purple-50 border border-purple-200 rounded-lg flex items-center justify-center">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 12 12"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0z" stroke="#8b5cf6" stroke-width="1.2"/></svg>
                                    </button>
                                </div>
                                <p class="text-[8px] text-gray-500 leading-relaxed mb-2">@lang('core::app.personalization.phone2.desc')</p>
                                <div class="flex gap-1 border-b border-gray-100 mb-2">
                                    <button class="text-green-600 text-[7px] font-black border-b-2 border-green-500 pb-1 px-1">@lang('core::app.personalization.phone2.tab_content')</button>
                                    <button class="text-gray-400 text-[7px] pb-1 px-1">@lang('core::app.personalization.phone2.tab_result')</button>
                                    <button class="text-gray-400 text-[7px] pb-1 px-1">@lang('core::app.personalization.phone2.tab_list')</button>
                                </div>
                                <div class="bg-green-50 rounded-lg p-2 border border-green-100">
                                    <p class="text-[8px] font-black text-gray-600 mb-0.5">@lang('core::app.personalization.phone2.part')</p>
                                    <p class="text-[8px] text-gray-500 mb-0.5">@lang('core::app.personalization.phone2.question')</p>
                                    <p class="text-[8px] text-gray-400 italic">@lang('core::app.personalization.phone2.q_preview')</p>
                                </div>
                            </div>
                            <div class="flex justify-center py-2 bg-white">
                                <div class="w-12 h-1 bg-gray-300 rounded-full"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Text content --}}
            <div class="flex-1 flex flex-col gap-6">
                <span class="bg-green-500 text-white text-xs font-black px-3 py-1 rounded-lg w-fit">@lang('core::app.personalization.badge')</span>
                <h2 class="text-[2.65rem] font-black leading-[1.02] tracking-[-0.045em] text-slate-950 sm:text-[3.2rem] lg:text-[3.65rem]">
                    @lang('core::app.personalization.title_1')<br>
                    <span class="text-green-600">@lang('core::app.personalization.title_2')</span> @lang('core::app.personalization.title_3')
                </h2>
                <div class="flex flex-col gap-5">
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                            <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><rect x="1" y="2" width="14" height="12" rx="2" stroke="#16a34a" stroke-width="1.3"/><path d="M4 7h8M4 10h5" stroke="#16a34a" stroke-width="1.3" stroke-linecap="round"/></svg>
                        </div>
                        <p class="text-gray-500 text-sm leading-relaxed">@lang('core::app.personalization.desc_1')</p>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                            <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><circle cx="8" cy="5" r="3" stroke="#16a34a" stroke-width="1.3"/><path d="M2 14c0-3 2.686-5 6-5s6 2 6 5" stroke="#16a34a" stroke-width="1.3" stroke-linecap="round"/></svg>
                        </div>
                        <p class="text-gray-500 text-sm leading-relaxed">@lang('core::app.personalization.desc_2')</p>
                    </div>
                </div>
                <a href="#" class="bg-green-500 hover:bg-green-400 text-white font-black text-sm px-7 py-3.5 rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 transition-all w-fit">
                    @lang('core::app.personalization.cta')
                </a>
            </div>
        </div>
    </div>
</section>
