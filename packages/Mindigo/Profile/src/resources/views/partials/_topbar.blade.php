<header class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm shadow-slate-200/70">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="flex items-center gap-1.5 text-xs font-black text-slate-400">
                <a href="{{ $dashboardUrl }}" class="text-slate-500 no-underline transition hover:text-green-700">@lang('Mindigo-profile::app.dashboard')</a>
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                <span class="text-slate-700">@lang('Mindigo-profile::app.my_account')</span>
            </div>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-black tracking-tight text-slate-950">@lang('Mindigo-profile::app.my_account')</h1>
                <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-black text-green-700 ring-1 ring-green-100">
                    {{ $user->is_active ? __('Mindigo-profile::app.active') : __('Mindigo-profile::app.inactive') }}
                </span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ $dashboardUrl }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-600 no-underline transition hover:border-green-200 hover:bg-green-50 hover:text-green-700">
                @lang('Mindigo-profile::app.back_to_dashboard')
            </a>
            <button type="submit" form="profile-form" class="btn-profile-save inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-green-600 px-5 text-sm font-black text-white shadow-sm transition hover:bg-green-500">
                <svg class="h-4 w-4 fill-none stroke-current stroke-[2.5]" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                @lang('Mindigo-profile::app.save_changes')
            </button>
        </div>
    </div>

    <div class="mt-5 flex gap-1 overflow-x-auto border-b border-slate-200">
        <button class="profile-tab border-b-2 border-green-500 px-4 py-3 text-sm font-black text-green-700 transition" data-tab="ho-so" type="button">
            @lang('Mindigo-profile::app.profile')
        </button>
        <button class="profile-tab border-b-2 border-transparent px-4 py-3 text-sm font-black text-slate-400 transition hover:text-green-700" data-tab="email" type="button">
            @lang('Mindigo-profile::app.email_settings')
        </button>
        <button class="profile-tab border-b-2 border-transparent px-4 py-3 text-sm font-black text-slate-400 transition hover:text-green-700" data-tab="bao-mat" type="button">
            @lang('Mindigo-profile::app.security')
        </button>
    </div>
</header>
