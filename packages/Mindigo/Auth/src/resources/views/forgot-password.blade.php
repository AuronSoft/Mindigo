<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@lang('Mindigo-auth::app.title') — Mindigo</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=be-vietnam-pro:300,400,500,600,700,800,900" rel="stylesheet"/>
    @vite([
        'packages/Mindigo/Auth/src/resources/css/app.css',
        'packages/Mindigo/Auth/src/resources/js/app.js',
    ])
</head>
<body class="min-h-screen bg-white" style="font-family:'Be Vietnam Pro',sans-serif;">

<div class="min-h-screen flex">
    <div class="w-full lg:w-1/2 flex flex-col min-h-screen bg-white">
        <div class="px-10 py-5 border-b border-gray-100">
            <a href="/" class="flex items-center gap-2">
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
        </div>

        {{-- Form area --}}
        <div class="flex-1 flex items-center justify-center px-10 py-12">
            <div class="w-full max-w-md">

                {{-- STEP 1: Nhập email --}}
                <div id="step-email">
                    <a href="{{ route('login') }}" class="flex items-center gap-1 text-sm text-gray-400 font-bold hover:text-green-600 transition mb-6">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                        @lang('Mindigo-auth::app.navigation.back_login')
                    </a>
                    <h1 class="text-3xl font-black text-gray-900 mb-2">@lang('Mindigo-auth::app.steps.email.title')</h1>
                    <p class="text-gray-500 text-sm mb-8">@lang('Mindigo-auth::app.steps.email.description')</p>

                    <div id="alert-email" class="hidden mb-4 flex items-center gap-2 bg-red-50 border border-red-200 text-red-600 text-sm font-bold px-4 py-3 rounded-xl">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span id="alert-email-msg"></span>
                    </div>

                    <div class="flex flex-col gap-1.5 mb-5">
                        <label class="text-sm font-black text-gray-700" for="fp-email">@lang('Mindigo-auth::app.auth.email')</label>
                        <div class="relative">
                            <input id="fp-email" type="email"
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 pr-10 text-sm font-medium text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                                placeholder="@lang('Mindigo-auth::app.auth.email_placeholder')"
                                autocomplete="email"/>
                            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </div>
                    </div>

                    <button id="btn-send-otp" class="w-full bg-green-500 hover:bg-green-400 active:bg-green-600 text-white font-black text-sm py-3.5 rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 active:shadow-none active:translate-y-1 transition-all flex items-center justify-center gap-2">
                        <span class="btn-text">@lang('Mindigo-auth::app.steps.email.send_otp')</span>
                        <div class="spinner hidden"></div>
                    </button>
                </div>

                {{-- STEP 2: Nhập OTP --}}
                <div id="step-otp" style="display:none">
                    <button class="flex items-center gap-1 text-sm text-gray-400 font-bold hover:text-green-600 transition mb-6" onclick="goTo('step-email')">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                        @lang('Mindigo-auth::app.navigation.back')
                    </button>
                    <h1 class="text-3xl font-black text-gray-900 mb-2">@lang('Mindigo-auth::app.steps.otp.title')</h1>
                    <p class="text-gray-500 text-sm mb-8">
                        @lang('Mindigo-auth::app.steps.otp.description')
                        <strong id="otp-email-display" class="text-green-600"></strong>
                    </p>

                    <div id="alert-otp" class="hidden mb-4 flex items-center gap-2 bg-red-50 border border-red-200 text-red-600 text-sm font-bold px-4 py-3 rounded-xl">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span id="alert-otp-msg"></span>
                    </div>

                    {{-- OTP inputs --}}
                    <div class="flex gap-3 mb-5">
                        @foreach(range(0,5) as $i)
                        <input class="otp-input flex-1 h-14 text-center text-xl font-black border-2 border-gray-200 rounded-xl focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200 transition"
                            type="text" maxlength="1" inputmode="numeric" id="otp{{ $i }}"/>
                        @endforeach
                    </div>

                    <div class="text-sm text-gray-400 mb-5">
                        @lang('Mindigo-auth::app.steps.otp.not_received')
                        <button id="btn-resend" class="text-green-600 font-black hover:underline" onclick="resendOtp()">
                            @lang('Mindigo-auth::app.steps.otp.resend')
                        </button>
                        <span id="resend-timer" style="display:none" class="text-gray-400">
                            (<span id="timer-count">60</span>s)
                        </span>
                    </div>

                    <button id="btn-verify-otp" class="w-full bg-green-500 hover:bg-green-400 active:bg-green-600 text-white font-black text-sm py-3.5 rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 active:shadow-none active:translate-y-1 transition-all flex items-center justify-center gap-2">
                        <span class="btn-text">@lang('Mindigo-auth::app.steps.otp.confirm')</span>
                        <div class="spinner hidden"></div>
                    </button>
                </div>

                {{-- STEP 3: Đặt lại mật khẩu --}}
                <div id="step-reset" style="display:none">
                    <button class="flex items-center gap-1 text-sm text-gray-400 font-bold hover:text-green-600 transition mb-6" onclick="goTo('step-otp')">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                        @lang('Mindigo-auth::app.navigation.back')
                    </button>
                    <h1 class="text-3xl font-black text-gray-900 mb-2">@lang('Mindigo-auth::app.steps.reset.title')</h1>
                    <p class="text-gray-500 text-sm mb-8">@lang('Mindigo-auth::app.steps.reset.description')</p>

                    <div id="alert-reset" class="hidden mb-4 flex items-center gap-2 bg-red-50 border border-red-200 text-red-600 text-sm font-bold px-4 py-3 rounded-xl">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span id="alert-reset-msg"></span>
                    </div>

                    <div class="flex flex-col gap-5 mb-5">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-black text-gray-700" for="new-password">@lang('Mindigo-auth::app.steps.reset.new_password')</label>
                            <div class="relative">
                                <input id="new-password" type="password"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-3 pr-10 text-sm font-medium text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                                    placeholder="@lang('Mindigo-auth::app.steps.reset.new_password_placeholder')"/>
                                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-black text-gray-700" for="confirm-password">@lang('Mindigo-auth::app.steps.reset.confirm_password')</label>
                            <div class="relative">
                                <input id="confirm-password" type="password"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-3 pr-10 text-sm font-medium text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                                    placeholder="@lang('Mindigo-auth::app.steps.reset.confirm_password_placeholder')"/>
                                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </div>
                        </div>
                    </div>

                    <button id="btn-reset" class="w-full bg-green-500 hover:bg-green-400 active:bg-green-600 text-white font-black text-sm py-3.5 rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 active:shadow-none active:translate-y-1 transition-all flex items-center justify-center gap-2">
                        <span class="btn-text">@lang('Mindigo-auth::app.steps.reset.submit')</span>
                        <div class="spinner hidden"></div>
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- ── RIGHT: Visual ── --}}
    <div class="hidden lg:flex flex-1 relative bg-gradient-to-br from-green-400 via-green-500 to-green-600 overflow-hidden">

        <div class="absolute -top-20 -right-20 w-80 h-80 bg-green-400 rounded-full opacity-40"></div>
        <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-green-700 rounded-full opacity-30"></div>
        <div class="absolute inset-0" style="background-image: radial-gradient(circle, rgba(255,255,255,0.15) 1px, transparent 1px); background-size: 28px 28px;"></div>

        <canvas id="connectorCanvas" class="absolute inset-0 w-full h-full opacity-40"></canvas>

        {{-- Floating cards --}}
        <div id="fc1" class="absolute top-8 left-8 bg-white rounded-2xl shadow-xl p-4 w-44 z-10">
            <div class="text-xs text-gray-400 font-bold mb-1">@lang('Mindigo-auth::app.dashboard.employees_active')</div>
            <div class="text-2xl font-black text-gray-800">1,284</div>
            <div class="text-xs text-green-500 font-black mt-1">@lang('Mindigo-auth::app.dashboard.today_up')</div>
        </div>

        <div id="fc2" class="absolute top-8 right-8 bg-white rounded-2xl shadow-xl p-4 w-44 z-10">
            <div class="text-xs text-gray-400 font-bold mb-1">@lang('Mindigo-auth::app.dashboard.system_status')</div>
            <div class="flex items-center gap-2 mt-2">
                <div class="w-2.5 h-2.5 rounded-full bg-green-500 shadow-[0_0_8px_#22c55e]"></div>
                <span class="text-xs font-black text-green-500">@lang('Mindigo-auth::app.dashboard.system_online')</span>
            </div>
        </div>

        <div id="fc3" class="absolute top-1/2 -translate-y-1/2 left-8 bg-white rounded-2xl shadow-xl p-4 w-44 z-10">
            <div class="text-xs text-gray-400 font-bold mb-2">@lang('Mindigo-auth::app.dashboard.today_approval')</div>
            <div class="flex gap-2 flex-wrap mt-1">
                <span class="bg-green-100 text-green-700 text-xs font-black px-2 py-0.5 rounded-full">@lang('Mindigo-auth::app.dashboard.approved')</span>
                <span class="bg-blue-100 text-blue-700 text-xs font-black px-2 py-0.5 rounded-full">@lang('Mindigo-auth::app.dashboard.pending')</span>
            </div>
        </div>

        <div id="fc4" class="absolute top-1/2 -translate-y-1/2 right-8 bg-white rounded-2xl shadow-xl p-4 w-44 z-10">
            <div class="text-xs text-gray-400 font-bold mb-1">@lang('Mindigo-auth::app.dashboard.salary_this_month')</div>
            <div class="text-2xl font-black text-gray-800">2.4 tỷ</div>
            <div class="text-xs font-black mt-1" style="color:#FBBF24">@lang('Mindigo-auth::app.dashboard.salary_processed')</div>
        </div>

        <div id="fc5" class="absolute bottom-8 left-8 bg-white rounded-2xl shadow-xl p-4 w-44 z-10">
            <div class="text-xs text-gray-400 font-bold mb-2">@lang('Mindigo-auth::app.dashboard.recruitment')</div>
            <div class="flex gap-2 flex-wrap mt-1">
                <span class="bg-purple-100 text-purple-700 text-xs font-black px-2 py-0.5 rounded-full">@lang('Mindigo-auth::app.dashboard.candidates')</span>
                <span class="bg-green-100 text-green-700 text-xs font-black px-2 py-0.5 rounded-full">@lang('Mindigo-auth::app.dashboard.offers')</span>
            </div>
        </div>

        <div id="fc6" class="absolute bottom-8 right-8 bg-white rounded-2xl shadow-xl p-4 w-44 z-10">
            <div class="text-xs text-gray-400 font-bold mb-1">@lang('Mindigo-auth::app.dashboard.attendance_today')</div>
            <div class="text-2xl font-black text-gray-800">96.4%</div>
            <div class="text-xs font-black mt-1" style="color:#34D399">@lang('Mindigo-auth::app.dashboard.attendance_ontime')</div>
        </div>

        <div id="fc7" class="absolute bottom-8 left-1/2 -translate-x-1/2 bg-white rounded-2xl shadow-xl p-4 w-44 z-10">
            <div class="text-xs text-gray-400 font-bold mb-1">@lang('Mindigo-auth::app.dashboard.training')</div>
            <div class="text-2xl font-black text-gray-800">@lang('Mindigo-auth::app.dashboard.courses')</div>
            <div class="text-xs font-black mt-1" style="color:#A78BFA">@lang('Mindigo-auth::app.dashboard.training_running')</div>
        </div>

        {{-- Center --}}
        <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-48 z-20 pointer-events-none">
            <div id="centerLogo" class="w-24 h-24 bg-white rounded-3xl shadow-2xl flex items-center justify-center mb-5">
                <svg width="60" height="60" viewBox="0 0 200 220" fill="none">
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
            <h2 class="text-2xl font-black text-white leading-tight mb-2">
                @lang('Mindigo-auth::app.hero.title_line_1')<br>
                @lang('Mindigo-auth::app.hero.title_line_2') <span class="text-green-900">@lang('Mindigo-auth::app.hero.title_highlight')</span>
            </h2>
            <p class="text-green-100 text-xs leading-relaxed mb-5">@lang('Mindigo-auth::app.hero.description')</p>
            <div class="flex items-center gap-5">
                <div class="text-center">
                    <div class="text-xl font-black text-white">10K+</div>
                    <div class="text-xs text-green-200 font-bold">@lang('Mindigo-auth::app.hero.businesses')</div>
                </div>
                <div class="w-px h-8 bg-white opacity-40"></div>
                <div class="text-center">
                    <div class="text-xl font-black text-white">99.9%</div>
                    <div class="text-xs text-green-200 font-bold">@lang('Mindigo-auth::app.hero.uptime')</div>
                </div>
                <div class="w-px h-8 bg-white opacity-40"></div>
                <div class="text-center">
                    <div class="text-xl font-black text-white">500K+</div>
                    <div class="text-xs text-green-200 font-bold">@lang('Mindigo-auth::app.hero.employees')</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Các message lấy từ Blade để JS dùng
    const __lang = {
        emailRequired:   @json(__('Mindigo-auth::app.steps.email.email_required')),
        otpSent:         @json(__('Mindigo-auth::app.steps.email.otp_sent')),
        otpRequired:     @json(__('Mindigo-auth::app.steps.otp.otp_required')),
        otpInvalid:      @json(__('Mindigo-auth::app.steps.otp.otp_invalid')),
        otpSuccess:      @json(__('Mindigo-auth::app.steps.otp.otp_success')),
        passwordMin:     @json(__('Mindigo-auth::app.steps.reset.password_min')),
        passwordNoMatch: @json(__('Mindigo-auth::app.steps.reset.password_not_match')),
        resetSuccess:    @json(__('Mindigo-auth::app.steps.reset.reset_success')),
    };

    let currentEmail = '';
    let timerInterval = null;

    function goTo(stepId) {
        document.querySelectorAll('[id^="step-"]').forEach(el => {
            el.style.display = 'none';
            el.style.animation = '';
        });
        const el = document.getElementById(stepId);
        el.style.display = 'block';
        el.style.animation = 'fadeUp 0.35s ease both';
    }

    function showAlert(id, msg) {
        MindigoToast(msg, 'error', 4200);
    }
    function hideAlert(id) { return id; }

    function setLoading(btnId, loading) {
        const btn = document.getElementById(btnId);
        btn.classList.toggle('loading', loading);
        btn.disabled = loading;
    }

    // STEP 1
    document.getElementById('btn-send-otp').addEventListener('click', async () => {
        const email = document.getElementById('fp-email').value.trim();
        if (!email) return showAlert('alert-email', __lang.emailRequired);
        hideAlert('alert-email');
        setLoading('btn-send-otp', true);
        try {
            const res = await fetch('/forgot-password/send-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ email })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || __lang.otpInvalid);
            currentEmail = email;
            document.getElementById('otp-email-display').textContent = email;
            MindigoToast(__lang.otpSent, 'success');
            goTo('step-otp');
            startTimer();
            document.getElementById('otp0').focus();
        } catch (e) {
            showAlert('alert-email', e.message);
        } finally {
            setLoading('btn-send-otp', false);
        }
    });

    // OTP input navigation
    document.querySelectorAll('.otp-input').forEach((input, i, inputs) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '').slice(-1);
            if (input.value && i < inputs.length - 1) inputs[i + 1].focus();
        });
        input.addEventListener('keydown', e => {
            if (e.key === 'Backspace' && !input.value && i > 0) inputs[i - 1].focus();
        });
        input.addEventListener('paste', e => {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            [...paste].slice(0, 6).forEach((ch, j) => {
                if (inputs[i + j]) inputs[i + j].value = ch;
            });
            const next = inputs[Math.min(i + paste.length, 5)];
            if (next) next.focus();
        });
    });

    function startTimer() {
        let count = 60;
        document.getElementById('btn-resend').style.display = 'none';
        document.getElementById('resend-timer').style.display = 'inline';
        document.getElementById('timer-count').textContent = count;
        clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            count--;
            document.getElementById('timer-count').textContent = count;
            if (count <= 0) {
                clearInterval(timerInterval);
                document.getElementById('btn-resend').style.display = 'inline';
                document.getElementById('resend-timer').style.display = 'none';
            }
        }, 1000);
    }

    async function resendOtp() {
        document.querySelectorAll('.otp-input').forEach(i => i.value = '');
        document.getElementById('otp0').focus();
        if (!currentEmail) return;
        try {
            const res = await fetch('/forgot-password/send-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ email: currentEmail })
            });
            const data = await res.json();                          
            if (!res.ok) throw new Error(data.message || '');     
            MindigoToast(__lang.otpSent, 'success');
            startTimer();
        } catch (e) {
            showAlert('alert-otp', e.message);
        }
    }

    // STEP 2
    document.getElementById('btn-verify-otp').addEventListener('click', async () => {
        const otp = [...document.querySelectorAll('.otp-input')].map(i => i.value).join('');
        if (otp.length < 6) return showAlert('alert-otp', __lang.otpRequired);
        hideAlert('alert-otp');
        setLoading('btn-verify-otp', true);
        try {
            const res = await fetch('/forgot-password/verify-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ email: currentEmail, otp })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || __lang.otpInvalid);
            MindigoToast(__lang.otpSuccess, 'success');
            goTo('step-reset');
        } catch (e) {
            showAlert('alert-otp', e.message);
        } finally {
            setLoading('btn-verify-otp', false);
        }
    });

    // STEP 3
    document.getElementById('btn-reset').addEventListener('click', async () => {
        const password = document.getElementById('new-password').value;
        const confirm  = document.getElementById('confirm-password').value;
        if (password.length < 8) return showAlert('alert-reset', __lang.passwordMin);
        if (password !== confirm) return showAlert('alert-reset', __lang.passwordNoMatch);
        hideAlert('alert-reset');
        setLoading('btn-reset', true);
        try {
            const res = await fetch('/forgot-password/reset', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ email: currentEmail, password, password_confirmation: confirm })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || __lang.otpInvalid);
            MindigoToast(__lang.resetSuccess, 'success', 1800);
            window.location.href = '/login';
        } catch (e) {
            showAlert('alert-reset', e.message);
        } finally {
            setLoading('btn-reset', false);
        }
    });
</script>
</body>
</html>