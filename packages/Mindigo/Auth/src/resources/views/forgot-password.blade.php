<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@lang('Mindigo-auth::app.title') — Mindigo</title>
    @vite([
        'packages/Mindigo/Auth/src/resources/css/app.css',
        'packages/Mindigo/Auth/src/resources/js/app.js',
    ])
</head>
<body class="min-h-screen bg-white" style="font-family:Inter,'Segoe UI',Arial,sans-serif;">

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
                <span class="text-xl font-black text-green-600 tracking-tight">Mindigo</span>
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

                    <div id="alert-email" class="mb-4 flex items-center gap-2 bg-red-50 border border-red-200 text-red-600 text-sm font-bold px-4 py-3 rounded-xl" style="display:none">
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
                    <button type="button" class="flex items-center gap-1 text-sm text-gray-400 font-bold hover:text-green-600 transition mb-6" data-forgot-step="step-email">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                        @lang('Mindigo-auth::app.navigation.back')
                    </button>
                    <h1 class="text-3xl font-black text-gray-900 mb-2">@lang('Mindigo-auth::app.steps.otp.title')</h1>
                    <p class="text-gray-500 text-sm mb-8">
                        @lang('Mindigo-auth::app.steps.otp.description')
                        <strong id="otp-email-display" class="text-green-600"></strong>
                    </p>

                    <div id="alert-otp" class="mb-4 flex items-center gap-2 bg-red-50 border border-red-200 text-red-600 text-sm font-bold px-4 py-3 rounded-xl" style="display:none">
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
                        <button id="btn-resend" type="button" class="text-green-600 font-black hover:underline" data-forgot-resend-otp>
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
                    <button type="button" class="flex items-center gap-1 text-sm text-gray-400 font-bold hover:text-green-600 transition mb-6" data-forgot-step="step-otp">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                        @lang('Mindigo-auth::app.navigation.back')
                    </button>
                    <h1 class="text-3xl font-black text-gray-900 mb-2">@lang('Mindigo-auth::app.steps.reset.title')</h1>
                    <p class="text-gray-500 text-sm mb-8">@lang('Mindigo-auth::app.steps.reset.description')</p>

                    <div id="alert-reset" class="mb-4 flex items-center gap-2 bg-red-50 border border-red-200 text-red-600 text-sm font-bold px-4 py-3 rounded-xl" style="display:none">
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
    <div class="hidden lg:flex flex-1 relative overflow-hidden" style="background:linear-gradient(160deg,#dcfce7 0%,#bbf7d0 30%,#4ade80 70%,#16a34a 100%)">

        <div id="floatStage" class="absolute inset-0" style="pointer-events:none"></div>

        {{-- Center content --}}
        <div class="relative flex flex-col items-center justify-center w-full px-12 text-center" style="z-index:10">
            <div class="w-24 h-24 bg-white rounded-3xl shadow-2xl flex items-center justify-center mb-6">
                <svg width="60" height="60" viewBox="0 0 200 220" fill="none">
                    <circle cx="105" cy="145" r="90" fill="#22c55e" stroke="#14532d" stroke-width="3"/>
                    <ellipse cx="115" cy="185" rx="55" ry="38" fill="#86efac" stroke="#14532d" stroke-width="2"/>
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
            <h2 class="text-3xl font-black text-white leading-tight mb-3" style="text-shadow:0 2px 12px rgba(0,0,0,0.15)">
                @lang('Mindigo-auth::app.hero.title_line_1')<br>
                @lang('Mindigo-auth::app.hero.title_line_2') <span style="color:#14532d">@lang('Mindigo-auth::app.hero.title_highlight')</span>
            </h2>
            <p class="text-green-50 text-sm leading-relaxed mb-8 max-w-xs">
                @lang('Mindigo-auth::app.hero.description')
            </p>
            <div class="flex items-center gap-8">
                <div class="text-center">
                    <div class="text-2xl font-black text-white">10K+</div>
                    <div class="text-xs font-bold" style="color:#bbf7d0">@lang('Mindigo-auth::app.hero.businesses')</div>
                </div>
                <div class="w-px h-8" style="background:#4ade80"></div>
                <div class="text-center">
                    <div class="text-2xl font-black text-white">99.9%</div>
                    <div class="text-xs font-bold" style="color:#bbf7d0">@lang('Mindigo-auth::app.hero.uptime')</div>
                </div>
                <div class="w-px h-8" style="background:#4ade80"></div>
                <div class="text-center">
                    <div class="text-2xl font-black text-white">500K+</div>
                    <div class="text-xs font-bold" style="color:#bbf7d0">@lang('Mindigo-auth::app.hero.employees')</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const stage = document.getElementById('floatStage');
    if (!stage || typeof gsap === 'undefined') return;

    function rand(a, b) { return a + Math.random() * (b - a); }

    const POSITIONS = [
        { x: 0.15, y: 0.14 },   
        { x: 0.72, y: 0.10 },  
        { x: 0.15, y: 0.58 },   
        { x: 0.78, y: 0.55 },   
        { x: 0.42, y: 0.80 },  
    ];

    const CUBE_POS = [
        { x: 0.38, y: 0.08 },
        { x: 0.55, y: 0.40 },
        { x: 0.25, y: 0.40 },
        { x: 0.88, y: 0.32 },
        { x: 0.62, y: 0.72 },
    ];

    const GEM_POS = [
        { x: 0.50, y: 0.18 },
        { x: 0.18, y: 0.82 },
        { x: 0.85, y: 0.74 },
        { x: 0.30, y: 0.24 },
        { x: 0.72, y: 0.85 },
    ];

    function makePhone(rot, contents) {
        const wrap = document.createElement('div');
        wrap.style.cssText = `position:absolute; width:110px; filter:drop-shadow(0 20px 32px rgba(0,0,0,0.30));`;

        const outer = document.createElement('div');
        outer.style.cssText = `
            position:relative;
            border-radius:2rem;
            background:linear-gradient(160deg,#d1fae5,#6ee7b7,#34d399);
            padding:2px;
            box-shadow:0 2px 0 rgba(0,0,0,0.12);
        `;

        /* nút volume trái */
        const btnL1 = document.createElement('div');
        btnL1.style.cssText = `position:absolute;top:38px;left:-3px;width:3px;height:18px;background:#86efac;border-radius:3px 0 0 3px;`;
        const btnL2 = document.createElement('div');
        btnL2.style.cssText = `position:absolute;top:62px;left:-3px;width:3px;height:26px;background:#86efac;border-radius:3px 0 0 3px;`;
        /* nút power phải */
        const btnR = document.createElement('div');
        btnR.style.cssText = `position:absolute;top:52px;right:-3px;width:3px;height:30px;background:#86efac;border-radius:0 3px 3px 0;`;

        const body = document.createElement('div');
        body.style.cssText = `background:white;border-radius:1.8rem;overflow:hidden;`;

        /* notch */
        const notchRow = document.createElement('div');
        notchRow.style.cssText = `display:flex;justify-content:center;padding:8px 0 0;`;
        const notch = document.createElement('div');
        notch.style.cssText = `background:#111827;border-radius:9999px;height:11px;width:38px;`;
        notchRow.appendChild(notch);

        /* status bar */
        const status = document.createElement('div');
        status.style.cssText = `display:flex;justify-content:space-between;align-items:center;padding:3px 12px 2px;`;
        status.innerHTML = `
            <span style="font-size:7px;font-weight:900;color:#374151;font-family:'Be Vietnam Pro',sans-serif;">9:41</span>
            <svg width="22" height="8" viewBox="0 0 22 8" fill="none">
                <rect x="0" y="3" width="3" height="5" rx="0.5" fill="#374151"/>
                <rect x="4" y="2" width="3" height="6" rx="0.5" fill="#374151"/>
                <rect x="8" y="1" width="3" height="7" rx="0.5" fill="#374151"/>
                <rect x="12" y="0" width="3" height="8" rx="0.5" fill="#374151"/>
                <rect x="17" y="1" width="4" height="6" rx="1" fill="none" stroke="#374151" stroke-width="0.8"/>
                <rect x="18" y="2.5" width="2" height="3" rx="0.5" fill="#374151"/>
            </svg>
        `;

        body.appendChild(notchRow);
        body.appendChild(status);
        body.appendChild(contents);

        /* home bar */
        const homeBar = document.createElement('div');
        homeBar.style.cssText = `display:flex;justify-content:center;padding:5px 0 8px;`;
        homeBar.innerHTML = `<div style="width:34px;height:3px;background:#d1d5db;border-radius:9999px;"></div>`;
        body.appendChild(homeBar);

        outer.appendChild(btnL1);
        outer.appendChild(btnL2);
        outer.appendChild(btnR);
        outer.appendChild(body);
        wrap.appendChild(outer);
        return wrap;
    }

    function screenExam() {
        const c = document.createElement('div');
        c.innerHTML = `
            <div style="background:#16a34a;padding:7px 10px;">
                <p style="color:white;font-size:7px;font-weight:900;margin:0 0 4px;font-family:'Be Vietnam Pro',sans-serif;">Section 1 · English</p>
                <div style="display:flex;align-items:center;gap:5px;">
                    <div style="flex:1;background:#15803d;border-radius:9999px;height:4px;">
                        <div style="background:white;height:4px;border-radius:9999px;width:40%;"></div>
                    </div>
                    <span style="color:#bbf7d0;font-size:6px;font-weight:700;">4/10</span>
                </div>
            </div>
            <div style="padding:8px 8px 4px;display:flex;flex-direction:column;gap:5px;">
                <div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                        <span style="font-size:7px;font-weight:900;color:#111827;font-family:'Be Vietnam Pro',sans-serif;">Question 3</span>
                        <span style="font-size:6px;color:#9ca3af;">Single choice</span>
                    </div>
                    <p style="font-size:6px;color:#6b7280;font-style:italic;margin:0 0 5px;">The cat is ___ the table.</p>
                    <div style="display:flex;flex-direction:column;gap:3px;">
                        <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:7px;padding:4px 6px;display:flex;align-items:center;gap:4px;">
                            <div style="width:9px;height:9px;border-radius:50%;background:#22c55e;flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                                <div style="width:4px;height:4px;background:white;border-radius:50%;"></div>
                            </div>
                            <span style="font-size:6px;color:#15803d;font-weight:700;">a. under</span>
                        </div>
                        <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:7px;padding:4px 6px;display:flex;align-items:center;gap:4px;">
                            <div style="width:9px;height:9px;border-radius:50%;border:1px solid #d1d5db;flex-shrink:0;"></div>
                            <span style="font-size:6px;color:#9ca3af;">b. on top</span>
                        </div>
                        <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:7px;padding:4px 6px;display:flex;align-items:center;gap:4px;">
                            <div style="width:9px;height:9px;border-radius:50%;border:1px solid #d1d5db;flex-shrink:0;"></div>
                            <span style="font-size:6px;color:#9ca3af;">c. above</span>
                        </div>
                    </div>
                </div>
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:7px;padding:5px 6px;">
                    <p style="font-size:6px;color:#15803d;font-weight:700;line-height:1.4;margin:0;">✓ Correct! "Under" = below something.</p>
                </div>
                <div style="display:flex;gap:4px;">
                    <button style="flex:1;background:#f3f4f6;color:#6b7280;font-size:6px;font-weight:700;padding:5px 0;border-radius:6px;border:none;">← Prev</button>
                    <button style="flex:1;background:#22c55e;color:white;font-size:6px;font-weight:900;padding:5px 0;border-radius:6px;border:none;box-shadow:0 2px 0 #15803d;">Next →</button>
                </div>
            </div>
        `;
        return c;
    }

    function screenDashboard() {
        const c = document.createElement('div');
        c.innerHTML = `
            <div style="background:#16a34a;padding:7px 10px 6px;">
                <div style="display:flex;align-items:center;gap:6px;">
                    <div style="width:14px;height:14px;background:#ffffff22;border:1px solid #bbf7d0;border-radius:4px;display:flex;align-items:center;justify-content:center;">
                        <div style="width:6px;height:6px;background:#bbf7d0;border-radius:999px;"></div>
                    </div>
                    <p style="color:white;font-size:7px;font-weight:900;margin:0;font-family:'Be Vietnam Pro',sans-serif;">
                        Hi, Nguyen Van A
                    </p>
                </div>
                <p style="color:#bbf7d0;font-size:6px;margin:4px 0 0 20px;">
                    Keep learning today!
                </p>
            </div>
            <div style="padding:8px 8px 4px;display:flex;flex-direction:column;gap:5px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px;">
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:6px;">
                        <p style="font-size:6px;color:#6b7280;margin:0 0 2px;">Score</p>
                        <p style="font-size:11px;font-weight:900;color:#15803d;margin:0;font-family:'Be Vietnam Pro',sans-serif;">87<span style="font-size:6px;">/100</span></p>
                    </div>
                    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:6px">
                        <p style="font-size:6px;color:#6b7280;margin:0">Streak</p>
                        <div style="display:flex;align-items:center;gap:3px">
                            <p style="font-size:11px;font-weight:900;color:#1d4ed8;margin:0">12</p>
                            <div style="width:7px;height:7px;background:#2563eb;border-radius:999px"></div>
                        </div>
                    </div>
                </div>
                <div style="background:#f9fafb;border:1px solid #f3f4f6;border-radius:8px;padding:6px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                        <p style="font-size:6px;font-weight:900;color:#374151;margin:0;">Weekly Progress</p>
                        <span style="font-size:6px;color:#22c55e;font-weight:700;">+12%</span>
                    </div>
                    <div style="display:flex;align-items:flex-end;gap:3px;height:24px;">
                        ${[0.4,0.6,0.5,0.8,0.7,0.9,0.75].map((h,i)=>`
                        <div style="flex:1;height:${h*100}%;background:${i===5?'#22c55e':'#bbf7d0'};border-radius:2px;"></div>
                        `).join('')}
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-top:2px;">
                        ${['M','T','W','T','F','S','S'].map(d=>`<span style="font-size:5px;color:#9ca3af;">${d}</span>`).join('')}
                    </div>
                </div>
                <svg viewBox="0 0 24 24" fill="none" width="12" height="12">
                    <path
                        d="M5 18L7 9L12 14L17 9L19 18H5Z"
                        fill="#f59e0b"
                        stroke="#d97706"
                        stroke-width="1.2"
                        stroke-linejoin="round"
                    />
                    <circle cx="7" cy="8" r="1.4" fill="#fbbf24"/>
                    <circle cx="12" cy="13" r="1.4" fill="#fbbf24"/>
                    <circle cx="17" cy="8" r="1.4" fill="#fbbf24"/>
                </svg>
            </div>
        `;
        return c;
    }

    function screenLesson() {
        const c = document.createElement('div');
        c.innerHTML = `
            <div style="background:#7c3aed;padding:7px 10px 6px;">
                <p style="color:white;font-size:7px;font-weight:900;margin:0;font-family:'Be Vietnam Pro',sans-serif;">Lesson · Grammar</p>
                <div style="display:flex;align-items:center;gap:5px;margin-top:3px;">
                    <div style="flex:1;background:rgba(255,255,255,0.2);border-radius:9999px;height:3px;">
                        <div style="background:white;height:3px;border-radius:9999px;width:65%;"></div>
                    </div>
                    <span style="color:#ddd6fe;font-size:6px;">65%</span>
                </div>
            </div>
            <div style="padding:8px 8px 4px;display:flex;flex-direction:column;gap:5px;">
                <div style="background:#f5f3ff;border:1px solid #ddd6fe;border-radius:8px;padding:6px;">
                    <p style="font-size:7px;font-weight:900;color:#5b21b6;margin:0 0 3px;font-family:'Be Vietnam Pro',sans-serif;">Fill in the blank</p>
                    <p style="font-size:6px;color:#6b7280;font-style:italic;margin:0 0 5px;">She ___ to school every day.</p>
                    <div style="background:white;border:2px solid #8b5cf6;border-radius:6px;padding:4px 8px;text-align:center;">
                        <span style="font-size:7px;color:#7c3aed;font-weight:900;">goes</span>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:3px;">
                    <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:6px;padding:4px;text-align:center;">
                        <span style="font-size:6px;color:#15803d;font-weight:700;">goes ✓</span>
                    </div>
                    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:4px;text-align:center;">
                        <span style="font-size:6px;color:#dc2626;">go ✗</span>
                    </div>
                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:4px;text-align:center;">
                        <span style="font-size:6px;color:#9ca3af;">going</span>
                    </div>
                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:4px;text-align:center;">
                        <span style="font-size:6px;color:#9ca3af;">gone</span>
                    </div>
                </div>
                <button style="width:100%;background:#8b5cf6;color:white;font-size:7px;font-weight:900;padding:7px 0;border-radius:8px;border:none;box-shadow:0 3px 0 #6d28d9;font-family:'Be Vietnam Pro',sans-serif;">Continue →</button>
            </div>
        `;
        return c;
    }

    function screenResult() {
        const c = document.createElement('div');
        c.innerHTML = `
            <div style="background:linear-gradient(135deg,#fbbf24,#f59e0b);padding:7px 10px 6px">
                <div style="display:flex;align-items:center;gap:5px">
                    <div style="width:8px;height:8px;border:2px solid #fff;border-radius:999px"></div>
                    <p style="color:#fff;font-size:7px;font-weight:900;margin:0">
                        Test Complete!
                    </p>
                </div>
                <p style="color:#fef3c7;font-size:6px;margin:2px 0 0 13px">
                    Great performance!
                </p>
            </div>
            <div style="padding:8px 8px 4px;display:flex;flex-direction:column;gap:5px;">
                <div style="text-align:center;padding:6px 0;">
                    <div style="font-size:22px;font-weight:900;color:#15803d;font-family:'Be Vietnam Pro',sans-serif;line-height:1;">92</div>
                    <div style="font-size:6px;color:#9ca3af;margin-top:1px;">out of 100</div>
                    <div style="display:flex;justify-content:center;gap:1px;margin-top:3px;">
                        ${[1,1,1,1,0.5].map(f=>`<svg width="10" height="10" viewBox="0 0 10 10"><polygon points="5,1 6.2,3.8 9,4.2 7,6.2 7.5,9 5,7.5 2.5,9 3,6.2 1,4.2 3.8,3.8" fill="${f===1?'#fbbf24':'#e5e7eb'}" /></svg>`).join('')}
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:3px;">
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:7px;padding:5px;text-align:center;">
                        <p style="font-size:8px;font-weight:900;color:#15803d;margin:0;">18</p>
                        <p style="font-size:5px;color:#6b7280;margin:0;">Correct</p>
                    </div>
                    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:7px;padding:5px;text-align:center;">
                        <p style="font-size:8px;font-weight:900;color:#dc2626;margin:0;">2</p>
                        <p style="font-size:5px;color:#6b7280;margin:0;">Wrong</p>
                    </div>
                    <div style="background:#fefce8;border:1px solid #fde68a;border-radius:7px;padding:5px;text-align:center;">
                        <p style="font-size:8px;font-weight:900;color:#d97706;margin:0;">24m</p>
                        <p style="font-size:5px;color:#6b7280;margin:0;">Time</p>
                    </div>
                </div>
                <button style="width:100%;background:#22c55e;color:white;font-size:7px;font-weight:900;padding:7px 0;border-radius:8px;border:none;box-shadow:0 3px 0 #15803d;font-family:'Be Vietnam Pro',sans-serif;">View Review →</button>
            </div>
        `;
        return c;
    }

    function screenProfile() {
        const c = document.createElement('div');
        c.innerHTML = `
            <div style="background:linear-gradient(135deg,#0ea5e9,#38bdf8);padding:8px 10px 6px;">
                <div style="display:flex;align-items:center;gap:6px;">
                    <div style="width:22px;height:22px;border-radius:50%;background:white;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <span style="font-size:10px;">👤</span>
                    </div>
                    <div>
                        <p style="color:white;font-size:7px;font-weight:900;margin:0;font-family:'Be Vietnam Pro',sans-serif;">Tran Thi B</p>
                        <p style="color:#bae6fd;font-size:6px;margin:0;">Premium Member</p>
                    </div>
                </div>
            </div>
            <div style="padding:7px 8px 4px;display:flex;flex-direction:column;gap:5px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:3px;">
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:5px;text-align:center;">
                        <p style="font-size:10px;font-weight:900;color:#15803d;margin:0;line-height:1.1;font-family:'Be Vietnam Pro',sans-serif;">42</p>
                        <p style="font-size:5px;color:#6b7280;margin:0;">Tests done</p>
                    </div>
                    <div style="background:#fefce8;border:1px solid #fde68a;border-radius:8px;padding:5px;text-align:center">
                        <div style="display:flex;align-items:center;justify-content:center;gap:3px">
                            <p style="font-size:10px;font-weight:900;color:#d97706;margin:0;line-height:1.1">
                                21
                            </p>
                            <div style="width:6px;height:6px;background:#f59e0b;border-radius:999px"></div>
                        </div>ss
                        <p style="font-size:5px;color:#6b7280;margin:0">
                            Day streak
                        </p>
                    </div>
                </div>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:6px;">
                    <p style="font-size:6px;font-weight:900;color:#374151;margin:0 0 4px;font-family:'Be Vietnam Pro',sans-serif;">Upcoming Exams</p>
                    <div style="display:flex;flex-direction:column;gap:3px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;background:white;border:1px solid #e5e7eb;border-radius:6px;padding:4px 6px;">
                            <span style="font-size:6px;color:#374151;font-weight:700;">TOEIC Reading</span>
                            <span style="font-size:5px;color:#22c55e;font-weight:700;background:#f0fdf4;padding:1px 4px;border-radius:4px;">2d left</span>
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;background:white;border:1px solid #e5e7eb;border-radius:6px;padding:4px 6px;">
                            <span style="font-size:6px;color:#374151;font-weight:700;">IELTS Writing</span>
                            <span style="font-size:5px;color:#f59e0b;font-weight:700;background:#fefce8;padding:1px 4px;border-radius:4px;">5d left</span>
                        </div>
                    </div>
                </div>
                <button style="width:100%;background:#0ea5e9;color:white;font-size:7px;font-weight:900;padding:6px 0;border-radius:8px;border:none;box-shadow:0 3px 0 #0369a1;font-family:'Be Vietnam Pro',sans-serif;">View Profile →</button>
            </div>
        `;
        return c;
    }

    /* ─── Cubes ─── */
    function makeCube(bg, dark, mid, label, size) {
        const d = Math.round(size * 0.17);
        const ns = 'http://www.w3.org/2000/svg';
        const svg = document.createElementNS(ns, 'svg');
        svg.setAttribute('viewBox', `0 0 ${size+d} ${size+d}`);
        svg.setAttribute('width', size+d);
        svg.setAttribute('height', size+d);
        svg.style.overflow = 'visible';

        const defs = document.createElementNS(ns, 'defs');
        const grad = document.createElementNS(ns, 'linearGradient');
        const gid = 'g' + bg.replace('#','');
        grad.setAttribute('id', gid);
        grad.setAttribute('x1','0%'); grad.setAttribute('y1','0%');
        grad.setAttribute('x2','100%'); grad.setAttribute('y2','100%');
        [[bg,'0%'],[mid,'100%']].forEach(([c,o]) => {
            const s = document.createElementNS(ns,'stop');
            s.setAttribute('offset',o); s.setAttribute('stop-color',c);
            grad.appendChild(s);
        });
        defs.appendChild(grad);
        svg.appendChild(defs);

        const er = document.createElementNS(ns,'path');
        er.setAttribute('d',`M${size} 0 L${size+d} ${d} L${size+d} ${size+d} L${size} ${size}Z`);
        er.setAttribute('fill', dark);
        svg.appendChild(er);

        const eb = document.createElementNS(ns,'path');
        eb.setAttribute('d',`M0 ${size} L${d} ${size+d} L${size+d} ${size+d} L${size} ${size}Z`);
        eb.setAttribute('fill', dark);
        svg.appendChild(eb);

        const face = document.createElementNS(ns,'rect');
        face.setAttribute('x',0); face.setAttribute('y',0);
        face.setAttribute('width',size); face.setAttribute('height',size);
        face.setAttribute('rx',13);
        face.setAttribute('fill',`url(#${gid})`);
        svg.appendChild(face);

        const shine = document.createElementNS(ns,'rect');
        shine.setAttribute('x',0); shine.setAttribute('y',0);
        shine.setAttribute('width',size); shine.setAttribute('height',size*0.42);
        shine.setAttribute('rx',13);
        shine.setAttribute('fill','rgba(255,255,255,0.22)');
        svg.appendChild(shine);

        const txt = document.createElementNS(ns,'text');
        txt.setAttribute('x', size/2);
        txt.setAttribute('y', size/2 + 2);
        txt.setAttribute('text-anchor','middle');
        txt.setAttribute('dominant-baseline','middle');
        txt.setAttribute('font-size', size * 0.44);
        txt.setAttribute('font-family',"'Be Vietnam Pro',Georgia,sans-serif");
        txt.setAttribute('font-weight','900');
        txt.setAttribute('fill','white');
        txt.textContent = label;
        svg.appendChild(txt);

        const wrap = document.createElement('div');
        wrap.style.cssText = 'position:absolute;filter:drop-shadow(0 12px 20px rgba(0,0,0,0.28))';
        wrap.appendChild(svg);
        return wrap;
    }

    /* ─── Gems ─── */
    function makeGem(color, size) {
        const ns = 'http://www.w3.org/2000/svg';
        const svg = document.createElementNS(ns,'svg');
        svg.setAttribute('viewBox',`0 0 ${size} ${size}`);
        svg.setAttribute('width',size); svg.setAttribute('height',size);

        const defs = document.createElementNS(ns,'defs');
        const g = document.createElementNS(ns,'linearGradient');
        const gid = 'gem' + color.replace('#','');
        g.setAttribute('id',gid);
        g.setAttribute('x1','0%'); g.setAttribute('y1','0%');
        g.setAttribute('x2','100%'); g.setAttribute('y2','100%');
        [[color,'0%'],['rgba(255,255,255,0.7)','50%'],[color,'100%']].forEach(([c,o]) => {
            const s = document.createElementNS(ns,'stop');
            s.setAttribute('offset',o); s.setAttribute('stop-color',c);
            g.appendChild(s);
        });
        defs.appendChild(g); svg.appendChild(defs);

        const r = document.createElementNS(ns,'rect');
        r.setAttribute('x',0); r.setAttribute('y',0);
        r.setAttribute('width',size); r.setAttribute('height',size);
        r.setAttribute('rx', Math.round(size*0.24));
        r.setAttribute('fill',`url(#${gid})`);
        svg.appendChild(r);

        const wrap = document.createElement('div');
        wrap.style.cssText = 'position:absolute;filter:drop-shadow(2px 5px 10px rgba(0,0,0,0.22))';
        wrap.appendChild(svg);
        return wrap;
    }

    const PHONES = [
        { screen: screenExam(),      rot: -38 },
        { screen: screenDashboard(), rot:   8 },
        { screen: screenLesson(),    rot:  12 },
        { screen: screenResult(),    rot: -14 },
        { screen: screenProfile(),   rot: -10 },  
    ];

    const CUBES = [
        { bg:'#f97316', mid:'#fb923c', dark:'#c2410c', label:'🔥', size:54 },
        { bg:'#8b5cf6', mid:'#a78bfa', dark:'#6d28d9', label:'ñ',  size:54 },
        { bg:'#ef4444', mid:'#f87171', dark:'#b91c1c', label:'中', size:56 },
        { bg:'#22c55e', mid:'#4ade80', dark:'#15803d', label:'가', size:52 },
        { bg:'#14b8a6', mid:'#2dd4bf', dark:'#0f766e', label:'♪',  size:46 },
    ];

    const GEMS = [
        { color:'#93c5fd', size:16 },
        { color:'#a5b4fc', size:13 },
        { color:'#fde68a', size:15 },
        { color:'#f9a8d4', size:14 },
        { color:'#86efac', size:16 },
    ];

    function spawnEl(el, xFrac, yFrac, baseRot, floatAmpY, floatAmpX, dur, hw, hh, delay) {
        stage.appendChild(el);
        const W = stage.offsetWidth, H = stage.offsetHeight;

        gsap.set(el, {
            x: W * xFrac - hw,
            y: H * yFrac - hh,
            rotation: baseRot,
            scale: 0,
            opacity: 0,
            transformOrigin: 'center center',
        });

        gsap.to(el, {
            scale: 1, opacity: 1,
            duration: 0.7,
            delay: delay,
            ease: 'back.out(1.6)',
        });

        gsap.to(el, { y: `+=${floatAmpY}`, duration: dur,       repeat:-1, yoyo:true, ease:'sine.inOut', delay });
        gsap.to(el, { x: `+=${floatAmpX}`, duration: dur * 1.3, repeat:-1, yoyo:true, ease:'sine.inOut', delay: delay + rand(0,0.8) });
        gsap.to(el, {
            rotation: baseRot + rand(-9, 9),
            duration: dur * 1.7,
            repeat:-1, yoyo:true, ease:'sine.inOut', delay
        });
    }

    /* phones */
    PHONES.forEach((p, i) => {
        const pos = POSITIONS[i];
        const el = makePhone(p.rot, p.screen);
        spawnEl(el, pos.x, pos.y, p.rot, rand(10,16), rand(6,10), rand(3.2,4.8), 55, 105, i * 0.14);
    });

    /* cubes */
    CUBES.forEach((c, i) => {
        const pos = CUBE_POS[i];
        const el = makeCube(c.bg, c.dark, c.mid, c.label, c.size);
        spawnEl(el, pos.x, pos.y, rand(-18,18), rand(8,14), rand(5,10), rand(2.8,4.2), c.size/2, c.size/2, 0.5 + i * 0.1);
    });

    /* gems */
    GEMS.forEach((g, i) => {
        const pos = GEM_POS[i];
        const el = makeGem(g.color, g.size);
        spawnEl(el, pos.x, pos.y, 18, rand(6,12), rand(4,8), rand(2.5,3.8), g.size/2, g.size/2, 1 + i * 0.08);
    });

})();
</script>

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

    document.querySelectorAll('[data-forgot-step]').forEach(button => {
        button.addEventListener('click', () => goTo(button.dataset.forgotStep));
    });

    document.querySelector('[data-forgot-resend-otp]')?.addEventListener('click', () => resendOtp());

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
