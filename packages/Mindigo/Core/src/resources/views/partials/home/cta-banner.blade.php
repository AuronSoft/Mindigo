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
            {{-- Right: Quiz cards --}}
            <div class="flex gap-4 items-start shrink-0">
                {{-- Card 1 --}}
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden w-56">
                    <img src="https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=400&q=80" class="w-full h-32 object-cover" alt=""/>
                    <div class="p-4">
                        <p class="font-black text-gray-800 text-sm mb-1">@lang('core::app.cta.card_1_question')</p>
                        <p class="text-gray-500 text-xs mb-3 leading-relaxed">@lang('core::app.cta.card_1_text')</p>
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2 text-xs text-green-600 font-bold bg-green-50 rounded-lg px-2 py-1.5">
                                <div class="w-4 h-4 bg-green-500 rounded-full flex items-center justify-center shrink-0"><svg width="8" height="8" fill="none" viewBox="0 0 8 8"><path d="M1.5 4l1.5 1.5 3.5-3" stroke="white" stroke-width="1.2" stroke-linecap="round"/></svg></div>
                                @lang('core::app.cta.card_1_a')
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-400 px-2 py-1">
                                <div class="w-4 h-4 border border-gray-300 rounded-full shrink-0"></div>
                                @lang('core::app.cta.card_1_b')
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-400 px-2 py-1">
                                <div class="w-4 h-4 border border-gray-300 rounded-full shrink-0"></div>
                                @lang('core::app.cta.card_1_c')
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 2 --}}
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden w-56 mt-10">
                    <img src="https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=400&q=80" class="w-full h-32 object-cover" alt=""/>
                    <div class="p-4">
                        <p class="font-black text-gray-800 text-sm mb-1">@lang('core::app.cta.card_2_question')</p>
                        <p class="text-gray-500 text-xs mb-3 leading-relaxed">@lang('core::app.cta.card_2_text')</p>
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2 text-xs text-gray-400 px-2 py-1">
                                <div class="w-4 h-4 border border-gray-300 rounded-full shrink-0"></div>
                                @lang('core::app.cta.card_2_a')
                            </div>
                            <div class="flex items-center gap-2 text-xs text-blue-600 font-bold bg-blue-50 rounded-lg px-2 py-1.5">
                                <div class="w-4 h-4 bg-blue-500 rounded-full flex items-center justify-center shrink-0"><svg width="8" height="8" fill="none" viewBox="0 0 8 8"><path d="M1.5 4l1.5 1.5 3.5-3" stroke="white" stroke-width="1.2" stroke-linecap="round"/></svg></div>
                                @lang('core::app.cta.card_2_b')
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-400 px-2 py-1">
                                <div class="w-4 h-4 border border-gray-300 rounded-full shrink-0"></div>
                                @lang('core::app.cta.card_2_c')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Footer --}}
<footer class="bg-gray-900 pt-32 pb-16 px-10">
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
                    <span class="text-xl font-black text-green-600 tracking-tight">mindigo</span>
                </a>
                <p class="text-gray-400 text-sm leading-relaxed">@lang('core::app.footer.tagline')</p>
                <div class="flex items-center gap-3">
                    <a href="#" class="w-8 h-8 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-green-500 transition">
                        <svg width="16" height="16" fill="white" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                    </a>
                    <a href="#" class="w-8 h-8 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-green-500 transition">
                        <svg width="16" height="16" fill="white" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.52V6.76a4.85 4.85 0 01-1.01-.07z"/></svg>
                    </a>
                    <a href="#" class="w-8 h-8 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-green-500 transition">
                        <svg width="16" height="16" fill="white" viewBox="0 0 24 24"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 001.95-1.95A29 29 0 0023 12a29 29 0 00-.46-5.58zM9.75 15.02V8.98L15.5 12l-5.75 3.02z"/></svg>
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
                    @foreach(['footer.legal_1', 'footer.legal_2', 'footer.legal_3', 'footer.legal_4', 'footer.legal_5'] as $item)
                    <a href="{{ match ($item) {
                        'footer.legal_1' => route('terms', [], false),
                        'footer.legal_2' => route('privacy', [], false),
                        'footer.legal_3' => route('technical-support-policy', [], false),
                        'footer.legal_4' => route('ai-assistant-policy', [], false),
                        'footer.legal_5' => route('refund-policy', [], false),
                        default => '#',
                    } }}" class="text-gray-400 text-sm hover:text-green-400 transition">@lang('core::app.' . $item)</a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="border-t border-gray-800 pt-6 flex flex-col md:flex-row items-center justify-between gap-3">
            <p class="text-gray-500 text-xs">@lang('core::app.footer.copyright')</p>
            <p class="text-gray-600 text-xs">@lang('core::app.footer.built_with')</p>
        </div>
    </div>
</footer>
