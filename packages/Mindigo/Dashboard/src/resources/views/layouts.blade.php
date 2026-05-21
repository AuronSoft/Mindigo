<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Mindigo-dashboard::app.title'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=be-vietnam-pro:400,500,600,700,800,900" rel="stylesheet"/>
    @yield('styles')
</head>
<body class="bg-slate-50 font-['Be_Vietnam_Pro',ui-sans-serif,system-ui,sans-serif] text-slate-900 antialiased">
@php($currentUser = Auth::user())
<div id="admin-shell" class="grid min-h-screen grid-cols-[5rem_minmax(0,1fr)] transition-[grid-template-columns] duration-200">
    <aside
        id="sidebar"
        class="sidebar sticky top-0 z-30 flex h-screen w-20 flex-col gap-3 bg-[#f7faf7] p-3 transition-all duration-200"
        data-expanded="false"
    >
        <a href="{{ route('dashboard') }}" class="flex min-h-12 items-center gap-3 overflow-hidden text-slate-900 no-underline">
            <span class="grid h-11 w-11 shrink-0 place-items-center">
                <svg width="40" height="44" viewBox="0 0 200 220" fill="none" aria-hidden="true">
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
            </span>
            <span class="hidden min-w-0 whitespace-nowrap" data-sidebar-text>
                <span class="block text-[10px] font-black uppercase tracking-wider text-slate-400">@lang('Mindigo-dashboard::app.platform')</span>
                <span class="block text-lg font-black tracking-tight text-slate-900">Mindigo<span class="text-green-600">Exam</span></span>
            </span>
        </a>

        <div class="flex h-11 items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3">
            <svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0 fill-none stroke-current stroke-2 text-slate-500" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input
                id="sidebar-search-input"
                type="text"
                placeholder="@lang('Mindigo-dashboard::app.search_placeholder')"
                autocomplete="off"
                class="hidden min-w-0 flex-1 bg-transparent text-sm font-bold text-slate-700 outline-none placeholder:text-slate-400"
                data-sidebar-text
            >
        </div>

        <div class="min-h-0 overflow-y-auto overflow-x-hidden pr-1">
            <nav class="flex flex-col gap-2">
                <div class="sidebar-group" data-sidebar-group data-group-name="@lang('Mindigo-dashboard::app.group_overview')">
                    <button class="sidebar-group-trigger flex min-h-14 w-full items-center gap-3 rounded-2xl bg-green-50 px-2 text-left text-green-800 transition hover:bg-green-50" type="button" title="@lang('Mindigo-dashboard::app.group_overview')">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-green-100 text-green-600">
                            <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/></svg>
                        </span>
                        <span class="hidden min-w-0 whitespace-nowrap" data-sidebar-text>
                            <span class="block truncate text-sm font-black">@lang('Mindigo-dashboard::app.group_overview')</span>
                            <span class="block truncate text-[11px] font-bold text-slate-400">@lang('Mindigo-dashboard::app.group_overview_desc')</span>
                        </span>
                    </button>
                    <div class="sidebar-submenu hidden gap-1 py-1 pl-[52px]" data-sidebar-submenu>
                        <a href="{{ route('dashboard') }}" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold no-underline {{ request()->routeIs('dashboard') ? 'bg-green-50 text-green-700' : 'text-slate-500 hover:bg-green-50 hover:text-green-700' }}" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.dashboard')">
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-green-500"></span><span>@lang('Mindigo-dashboard::app.dashboard')</span>
                        </a>
                        @if(Route::has('question-bank.index') && ($currentUser?->hasPermissionTo('questions.view') ?? false))
                            <a href="{{ route('question-bank.index') }}" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold {{ request()->routeIs('question-bank.*') ? 'bg-green-50 text-green-700' : 'text-slate-500 hover:bg-green-50 hover:text-green-700' }} no-underline" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.question_bank')">
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-slate-300"></span><span>@lang('Mindigo-dashboard::app.question_bank')</span>
                            </a>
                        @endif
                        <a href="#exams" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold text-slate-500 no-underline hover:bg-green-50 hover:text-green-700" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.exams')">
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-slate-300"></span><span>@lang('Mindigo-dashboard::app.exams')</span>
                        </a>
                        <a href="#learners" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold text-slate-500 no-underline hover:bg-green-50 hover:text-green-700" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.learners')">
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-slate-300"></span><span>@lang('Mindigo-dashboard::app.learners')</span>
                        </a>
                    </div>
                </div>

                <div class="sidebar-group" data-sidebar-group data-group-name="@lang('Mindigo-dashboard::app.group_content')">
                    <button class="sidebar-group-trigger flex min-h-14 w-full items-center gap-3 rounded-2xl px-2 text-left text-slate-700 transition hover:bg-green-50 hover:text-green-800" type="button" title="@lang('Mindigo-dashboard::app.group_content')">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-green-100 text-green-600">
                            <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><path d="M8 7h8"/><path d="M8 11h6"/></svg>
                        </span>
                        <span class="hidden min-w-0 whitespace-nowrap" data-sidebar-text>
                            <span class="block truncate text-sm font-black">@lang('Mindigo-dashboard::app.group_content')</span>
                            <span class="block truncate text-[11px] font-bold text-slate-400">@lang('Mindigo-dashboard::app.group_content_desc')</span>
                        </span>
                    </button>
                    <div class="sidebar-submenu hidden gap-1 py-1 pl-[52px]" data-sidebar-submenu>
                        <a href="#subjects" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold text-slate-500 no-underline hover:bg-green-50 hover:text-green-700" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.subjects')"><span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span><span>@lang('Mindigo-dashboard::app.subjects')</span></a>
                        <a href="#documents" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold text-slate-500 no-underline hover:bg-green-50 hover:text-green-700" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.documents')"><span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span><span>@lang('Mindigo-dashboard::app.documents')</span></a>
                        <a href="#ai-review" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold text-slate-500 no-underline hover:bg-green-50 hover:text-green-700" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.ai_review')"><span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span><span>@lang('Mindigo-dashboard::app.ai_review')</span></a>
                    </div>
                </div>

                <div class="sidebar-group" data-sidebar-group data-group-name="@lang('Mindigo-dashboard::app.group_operations')">
                    <button class="sidebar-group-trigger flex min-h-14 w-full items-center gap-3 rounded-2xl px-2 text-left text-slate-700 transition hover:bg-green-50 hover:text-green-800" type="button" title="@lang('Mindigo-dashboard::app.group_operations')">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-green-100 text-green-600">
                            <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-7"/></svg>
                        </span>
                        <span class="hidden min-w-0 whitespace-nowrap" data-sidebar-text>
                            <span class="block truncate text-sm font-black">@lang('Mindigo-dashboard::app.group_operations')</span>
                            <span class="block truncate text-[11px] font-bold text-slate-400">@lang('Mindigo-dashboard::app.group_operations_desc')</span>
                        </span>
                    </button>
                    <div class="sidebar-submenu hidden gap-1 py-1 pl-[52px]" data-sidebar-submenu>
                        <a href="#sessions" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold text-slate-500 no-underline hover:bg-green-50 hover:text-green-700" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.exam_sessions')"><span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span><span>@lang('Mindigo-dashboard::app.exam_sessions')</span></a>
                        <a href="#reports" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold text-slate-500 no-underline hover:bg-green-50 hover:text-green-700" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.reports')"><span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span><span>@lang('Mindigo-dashboard::app.reports')</span></a>
                        @if(Route::has('support-tickets.index'))
                            <a href="{{ route('support-tickets.index') }}" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold {{ request()->routeIs('support-tickets.*') ? 'bg-green-50 text-green-700' : 'text-slate-500 hover:bg-green-50 hover:text-green-700' }} no-underline" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.support_tickets')"><span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span><span>@lang('Mindigo-dashboard::app.support_tickets')</span></a>
                        @endif
                    </div>
                </div>

                <div class="sidebar-group" data-sidebar-group data-group-name="@lang('Mindigo-dashboard::app.settings')">
                    <button class="sidebar-group-trigger flex min-h-14 w-full items-center gap-3 rounded-2xl px-2 text-left text-slate-700 transition hover:bg-green-50 hover:text-green-800" type="button" title="@lang('Mindigo-dashboard::app.settings')">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-600">
                            <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8 2 2 0 1 1-2.8 2.8 1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5 2 2 0 1 1-4 0 1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3 2 2 0 1 1-2.8-2.8 1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1 2 2 0 1 1 0-4 1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8 2 2 0 1 1 2.8-2.8 1.7 1.7 0 0 0 1.8.3 1.7 1.7 0 0 0 1-1.5 2 2 0 1 1 4 0 1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3 2 2 0 1 1 2.8 2.8 1.7 1.7 0 0 0-.3 1.8 1.7 1.7 0 0 0 1.5 1 2 2 0 1 1 0 4 1.7 1.7 0 0 0-1.5 1z"/></svg>
                        </span>
                        <span class="hidden min-w-0 whitespace-nowrap" data-sidebar-text>
                            <span class="block truncate text-sm font-black">@lang('Mindigo-dashboard::app.settings')</span>
                            <span class="block truncate text-[11px] font-bold text-slate-400">@lang('Mindigo-dashboard::app.settings_desc')</span>
                        </span>
                    </button>
                    <div class="sidebar-submenu hidden gap-1 py-1 pl-[52px]" data-sidebar-submenu>
                        @if(Route::has('system-settings.index'))
                            <a href="{{ route('system-settings.index') }}" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold {{ request()->routeIs('system-settings.*') ? 'bg-green-50 text-green-700' : 'text-slate-500 hover:bg-green-50 hover:text-green-700' }} no-underline" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.system_settings')"><span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span><span>@lang('Mindigo-dashboard::app.system_settings')</span></a>
                        @endif
                        @if(Route::has('users.index') && ($currentUser?->hasPermissionTo('users.view') ?? false))
                            <a href="{{ route('users.index') }}" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold {{ request()->routeIs('users.*') ? 'bg-green-50 text-green-700' : 'text-slate-500 hover:bg-green-50 hover:text-green-700' }} no-underline" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.user_management')"><span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span><span>@lang('Mindigo-dashboard::app.user_management')</span></a>
                        @endif
                        @if(Route::has('role-permissions.index'))
                            <a href="{{ route('role-permissions.index') }}" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold {{ request()->routeIs('role-permissions.*') ? 'bg-green-50 text-green-700' : 'text-slate-500 hover:bg-green-50 hover:text-green-700' }} no-underline" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.role_permissions')"><span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span><span>@lang('Mindigo-dashboard::app.role_permissions')</span></a>
                        @endif
                        @if(Route::has('audit-logs.index'))
                            <a href="{{ route('audit-logs.index') }}" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold {{ request()->routeIs('audit-logs.*') ? 'bg-green-50 text-green-700' : 'text-slate-500 hover:bg-green-50 hover:text-green-700' }} no-underline" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.audit_logs')"><span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span><span>@lang('Mindigo-dashboard::app.audit_logs')</span></a>
                        @endif
                    </div>
                </div>
            </nav>
        </div>

        <button class="mt-auto flex min-h-12 w-full items-center gap-3 overflow-hidden rounded-xl border-0 bg-transparent text-left" id="sidebar-avatar-btn" type="button">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-green-100 text-sm font-black text-green-700">{{ mb_substr($currentUser?->name ?? 'A', 0, 1) }}</span>
            <span class="hidden min-w-0 whitespace-nowrap" data-sidebar-text>
                <span class="block max-w-44 truncate text-sm font-black text-slate-900">{{ $currentUser?->name ?? 'Guest' }}</span>
                <span class="block text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $currentUser?->role_label ?? $currentUser?->role ?? '-' }}</span>
            </span>
        </button>

        <div class="fixed z-50 hidden w-64 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/10" id="user-menu">
            <div class="mb-1 flex items-center gap-3 rounded-xl bg-green-50 px-3 py-2.5">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white text-sm font-black text-green-700 ring-1 ring-green-100">{{ mb_substr($currentUser?->name ?? 'A', 0, 1) }}</span>
                <span class="min-w-0">
                    <span class="block truncate text-sm font-black text-slate-900">{{ $currentUser?->name ?? 'Guest' }}</span>
                    <span class="block text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $currentUser?->role_label ?? $currentUser?->role ?? '-' }}</span>
                </span>
            </div>
            @if(Route::has('profile.index'))
                <a href="{{ route('profile.index') }}" class="flex min-h-10 items-center gap-2 rounded-xl px-3 text-sm font-extrabold text-slate-600 no-underline hover:bg-slate-50 hover:text-slate-900">
                    <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    @lang('Mindigo-dashboard::app.my_account')
                </a>
            @endif
            <a href="#" class="flex min-h-10 items-center gap-2 rounded-xl px-3 text-sm font-extrabold text-red-600 no-underline hover:bg-red-50" data-logout data-logout-form="logout-form">
                <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                @lang('Mindigo-dashboard::app.logout')
            </a>
        </div>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
        <button class="grid h-9 w-9 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:text-green-700" id="sidebar-toggle" type="button" aria-label="@lang('Mindigo-dashboard::app.expand_sidebar')">
            <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-2 transition" id="sidebar-toggle-icon" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
    </aside>

    <main class="min-w-0 {{ request()->routeIs('dashboard') ? 'p-0' : 'p-6 max-md:p-4' }}">
        @yield('content')
    </main>
</div>

@foreach(['success' => 'success', 'info' => 'info', 'error' => 'error', 'warning' => 'warning'] as $flashKey => $toastType)
    @if(session($flashKey))
        <div class="hidden" data-mindigo-toast-message="{{ session($flashKey) }}" data-mindigo-toast-type="{{ $toastType }}"></div>
    @endif
@endforeach

@if($errors->any())
    @foreach($errors->all() as $error)
        <div class="hidden" data-mindigo-toast-message="{{ $error }}" data-mindigo-toast-type="error" data-mindigo-toast-duration="4200"></div>
    @endforeach
@endif

<div class="fixed z-[80] hidden rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs font-extrabold text-white" id="sidebar-tooltip"></div>
@yield('scripts')
</body>
</html>
