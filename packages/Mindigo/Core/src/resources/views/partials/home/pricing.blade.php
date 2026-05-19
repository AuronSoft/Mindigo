{{-- Pricing Section --}}
<section id="section-pricing" class="hidden">
    <div class="max-w-7xl mx-auto px-10 py-20">

        {{-- Header --}}
        <div class="text-center mb-16">
            <span class="inline-block bg-green-50 text-green-700 text-xs font-bold px-4 py-1.5 rounded-full mb-4">@lang('core::app.pricing.header_badge')</span>
            <h1 class="text-4xl font-black text-gray-900 mb-4">@lang('core::app.pricing.header_title_plain') <span class="text-green-500">@lang('core::app.pricing.header_title_highlight')</span></h1>
            <p class="text-gray-500 text-sm max-w-lg mx-auto leading-relaxed">@lang('core::app.pricing.header_description')</p>

            {{-- Toggle --}}
            <div class="inline-flex items-center gap-3 mt-8 bg-gray-100 rounded-2xl p-1.5">
                <button id="toggle-monthly"
                    onclick="setPricingPeriod('monthly')"
                    class="text-sm font-black px-5 py-2 rounded-xl transition-all bg-white text-green-600 shadow-sm">
                    @lang('core::app.pricing.toggle_monthly')
                </button>
                <button id="toggle-yearly"
                    onclick="setPricingPeriod('yearly')"
                    class="text-sm font-black px-5 py-2 rounded-xl transition-all text-gray-400 hover:text-gray-600">
                    @lang('core::app.pricing.toggle_yearly')
                    <span class="ml-1.5 bg-green-500 text-white text-xs font-black px-2 py-0.5 rounded-full">@lang('core::app.pricing.save_percent')</span>
                </button>
            </div>
        </div>

        {{-- Pricing Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-20">

            {{-- Free --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-sm flex flex-col hover:shadow-md transition-shadow">
                <div class="mb-6">
                    <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </div>
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">@lang('core::app.pricing.free.plan_name')</div>
                    <div class="flex items-end gap-1 mb-1">
                        <span class="text-4xl font-black text-gray-900">@lang('core::app.pricing.free.price')</span>
                    </div>
                    <div class="text-xs text-gray-400">@lang('core::app.pricing.free.period')</div>
                </div>

                <div class="flex flex-col gap-3 mb-8 flex-1">
                    @foreach([
                        ['ok' => true,  'text' => __('core::app.pricing.free_features.max_5_exams')],
                        ['ok' => true,  'text' => __('core::app.pricing.free_features.max_30_questions')],
                        ['ok' => true,  'text' => __('core::app.pricing.free_features.max_10_practice')],
                        ['ok' => true,  'text' => __('core::app.pricing.free_features.basic_quiz')],
                        ['ok' => false, 'text' => __('core::app.pricing.features.ai_generate')],
                        ['ok' => false, 'text' => __('core::app.pricing.features.virtual_exam')],
                        ['ok' => false, 'text' => __('core::app.pricing.features.class_management')],
                        ['ok' => false, 'text' => __('core::app.pricing.features.priority_support')],
                    ] as $feature)
                    <div class="flex items-center gap-3">
                        @if($feature['ok'])
                        <div class="w-5 h-5 rounded-full bg-green-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm text-gray-700">{{ $feature['text'] }}</span>
                        @else
                        <div class="w-5 h-5 rounded-full bg-gray-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <span class="text-sm text-gray-400">{{ $feature['text'] }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>

                <a href="#" class="block text-center border-2 border-gray-200 text-gray-600 font-black text-sm py-3 rounded-xl hover:border-green-400 hover:text-green-600 transition-all">
                    @lang('core::app.pricing.free.cta')
                </a>
            </div>

            {{-- Pro --}}
            <div class="bg-green-500 rounded-2xl p-8 shadow-[0_8px_0_#15803d] flex flex-col relative overflow-hidden">
                <div class="absolute top-5 right-5">
                    <span class="bg-white text-green-600 text-xs font-black px-3 py-1 rounded-full shadow-sm">&#11088; @lang('core::app.pricing.pro.badge')</span>
                </div>

                <div class="absolute -bottom-8 -right-8 w-40 h-40 bg-green-400 rounded-full opacity-30"></div>
                <div class="absolute -top-6 -left-6 w-28 h-28 bg-green-600 rounded-full opacity-20"></div>

                <div class="mb-6 relative">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div class="text-xs font-bold text-white/70 uppercase tracking-wider mb-1">@lang('core::app.pricing.pro.plan_name')</div>
                    <div class="flex items-end gap-1 mb-1">
                        <span class="pricing-price text-4xl font-black text-white"
                              data-monthly="{{ __('core::app.pricing.pro.price_monthly') }}"
                              data-yearly="{{ __('core::app.pricing.pro.price_yearly') }}">
                            @lang('core::app.pricing.pro.price_monthly')
                        </span>
                        <span class="pricing-period text-white/70 text-sm mb-1" data-period="@lang('core::app.pricing.pro.period')">@lang('core::app.pricing.pro.period')</span>
                    </div>
                    <div class="pricing-yearly-note text-xs text-white/60 hidden">@lang('core::app.pricing.pro.yearly_note')</div>
                    <div class="pricing-monthly-note text-xs text-white/60">@lang('core::app.pricing.pro.monthly_note')</div>
                </div>

                <div class="flex flex-col gap-3 mb-8 flex-1 relative">
                    @foreach([
                        __('core::app.pricing.features.unlimited_exams'),
                        __('core::app.pricing.features.unlimited_questions'),
                        __('core::app.pricing.features.unlimited_practice'),
                        __('core::app.pricing.features.ai_generate'),
                        __('core::app.pricing.features.upload_document'),
                        __('core::app.pricing.features.virtual_exam'),
                        __('core::app.pricing.features.class_management'),
                        __('core::app.pricing.features.priority_support'),
                    ] as $feature)
                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm text-white">{{ $feature }}</span>
                    </div>
                    @endforeach
                </div>

                <a href="#" class="relative block text-center bg-white text-green-600 font-black text-sm py-3 rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 active:shadow-none active:translate-y-1 transition-all">
                    @lang('core::app.pricing.pro.cta')
                </a>
            </div>

            {{-- Enterprise --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-sm flex flex-col hover:shadow-md transition-shadow">
                <div class="mb-6">
                    <div class="w-10 h-10 rounded-xl bg-gray-900 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">@lang('core::app.pricing.enterprise.plan_name')</div>
                    <div class="flex items-end gap-1 mb-1">
                        <span class="text-4xl font-black text-gray-900">@lang('core::app.pricing.enterprise.price')</span>
                    </div>
                    <div class="text-xs text-gray-400">@lang('core::app.pricing.enterprise.period')</div>
                </div>

                <div class="flex flex-col gap-3 mb-8 flex-1">
                    @foreach([
                        __('core::app.pricing.features.unlimited_exams'),
                        __('core::app.pricing.features.unlimited_questions'),
                        __('core::app.pricing.features.unlimited_practice'),
                        __('core::app.pricing.features.ai_generate'),
                        __('core::app.pricing.features.upload_document'),
                        __('core::app.pricing.features.virtual_exam'),
                        __('core::app.pricing.features.class_management'),
                        __('core::app.pricing.features.priority_support'),
                    ] as $feature)
                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 rounded-full bg-gray-900 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm text-gray-700">{{ $feature }}</span>
                    </div>
                    @endforeach
                </div>

                <a href="#" id="btn-contact-from-pricing" class="block text-center bg-gray-900 text-white font-black text-sm py-3 rounded-xl shadow-[0_4px_0_#374151] hover:shadow-[0_2px_0_#374151] hover:translate-y-0.5 active:shadow-none active:translate-y-1 transition-all">
                    @lang('core::app.pricing.enterprise.cta')
                </a>
            </div>
        </div>

        {{-- Feature comparison table --}}
        <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm mb-16">
            <div class="px-8 py-6 border-b border-gray-100">
                <div class="text-xs font-bold text-green-700 bg-green-50 rounded-full px-4 py-1.5 inline-block mb-2">@lang('core::app.pricing.comparison.title')</div>
                <h2 class="text-xl font-black text-gray-800">@lang('core::app.pricing.comparison.heading')</h2>
            </div>
            <div class="overflow-x-auto">
                @php
                    $comparisonRows = [
                        ['name' => __('core::app.pricing.features.unlimited_exams'),    'free' => __('core::app.pricing.comparison.limits.exams'),     'pro' => true,  'ent' => true],
                        ['name' => __('core::app.pricing.features.unlimited_questions'), 'free' => __('core::app.pricing.comparison.limits.questions'), 'pro' => true,  'ent' => true],
                        ['name' => __('core::app.pricing.features.unlimited_practice'),  'free' => __('core::app.pricing.comparison.limits.practice'),  'pro' => true,  'ent' => true],
                        ['name' => __('core::app.pricing.features.ai_generate'),         'free' => false,                                             'pro' => true,  'ent' => true],
                        ['name' => __('core::app.pricing.features.upload_document'),     'free' => false,                                             'pro' => true,  'ent' => true],
                        ['name' => __('core::app.pricing.features.virtual_exam'),        'free' => false,                                             'pro' => true,  'ent' => true],
                        ['name' => __('core::app.pricing.features.class_management'),    'free' => false,                                             'pro' => true,  'ent' => true],
                        ['name' => __('core::app.pricing.comparison.api_webhook'),       'free' => false,                                             'pro' => false, 'ent' => true],
                        ['name' => __('core::app.pricing.comparison.white_label'),       'free' => false,                                             'pro' => false, 'ent' => true],
                        ['name' => __('core::app.pricing.comparison.sso_saml'),          'free' => false,                                             'pro' => false, 'ent' => true],
                        ['name' => __('core::app.pricing.comparison.sla'),               'free' => false,                                             'pro' => false, 'ent' => true],
                        ['name' => __('core::app.pricing.features.priority_support'),    'free' => __('core::app.pricing.comparison.support.email'),  'pro' => __('core::app.pricing.trust.support_247'), 'ent' => __('core::app.pricing.comparison.support.slack_teams')],
                    ];
                @endphp
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left px-8 py-4 text-sm font-black text-gray-500 w-1/2">@lang('core::app.pricing.comparison.feature')</th>
                            <th class="text-center px-4 py-4 text-sm font-black text-gray-500">@lang('core::app.pricing.comparison.free_plan')</th>
                            <th class="text-center px-4 py-4 text-sm font-black text-green-600 bg-green-50">@lang('core::app.pricing.comparison.pro_plan')</th>
                            <th class="text-center px-4 py-4 text-sm font-black text-gray-500">@lang('core::app.pricing.comparison.enterprise_plan')</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($comparisonRows as $row)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-8 py-3.5 text-sm font-bold text-gray-700">{{ $row['name'] }}</td>
                            <td class="px-4 py-3.5 text-center">
                                @if($row['free'] === true)
                                    <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @elseif($row['free'] === false)
                                    <svg class="w-4 h-4 text-gray-200 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                @else
                                    <span class="text-xs text-gray-500">{{ $row['free'] }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center bg-green-50/50">
                                @if($row['pro'] === true)
                                    <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @elseif($row['pro'] === false)
                                    <svg class="w-4 h-4 text-gray-200 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                @else
                                    <span class="text-xs text-green-600 font-bold">{{ $row['pro'] }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($row['ent'] === true)
                                    <svg class="w-5 h-5 text-gray-700 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @elseif($row['ent'] === false)
                                    <svg class="w-4 h-4 text-gray-200 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                @else
                                    <span class="text-xs text-gray-500">{{ $row['ent'] }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Trust badges --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-16">
            @foreach([
                ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'label' => __('core::app.pricing.trust.ssl'),            'sub' => __('core::app.pricing.trust.ssl_sub')],
                ['icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',                                                                                                                                            'label' => __('core::app.pricing.trust.cancel_anytime'), 'sub' => __('core::app.pricing.trust.cancel_sub')],
                ['icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',                                                                                                                                                              'label' => __('core::app.pricing.trust.secure_payment'), 'sub' => __('core::app.pricing.trust.secure_sub')],
                ['icon' => 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z',                                                                                   'label' => __('core::app.pricing.trust.support_247'),    'sub' => __('core::app.pricing.trust.support_sub')],
            ] as $badge)
            <div class="bg-white border border-gray-100 rounded-2xl p-5 flex items-center gap-4 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $badge['icon'] }}"/></svg>
                </div>
                <div>
                    <div class="text-sm font-black text-gray-800">{{ $badge['label'] }}</div>
                    <div class="text-xs text-gray-400">{{ $badge['sub'] }}</div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- FAQ --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-8">
            <div class="text-xs font-bold text-green-700 bg-green-50 rounded-full px-4 py-1.5 inline-block mb-4">@lang('core::app.pricing.faq.title')</div>
            <h2 class="text-xl font-black text-gray-800 mb-6">@lang('core::app.pricing.faq.heading')</h2>
            <div class="divide-y divide-gray-100">
                @foreach(__('core::app.pricing.faq.items') as $faq)
                <div x-data="{ open: false }" class="py-4 cursor-pointer" @click="open = !open">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-black text-gray-800">{{ $faq['q'] }}</span>
                        <svg class="w-4 h-4 text-green-500 transition-transform flex-shrink-0 ml-4" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div x-show="open" x-transition class="text-xs text-gray-500 leading-relaxed mt-3">{{ $faq['a'] }}</div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</section>

<script>
    function setPricingPeriod(period) {
        const btnMonthly = document.getElementById('toggle-monthly');
        const btnYearly  = document.getElementById('toggle-yearly');
        const prices     = document.querySelectorAll('.pricing-price');
        const periods    = document.querySelectorAll('.pricing-period');
        const yearlyNotes  = document.querySelectorAll('.pricing-yearly-note');
        const monthlyNotes = document.querySelectorAll('.pricing-monthly-note');

        if (period === 'yearly') {
            btnYearly.classList.add('bg-white', 'text-green-600', 'shadow-sm');
            btnYearly.classList.remove('text-gray-400');
            btnMonthly.classList.remove('bg-white', 'text-green-600', 'shadow-sm');
            btnMonthly.classList.add('text-gray-400');
            prices.forEach(el => el.textContent = el.dataset.yearly);
            periods.forEach(el => el.textContent = el.dataset.period);
            yearlyNotes.forEach(el => el.classList.remove('hidden'));
            monthlyNotes.forEach(el => el.classList.add('hidden'));
        } else {
            btnMonthly.classList.add('bg-white', 'text-green-600', 'shadow-sm');
            btnMonthly.classList.remove('text-gray-400');
            btnYearly.classList.remove('bg-white', 'text-green-600', 'shadow-sm');
            btnYearly.classList.add('text-gray-400');
            prices.forEach(el => el.textContent = el.dataset.monthly);
            periods.forEach(el => el.textContent = el.dataset.period);
            yearlyNotes.forEach(el => el.classList.add('hidden'));
            monthlyNotes.forEach(el => el.classList.remove('hidden'));
        }
    }

    document.getElementById('btn-contact-from-pricing')?.addEventListener('click', function(e) {
        e.preventDefault();
        const pricingSection = document.getElementById('section-pricing');
        const contactSection = document.getElementById('section-contact');
        const homeSections   = document.getElementById('home-sections');
        const btnPricing     = document.getElementById('btn-pricing');
        const btnContact     = document.getElementById('btn-contact');

        pricingSection.classList.add('hidden');
        homeSections.classList.add('hidden');
        contactSection.classList.remove('hidden');
        if (btnPricing) btnPricing.classList.remove('bg-green-50', 'text-green-600');
        if (btnContact) btnContact.classList.add('bg-green-50', 'text-green-600');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
</script>
