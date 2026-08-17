{{-- Navbar --}}
<nav class="sticky top-0 z-50 bg-[#f7c86f]">
    <div class="max-w-7xl mx-auto flex items-center justify-between px-10 py-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <svg class="h-10 w-10" viewBox="0 0 96 96" fill="none" aria-label="Mindigo">
                <path d="M23 64 7 56l8 13-7 10 19-5" fill="#2563a8" stroke="#0f2346" stroke-width="4" stroke-linejoin="round"/>
                <path d="M38 22C25 8 40 3 50 19 48 3 64 4 63 24" fill="#5aa9df" stroke="#0f2346" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="49" cy="54" r="37" fill="#4f98d1" stroke="#0f2346" stroke-width="5"/>
                <path d="M20 61c5 20 21 30 39 29 12-1 22-6 29-15-13 7-28 9-42 5-13-3-21-10-26-19Z" fill="#337fbd"/>
                <ellipse cx="42" cy="48" rx="15" ry="17" fill="#fff" stroke="#0f2346" stroke-width="4"/>
                <ellipse cx="64" cy="48" rx="15" ry="17" fill="#fff" stroke="#0f2346" stroke-width="4"/>
                <circle cx="47" cy="50" r="5" fill="#0f172a"/><circle cx="68" cy="50" r="5" fill="#0f172a"/>
                <circle cx="49" cy="48" r="1.5" fill="#fff"/><circle cx="70" cy="48" r="1.5" fill="#fff"/>
                <path d="M33 58c7 5 14 7 20 5l4 17c-12-2-21-9-24-22Z" fill="#e94e1b" stroke="#401b19" stroke-width="4" stroke-linejoin="round"/>
                <path d="m51 63 38 13-32-25-18 13 18 15" fill="#f9a825" stroke="#401b19" stroke-width="4" stroke-linejoin="round"/>
            </svg>
            <span class="text-xl font-black tracking-tight text-blue-600">Mindigo</span>
        </a>
        <div class="hidden md:flex items-center gap-1">
            <div class="relative group">
                <button type="button"
                    class="nav-link text-gray-600 font-bold text-sm px-4 py-2 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition flex items-center gap-1.5"
                    aria-haspopup="true">
                    @lang('core::app.navbar.features')
                    <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:rotate-180 group-focus-within:rotate-180" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <div class="absolute top-full left-1/2 z-50 w-screen max-w-5xl -translate-x-1/2 translate-y-2 pt-3 opacity-0 invisible transition-all duration-200 group-hover:visible group-hover:translate-y-0 group-hover:opacity-100 group-focus-within:visible group-focus-within:translate-y-0 group-focus-within:opacity-100">
                    <div class="bg-white border border-gray-100 rounded-3xl shadow-[0_24px_70px_rgba(15,23,42,0.18)] overflow-hidden">
                        <div class="px-8 pt-7 pb-5 flex items-start justify-between gap-8 border-b border-gray-100">
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-blue-600 mb-1.5">@lang('core::app.navbar.mega.eyebrow')</p>
                                <h3 class="text-xl font-black text-gray-900">@lang('core::app.navbar.mega.title')</h3>
                                <p class="text-xs text-gray-500 mt-1.5">@lang('core::app.navbar.mega.subtitle')</p>
                            </div>
                            <a href="#features" class="shrink-0 flex items-center gap-2 border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100 font-black text-xs px-4 py-2.5 rounded-xl transition">
                                @lang('core::app.navbar.mega.overview')
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3 7h8M8 4l3 3-3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </a>
                        </div>

                        @php
                            $megaIcons = [
                                'M3 4.5A2.5 2.5 0 015.5 2H13v13H5.5A2.5 2.5 0 013 12.5v-8zm2.5 7.5H13M6 5h4',
                                'M2.5 6L8 3l5.5 3L8 9 2.5 6zm2 2.5V12c2 1.3 5 1.3 7 0V8.5',
                                'M4 2.5h8A1.5 1.5 0 0113.5 4v9A1.5 1.5 0 0112 14.5H4A1.5 1.5 0 012.5 13V4A1.5 1.5 0 014 2.5zM5 6h6M5 9h4',
                                'M8 2.5a5.5 5.5 0 105.5 5.5M8 5v3l2 1.5M11.5 2.5v3h3',
                                'M4 3.5h8A1.5 1.5 0 0113.5 5v8A1.5 1.5 0 0112 14.5H4A1.5 1.5 0 012.5 13V5A1.5 1.5 0 014 3.5zM5.5 2v3M10.5 2v3M5 8h6M5 11h3',
                                'M3 3.5h10A1.5 1.5 0 0114.5 5v6A1.5 1.5 0 0113 12.5H8l-3.5 2v-2H3A1.5 1.5 0 011.5 11V5A1.5 1.5 0 013 3.5zM5 7h6M5 9.5h4',
                                'M2.5 13.5h11M4 11V7.5M8 11V3M12 11V5.5',
                                'M8 8a3 3 0 100-6 3 3 0 000 6zm-5.5 6c0-2.6 2.5-4.5 5.5-4.5s5.5 1.9 5.5 4.5',
                                'M3 4.5h7.5M3 8h10M3 11.5h6M11 2.5l2 2-2 2',
                                'M2.5 3.5h5v5h-5v-5zm6 0h5v3h-5v-3zm0 4h5v5h-5v-5zm-6 2h5v3h-5v-3z',
                                'M8 2.5l1.4 2.8 3.1.5-2.25 2.2.55 3.1L8 9.65 5.2 11.1 5.75 8 3.5 5.8l3.1-.5L8 2.5zM3 13.5h10',
                                'M8 2.5l1.4 2.9 3.1.45-2.25 2.2.55 3.1L8 9.7l-2.8 1.45.55-3.1-2.25-2.2 3.1-.45L8 2.5z',
                            ];
                            $megaItems = __('core::app.navbar.mega.items');
                            $megaHighlights = __('core::app.navbar.mega.highlights');
                        @endphp

                        <div class="grid grid-cols-4 gap-x-5 gap-y-2 px-7 py-5">
                            @foreach($megaItems as $index => $item)
                                <a href="#features" class="group/item flex gap-3 p-3 rounded-2xl hover:bg-blue-50 transition">
                                    <span class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0 group-hover/item:bg-blue-500 group-hover/item:text-white transition">
                                        <svg width="18" height="18" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                            <path d="{{ $megaIcons[$index] }}" stroke="currentColor" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <span>
                                        <span class="block text-[13px] font-black text-gray-800 group-hover/item:text-blue-700">{{ $item['title'] }}</span>
                                        <span class="block text-[11px] leading-relaxed text-gray-500 mt-0.5">{{ $item['desc'] }}</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>

                        <div class="grid grid-cols-4 gap-3 bg-gray-50 border-t border-gray-100 px-8 py-4">
                            @foreach($megaHighlights as $index => $item)
                                <a href="#features" class="flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-white hover:shadow-sm transition">
                                    <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $index === 0 ? 'bg-purple-100 text-purple-600' : ($index === 1 ? 'bg-blue-100 text-blue-600' : 'bg-amber-100 text-amber-600') }}">
                                        @if($index === 0)
                                            <svg width="17" height="17" viewBox="0 0 17 17" fill="currentColor"><path d="M8.5 1.5c.65 3.7 2.8 5.85 6.5 6.5-3.7.65-5.85 2.8-6.5 6.5C7.85 10.8 5.7 8.65 2 8c3.7-.65 5.85-2.8 6.5-6.5z"/></svg>
                                        @elseif($index === 1)
                                            <svg width="17" height="17" viewBox="0 0 17 17" fill="none"><rect x="4" y="1.5" width="9" height="14" rx="2" stroke="currentColor" stroke-width="1.4"/><path d="M7 13h3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                                        @else
                                            <svg width="17" height="17" viewBox="0 0 17 17" fill="none"><path d="M8.5 1.5l5.5 2v4c0 3.5-2.3 6.4-5.5 8-3.2-1.6-5.5-4.5-5.5-8v-4l5.5-2z" stroke="currentColor" stroke-width="1.4"/><path d="M6 8.5l1.6 1.6L11.5 6" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        @endif
                                    </span>
                                    <span>
                                        <span class="block text-xs font-black text-gray-800">{{ $item['title'] }}</span>
                                        <span class="block text-[10px] text-gray-500 mt-0.5">{{ $item['desc'] }}</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <a href="{{ route('courses.index') }}" class="nav-link text-gray-600 font-bold text-sm px-4 py-2 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition">@lang('core::app.navbar.explore')</a>
            @if(Route::has('teacher-applications.create'))
                <a href="{{ route('teacher-applications.create', [], false) }}" class="nav-link text-gray-600 font-bold text-sm px-4 py-2 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition">@lang('core::app.navbar.become_teacher')</a>
            @endif
            <a href="#" class="nav-link text-gray-600 font-bold text-sm px-4 py-2 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition">@lang('core::app.navbar.exam_prep')</a>
            <a href="#" id="btn-pricing" class="nav-link text-gray-600 font-bold text-sm px-4 py-2 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition">@lang('core::app.navbar.pricing')</a>
            <a href="#" id="btn-news" class="nav-link text-gray-600 font-bold text-sm px-4 py-2 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition">@lang('core::app.navbar.news')</a>
            <a href="#" id="btn-contact" class="nav-link text-gray-600 font-bold text-sm px-4 py-2 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition">@lang('core::app.navbar.contact')</a>
        </div>

        <div class="flex items-center gap-1 bg-gray-100 rounded-xl p-1">
            <a href="{{ route('lang.switch', ['locale' => 'vi'], false) }}"
               class="text-xs font-black px-3 py-1.5 rounded-lg transition-all {{ app()->getLocale() === 'vi' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-400 hover:text-blue-600' }}">
                VI
            </a>
            <a href="{{ route('lang.switch', ['locale' => 'en'], false) }}"
               class="text-xs font-black px-3 py-1.5 rounded-lg transition-all {{ app()->getLocale() === 'en' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-400 hover:text-blue-600' }}">
                EN
            </a>
        </div>

        <a href="{{ route('login', [], false) }}" class="rounded-full bg-blue-500 px-6 py-2.5 text-sm font-black text-white shadow-[0_5px_0_#9a3412] transition-all hover:-translate-y-0.5 hover:bg-blue-400 hover:shadow-[0_7px_0_#9a3412] active:translate-y-1 active:shadow-none">
            @lang('core::app.navbar.login')
        </a>
    </div>
</nav>
