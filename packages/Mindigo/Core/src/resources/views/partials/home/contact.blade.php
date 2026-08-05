{{-- Contact Section --}}
<section id="section-contact" class="hidden">
    <div class="max-w-7xl mx-auto px-10 py-20">

        {{-- Header --}}
        <div class="text-center mb-16">
            <span class="inline-block bg-green-50 text-green-700 text-xs font-bold px-4 py-1.5 rounded-full mb-4">@lang('core::app.contact.header_badge')</span>
            <h1 class="text-4xl font-black text-gray-900 mb-4">@lang('core::app.contact.header_title')</h1>
            <p class="text-gray-500 text-sm max-w-lg mx-auto leading-relaxed">@lang('core::app.contact.header_description')</p>
        </div>

        {{-- Main Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-16">

            {{-- Form --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-sm">
                <h2 class="text-lg font-black text-gray-800 mb-6">@lang('core::app.contact.form_title')</h2>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="text-xs font-bold text-gray-500 mb-1.5 block">@lang('core::app.contact.label_name')</label>
                        <input type="text" placeholder="@lang('core::app.contact.placeholder_name')" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-green-400 focus:ring-2 focus:ring-green-100 transition" />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 mb-1.5 block">@lang('core::app.contact.label_email')</label>
                        <input type="email" placeholder="@lang('core::app.contact.placeholder_email')" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-green-400 focus:ring-2 focus:ring-green-100 transition" />
                    </div>
                </div>
                <div class="mb-4">
                    <label class="text-xs font-bold text-gray-500 mb-1.5 block">@lang('core::app.contact.label_phone')</label>
                    <input type="tel" placeholder="@lang('core::app.contact.placeholder_phone')" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-green-400 focus:ring-2 focus:ring-green-100 transition" />
                </div>
                <div class="mb-4">
                    <label class="text-xs font-bold text-gray-500 mb-1.5 block">@lang('core::app.contact.label_subject')</label>
                    <select class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-green-400 focus:ring-2 focus:ring-green-100 transition text-gray-600">
                        <option>@lang('core::app.contact.subject_technical')</option>
                        <option>@lang('core::app.contact.subject_product')</option>
                        <option>@lang('core::app.contact.subject_business')</option>
                        <option>@lang('core::app.contact.subject_bug')</option>
                        <option>@lang('core::app.contact.subject_other')</option>
                    </select>
                </div>
                <div class="mb-6">
                    <label class="text-xs font-bold text-gray-500 mb-1.5 block">@lang('core::app.contact.label_message')</label>
                    <textarea rows="4" placeholder="@lang('core::app.contact.placeholder_message')" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-green-400 focus:ring-2 focus:ring-green-100 transition resize-none"></textarea>
                </div>
                <button class="w-full bg-green-500 hover:bg-green-400 active:bg-green-600 text-white font-black text-sm py-3 rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 active:shadow-none active:translate-y-1 transition-all">
                    @lang('core::app.contact.button_send')
                </button>
            </div>

            {{-- Info --}}
            <div class="flex flex-col gap-4">

                {{-- Brand card --}}
                <div class="bg-green-500 rounded-2xl overflow-hidden text-white relative">
                    <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&q=80"
                         alt="Mindigo"
                         class="w-full h-40 object-cover opacity-60" />
                    <div class="p-6">
                        <div class="text-xs opacity-80 mb-1">@lang('core::app.contact.brand_tagline')</div>
                        <div class="text-2xl font-black mb-2">@lang('core::app.contact.brand_name')</div>
                        <p class="text-sm opacity-85 leading-relaxed mb-4">@lang('core::app.contact.brand_description')</p>
                        <div class="flex gap-2 flex-wrap">
                            <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">@lang('core::app.contact.brand_users')</span>
                            <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">@lang('core::app.contact.brand_ai')</span>
                            <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">@lang('core::app.contact.brand_madein')</span>
                        </div>
                    </div>
                </div>

                {{-- Info cards --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-4 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-black text-gray-800 mb-0.5">@lang('core::app.contact.info_address')</div>
                        <div class="text-xs text-gray-500 leading-relaxed">@lang('core::app.contact.address_detail')</div>
                    </div>
                </div>

                <div class="bg-white border border-gray-100 rounded-2xl p-4 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-black text-gray-800 mb-0.5">@lang('core::app.contact.info_hotline')</div>
                        <div class="text-xs text-gray-500">@lang('core::app.contact.hotline_number')</div>
                        <div class="text-xs text-gray-400 mt-0.5">@lang('core::app.contact.hotline_time')</div>
                    </div>
                </div>

                <div class="bg-white border border-gray-100 rounded-2xl p-4 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-black text-gray-800 mb-0.5">@lang('core::app.contact.info_email')</div>
                        <div class="text-xs text-green-500">@lang('core::app.contact.email_value')</div>
                        <div class="text-xs text-gray-400 mt-0.5">@lang('core::app.contact.email_response')</div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Map --}}
        <div class="mb-16">
            <div class="text-xs font-bold text-green-700 bg-green-50 rounded-full px-4 py-1.5 inline-block mb-4">@lang('core::app.contact.map_title')</div>
            <h2 class="text-xl font-black text-gray-800 mb-6">@lang('core::app.contact.map_heading')</h2>
            <div class="rounded-2xl overflow-hidden border border-gray-100">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.4946508904!2d106.6884!3d10.7769!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTDCsDQ2JzM3LjAiTiAxMDbCsDQxJzE4LjIiRQ!5e0!3m2!1svi!2svn!4v1234567890"
                    width="100%" height="300" style="border:0; display:block;" allowfullscreen loading="lazy">
                </iframe>
            </div>
        </div>

        {{-- FAQ --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-8">
            <div class="text-xs font-bold text-green-700 bg-green-50 rounded-full px-4 py-1.5 inline-block mb-4">@lang('core::app.contact.faq_title')</div>
            <h2 class="text-xl font-black text-gray-800 mb-6">@lang('core::app.contact.faq_heading')</h2>
            <div class="divide-y divide-gray-100">
                @foreach(trans('core::app.contact.faq_items') as $faq)
                <div x-data="{ open: false }" class="py-4 cursor-pointer" @click="open = !open">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-black text-gray-800">{{ $faq['q'] }}</span>
                        <svg class="w-4 h-4 text-green-500 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div x-show="open" x-transition class="text-xs text-gray-500 leading-relaxed mt-3">{{ $faq['a'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
