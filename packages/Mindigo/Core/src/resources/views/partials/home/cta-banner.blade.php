{{-- CTA nổi --}}
<div class="relative bg-white">
    <div class="max-w-5xl mx-auto px-10">
        <div class="bg-green-500 rounded-3xl px-10 py-10 flex flex-col lg:flex-row items-center justify-between gap-10 shadow-2xl translate-y-16">
            {{-- Left --}}
            <div class="flex-1 flex flex-col gap-5">
                <h2 class="text-3xl font-black text-white leading-tight">
                    @lang('core::app.cta.heading_1') <span class="text-green-900">@lang('core::app.cta.heading_2')</span> @lang('core::app.cta.heading_3')
                </h2>
                <a href="#" class="bg-green-700 hover:bg-green-800 text-white font-black text-sm px-6 py-3 rounded-xl w-fit shadow-[0_4px_0_#14532d] hover:shadow-[0_2px_0_#14532d] hover:translate-y-0.5 transition-all">
                    @lang('core::app.cta.btn')
                </a>
                <div class="flex items-center gap-6 flex-wrap">
                    <div class="flex items-center gap-2 text-white font-bold text-sm">
                        <div class="w-5 h-5 bg-green-700 rounded-full flex items-center justify-center">
                            <svg width="10" height="10" fill="none" viewBox="0 0 10 10"><path d="M2 5l2 2 4-4" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        @lang('core::app.cta.badge_1')
                    </div>
                    <div class="flex items-center gap-2 text-white font-bold text-sm">
                        <div class="w-5 h-5 bg-green-700 rounded-full flex items-center justify-center">
                            <svg width="10" height="10" fill="none" viewBox="0 0 10 10"><path d="M2 5l2 2 4-4" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        @lang('core::app.cta.badge_2')
                    </div>
                </div>
            </div>
            {{-- Right: LMS cards --}}
            <div class="flex gap-4 items-start shrink-0">
                {{-- Card 1 --}}
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden w-56">
                    <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=400&q=80" class="w-full h-32 object-cover" alt=""/>
                    <div class="p-4">
                        <p class="font-black text-gray-800 text-sm mb-1">@lang('core::app.cta.card_1_question')</p>
                        <p class="text-gray-500 text-xs mb-3 leading-relaxed">@lang('core::app.cta.card_1_text')</p>
                        <div class="space-y-2">
                            <div class="rounded-lg bg-green-50 px-2.5 py-2">
                                <div class="mb-1 flex items-center justify-between gap-2">
                                    <span class="text-xs font-black text-green-700">@lang('core::app.cta.card_1_a')</span>
                                    <span class="text-[10px] font-black text-green-500">84%</span>
                                </div>
                                <div class="h-1.5 overflow-hidden rounded-full bg-green-100">
                                    <div class="h-full w-[84%] rounded-full bg-green-500"></div>
                                </div>
                            </div>
                            <div class="rounded-lg bg-gray-50 px-2.5 py-2">
                                <div class="mb-1 flex items-center justify-between gap-2">
                                    <span class="text-xs font-bold text-gray-500">@lang('core::app.cta.card_1_b')</span>
                                    <span class="text-[10px] font-black text-gray-400">12</span>
                                </div>
                                <div class="h-1.5 overflow-hidden rounded-full bg-gray-200">
                                    <div class="h-full w-[62%] rounded-full bg-gray-400"></div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between rounded-lg border border-gray-100 px-2.5 py-2">
                                <span class="text-xs font-bold text-gray-500">@lang('core::app.cta.card_1_c')</span>
                                <span class="rounded-md bg-green-500 px-2 py-0.5 text-[10px] font-black text-white">LIVE</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 2 --}}
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden w-56 mt-10">
                    <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=400&q=80" class="w-full h-32 object-cover" alt=""/>
                    <div class="p-4">
                        <p class="font-black text-gray-800 text-sm mb-1">@lang('core::app.cta.card_2_question')</p>
                        <p class="text-gray-500 text-xs mb-3 leading-relaxed">@lang('core::app.cta.card_2_text')</p>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between rounded-lg bg-blue-50 px-2.5 py-2">
                                <span class="text-xs font-black text-blue-700">@lang('core::app.cta.card_2_a')</span>
                                <span class="text-xs font-black text-blue-600">78%</span>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-green-50 px-2.5 py-2">
                                <span class="text-xs font-black text-green-700">@lang('core::app.cta.card_2_b')</span>
                                <span class="text-xs font-black text-green-600">8.6</span>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-amber-50 px-2.5 py-2">
                                <span class="text-xs font-black text-amber-700">@lang('core::app.cta.card_2_c')</span>
                                <span class="text-xs font-black text-amber-600">14</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Footer --}}
<footer class="bg-gray-900 pt-32 pb-6 px-10">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
            {{-- Brand --}}
            <div class="flex flex-col gap-4">
                <a href="#" class="flex items-center gap-2">
                    <svg width="36" height="36" viewBox="0 0 200 220" fill="none">
                        <path d="M48 160 L22 148 L38 158 L16 152 L35 164" fill="#15803d" stroke="#14532d" stroke-width="1"/>
                        <circle cx="105" cy="145" r="90" fill="#22c55e" stroke="#14532d" stroke-width="3"/>
                        <ellipse cx="115" cy="185" rx="55" ry="38" fill="#86efac" stroke="#14532d" stroke-width="2"/>
                        <ellipse cx="80" cy="170" rx="12" ry="9" fill="#16a34a" opacity="0.5"/>
                        <ellipse cx="110" cy="175" rx="10" ry="7" fill="#16a34a" opacity="0.4"/>
                        <path d="M95 58 Q85 20 105 8 Q118 22 112 58" fill="#16a34a" stroke="#14532d" stroke-width="2.5" stroke-linejoin="round"/>
                        <path d="M108 55 Q100 18 118 10 Q128 26 120 56" fill="#22c55e" stroke="#14532d" stroke-width="2" stroke-linejoin="round"/>
                        <path d="M52 118 L95 108 L88 128 Z" fill="#14532d"/>
                        <path d="M148 118 L108 108 L114 128 Z" fill="#14532d"/>
                        <circle cx="82" cy="135" r="20" fill="white" stroke="#14532d" stroke-width="2"/>
                        <circle cx="86" cy="138" r="12" fill="#14532d"/>
                        <circle cx="91" cy="132" r="5" fill="white"/>
                        <circle cx="128" cy="135" r="20" fill="white" stroke="#14532d" stroke-width="2"/>
                        <circle cx="132" cy="138" r="12" fill="#14532d"/>
                        <circle cx="137" cy="132" r="5" fill="white"/>
                        <path d="M85 158 Q105 148 130 158 L118 175 Q105 180 92 175 Z" fill="#f59e0b" stroke="#14532d" stroke-width="2"/>
                        <path d="M92 175 Q105 182 118 175 L112 190 Q105 195 98 190 Z" fill="#d97706" stroke="#14532d" stroke-width="2"/>
                    </svg>
                    <span class="text-xl font-black text-green-600 tracking-tight">Mindigo</span>
                </a>
                <p class="text-gray-400 text-sm leading-relaxed">@lang('core::app.footer.tagline')</p>
                <div class="flex items-center gap-3">
                    <a href="#" aria-label="Facebook" title="Facebook" class="flex h-9 w-9 items-center justify-center transition hover:-translate-y-0.5 hover:scale-110">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="11" fill="#1877F2"/>
                            <path fill="#fff" d="M13.52 20v-7.3h2.45l.37-2.85h-2.82V8.03c0-.82.23-1.38 1.41-1.38h1.5V4.11A20 20 0 0 0 14.24 4c-2.17 0-3.66 1.33-3.66 3.76v2.09H8.12v2.85h2.46V20h2.94Z"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="TikTok" title="TikTok" class="flex h-9 w-9 items-center justify-center transition hover:-translate-y-0.5 hover:scale-110">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="#25F4EE" d="M16.62 6.61a5.13 5.13 0 0 1-1.26-3.37h-3.08v12.39a2.58 2.58 0 1 1-2.58-2.58c.26 0 .51.04.75.11V10a5.75 5.75 0 1 0 4.91 5.69V9.4a8.2 8.2 0 0 0 4.79 1.53V7.86a5.1 5.1 0 0 1-3.53-1.25Z" transform="translate(-.7 .55)"/>
                            <path fill="#FE2C55" d="M16.62 6.61a5.13 5.13 0 0 1-1.26-3.37h-3.08v12.39a2.58 2.58 0 1 1-2.58-2.58c.26 0 .51.04.75.11V10a5.75 5.75 0 1 0 4.91 5.69V9.4a8.2 8.2 0 0 0 4.79 1.53V7.86a5.1 5.1 0 0 1-3.53-1.25Z" transform="translate(.7 -.35)"/>
                            <path fill="#000" d="M16.62 6.61a5.13 5.13 0 0 1-1.26-3.37h-3.08v12.39a2.58 2.58 0 1 1-2.58-2.58c.26 0 .51.04.75.11V10a5.75 5.75 0 1 0 4.91 5.69V9.4a8.2 8.2 0 0 0 4.79 1.53V7.86a5.1 5.1 0 0 1-3.53-1.25Z"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="YouTube" title="YouTube" class="flex h-9 w-9 items-center justify-center transition hover:-translate-y-0.5 hover:scale-110">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="#FF0000" d="M23.5 6.19a3 3 0 0 0-2.11-2.12C19.52 3.57 12 3.57 12 3.57s-7.52 0-9.39.5A3 3 0 0 0 .5 6.19 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.81 3 3 0 0 0 2.11 2.12c1.87.5 9.39.5 9.39.5s7.52 0 9.39-.5a3 3 0 0 0 2.11-2.12A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.81Z"/>
                            <path fill="#fff" d="m9.6 15.6 6.23-3.6L9.6 8.4v7.2Z"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Sản phẩm --}}
            <div>
                <p class="text-white font-black text-sm mb-5">@lang('core::app.footer.col_product')</p>
                <div class="flex flex-col gap-3">
                    @foreach(['footer.product_1', 'footer.product_2', 'footer.product_3'] as $item)
                    <a href="#" class="text-gray-400 text-sm hover:text-green-400 transition">@lang('core::app.' . $item)</a>
                    @endforeach
                </div>
            </div>

            {{-- Tài nguyên --}}
            <div>
                <p class="text-white font-black text-sm mb-5">@lang('core::app.footer.col_resource')</p>
                <div class="flex flex-col gap-3">
                    @foreach(['footer.resource_1', 'footer.resource_2', 'footer.resource_3', 'footer.resource_4', 'footer.resource_5', 'footer.resource_6'] as $item)
                    <a href="{{ match ($item) {
                        'footer.resource_1' => route('news.index', [], false),
                        'footer.resource_2' => route('exam-tips', [], false),
                        default => '#',
                    } }}" class="text-gray-400 text-sm hover:text-green-400 transition">@lang('core::app.' . $item)</a>
                    @endforeach
                </div>
            </div>

            {{-- Điều khoản --}}
            <div>
                <p class="text-white font-black text-sm mb-5">@lang('core::app.footer.col_legal')</p>
                <div class="flex flex-col gap-3">
                    @foreach(['footer.legal_1', 'footer.legal_2', 'footer.legal_3', 'footer.legal_4', 'footer.legal_5', 'footer.legal_6'] as $item)
                    <a href="{{ match ($item) {
                        'footer.legal_1' => route('terms', [], false),
                        'footer.legal_2' => route('privacy', [], false),
                        'footer.legal_3' => route('technical-support-policy', [], false),
                        'footer.legal_4' => route('ai-assistant-policy', [], false),
                        'footer.legal_5' => route('refund-policy', [], false),
                        'footer.legal_6' => route('tutor-policy', [], false),
                        default => '#',
                    } }}" class="text-gray-400 text-sm hover:text-green-400 transition">@lang('core::app.' . $item)</a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="border-t border-gray-800 pt-6 flex items-center justify-center">
            <p class="text-gray-500 text-xs text-center">
                @lang('core::app.footer.copyright')
            </p>
        </div>
    </div>
</footer>
