{{-- Navbar --}}
<nav class="border-b border-gray-100 bg-white sticky top-0 z-50">
    <div class="max-w-7xl mx-auto flex items-center justify-between px-10 py-3">
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
        <div class="hidden md:flex items-center gap-1">
            <a href="#" class="nav-link text-gray-600 font-bold text-sm px-4 py-2 rounded-xl hover:bg-green-50 hover:text-green-600 transition">@lang('core::app.navbar.features')</a>
            <a href="#" class="nav-link text-gray-600 font-bold text-sm px-4 py-2 rounded-xl hover:bg-green-50 hover:text-green-600 transition">@lang('core::app.navbar.explore')</a>
            <a href="#" class="nav-link text-gray-600 font-bold text-sm px-4 py-2 rounded-xl hover:bg-green-50 hover:text-green-600 transition">@lang('core::app.navbar.exam_prep')</a>
            <a href="#" class="nav-link text-gray-600 font-bold text-sm px-4 py-2 rounded-xl hover:bg-green-50 hover:text-green-600 transition">@lang('core::app.navbar.classroom')</a>
            <a href="#" class="nav-link text-gray-600 font-bold text-sm px-4 py-2 rounded-xl hover:bg-green-50 hover:text-green-600 transition">@lang('core::app.navbar.pricing')</a>
            <a href="#" class="nav-link text-gray-600 font-bold text-sm px-4 py-2 rounded-xl hover:bg-green-50 hover:text-green-600 transition">@lang('core::app.navbar.news')</a>
            <a href="#" id="btn-contact" class="nav-link text-gray-600 font-bold text-sm px-4 py-2 rounded-xl hover:bg-green-50 hover:text-green-600 transition">@lang('core::app.navbar.contact')</a>
        </div>

        <div class="flex items-center gap-1 bg-gray-100 rounded-xl p-1">
            <a href="{{ route('lang.switch', 'vi') }}"
            class="text-xs font-black px-3 py-1.5 rounded-lg transition-all {{ app()->getLocale() === 'vi' ? 'bg-white text-green-600 shadow-sm' : 'text-gray-400 hover:text-gray-600' }}">
                🇻🇳 VI
            </a>
            <a href="{{ route('lang.switch', 'en') }}"
            class="text-xs font-black px-3 py-1.5 rounded-lg transition-all {{ app()->getLocale() === 'en' ? 'bg-white text-green-600 shadow-sm' : 'text-gray-400 hover:text-gray-600' }}">
                🇺🇸 EN
            </a>
        </div>
        
        <a href="{{ route('login') }}" class="bg-green-500 hover:bg-green-400 active:bg-green-600 text-white font-black text-sm px-5 py-2.5 rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 active:shadow-none active:translate-y-1 transition-all">
            @lang('core::app.navbar.login')
        </a>
    </div>
</nav>