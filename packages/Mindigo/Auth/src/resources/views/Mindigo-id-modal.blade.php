<style>
#nid-form .nid-step          { display: none; }
#nid-form[data-step="1"] .nid-step-1 { display: flex; }
#nid-form[data-step="2"] .nid-step-2 { display: flex; }
#nid-form[data-step="3"] .nid-step-3 { display: flex; }
</style>

<div id="MindigoIdOverlay" class="hidden fixed inset-0 z-9999 bg-green-50 overflow-hidden" style="font-family:'Be Vietnam Pro',sans-serif;">

    {{-- Background shapes --}}
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute top-[-10%] right-[-5%] w-[55%] h-[80%] bg-linear-to-br from-green-100 to-green-200 rounded-bl-[80px] rotate-[-8deg] opacity-60"></div>
        <div class="absolute bottom-[-15%] left-[-5%] w-[45%] h-[60%] bg-linear-to-tr from-green-100 to-green-50 rounded-t-[80px] opacity-40"></div>
    </div>

    {{-- Header --}}
    <div class="absolute top-0 left-0 right-0 px-10 py-5 flex items-center justify-between z-20">
        <div class="flex items-center gap-2">
            <div class="w-9 h-9 flex items-center justify-center">
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
            </div>
            <span class="text-sm font-black text-green-600">Auronsoft<span class="text-gray-900">ID</span></span>
        </div>
        <button type="button" data-nid-action="close" class="bg-white/70 border border-black/10 rounded-xl w-8 h-8 flex items-center justify-center cursor-pointer hover:bg-white transition">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#4a6080" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>

    {{-- Card --}}
    <div class="relative z-10 flex items-center justify-center min-h-screen px-6 py-20">
        <div class="bg-white rounded-3xl border border-black/5 w-full max-w-3xl grid grid-cols-2 shadow-2xl shadow-green-200 overflow-hidden">

            {{-- LEFT: Branding --}}
            <div class="p-12 flex flex-col justify-center border-r border-gray-100">
                <div class="flex items-center gap-2 mb-8">
                    <div class="w-9 h-9 flex items-center justify-center">
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
                    </div>
                    <span class="text-base font-black text-green-600">Auronsoft<span class="text-gray-900">ID</span></span>
                </div>
                <h2 class="text-3xl font-black text-gray-900 leading-tight mb-3">
                    @lang('Mindigo-auth::app.auth.login_title')
                </h2>
                <p id="nid-left-sub" class="text-sm text-gray-500 leading-relaxed">
                    @lang('Mindigo-auth::app.auth.login_subtitle', ['platform' => __('Mindigo-auth::app.auth.platform')])
                    <a href="#" class="text-green-600 font-bold">@lang('Mindigo-auth::app.auth.platform')</a>
                </p>
            </div>

            {{-- RIGHT: Steps --}}
            <div id="nid-form" data-step="1" class="p-12 flex flex-col justify-center">

                {{-- STEP 1: Email --}}
                <div class="nid-step nid-step-1 flex-col">
                    <div class="mb-4">
                        <label class="text-sm font-black text-gray-700 block mb-1.5">
                            @lang('Mindigo-auth::app.auth.email')
                        </label>
                        <div class="relative">
                            <input id="nid-email-input" type="email"
                                placeholder="@lang('Mindigo-auth::app.auth.email_placeholder')"
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 pr-10 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                                data-nid-email-input>
                            <div id="nid-email-err-icon" class="hidden absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            </div>
                        </div>
                        <p id="nid-email-error" class="hidden text-xs text-red-500 mt-1.5">
                            @lang('Mindigo-auth::app.auth.email_invalid')
                        </p>
                    </div>
                    <p class="text-xs text-gray-400 leading-relaxed mb-6">
                        @lang('Mindigo-auth::app.auth.incognito_notice')
                        <strong class="text-gray-500 font-bold">@lang('Mindigo-auth::app.auth.incognito_highlight')</strong>
                        @lang('Mindigo-auth::app.auth.incognito_suffix')
                    </p>
                    <button id="nid-email-btn" type="button" data-nid-action="submitEmail" disabled
                        class="w-full bg-green-500 hover:bg-green-400 disabled:bg-gray-200 disabled:cursor-not-allowed text-white font-black text-sm py-3.5 rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 disabled:shadow-none transition-all">
                        @lang('Mindigo-auth::app.auth.continue')
                    </button>
                </div>

                {{-- STEP 2: Magic Link sent --}}
                <div class="nid-step nid-step-2 flex-col items-center text-center">
                    <div class="w-18 h-18 bg-green-100 rounded-full flex items-center justify-center mb-5 mx-auto" style="width:72px;height:72px;">
                        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed mb-6">
                        <strong class="text-gray-900">Auronsoft ID</strong>
                        @lang('Mindigo-auth::app.auth.magic_link_sent')<br>
                        <strong id="nid-ml-email" class="text-green-600"></strong>.<br>
                        <span class="text-gray-400 text-xs">@lang('Mindigo-auth::app.auth.magic_link_check')</span>
                    </p>
                    <div class="flex gap-2 w-full">
                        <button type="button" data-nid-action="switchToOtp"
                            class="flex-1 border border-gray-200 rounded-xl py-2.5 text-sm font-bold text-gray-600 hover:border-green-400 hover:text-green-600 transition">
                            @lang('Mindigo-auth::app.auth.try_other_method')
                        </button>
                        <button id="nid-ml-resend" type="button" data-nid-action="resendMagicLink" disabled
                            class="flex-1 border border-gray-200 rounded-xl py-2.5 text-sm font-bold text-gray-400 hover:border-green-400 hover:text-green-600 disabled:opacity-50 disabled:cursor-not-allowed transition">
                            <span id="nid-ml-resend-text">
                                @lang('Mindigo-auth::app.auth.resend') (<span id="nid-ml-countdown">60</span>s)
                            </span>
                        </button>
                    </div>
                </div>

                {{-- STEP 3: OTP --}}
                <div class="nid-step nid-step-3 flex-col">
                    <p class="text-sm text-gray-500 mb-5 text-center">@lang('Mindigo-auth::app.auth.otp_title')</p>
                    <div id="nid-otp-wrap" class="flex gap-2 justify-center mb-2">
                        @foreach(range(0,5) as $i)
                        <input class="nid-otp-input w-11 h-14 text-center text-xl font-black border-2 border-gray-200 rounded-xl focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100 transition"
                            type="text" inputmode="numeric" maxlength="1" data-idx="{{ $i }}">
                        @endforeach
                    </div>
                    <p id="nid-otp-error" class="hidden text-xs text-red-500 text-center mb-2">
                        @lang('Mindigo-auth::app.auth.otp_invalid')
                    </p>
                    <p class="text-xs text-gray-400 text-center mb-5">
                        @lang('Mindigo-auth::app.auth.otp_not_received')&nbsp;
                        <span id="nid-otp-resend" class="text-gray-400 cursor-default" data-nid-action="resendOtp">
                            @lang('Mindigo-auth::app.auth.resend_otp') (<span id="nid-otp-countdown">60</span>s)
                        </span>
                    </p>
                    <button id="nid-otp-btn" type="button" data-nid-action="submitOtp" disabled
                        class="w-full bg-green-500 hover:bg-green-400 disabled:bg-gray-200 disabled:cursor-not-allowed text-white font-black text-sm py-3.5 rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 disabled:shadow-none transition-all">
                        @lang('Mindigo-auth::app.auth.confirm')
                    </button>
                    <button type="button" data-nid-action="goStep" data-nid-step="1" class="text-gray-400 text-xs font-bold mt-3 hover:text-green-600 transition bg-transparent border-none cursor-pointer w-full text-center">
                        ← @lang('Mindigo-auth::app.auth.back')
                    </button>
                </div>

                {{-- Footer --}}
                <div class="mt-6 flex items-center justify-center gap-1 flex-wrap">
                    <span class="text-xs text-gray-400">@lang('Mindigo-auth::app.footer.protected_by')</span>
                    <span class="text-xs font-black text-green-600">Auronsoft</span>
                    <span class="text-xs font-black text-gray-900">ID</span>
                    <span class="text-gray-300 text-xs">·</span>
                    <a href="{{ route('terms', [], false) }}" class="text-xs text-gray-400 hover:text-green-600 transition">@lang('Mindigo-auth::app.footer.terms')</a>
                    <span class="text-gray-300 text-xs">·</span>
                    <a href="{{ route('privacy', [], false) }}" class="text-xs text-gray-400 hover:text-green-600 transition">@lang('Mindigo-auth::app.footer.privacy')</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom bar --}}
    <div class="absolute bottom-0 left-0 right-0 px-10 py-4 flex items-center justify-between z-20">
        <select class="bg-white/80 border border-black/10 rounded-xl px-3 py-1.5 text-xs text-gray-500 cursor-pointer" style="font-family:'Be Vietnam Pro',sans-serif;">
            <option value="vi">@lang('Mindigo-auth::app.language.vi')</option>
            <option value="en">@lang('Mindigo-auth::app.language.en')</option>
        </select>
        <div class="flex items-center gap-1.5">
            <span class="text-xs text-gray-400">@lang('Mindigo-auth::app.brand.powered_by')</span>
        </div>
        <div class="flex gap-5">
            <a href="#" class="text-xs text-gray-400 hover:text-green-600 transition">@lang('Mindigo-auth::app.footer.help')</a>
            <a href="{{ route('privacy', [], false) }}" class="text-xs text-gray-400 hover:text-green-600 transition">@lang('Mindigo-auth::app.footer.privacy')</a>
            <a href="{{ route('terms', [], false) }}" class="text-xs text-gray-400 hover:text-green-600 transition">@lang('Mindigo-auth::app.footer.terms')</a>
        </div>
    </div>
</div>
