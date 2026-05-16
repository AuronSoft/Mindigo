<header class="sticky top-0 z-30 bg-white border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-1.5 text-xs text-gray-400 font-bold mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-green-600 transition">@lang('Mindigo-profile::app.dashboard')</a>
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                <span class="text-gray-600">@lang('Mindigo-profile::app.my_account')</span>
            </div>
            <h1 class="text-xl font-black text-gray-900">@lang('Mindigo-profile::app.my_account')</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm font-black text-gray-500 border border-gray-200 rounded-xl hover:border-green-400 hover:text-green-600 transition">
                @lang('Mindigo-profile::app.cancel')
            </a>
            <button type="submit" form="profile-form" class="flex items-center gap-1.5 px-5 py-2 bg-green-500 hover:bg-green-400 text-white text-sm font-black rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                @lang('Mindigo-profile::app.save_changes')
            </button>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="max-w-6xl mx-auto px-6 flex gap-1">
        <button class="profile-tab px-4 py-2.5 text-sm font-black text-green-600 border-b-2 border-green-500 transition" data-tab="ho-so">
            @lang('Mindigo-profile::app.profile')
        </button>
        <button class="profile-tab px-4 py-2.5 text-sm font-bold text-gray-400 border-b-2 border-transparent hover:text-green-600 transition" data-tab="email">
            @lang('Mindigo-profile::app.email_settings')
        </button>
        <button class="profile-tab px-4 py-2.5 text-sm font-bold text-gray-400 border-b-2 border-transparent hover:text-green-600 transition" data-tab="bao-mat">
            @lang('Mindigo-profile::app.security')
        </button>
    </div>
</header>