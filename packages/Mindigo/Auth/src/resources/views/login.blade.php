<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@lang('Mindigo-auth::app.auth.login_title') — Mindigo</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=be-vietnam-pro:300,400,500,600,700,800,900" rel="stylesheet"/>
    <script>
        window.__routes = {
            MindigoIdSend:      "{{ route('Mindigo-id.send') }}",
            MindigoIdVerifyOtp: "{{ route('Mindigo-id.verify-otp') }}",
        };
    </script>
    @vite([
        'packages/Mindigo/Auth/src/resources/css/app.css',
        'packages/Mindigo/Auth/src/resources/js/app.js',
    ])
</head>
<body class="min-h-screen bg-white" style="font-family:'Be Vietnam Pro',sans-serif;">

<div class="min-h-screen flex">

    {{-- ── LEFT: Form ── --}}
    <div class="w-full lg:w-1/2 flex flex-col min-h-screen bg-white">

        {{-- Logo --}}
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

        {{-- Form --}}
        <div class="flex-1 flex items-center justify-center px-10 py-12">
            <div class="w-full max-w-md">
                <h1 class="text-3xl font-black text-gray-900 mb-2">@lang('Mindigo-auth::app.auth.login_title')</h1>
                <p class="text-gray-500 text-sm mb-8">@lang('Mindigo-auth::app.auth.login_subtitle', ['platform' => __('Mindigo-auth::app.auth.platform')])</p>

                <form method="POST" action="{{ route('login.store') }}" id="loginForm" class="flex flex-col gap-5">
                    @csrf

                    {{-- Email --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-black text-gray-700" for="email">@lang('Mindigo-auth::app.auth.email')</label>
                        <div class="relative">
                            <input id="email" type="email" name="email"
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 pr-10 text-sm font-medium text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                                placeholder="@lang('Mindigo-auth::app.auth.email_placeholder')"
                                value="{{ old('email') }}" autocomplete="email" required>
                            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-black text-gray-700" for="password">@lang('Mindigo-auth::app.auth.password')</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs text-green-600 font-bold hover:underline">
                                    @lang('Mindigo-auth::app.auth.forgot_password')
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <input id="password" type="password" name="password"
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 pr-16 text-sm font-medium text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                                placeholder="@lang('Mindigo-auth::app.auth.password_placeholder')"
                                autocomplete="current-password" required>
                            <button type="button" id="pwToggle" tabindex="-1" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-green-500 transition">
                                <svg id="eyeIcon" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Remember --}}
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded accent-green-500" {{ old('remember') ? 'checked' : '' }}>
                        <span class="text-sm text-gray-600 font-medium">@lang('Mindigo-auth::app.auth.remember_me')</span>
                    </label>

                    {{-- Submit --}}
                    <button type="submit" id="loginBtn" class="w-full bg-green-500 hover:bg-green-400 active:bg-green-600 text-white font-black text-sm py-3.5 rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 active:shadow-none active:translate-y-1 transition-all flex items-center justify-center gap-2">
                        <span class="btn-text">@lang('Mindigo-auth::app.auth.login_button')</span>
                        <div class="spinner hidden"></div>
                    </button>
                </form>

                {{-- Mindigo ID --}}
                <a href="#" onclick="event.preventDefault(); nidOpen()" class="mt-4 w-full flex items-center justify-center gap-2 border-2 border-green-500 text-green-600 font-black text-sm py-3 rounded-xl hover:bg-green-50 transition">
                    <svg width="18" height="18" viewBox="0 0 200 220" fill="none">
                        <circle cx="105" cy="145" r="90" fill="#22c55e" stroke="#14532d" stroke-width="3"/>
                        <circle cx="82" cy="135" r="20" fill="white" stroke="#14532d" stroke-width="2"/>
                        <circle cx="86" cy="138" r="12" fill="#14532d"/>
                        <circle cx="91" cy="132" r="5" fill="white"/>
                        <circle cx="128" cy="135" r="20" fill="white" stroke="#14532d" stroke-width="2"/>
                        <circle cx="132" cy="138" r="12" fill="#14532d"/>
                        <circle cx="137" cy="132" r="5" fill="white"/>
                    </svg>
                    @lang('Mindigo-auth::app.auth.login_with_Mindigo_id')
                </a>

                {{-- Divider --}}
                <div class="flex items-center gap-3 my-5">
                    <div class="flex-1 h-px bg-gray-100"></div>
                    <span class="text-xs text-gray-400 font-bold">@lang('Mindigo-auth::app.sso.divider')</span>
                    <div class="flex-1 h-px bg-gray-100"></div>
                </div>

                {{-- SSO --}}
                <div class="grid grid-cols-2 gap-2">
                    <a href="#" class="flex items-center justify-center gap-2 border border-gray-200 rounded-xl py-2.5 px-3 hover:border-green-300 hover:bg-green-50 transition">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                        <span class="text-xs font-bold text-gray-600">@lang('Mindigo-auth::app.sso.google')</span>
                    </a>
                    <a href="#" class="flex items-center justify-center gap-2 border border-gray-200 rounded-xl py-2.5 px-3 hover:border-green-300 hover:bg-green-50 transition">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none"><path d="M11.4 0H0v11.4h11.4V0z" fill="#F25022"/><path d="M24 0H12.6v11.4H24V0z" fill="#7FBA00"/><path d="M11.4 12.6H0V24h11.4V12.6z" fill="#00A4EF"/><path d="M24 12.6H12.6V24H24V12.6z" fill="#FFB900"/></svg>
                        <span class="text-xs font-bold text-gray-600">@lang('Mindigo-auth::app.sso.microsoft')</span>
                    </a>
                    <a href="#" class="flex items-center justify-center gap-2 border border-gray-200 rounded-xl py-2.5 px-3 hover:border-green-300 hover:bg-green-50 transition">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
                        <span class="text-xs font-bold text-gray-600">@lang('Mindigo-auth::app.sso.apple')</span>
                    </a>
                    <a href="#" class="flex items-center justify-center gap-2 border border-gray-200 rounded-xl py-2.5 px-3 hover:border-green-300 hover:bg-green-50 transition">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <span class="text-xs font-bold text-gray-600">@lang('Mindigo-auth::app.sso.saml')</span>
                    </a>
                </div>

                <p class="text-center text-xs text-gray-400 mt-6">
                    @lang('Mindigo-auth::app.support.need_help')
                    <a href="#" class="text-green-600 font-bold hover:underline">@lang('Mindigo-auth::app.support.contact_admin')</a>
                </p>
            </div>
        </div>
    </div>

    {{-- ── RIGHT: Visual ── --}}
    <div class="hidden lg:flex flex-1 relative bg-gradient-to-br from-green-400 via-green-500 to-green-600 overflow-hidden">

        <canvas id="connectorCanvas" class="absolute inset-0 w-full h-full"></canvas>

        {{-- Floating cards --}}
        <div id="fc1" class="absolute top-16 left-8 bg-white rounded-2xl shadow-xl p-4 w-48 z-10">
            <div class="text-xs text-gray-400 font-bold mb-1">@lang('Mindigo-auth::app.dashboard.employees_active')</div>
            <div class="text-2xl font-black text-gray-800">1,284</div>
            <div class="text-xs text-green-500 font-bold mt-1">@lang('Mindigo-auth::app.dashboard.today_up')</div>
        </div>

        <div id="fc2" class="absolute top-16 right-8 bg-white rounded-2xl shadow-xl p-4 w-48 z-10">
            <div class="text-xs text-gray-400 font-bold mb-1">@lang('Mindigo-auth::app.dashboard.system_status')</div>
            <div class="flex items-center gap-2 mt-1">
                <div class="w-2 h-2 rounded-full bg-green-500 shadow-[0_0_6px_#22c55e]"></div>
                <span class="text-xs font-black text-green-500">@lang('Mindigo-auth::app.dashboard.system_online')</span>
            </div>
        </div>

        <div id="fc3" class="absolute top-40 left-4 bg-white rounded-2xl shadow-xl p-4 w-48 z-10">
            <div class="text-xs text-gray-400 font-bold mb-2">@lang('Mindigo-auth::app.dashboard.today_approval')</div>
            <div class="flex gap-2 flex-wrap">
                <span class="bg-green-100 text-green-700 text-xs font-black px-2 py-0.5 rounded-full">@lang('Mindigo-auth::app.dashboard.approved')</span>
                <span class="bg-blue-100 text-blue-700 text-xs font-black px-2 py-0.5 rounded-full">@lang('Mindigo-auth::app.dashboard.pending')</span>
            </div>
        </div>

        <div id="fc4" class="absolute top-40 right-4 bg-white rounded-2xl shadow-xl p-4 w-48 z-10">
            <div class="text-xs text-gray-400 font-bold mb-1">@lang('Mindigo-auth::app.dashboard.salary_this_month')</div>
            <div class="text-2xl font-black text-gray-800">2.4 tỷ</div>
            <div class="text-xs font-bold mt-1" style="color:#FBBF24">@lang('Mindigo-auth::app.dashboard.salary_processed')</div>
        </div>

        <div id="fc5" class="absolute bottom-48 left-4 bg-white rounded-2xl shadow-xl p-4 w-48 z-10">
            <div class="text-xs text-gray-400 font-bold mb-2">@lang('Mindigo-auth::app.dashboard.recruitment')</div>
            <div class="flex gap-2 flex-wrap">
                <span class="bg-purple-100 text-purple-700 text-xs font-black px-2 py-0.5 rounded-full">@lang('Mindigo-auth::app.dashboard.candidates')</span>
                <span class="bg-green-100 text-green-700 text-xs font-black px-2 py-0.5 rounded-full">@lang('Mindigo-auth::app.dashboard.offers')</span>
            </div>
        </div>

        <div id="fc6" class="absolute bottom-48 right-4 bg-white rounded-2xl shadow-xl p-4 w-48 z-10">
            <div class="text-xs text-gray-400 font-bold mb-1">@lang('Mindigo-auth::app.dashboard.attendance_today')</div>
            <div class="text-2xl font-black text-gray-800">96.4%</div>
            <div class="text-xs font-bold mt-1" style="color:#34D399">@lang('Mindigo-auth::app.dashboard.attendance_ontime')</div>
        </div>

        <div id="fc7" class="absolute bottom-20 left-1/2 -translate-x-1/2 bg-white rounded-2xl shadow-xl p-4 w-48 z-10">
            <div class="text-xs text-gray-400 font-bold mb-1">@lang('Mindigo-auth::app.dashboard.training')</div>
            <div class="text-2xl font-black text-gray-800">@lang('Mindigo-auth::app.dashboard.courses')</div>
            <div class="text-xs font-bold mt-1" style="color:#A78BFA">@lang('Mindigo-auth::app.dashboard.training_running')</div>
        </div>

        {{-- Center content --}}
        <div class="relative z-20 flex flex-col items-center justify-center w-full px-12 text-center">
            <div id="centerLogo" class="w-24 h-24 bg-white rounded-3xl shadow-2xl flex items-center justify-center mb-6">
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

            <h2 class="text-3xl font-black text-white leading-tight mb-3">
                @lang('Mindigo-auth::app.hero.title_line_1')<br>
                @lang('Mindigo-auth::app.hero.title_line_2') <span class="text-green-900">@lang('Mindigo-auth::app.hero.title_highlight')</span>
            </h2>
            <p class="text-green-100 text-sm leading-relaxed mb-8 max-w-xs">
                @lang('Mindigo-auth::app.hero.description')
            </p>

            <div class="flex items-center gap-8">
                <div class="text-center">
                    <div class="text-2xl font-black text-white">10K+</div>
                    <div class="text-xs text-green-200 font-bold">@lang('Mindigo-auth::app.hero.businesses')</div>
                </div>
                <div class="w-px h-8 bg-green-400"></div>
                <div class="text-center">
                    <div class="text-2xl font-black text-white">99.9%</div>
                    <div class="text-xs text-green-200 font-bold">@lang('Mindigo-auth::app.hero.uptime')</div>
                </div>
                <div class="w-px h-8 bg-green-400"></div>
                <div class="text-center">
                    <div class="text-2xl font-black text-white">500K+</div>
                    <div class="text-xs text-green-200 font-bold">@lang('Mindigo-auth::app.hero.employees')</div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('login_success'))
    <script>window.__loginSuccess = true;</script>
@endif

@if(session('logout_success'))
    <script>window.__logoutSuccess = true;</script>
@endif

@if ($errors->any())
    <script>window.__loginError = @json($errors->first());</script>
@elseif (session('error'))
    <script>window.__loginError = @json(session('error'));</script>
@endif

<form id="logoutForm" method="POST" action="{{ route('logout') }}" style="display:none;">
    @csrf
</form>

@include('Mindigo-auth::Mindigo-id-modal')

</body>
</html>