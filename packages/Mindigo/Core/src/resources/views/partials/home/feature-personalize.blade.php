{{-- Personalization section --}}
<section class="-mt-px bg-[#fbfdf9] px-5 py-20 sm:px-8 lg:flex lg:min-h-screen lg:items-center lg:px-12 lg:py-12">
    <div class="mx-auto w-full max-w-7xl 2xl:max-w-[1480px]">
        <div class="flex flex-col lg:flex-row items-center gap-20">

            {{-- LEFT: Phone mockups --}}
            <div class="flex-1 relative flex items-center justify-center min-h-130">
                {{-- Soft color accents behind the mockups --}}
                <div class="absolute left-16 top-16 h-40 w-40 rounded-full bg-orange-200/55 blur-3xl pointer-events-none"></div>
                <div class="absolute right-12 bottom-16 h-44 w-44 rounded-full bg-blue-200/55 blur-3xl pointer-events-none"></div>
                <div class="absolute left-1/2 top-1/2 h-56 w-56 -translate-x-1/2 -translate-y-1/2 rounded-full bg-amber-100/75 blur-3xl pointer-events-none"></div>

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
                <div class="absolute bottom-6 right-20 text-blue-400 text-3xl pointer-events-none" style="animation:floatStar 4s ease-in-out infinite">✦</div>
                <div class="absolute top-10 right-10 text-rose-300 text-xl pointer-events-none" style="animation:floatStar 3s .8s ease-in-out infinite">✦</div>
                <div class="absolute top-28 left-10 z-20 w-9 h-9 bg-blue-100 border border-blue-200 rounded-xl flex items-center justify-center shadow-md -rotate-12 pointer-events-none" style="animation:floatStar 4s .2s ease-in-out infinite">
                    <svg width="17" height="17" fill="none" viewBox="0 0 17 17"><path d="M3 13V9M8.5 13V4M14 13V7" stroke="#2f80ed" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <div class="absolute bottom-24 right-6 z-20 w-9 h-9 bg-rose-100 border border-rose-200 rounded-full flex items-center justify-center shadow-md rotate-12 pointer-events-none" style="animation:floatStar 3.5s .6s ease-in-out infinite">
                    <svg width="17" height="17" fill="none" viewBox="0 0 17 17"><path d="M9.5 2.5L4.5 9h3l-1 5.5 6-7h-3v-5z" fill="#f43f5e"/></svg>
                </div>

                {{-- Phone 1 (Khám phá) --}}
                <div class="relative z-10 -mr-7 mt-8" style="transform: rotate(-5deg); width: 195px;">
                    <div class="relative rounded-[2.6rem] shadow-2xl" style="background: linear-gradient(160deg, #ffedd5 0%, #fb923c 52%, #ea580c 100%); padding: 3px;">
                        <div class="absolute top-14 h-6 rounded-l-full" style="left:-4px; width:4px; background:#fdba74;"></div>
                        <div class="absolute top-24 h-9 rounded-l-full" style="left:-4px; width:4px; background:#fdba74;"></div>
                        <div class="absolute top-36 h-9 rounded-l-full" style="left:-4px; width:4px; background:#fdba74;"></div>
                        <div class="absolute top-20 h-12 rounded-r-full" style="right:-4px; width:4px; background:#fdba74;"></div>
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
                            <div style="background: linear-gradient(135deg, #f97316, #ea580c);" class="px-3 py-2.5">
                                <p class="text-white text-[10px] font-black text-center mb-1.5">@lang('core::app.personalization.phone1.title')</p>
                                <div class="flex gap-1 border-b border-orange-700">
                                    <button class="text-white text-[8px] font-black border-b-2 border-white pb-1 px-2">@lang('core::app.personalization.phone1.tab_exam')</button>
                                    <button class="text-orange-200 text-[8px] px-2 pb-1">@lang('core::app.personalization.phone1.tab_course')</button>
                                </div>
                            </div>
                            <div class="px-2.5 py-2 bg-orange-50 flex-1">
                                <div class="bg-white rounded-lg px-2 py-1.5 flex items-center gap-1.5 border border-orange-100 mb-2 shadow-sm">
                                    <svg width="9" height="9" fill="none" viewBox="0 0 9 9"><circle cx="4" cy="4" r="3" stroke="#ea580c" stroke-width="1"/><path d="M6.5 6.5l1.5 1.5" stroke="#ea580c" stroke-width="1" stroke-linecap="round"/></svg>
                                    <span class="text-[8px] text-gray-400">@lang('core::app.personalization.phone1.search')</span>
                                </div>
                                <div class="flex gap-1 mb-2">
                                    <span class="text-white text-[7px] font-black px-2 py-0.5 rounded-full shadow-sm" style="background:#f97316;">@lang('core::app.personalization.phone1.filter_new')</span>
                                    <span class="bg-amber-50 text-amber-600 text-[7px] px-2 py-0.5 rounded-full border border-amber-200">@lang('core::app.personalization.phone1.filter_hot')</span>
                                </div>
                                <div class="bg-white rounded-xl overflow-hidden shadow-md mb-2 border border-orange-100">
                                    <div class="h-16 flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);">
                                        <div class="absolute inset-0 opacity-10" style="background-image: repeating-linear-gradient(45deg, white 0, white 1px, transparent 0, transparent 50%); background-size: 8px 8px;"></div>
                                        <div class="text-center relative z-10">
                                            <p class="text-white text-[8px] font-black">@lang('core::app.personalization.phone1.card1_subject')</p>
                                            <p class="text-orange-200 text-[7px] font-bold">@lang('core::app.personalization.phone1.card1_label')</p>
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
                                <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-orange-100">
                                    <div class="h-10 flex items-center px-2 justify-between" style="background: linear-gradient(135deg, #fb923c, #f97316);">
                                        <p class="text-white text-[7px] font-black">@lang('core::app.personalization.phone1.card2_title')</p>
                                        <div class="w-5 h-5 bg-white/20 rounded-full flex items-center justify-center">
                                            <svg width="8" height="8" fill="white" viewBox="0 0 8 8"><path d="M3 1l3 3-3 3" stroke="white" stroke-width="1.2" stroke-linecap="round" fill="none"/></svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-around py-2.5 border-t border-gray-100 bg-white">
                                <div class="flex flex-col items-center gap-0.5">
                                    <div class="w-5 h-5 bg-orange-100 rounded-md flex items-center justify-center">
                                        <svg width="10" height="10" fill="none" viewBox="0 0 10 10"><rect x="1" y="1" width="3.5" height="3.5" rx="0.7" fill="#ea580c"/><rect x="5.5" y="1" width="3.5" height="3.5" rx="0.7" fill="#ea580c" opacity=".4"/><rect x="1" y="5.5" width="3.5" height="3.5" rx="0.7" fill="#ea580c" opacity=".4"/><rect x="5.5" y="5.5" width="3.5" height="3.5" rx="0.7" fill="#ea580c" opacity=".4"/></svg>
                                    </div>
                                    <span class="text-[6px] text-orange-600 font-black">@lang('core::app.personalization.phone1.nav_explore')</span>
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
                    <div class="relative rounded-[2.6rem] shadow-2xl" style="background: linear-gradient(160deg, #dbeafe 0%, #60a5fa 52%, #2563eb 100%); padding: 3px;">
                        <div class="absolute top-14 h-6 rounded-l-full" style="left:-4px; width:4px; background:#93c5fd;"></div>
                        <div class="absolute top-24 h-9 rounded-l-full" style="left:-4px; width:4px; background:#93c5fd;"></div>
                        <div class="absolute top-20 h-12 rounded-r-full" style="right:-4px; width:4px; background:#93c5fd;"></div>
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
                                <span class="text-blue-500 text-sm font-black">‹</span>
                                <span class="text-[10px] font-black text-gray-700">@lang('core::app.personalization.phone2.back')</span>
                            </div>
                            <div class="mx-2.5 mt-2 rounded-xl overflow-hidden shadow-md">
                                <div class="p-3 flex items-center gap-2" style="background: linear-gradient(135deg, #2f80ed, #2563eb);">
                                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center shrink-0 border border-white/30">
                                        <svg width="20" height="20" fill="none" viewBox="0 0 20 20"><rect x="3" y="2" width="14" height="16" rx="2" stroke="white" stroke-width="1.5"/><path d="M6 7h8M6 10h6M6 13h4" stroke="white" stroke-width="1.2" stroke-linecap="round"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-white text-[9px] font-black leading-tight">@lang('core::app.personalization.phone2.exam_title')</p>
                                        <p class="text-blue-200 text-[8px] font-bold">@lang('core::app.personalization.phone2.exam_label')</p>
                                    </div>
                                </div>
                                <div class="px-3 py-1.5 flex items-center gap-3" style="background:#1d4ed8;">
                                    <span class="text-blue-100 text-[10px] flex items-center gap-1">
                                        <i class="fa-solid fa-eye"></i> @lang('core::app.personalization.phone2.stat_views')
                                    </span>
                                    <span class="text-blue-100 text-[10px] flex items-center gap-1">
                                        <i class="fa-solid fa-clock"></i> @lang('core::app.personalization.phone2.stat_time')
                                    </span>
                                    <span class="text-blue-100 text-[10px] flex items-center gap-1">
                                        <i class="fa-solid fa-circle-question"></i> @lang('core::app.personalization.phone2.stat_q')
                                    </span>
                                </div>
                            </div>
                            <div class="px-2.5 py-2">
                                <div class="flex gap-1.5 mb-2">
                                    <button class="flex-1 text-white text-[8px] font-black py-1.5 rounded-lg shadow-[0_2px_0_#1d4ed8]" style="background:#2f80ed;">@lang('core::app.personalization.phone2.btn_start')</button>
                                    <button class="w-8 h-7 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-center">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 12 12"><path d="M6 2v8M2 6h8" stroke="#3b82f6" stroke-width="1.3" stroke-linecap="round"/></svg>
                                    </button>
                                    <button class="w-8 h-7 bg-teal-50 border border-teal-200 rounded-lg flex items-center justify-center">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 12 12"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0z" stroke="#0f766e" stroke-width="1.2"/></svg>
                                    </button>
                                </div>
                                <p class="text-[8px] text-gray-500 leading-relaxed mb-2">@lang('core::app.personalization.phone2.desc')</p>
                                <div class="flex gap-1 border-b border-gray-100 mb-2">
                                    <button class="text-blue-600 text-[7px] font-black border-b-2 border-blue-500 pb-1 px-1">@lang('core::app.personalization.phone2.tab_content')</button>
                                    <button class="text-gray-400 text-[7px] pb-1 px-1">@lang('core::app.personalization.phone2.tab_result')</button>
                                    <button class="text-gray-400 text-[7px] pb-1 px-1">@lang('core::app.personalization.phone2.tab_list')</button>
                                </div>
                                <div class="bg-blue-50 rounded-lg p-2 border border-blue-100">
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
            <div class="flex flex-1 flex-col gap-7">
                <span class="w-fit -rotate-2 rounded-sm bg-[#f4c767] px-4 py-1.5 text-xs font-black uppercase tracking-[0.12em] text-slate-900 shadow-[4px_4px_0_#e85d32]">@lang('core::app.personalization.badge')</span>
                <h2 class="text-[3.1rem] font-black leading-[1.01] tracking-[-0.045em] text-slate-950 sm:text-[3.85rem] lg:text-[4.25rem]">
                    @lang('core::app.personalization.title_1')<br>
                    <span class="relative isolate inline-flex -rotate-2 px-3 pb-4 pt-1">
                        <span class="absolute -inset-x-4 bottom-3 top-2 -z-20 rotate-1 rounded-[48%_42%_52%_38%] bg-[#ffe3a6]" aria-hidden="true"></span>
                        <span class="relative italic text-[#f15a0c]" style="-webkit-text-stroke: 2px #fff7e6; text-shadow: 0 4px 0 #c2410c, 0 7px 0 #f4c767, 0 11px 15px rgba(124,45,18,.22);">@lang('core::app.personalization.title_2')</span>
                        <svg class="pointer-events-none absolute -bottom-2 -left-2 -right-3 -z-10 h-8 w-[calc(100%+1.25rem)] text-amber-500" viewBox="0 0 240 34" preserveAspectRatio="none" fill="none" aria-hidden="true">
                            <path d="M8 20c58-17 152-18 224-7-45 1-94 8-137 17 24-12 66-20 124-18" stroke="currentColor" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <svg class="pointer-events-none absolute -left-5 -top-2 h-8 w-7 text-orange-500" viewBox="0 0 28 32" fill="none" aria-hidden="true"><path d="M21 3l-4 9M9 7l3 8M3 17l8 2" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                        <svg class="pointer-events-none absolute -right-7 -top-4 h-7 w-8 text-blue-500" viewBox="0 0 32 28" fill="none" aria-hidden="true"><path d="M3 18c5-9 9-9 13 0 4 7 8 6 13-2" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                    </span> @lang('core::app.personalization.title_3')
                </h2>
                <div class="flex flex-col gap-5">
                    <div class="flex items-start gap-4">
                        <div class="mt-0.5 grid h-12 w-12 shrink-0 place-items-center rounded-sm border-2 border-orange-200 bg-orange-50 shadow-[4px_4px_0_#fb923c]">
                            <img src="{{ asset('images/home/document.svg') }}" alt="" class="h-8 w-8 object-contain" aria-hidden="true">
                        </div>
                        <p class="text-sm font-medium leading-7 text-slate-600">@lang('core::app.personalization.desc_1')</p>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="mt-0.5 grid h-12 w-12 shrink-0 place-items-center rounded-sm border-2 border-blue-200 bg-blue-50 shadow-[4px_4px_0_#2f80ed]">
                            <img src="{{ asset('images/home/user.svg') }}" alt="" class="h-8 w-8 object-contain" aria-hidden="true">
                        </div>
                        <p class="text-sm font-medium leading-7 text-slate-600">@lang('core::app.personalization.desc_2')</p>
                    </div>
                </div>
                <a href="{{ route('courses.index') }}" class="w-fit rounded-full bg-orange-500 px-8 py-3.5 text-sm font-black text-white shadow-[0_5px_0_#c2410c] transition-all hover:-translate-y-1 hover:bg-orange-400 hover:shadow-[0_8px_0_#c2410c] active:translate-y-1 active:shadow-none">
                    @lang('core::app.personalization.cta')
                </a>
            </div>
        </div>
    </div>
</section>
