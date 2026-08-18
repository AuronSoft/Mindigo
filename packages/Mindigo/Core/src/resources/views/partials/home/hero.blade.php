{{-- Hero --}}
<section class="relative overflow-hidden bg-green-50 lg:flex lg:min-h-[calc(100svh-5rem)] lg:items-center">
    <div class="absolute top-0 right-0 h-125 w-125 bg-green-200 rounded-full opacity-30 translate-x-1/3 -translate-y-1/3 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-green-200 rounded-full opacity-20 -translate-x-1/3 translate-y-1/3 pointer-events-none"></div>

    <div class="relative z-10 mx-auto flex w-full max-w-7xl flex-col items-center gap-12 px-6 py-16 sm:px-8 lg:flex-row lg:gap-10 lg:px-10 lg:py-12">

        {{-- LEFT --}}
        <div class="flex w-full flex-1 flex-col items-start gap-5 lg:flex-[1.02]">
            <span class="bg-white border border-green-200 text-green-700 text-xs font-black px-4 py-1.5 rounded-full">
                @lang('core::app.hero.badge')
            </span>
            <div class="w-full max-w-2xl text-balance">
                <h1 class="text-[2.75rem] font-black leading-[1.03] tracking-[-0.045em] text-slate-950 sm:text-[3.2rem] lg:text-[3.45rem] xl:text-[3.7rem]">
                    @lang('core::app.hero.heading_1')
                </h1>
                <h2 class="min-h-12 text-[2.75rem] font-black leading-[1.03] tracking-[-0.045em] text-green-600 sm:min-h-14 sm:text-[3.2rem] lg:min-h-16 lg:whitespace-nowrap lg:text-[3.45rem] xl:text-[3.7rem]">
                    <span id="typewriter" class="inline">@lang('core::app.hero.heading_2')</span><span class="ml-1 inline-block h-[0.82em] w-0.5 align-[-0.06em] bg-green-500 animate-pulse" aria-hidden="true"></span>
                </h2>
                <h3 class="text-[2.75rem] font-black leading-[1.03] tracking-[-0.045em] text-slate-950 sm:text-[3.2rem] lg:text-[3.45rem] xl:text-[3.7rem]">
                    @lang('core::app.hero.heading_3')
                </h3>
            </div>
            <div class="w-16 h-1 bg-green-500 rounded-full"></div>
            <p class="text-gray-500 font-semibold text-base leading-relaxed max-w-lg">
                @lang('core::app.hero.desc')
            </p>
            <div class="flex items-center gap-3">
                <div class="flex -space-x-2">
                    <img src="https://api.dicebear.com/9.x/personas/svg?seed=Mia&backgroundColor=d1fae5" class="w-9 h-9 rounded-full border-2 border-white object-cover bg-green-200" alt="user">
                    <img src="https://api.dicebear.com/9.x/personas/svg?seed=Linh&backgroundColor=bbf7d0" class="w-9 h-9 rounded-full border-2 border-white object-cover bg-green-300" alt="user">
                    <img src="https://api.dicebear.com/9.x/personas/svg?seed=Nam&backgroundColor=86efac" class="w-9 h-9 rounded-full border-2 border-white object-cover bg-green-400" alt="user">
                    <img src="https://api.dicebear.com/9.x/personas/svg?seed=Hoa&backgroundColor=4ade80" class="w-9 h-9 rounded-full border-2 border-white object-cover bg-green-500" alt="user">
                </div>
                <span class="text-sm font-bold text-gray-600">{!! __('core::app.hero.customers') !!}</span>
            </div>
            <div class="flex gap-0.5 text-yellow-400 text-2xl -mt-1">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
            <div class="flex gap-3 mt-1 flex-wrap">
                <a href="#" class="flex items-center gap-2 bg-green-500 hover:bg-green-400 active:bg-green-600 text-white font-black text-sm px-7 py-4 rounded-2xl shadow-[0_5px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 active:shadow-none active:translate-y-1.5 transition-all">
                    <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><circle cx="8" cy="8" r="6.5" stroke="white" stroke-width="1.5"/><path d="M6.5 5.5l5 2.5-5 2.5V5.5z" fill="white"/></svg>
                    @lang('core::app.hero.cta_create')
                </a>
                <a href="{{ route('courses.index') }}" class="flex items-center gap-2 bg-white hover:bg-green-50 text-green-600 font-black text-sm px-7 py-4 rounded-2xl border-2 border-green-200 hover:border-green-400 transition-all">
                    <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><circle cx="6.5" cy="6.5" r="5" stroke="#16a34a" stroke-width="1.5"/><path d="M10.5 10.5l3 3" stroke="#16a34a" stroke-width="1.5" stroke-linecap="round"/></svg>
                    @lang('core::app.hero.cta_search')
                </a>
            </div>
        </div>

        {{-- RIGHT --}}
        <div class="relative flex min-h-132 flex-1 items-center justify-center lg:-mr-14 lg:min-h-140 lg:translate-x-5 lg:flex-[1.08]">

            {{-- Floating learner community card --}}
            <div class="hero-floating-card absolute -top-5 left-5 z-30 hidden cursor-grab touch-none select-none sm:block active:cursor-grabbing" aria-hidden="true">
                <div class="relative flex -rotate-8 items-center gap-2 rounded-2xl border border-slate-100 bg-white px-3.5 py-3 pr-9 shadow-[0_18px_40px_rgba(15,23,42,0.16)] transition-transform duration-300 hover:-rotate-5 hover:scale-[1.03]">
                    <div class="flex -space-x-2">
                        @foreach([12, 32, 44, 47] as $portrait)
                            <img src="https://randomuser.me/api/portraits/{{ $portrait % 2 === 0 ? 'women' : 'men' }}/{{ $portrait }}.jpg" alt="" class="h-8 w-8 rounded-full border-2 border-white object-cover shadow-sm">
                        @endforeach
                    </div>
                    <span class="whitespace-nowrap text-[10px] font-black text-slate-800">@lang('core::app.hero.floating_students_joined')</span>
                    <span class="absolute -right-4 -top-7 grid h-12 w-12 rotate-12 place-items-center rounded-xl bg-pink-500 text-white shadow-[0_8px_18px_rgba(236,72,153,0.3)]">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none"><path d="m12 3 2 2.1 2.9-.2.8 2.8 2.5 1.6-1.1 2.7 1.1 2.7-2.5 1.6-.8 2.8-2.9-.2-2 2.1-2-2.1-2.9.2-.8-2.8-2.5-1.6 1.1-2.7-1.1-2.7 2.5-1.6.8-2.8 2.9.2L12 3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m9.5 12 1.7 1.7 3.5-3.7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </div>
            </div>

            {{-- Stats pill top right --}}
            <div class="hero-floating-card absolute right-0 top-0 z-20 flex cursor-grab touch-none select-none items-center gap-2 rounded-2xl bg-green-500 px-4 py-2 text-xs font-black text-white active:cursor-grabbing"
                style="box-shadow: 0 4px 0 #15803d, 0 8px 20px rgba(22,163,74,0.3);">
                <svg width="14" height="14" fill="none" viewBox="0 0 14 14">
                    <rect x="1" y="1" width="12" height="12" rx="3" stroke="white" stroke-width="1.5"/>
                    <path d="M4 7h6M4 4.5h3M4 9.5h4" stroke="white" stroke-width="1.2" stroke-linecap="round"/>
                </svg>
                 @lang('core::app.hero.advanced')
            </div>

            {{-- Floating e-learning context cards --}}
            <div class="hero-floating-card absolute -right-7 top-20 z-30 hidden w-40 cursor-grab touch-none select-none flex-col items-center rounded-2xl border border-slate-100 bg-white/95 px-3 pb-3 pt-4 text-center shadow-[0_18px_40px_rgba(15,23,42,0.16)] backdrop-blur xl:flex active:cursor-grabbing" aria-hidden="true">
                <div class="relative h-14 w-14 rounded-full bg-linear-to-br from-blue-100 to-violet-100 p-0.5 shadow-sm">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="" class="h-full w-full rounded-full object-cover">
                    <span class="absolute bottom-0 right-0 h-4 w-4 rounded-full border-[3px] border-white bg-green-500"></span>
                </div>
                <p class="mt-2.5 max-w-full truncate text-[10px] font-black text-slate-800">@lang('core::app.hero.floating_instructor_name')</p>
                <p class="mt-0.5 text-[8px] font-bold text-slate-400">@lang('core::app.hero.floating_instructor')</p>
                <span class="mt-2 inline-flex w-full items-center justify-center rounded-lg bg-violet-600 px-2 py-1.5 text-[8px] font-black text-white shadow-[0_2px_0_#6d28d9]">@lang('core::app.hero.floating_profile')</span>
                <div class="mt-2.5 w-full space-y-1.5 border-t border-slate-100 pt-2">
                    <div class="flex items-center justify-between gap-2"><span class="truncate text-[7px] font-bold text-slate-500">@lang('core::app.hero.floating_live_class')</span><span class="rounded-full bg-green-50 px-1.5 py-0.5 text-[6px] font-black text-green-700">@lang('core::app.hero.floating_active')</span></div>
                    <div class="flex items-center justify-between gap-2"><span class="truncate text-[7px] font-bold text-slate-500">@lang('core::app.hero.floating_students')</span><span class="text-[7px] font-black text-blue-600">24</span></div>
                </div>
            </div>

            <div class="hero-floating-card absolute -right-5 bottom-16 z-30 hidden cursor-grab touch-none select-none items-center gap-2 rounded-2xl border border-amber-100 bg-white/95 px-3 py-2.5 shadow-[0_14px_30px_rgba(15,23,42,0.12)] backdrop-blur lg:flex active:cursor-grabbing" aria-hidden="true">
                <span class="grid h-8 w-8 place-items-center rounded-xl bg-amber-100 text-amber-600">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M4 12.5 9 17l11-11" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div>
                    <p class="text-[9px] font-black text-slate-800">@lang('core::app.hero.floating_progress')</p>
                    <div class="mt-1.5 h-1.5 w-20 overflow-hidden rounded-full bg-amber-100"><span class="block h-full w-3/4 rounded-full bg-amber-400"></span></div>
                </div>
                <strong class="text-xs font-black text-amber-600">76%</strong>
            </div>

            <div class="hero-floating-card absolute bottom-20 left-7 z-20 hidden cursor-grab touch-none select-none items-center gap-2 rounded-xl border border-violet-100 bg-white/95 px-3 py-2 shadow-[0_12px_28px_rgba(15,23,42,0.12)] backdrop-blur xl:flex active:cursor-grabbing" aria-hidden="true">
                <span class="relative flex h-2.5 w-2.5"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-violet-400 opacity-50"></span><span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-violet-600"></span></span>
                <span class="whitespace-nowrap text-[9px] font-black text-slate-700">@lang('core::app.hero.floating_online')</span>
            </div>

            {{-- Main card with 3D effect --}}
            <div class="hero-lms-demo relative mt-12 min-h-126 w-full overflow-hidden rounded-3xl bg-white"
                style="
                box-shadow:
                    0 1px 2px rgba(15,23,42,0.08),
                    0 14px 28px rgba(15,23,42,0.12),
                    0 34px 70px rgba(15,23,42,0.16),
                    12px 18px 46px rgba(22,163,74,0.08);
                border: 1px solid #e2e8f0;
                transform: perspective(1200px) rotateX(1.5deg) rotateY(-1deg);
                transform-style: preserve-3d;
                ">

                {{-- Browser bar --}}
                <div class="bg-gray-50 border-b border-gray-100 px-4 py-2.5 flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-red-400 shadow-sm"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-400 shadow-sm"></div>
                    <div class="w-3 h-3 rounded-full bg-green-400 shadow-sm"></div>
                    <div class="flex-1 bg-white rounded-lg h-6 mx-4 border border-gray-200 flex items-center px-3 gap-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-green-400"></div>
                        <span class="text-[10px] text-gray-400 font-medium">@lang('core::app.hero.url_display')</span>
                    </div>
                </div>

                <div class="p-6">
                    {{-- Upload bar --}}
                    <div class="mb-5 flex items-center gap-3">
                        <div class="hero-upload-zone flex flex-1 cursor-pointer items-center gap-2 rounded-xl border-2 border-dashed border-green-300 bg-green-50 px-3 py-2.5 text-xs font-bold text-green-700 transition hover:bg-green-100"
                            style="box-shadow: inset 0 2px 4px rgba(22,163,74,0.06);">
                            <svg width="14" height="14" fill="none" viewBox="0 0 14 14">
                                <path d="M7 1v7M4.5 3.5L7 1l2.5 2.5" stroke="#16a34a" stroke-width="1.4" stroke-linecap="round"/>
                                <rect x="1" y="10" width="12" height="3" rx="1.5" fill="#16a34a" opacity=".2"/>
                            </svg>
                            @lang('core::app.hero.upload')
                        </div>
                        <button type="button" class="hero-review-button whitespace-nowrap rounded-xl px-4 py-2.5 text-xs font-black text-white transition hover:brightness-110"
                                style="background: linear-gradient(135deg, #22c55e, #16a34a); box-shadow: 0 4px 0 #15803d, 0 6px 12px rgba(22,163,74,0.3);">
                            @lang('core::app.hero.review')
                        </button>
                    </div>

                    {{-- Action pills --}}
                    <div class="mb-6 flex gap-2">
                        <span class="bg-red-50 text-red-400 border border-red-100 text-xs font-black px-3 py-1 rounded-lg">@lang('core::app.hero.return')</span>
                        <span class="hero-save-action rounded-lg px-3 py-1 text-xs font-black text-white"
                            style="background: linear-gradient(135deg, #22c55e, #16a34a); box-shadow: 0 3px 0 #15803d;">
                            @lang('core::app.hero.save')
                        </span>
                    </div>

                    <div class="flex gap-5">

                        {{-- Left sidebar --}}
                        <div class="w-44 shrink-0 space-y-4">
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-wide mb-2">@lang('core::app.hero.section_list')</p>
                                <div class="space-y-1.5">
                                    <div class="text-white text-xs font-black px-3 py-1.5 rounded-lg text-center"
                                        style="background: linear-gradient(135deg, #22c55e, #16a34a); box-shadow: 0 3px 0 #15803d, 0 6px 12px rgba(22,163,74,0.25);">
                                        @lang('core::app.hero.part_1')
                                    </div>
                                    <div class="bg-gray-100 text-gray-500 text-xs font-semibold px-2 py-1.5 rounded-lg text-center leading-tight hover:bg-gray-200 transition cursor-pointer">
                                        @lang('core::app.hero.part_2')
                                    </div>
                                </div>
                            </div>

                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-wide mb-2">@lang('core::app.hero.question_index')</p>
                                <div class="grid grid-cols-5 gap-1">
                                    @foreach(range(1,10) as $n)
                                    <div class="w-7 h-7 rounded-md flex items-center justify-center text-[10px] font-black transition cursor-pointer"
                                        style="{{ $n <= 3
                                        ? 'background: linear-gradient(135deg, #22c55e, #16a34a); color: white; box-shadow: 0 2px 0 #15803d, 0 4px 8px rgba(22,163,74,0.3);'
                                        : 'background: #f3f4f6; color: #9ca3af;' }}">
                                        {{ $n }}
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Progress --}}
                            <div class="bg-linear-to-br from-green-50 to-emerald-50 rounded-xl p-3 border border-green-100"
                                style="box-shadow: inset 0 1px 3px rgba(22,163,74,0.08);">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-[10px] font-black text-green-700">@lang('core::app.hero.progress')</p>
                                    <p class="text-[10px] font-black text-green-500">30%</p>
                                </div>
                                <div class="w-full bg-green-100 rounded-full h-2 mb-1.5 overflow-hidden">
                                    <div class="h-2 rounded-full" style="width:30%; background: linear-gradient(90deg, #22c55e, #16a34a); box-shadow: 0 0 6px rgba(22,163,74,0.5);"></div>
                                </div>
                                <p class="text-[10px] text-green-600 font-bold">@lang('core::app.hero.progress_done')</p>
                            </div>
                        </div>

                        {{-- Current Mindigo LMS course workspace --}}
                        <div class="min-w-0 flex-1 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0"><p class="truncate text-xs font-black text-gray-800">@lang('core::app.hero.course_name')</p><p class="mt-0.5 text-[9px] font-semibold text-gray-400">@lang('core::app.hero.course_meta')</p></div>
                                <div class="hidden shrink-0 gap-1.5 sm:flex"><span class="rounded-lg bg-blue-50 px-2 py-1 text-[8px] font-black text-blue-600">248 @lang('core::app.hero.learners_label')</span><span class="rounded-lg bg-violet-50 px-2 py-1 text-[8px] font-black text-violet-600">76% @lang('core::app.hero.completion_label')</span></div>
                            </div>

                            <article class="hero-lesson-one rounded-xl border border-green-100 bg-linear-to-br from-green-50 to-white p-4 shadow-sm">
                                <div class="flex items-start gap-3"><span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-green-500 text-white shadow-[0_2px_0_#15803d]"><svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="m8 5 11 7-11 7V5Z"/></svg></span><div class="min-w-0 flex-1"><div class="flex items-start justify-between gap-2"><div><p class="text-xs font-black text-gray-800">@lang('core::app.hero.lesson_1_name')</p><p class="mt-0.5 text-[9px] font-bold text-blue-500">@lang('core::app.hero.lesson_1_meta')</p></div><span class="shrink-0 rounded-full border border-green-200 bg-green-100 px-2 py-0.5 text-[8px] font-black text-green-700">@lang('core::app.hero.has_answer')</span></div><p class="mt-2 text-[9px] leading-relaxed text-gray-500">@lang('core::app.hero.lesson_1_desc')</p></div></div>
                            </article>

                            <article class="hero-lesson-two rounded-xl border border-gray-100 bg-gray-50 p-4 shadow-sm">
                                <div class="flex items-start gap-3"><span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-amber-100 text-amber-600"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l5 5v11a2 2 0 0 1-2 2Z"/></svg></span><div class="min-w-0 flex-1"><div class="flex items-start justify-between gap-2"><div><p class="text-xs font-black text-gray-800">@lang('core::app.hero.lesson_2_name')</p><p class="mt-0.5 text-[9px] font-bold text-amber-500">@lang('core::app.hero.lesson_2_meta')</p></div><span class="hero-publish-state shrink-0 rounded-full border px-2 py-0.5 text-[8px] font-black">@lang('core::app.hero.has_answer')</span></div><p class="mt-2 text-[9px] leading-relaxed text-gray-500">@lang('core::app.hero.lesson_2_desc')</p></div></div>
                            </article>
                        </div>
                    </div>
                </div>

                <div class="hero-processing pointer-events-none absolute left-1/2 top-20 z-30 flex -translate-x-1/2 items-center gap-2 rounded-xl border border-violet-100 bg-white px-3 py-2 shadow-xl" aria-hidden="true"><span class="h-3 w-3 animate-spin rounded-full border-2 border-violet-200 border-t-violet-600"></span><span class="whitespace-nowrap text-[9px] font-black text-violet-700">@lang('core::app.hero.ai_processing')</span></div>
                <div class="hero-save-toast pointer-events-none absolute bottom-4 left-1/2 z-30 flex -translate-x-1/2 items-center gap-2 rounded-xl border border-green-100 bg-white px-3 py-2 shadow-xl" aria-hidden="true"><span class="grid h-5 w-5 place-items-center rounded-full bg-green-500 text-[9px] font-black text-white">✓</span><span class="whitespace-nowrap text-[9px] font-black text-gray-700">@lang('core::app.hero.save_success')</span></div>
                <div class="hero-demo-cursor pointer-events-none absolute left-0 top-0 z-40 hidden sm:block" aria-hidden="true"><span class="hero-demo-click absolute -left-2 -top-2 h-7 w-7 rounded-full border-2 border-blue-400"></span><svg class="relative h-6 w-6 drop-shadow-md" viewBox="0 0 24 24" fill="none"><path d="M5 3.5 18.3 13l-6.1 1.1-3.6 5.1L5 3.5Z" fill="#2563eb" stroke="white" stroke-width="1.5" stroke-linejoin="round"/></svg></div>
            </div>

            {{-- Phone mockup --}}
            <div class="absolute -bottom-8 -left-16 z-10 lg:-bottom-10 lg:-left-14" style="transform: perspective(800px) rotateY(20deg) rotateX(4deg) rotate(-6deg); width: 160px;">
                <div class="relative rounded-[2.8rem] p-1"
                    style="background: linear-gradient(145deg, #e2e8f0, #cbd5e1); box-shadow: 0 25px 50px rgba(0,0,0,0.22), 0 8px 0 #94a3b8, inset 0 1px 0 rgba(255,255,255,0.9);">
                    <div class="absolute top-16 h-5 bg-slate-300 rounded-l-full" style="left:-4px; width:4px;"></div>
                    <div class="absolute top-24 h-8 bg-slate-300 rounded-l-full" style="left:-4px; width:4px;"></div>
                    <div class="absolute top-36 h-8 bg-slate-300 rounded-l-full" style="left:-4px; width:4px;"></div>
                    <div class="absolute top-20 h-10 bg-slate-300 rounded-r-full" style="right:-4px; width:4px;"></div>
                    <div class="bg-white overflow-hidden" style="border-radius: 2.4rem; max-height: 300px;">
                        <div class="flex justify-center pt-3 pb-1 bg-white">
                            <div class="bg-gray-900 rounded-full flex items-center justify-center gap-1.5 px-3" style="height:18px; width:60px">
                                <div class="w-1.5 h-1.5 rounded-full bg-gray-600"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-gray-700"></div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center px-4 py-0.5">
                            <span class="text-[9px] font-black text-gray-700">9:41</span>
                            <div class="flex items-center gap-1">
                                <svg width="10" height="8" fill="#374151" viewBox="0 0 10 8"><rect x="0" y="4" width="2" height="4" rx="0.5"/><rect x="2.5" y="2.5" width="2" height="5.5" rx="0.5"/><rect x="5" y="1" width="2" height="7" rx="0.5"/><rect x="7.5" y="0" width="2" height="8" rx="0.5"/></svg>
                                <svg width="14" height="8" fill="none" viewBox="0 0 14 8"><rect x="0.5" y="0.5" width="11" height="7" rx="1.5" stroke="#374151" stroke-width="1"/><rect x="1.5" y="1.5" width="8" height="5" rx="1" fill="#374151"/></svg>
                            </div>
                        </div>
                        <div class="bg-white px-3 py-2 mt-1 flex items-center justify-between border-b border-gray-100">
                            <p class="text-gray-800 text-[10px] font-black">@lang('core::app.hero.phone_add_q')</p>
                            <div class="w-4 h-4 bg-red-400 rounded-full flex items-center justify-center">
                                <span class="text-white text-[8px] font-black">&#10005;</span>
                            </div>
                        </div>
                        <div class="p-2.5 space-y-2 bg-white">
                            <div class="flex gap-1.5">
                                <div class="flex-1">
                                    <p class="text-[7px] font-black text-gray-400 mb-0.5">@lang('core::app.hero.q_type_label')</p>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-1.5 py-1 flex items-center justify-between">
                                        <span class="text-[8px] font-bold text-gray-700">@lang('core::app.hero.q_types.0')</span>
                                        <span class="text-gray-400 text-[7px]">&#9662;</span>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-[7px] font-black text-gray-400 mb-0.5">@lang('core::app.hero.difficulty')</p>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-1.5 py-1 flex items-center justify-between">
                                        <span class="text-[8px] font-bold text-gray-700">@lang('core::app.hero.difficulty_med')</span>
                                        <span class="text-gray-400 text-[7px]">&#9662;</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <p class="mb-0.5 text-[7px] font-black text-gray-400">@lang('core::app.hero.lesson_name_label')</p>
                                <div class="rounded-lg border border-gray-200 bg-gray-50 px-2 py-1.5"><span class="hero-phone-typing inline-block max-w-full overflow-hidden whitespace-nowrap align-bottom text-[8px] font-bold text-gray-700">@lang('core::app.hero.lesson_name_value')</span></div>
                            </div>
                            <div class="bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                                <p class="text-[7px] font-black text-gray-400 px-2 pt-1.5 pb-0.5">@lang('core::app.hero.q_type_label')</p>
                                @foreach(__('core::app.hero.q_types') as $i => $type)
                                <div class="hero-phone-option hero-phone-option-{{ $i }} flex items-center justify-between border-t border-gray-50 px-2 py-1 {{ $i === 0 ? 'bg-green-50' : '' }}">
                                    <span class="hero-phone-option-label text-[8px] font-bold {{ $i === 0 ? 'text-green-600' : 'text-gray-600' }}">{{ $type }}</span>
                                    <span class="hero-phone-option-check text-[8px] text-green-500 {{ $i === 0 ? '' : 'opacity-0' }}">&#10003;</span>
                                </div>
                                @endforeach
                                <div class="px-2 py-1 border-t border-gray-100 flex items-center gap-1.5">
                                    <span class="text-[8px] font-bold text-gray-600">@lang('core::app.hero.q_types.6')</span>
                                    <span class="text-white text-[6px] font-black px-1 py-0.5 rounded-full" style="background: linear-gradient(135deg, #22c55e, #16a34a);">PRO</span>
                                </div>
                            </div>
                        </div>
                        <span class="hero-phone-tap pointer-events-none absolute z-30 h-4 w-4 rounded-full border-2 border-blue-400 bg-blue-100/60" aria-hidden="true"></span>
                        <div class="flex justify-center py-2 bg-white">
                            <div class="w-12 h-1 bg-gray-300 rounded-full"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="absolute bottom-10 right-2 text-green-400 text-3xl pointer-events-none select-none" style="animation:floatStar 4s ease-in-out infinite">&#10022;</div>
            <div class="absolute bottom-2 right-14 text-green-300 text-xl pointer-events-none select-none" style="animation:floatStar 3s .6s ease-in-out infinite">&#10022;</div>
        </div>
    </div>
</section>
