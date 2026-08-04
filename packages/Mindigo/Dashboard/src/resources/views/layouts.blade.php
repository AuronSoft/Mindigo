<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Mindigo-dashboard::app.title'))</title>
    <meta name="description" content="@yield('meta_description', __('Mindigo-dashboard::app.title'))">
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=be-vietnam-pro:400,500,600,700,800,900" rel="stylesheet"/>
    @yield('styles')
</head>
<body class="bg-slate-50 font-['Be_Vietnam_Pro',ui-sans-serif,system-ui,sans-serif] text-slate-900 antialiased">
@php
    $currentUser = Auth::user();
@endphp
<div id="admin-shell" class="grid min-h-screen grid-cols-[5rem_minmax(0,1fr)] transition-[grid-template-columns] duration-200" data-compact-grid="grid-cols-[5rem_minmax(0,1fr)]">
    <aside
        id="sidebar"
        class="sidebar sticky top-0 z-30 flex h-screen w-20 flex-col gap-3 bg-[#f7faf7] p-3 transition-all duration-200"
        data-expanded="false"
        data-compact-width="w-20"
    >
        @php
            $homeRoute = match (true) {
                $currentUser?->role === 'student' && Route::has('student.dashboard') => route('student.dashboard'),
                $currentUser?->role === 'teacher' && Route::has('teacher.dashboard') => route('teacher.dashboard'),
                default => route('dashboard'),
            };
        @endphp
        <a href="{{ $homeRoute }}" class="flex min-h-12 items-center gap-3 overflow-hidden text-slate-900 no-underline" data-sidebar-compact-center>
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
                <span class="block text-lg font-black tracking-tight text-slate-900">Mindigo <span class="text-green-600">LMS</span></span>
            </span>
        </a>

        <div id="sidebar-search-shell" class="flex h-11 items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3" data-sidebar-compact-center>
            <span class="grid h-7 w-7 shrink-0 place-items-center text-slate-500">
                <svg viewBox="0 0 24 24" class="h-[18px] w-[18px] fill-none stroke-current stroke-2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="10.75" cy="10.75" r="7.25"/><path d="m16.25 16.25 4.25 4.25"/>
                </svg>
            </span>
            <input
                id="sidebar-search-input"
                type="text"
                placeholder="@lang('Mindigo-dashboard::app.search_placeholder')"
                autocomplete="off"
                class="hidden min-w-0 flex-1 bg-transparent text-sm font-bold leading-none text-slate-700 outline-none placeholder:text-slate-400"
                data-sidebar-text
            >
        </div>

        <div class="min-h-0 overflow-y-auto overflow-x-hidden">
            <nav class="flex flex-col gap-2">

            @if($currentUser?->role === 'student')
            {{-- ── NAV HỌC SINH (chỉ route student.* — dữ liệu scope theo học sinh) ── --}}
            @php
                $stuBase = 'sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold no-underline';
                $stuInactive = 'text-slate-500 hover:bg-green-50 hover:text-green-700';
                $studentNavGroups = [
                    [
                        'name' => __('student-dashboard::app.group_overview'),
                        'desc' => __('student-dashboard::app.group_overview_desc'),
                        'icon' => 'heroicon-o-squares-2x2',
                        'items' => [
                            ['route' => 'student.dashboard', 'match' => 'student.dashboard', 'label' => __('student-dashboard::app.nav_dashboard'), 'icon' => 'heroicon-o-home'],
                        ],
                    ],
                    [
                        'name' => __('student-dashboard::app.group_learning'),
                        'desc' => __('student-dashboard::app.group_learning_desc'),
                        'icon' => 'heroicon-o-academic-cap',
                        'items' => [
                            ['route' => 'student.classrooms.index', 'match' => 'student.classrooms.*', 'label' => __('student-dashboard::app.nav_classrooms'), 'icon' => 'heroicon-o-user-group'],
                            ['route' => 'student.courses.index', 'match' => 'student.courses.*', 'label' => __('student-dashboard::app.nav_courses'), 'icon' => 'heroicon-o-book-open'],
                            ['route' => 'student.assignments.index', 'match' => 'student.assignments.*', 'label' => __('student-dashboard::app.nav_assignments'), 'icon' => 'heroicon-o-clipboard-document-list'],
                            ['route' => 'student.exams.index', 'match' => 'student.exams.*', 'label' => __('student-dashboard::app.nav_exams'), 'icon' => 'heroicon-o-document-text'],
                            ['route' => 'student.practice.index', 'match' => 'student.practice.*', 'label' => __('student-dashboard::app.nav_practice'), 'icon' => 'heroicon-o-pencil-square'],
                        ],
                    ],
                    [
                        'name' => __('student-dashboard::app.group_tracking'),
                        'desc' => __('student-dashboard::app.group_tracking_desc'),
                        'icon' => 'heroicon-o-chart-bar',
                        'items' => [
                            ['route' => 'student.schedule.index', 'match' => 'student.schedule.*', 'label' => __('student-dashboard::app.nav_schedule'), 'icon' => 'heroicon-o-calendar-days'],
                            ['route' => 'student.progress.index', 'match' => 'student.progress.*', 'label' => __('student-dashboard::app.nav_progress'), 'icon' => 'heroicon-o-presentation-chart-line'],
                            ['route' => 'student.history.index', 'match' => 'student.history.*', 'label' => __('student-dashboard::app.nav_history'), 'icon' => 'heroicon-o-clock'],
                            ['route' => 'student.leaderboard.index', 'match' => 'student.leaderboard.*', 'label' => __('student-dashboard::app.nav_leaderboard'), 'icon' => 'heroicon-o-trophy'],
                        ],
                    ],
                    [
                        'name' => __('student-dashboard::app.group_interaction'),
                        'desc' => __('student-dashboard::app.group_interaction_desc'),
                        'icon' => 'heroicon-o-chat-bubble-left-right',
                        'items' => [
                            ['route' => 'student.discussions.index', 'match' => 'student.discussions.*', 'label' => __('student-dashboard::app.nav_discussions'), 'icon' => 'heroicon-o-chat-bubble-left-right'],
                            ['route' => 'exam-tips', 'match' => 'exam-tips', 'label' => __('student-dashboard::app.nav_exam_tips'), 'icon' => 'heroicon-o-light-bulb'],
                            ['route' => 'student.live-sessions.index', 'match' => 'student.live-sessions.*', 'label' => __('student-dashboard::app.nav_live'), 'icon' => 'heroicon-o-video-camera'],
                        ],
                    ],
                    [
                        'name' => __('student-dashboard::app.group_personal'),
                        'desc' => __('student-dashboard::app.group_personal_desc'),
                        'icon' => 'heroicon-o-cog-6-tooth',
                        'items' => [
                            ['route' => 'student.notebook.index', 'match' => 'student.notebook.*', 'label' => __('student-dashboard::app.nav_notebook'), 'icon' => 'heroicon-o-book-open'],
                            ['route' => 'learning-tools.index', 'match' => 'learning-tools.*', 'label' => __('learning-tools::app.title'), 'icon' => 'heroicon-o-wrench-screwdriver'],
                            ['route' => 'profile.index', 'match' => 'profile.*', 'label' => __('student-dashboard::app.nav_profile'), 'icon' => 'heroicon-o-user-circle'],
                        ],
                    ],
                ];
            @endphp
            <div class="flex flex-col gap-2 pt-2">
                <p class="mb-1 px-2 text-[10px] font-black uppercase tracking-wider text-slate-400" data-sidebar-text>@lang('student-dashboard::app.nav_section')</p>
                @foreach($studentNavGroups as $group)
                    <div class="sidebar-group" data-sidebar-group data-group-name="{{ $group['name'] }}">
                        <button class="sidebar-group-trigger flex min-h-14 w-full items-center gap-3 rounded-2xl px-2 text-left text-slate-700 transition hover:bg-green-50 hover:text-green-800" type="button" title="{{ $group['name'] }}">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-600">
                                <x-dynamic-component :component="$group['icon']" class="h-5 w-5 shrink-0" />
                            </span>
                            <span class="hidden min-w-0 whitespace-nowrap" data-sidebar-text>
                                <span class="block truncate text-sm font-black">{{ $group['name'] }}</span>
                                <span class="block truncate text-[11px] font-bold text-slate-400">{{ $group['desc'] }}</span>
                            </span>
                        </button>
                        <div class="sidebar-submenu hidden gap-1 py-1 pl-[52px]" data-sidebar-submenu>
                            @foreach($group['items'] as $nav)
                                @php $stuActive = request()->routeIs($nav['match']); @endphp
                                @if(Route::has($nav['route']))
                                    <a href="{{ route($nav['route']) }}" class="{{ $stuBase }} {{ $stuActive ? 'bg-green-50 text-green-700' : $stuInactive }}" data-sidebar-search-item data-search-label="{{ $nav['label'] }}" data-sidebar-tooltip="{{ $nav['label'] }}" title="{{ $nav['label'] }}">
                                        <x-dynamic-component :component="$nav['icon']" class="h-4 w-4 shrink-0" />
                                        <span data-sidebar-text>{{ $nav['label'] }}</span>
                                        @if(($nav['badge'] ?? 0) > 0)
                                            <span class="ml-auto grid h-5 min-w-5 place-items-center rounded-full bg-green-600 px-1.5 text-[10px] font-black text-white" data-sidebar-text>{{ $nav['badge'] > 99 ? '99+' : $nav['badge'] }}</span>
                                        @endif
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            @elseif($currentUser?->role === 'teacher')
            {{-- ── NAV GIÁO VIÊN (chỉ route teacher.* — dữ liệu được scope theo giáo viên) ── --}}
            @php
                $teacherBase = 'sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold no-underline';
                $teacherInactive = 'text-slate-500 hover:bg-green-50 hover:text-green-700';
                $teacherNavGroups = [
                    [
                        'name' => __('teacher-dashboard::app.group_overview'),
                        'desc' => __('teacher-dashboard::app.group_overview_desc'),
                        'icon' => 'heroicon-o-squares-2x2',
                        'items' => [
                            ['route' => 'teacher.dashboard', 'match' => 'teacher.dashboard', 'label' => __('teacher-dashboard::app.dashboard'), 'icon' => 'heroicon-o-home'],
                        ],
                    ],
                    [
                        'name' => __('teacher-dashboard::app.group_teaching'),
                        'desc' => __('teacher-dashboard::app.group_teaching_desc'),
                        'icon' => 'heroicon-o-academic-cap',
                        'items' => [
                            ['route' => 'teacher.courses.index', 'fallback_route' => 'teacher.classrooms.index', 'match' => 'teacher.courses.*', 'label' => __('teacher-dashboard::app.courses'), 'icon' => 'heroicon-o-book-open'],
                            ['route' => 'teacher.classrooms.index', 'match' => 'teacher.classrooms.*', 'label' => __('teacher-dashboard::app.my_classrooms'), 'icon' => 'heroicon-o-user-group'],
                            ['route' => 'teacher.exams.index', 'match' => 'teacher.exams.*', 'label' => __('teacher-dashboard::app.my_exams'), 'icon' => 'heroicon-o-document-text'],
                            ['route' => 'teacher.assignments.index', 'match' => 'teacher.assignments.index', 'label' => __('teacher-dashboard::app.my_assignments'), 'icon' => 'heroicon-o-clipboard-document-list'],
                            ['route' => 'teacher.questions.index', 'match' => 'teacher.questions.*', 'label' => __('teacher-dashboard::app.my_questions'), 'icon' => 'heroicon-o-circle-stack'],
                            ['route' => 'teacher.live-sessions.index', 'match' => 'teacher.live-sessions.*', 'label' => __('teacher-dashboard::app.live_sessions'), 'icon' => 'heroicon-o-video-camera'],
                        ],
                    ],
                    [
                        'name' => __('teacher-dashboard::app.group_tracking'),
                        'desc' => __('teacher-dashboard::app.group_tracking_desc'),
                        'icon' => 'heroicon-o-chart-bar',
                        'items' => [
                            ['route' => 'teacher.assignments.grading', 'match' => 'teacher.assignments.grading', 'label' => __('teacher-dashboard::app.grading'), 'icon' => 'heroicon-o-check-badge'],
                            ['route' => 'teacher.reports.index', 'match' => 'teacher.reports.*', 'label' => __('teacher-dashboard::app.reports'), 'icon' => 'heroicon-o-presentation-chart-line'],
                            ['route' => 'teacher.announcements.index', 'match' => 'teacher.announcements.*', 'label' => __('teacher-dashboard::app.announcements'), 'icon' => 'heroicon-o-megaphone'],
                            ['route' => 'teacher.discussions.index', 'fallback_route' => 'teacher.announcements.index', 'match' => 'teacher.discussions.*', 'label' => __('teacher-dashboard::app.discussions'), 'icon' => 'heroicon-o-chat-bubble-left-right'],
                        ],
                    ],
                    [
                        'name' => __('teacher-dashboard::app.group_account'),
                        'desc' => __('teacher-dashboard::app.group_account_desc'),
                        'icon' => 'heroicon-o-cog-6-tooth',
                        'items' => [
                            ['route' => 'learning-tools.index', 'match' => 'learning-tools.*', 'label' => __('learning-tools::app.title'), 'icon' => 'heroicon-o-wrench-screwdriver'],
                            ['route' => 'profile.index', 'match' => 'profile.*', 'label' => __('teacher-dashboard::app.profile'), 'icon' => 'heroicon-o-user-circle'],
                        ],
                    ],
                ];
            @endphp
            <div class="flex flex-col gap-2 pt-2">
                <p class="mb-1 px-2 text-[10px] font-black uppercase tracking-wider text-slate-400" data-sidebar-text>@lang('teacher-dashboard::app.title')</p>
                @foreach($teacherNavGroups as $group)
                    <div class="sidebar-group" data-sidebar-group data-group-name="{{ $group['name'] }}">
                        <button class="sidebar-group-trigger flex min-h-14 w-full items-center gap-3 rounded-2xl px-2 text-left text-slate-700 transition hover:bg-green-50 hover:text-green-800" type="button" title="{{ $group['name'] }}">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-600">
                                <x-dynamic-component :component="$group['icon']" class="h-5 w-5 shrink-0" />
                            </span>
                            <span class="hidden min-w-0 whitespace-nowrap" data-sidebar-text>
                                <span class="block truncate text-sm font-black">{{ $group['name'] }}</span>
                                <span class="block truncate text-[11px] font-bold text-slate-400">{{ $group['desc'] }}</span>
                            </span>
                        </button>
                        <div class="sidebar-submenu hidden gap-1 py-1 pl-[52px]" data-sidebar-submenu>
                            @foreach($group['items'] as $nav)
                                @php
                                    $teacherRoute = Route::has($nav['route'])
                                        ? $nav['route']
                                        : ($nav['fallback_route'] ?? null);
                                    $teacherRoute = $teacherRoute && Route::has($teacherRoute) ? $teacherRoute : null;
                                    $teacherActive = request()->routeIs($nav['match']);
                                @endphp
                                @if($teacherRoute)
                                    <a href="{{ route($teacherRoute) }}" class="{{ $teacherBase }} {{ $teacherActive ? 'bg-green-50 text-green-700' : $teacherInactive }}" data-sidebar-search-item data-search-label="{{ $nav['label'] }}" data-sidebar-tooltip="{{ $nav['label'] }}" title="{{ $nav['label'] }}">
                                        <x-dynamic-component :component="$nav['icon']" class="h-4 w-4 shrink-0" />
                                        <span data-sidebar-text>{{ $nav['label'] }}</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            @else
            {{-- ── NAV ADMIN (giữ nguyên) ── --}}
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
                        @php
                            $overviewBase = 'sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold no-underline';
                            $overviewInactive = 'text-slate-500 hover:bg-green-50 hover:text-green-700';
                            $overviewNav = [
                                [
                                    'route' => 'dashboard',
                                    'match' => 'dashboard',
                                    'label' => __('Mindigo-dashboard::app.dashboard'),
                                    'icon' => 'heroicon-o-squares-2x2',
                                    'show' => Route::has('dashboard'),
                                ],
                                [
                                    'route' => 'question-bank.index',
                                    'match' => 'question-bank.*',
                                    'label' => __('Mindigo-dashboard::app.question_bank'),
                                    'icon' => 'heroicon-o-circle-stack',
                                    'show' => Route::has('question-bank.index') && ($currentUser?->hasPermissionTo('questions.view') ?? false),
                                ],
                                [
                                    'route' => 'exams.index',
                                    'match' => 'exams.*',
                                    'label' => __('Mindigo-dashboard::app.exams'),
                                    'icon' => 'heroicon-o-document-text',
                                    'show' => Route::has('exams.index') && ($currentUser?->hasPermissionTo('exams.view') ?? false),
                                ],
                                [
                                    'route' => 'classrooms.index',
                                    'match' => 'classrooms.*',
                                    'label' => __('Mindigo-dashboard::app.classrooms'),
                                    'icon' => 'heroicon-o-user-group',
                                    'show' => Route::has('classrooms.index') && ($currentUser?->hasPermissionTo('classrooms.view') ?? false),
                                ],
                            ];
                        @endphp
                        @foreach($overviewNav as $nav)
                            @if($nav['show'])
                                <a href="{{ route($nav['route']) }}" class="{{ $overviewBase }} {{ request()->routeIs($nav['match']) ? 'bg-green-50 text-green-700' : $overviewInactive }}" data-sidebar-search-item data-search-label="{{ $nav['label'] }}">
                                    <x-dynamic-component :component="$nav['icon']" class="h-4 w-4 shrink-0" />
                                    <span data-sidebar-text>{{ $nav['label'] }}</span>
                                </a>
                            @endif
                        @endforeach
                        <a href="#learners" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold text-slate-500 no-underline hover:bg-green-50 hover:text-green-700" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.learners')">
                            <x-heroicon-o-users class="h-4 w-4 shrink-0" /><span>@lang('Mindigo-dashboard::app.learners')</span>
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
                        @if(Route::has('subjects.index') && ($currentUser?->hasPermissionTo('subjects.view') ?? false))
                            <a href="{{ route('subjects.index') }}" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold {{ request()->routeIs('subjects.*') ? 'bg-green-50 text-green-700' : 'text-slate-500 hover:bg-green-50 hover:text-green-700' }} no-underline" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.subjects')"><x-heroicon-o-book-open class="h-4 w-4 shrink-0" /><span>@lang('Mindigo-dashboard::app.subjects')</span></a>
                        @endif
                        @if(Route::has('admin.course-categories.index') && ($currentUser?->hasPermissionTo('course-categories.view') ?? false))
                            <a href="{{ route('admin.course-categories.index') }}" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold {{ request()->routeIs('admin.course-categories.*') ? 'bg-green-50 text-green-700' : 'text-slate-500 hover:bg-green-50 hover:text-green-700' }} no-underline" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.course_categories')"><x-heroicon-o-tag class="h-4 w-4 shrink-0" /><span>@lang('Mindigo-dashboard::app.course_categories')</span></a>
                        @endif
                        @if(Route::has('admin.course-publication-reviews.index'))
                            <a href="{{ route('admin.course-publication-reviews.index') }}" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold {{ request()->routeIs('admin.course-publication-reviews.*') ? 'bg-green-50 text-green-700' : 'text-slate-500 hover:bg-green-50 hover:text-green-700' }} no-underline" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.course_reviews')"><x-heroicon-o-clipboard-document-check class="h-4 w-4 shrink-0" /><span>@lang('Mindigo-dashboard::app.course_reviews')</span></a>
                        @endif
                        <a href="#documents" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold text-slate-500 no-underline hover:bg-green-50 hover:text-green-700" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.documents')"><x-heroicon-o-document class="h-4 w-4 shrink-0" /><span>@lang('Mindigo-dashboard::app.documents')</span></a>
                        <a href="#ai-review" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold text-slate-500 no-underline hover:bg-green-50 hover:text-green-700" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.ai_review')"><x-heroicon-o-sparkles class="h-4 w-4 shrink-0" /><span>@lang('Mindigo-dashboard::app.ai_review')</span></a>
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
                        <a href="#sessions" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold text-slate-500 no-underline hover:bg-green-50 hover:text-green-700" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.exam_sessions')"><x-heroicon-o-clock class="h-4 w-4 shrink-0" /><span>@lang('Mindigo-dashboard::app.exam_sessions')</span></a>
                        @if(Route::has('reports.index'))
                        <a href="{{ route('reports.index') }}" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold text-slate-500 no-underline hover:bg-green-50 hover:text-green-700 {{ request()->routeIs('reports.*') ? 'bg-green-50 text-green-700' : '' }}" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.reports')"><x-heroicon-o-chart-bar class="h-4 w-4 shrink-0" /><span>@lang('Mindigo-dashboard::app.reports')</span></a>
                        @else
                        <a href="#reports" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold text-slate-500 no-underline hover:bg-green-50 hover:text-green-700" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.reports')"><x-heroicon-o-chart-bar class="h-4 w-4 shrink-0" /><span>@lang('Mindigo-dashboard::app.reports')</span></a>
                        @endif
                        @if(Route::has('support-tickets.index'))
                            <a href="{{ route('support-tickets.index') }}" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold {{ request()->routeIs('support-tickets.*') ? 'bg-green-50 text-green-700' : 'text-slate-500 hover:bg-green-50 hover:text-green-700' }} no-underline" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.support_tickets')"><x-heroicon-o-lifebuoy class="h-4 w-4 shrink-0" /><span>@lang('Mindigo-dashboard::app.support_tickets')</span></a>
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
                            <a href="{{ route('system-settings.index') }}" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold {{ request()->routeIs('system-settings.*') ? 'bg-green-50 text-green-700' : 'text-slate-500 hover:bg-green-50 hover:text-green-700' }} no-underline" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.system_settings')"><x-heroicon-o-cog-6-tooth class="h-4 w-4 shrink-0" /><span>@lang('Mindigo-dashboard::app.system_settings')</span></a>
                        @endif
                        @if(Route::has('users.index') && ($currentUser?->hasPermissionTo('users.view') ?? false))
                            <a href="{{ route('users.index') }}" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold {{ request()->routeIs('users.*') ? 'bg-green-50 text-green-700' : 'text-slate-500 hover:bg-green-50 hover:text-green-700' }} no-underline" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.user_management')"><x-heroicon-o-user-group class="h-4 w-4 shrink-0" /><span>@lang('Mindigo-dashboard::app.user_management')</span></a>
                        @endif
                        @if(Route::has('role-permissions.index'))
                            <a href="{{ route('role-permissions.index') }}" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold {{ request()->routeIs('role-permissions.*') ? 'bg-green-50 text-green-700' : 'text-slate-500 hover:bg-green-50 hover:text-green-700' }} no-underline" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.role_permissions')"><x-heroicon-o-shield-check class="h-4 w-4 shrink-0" /><span>@lang('Mindigo-dashboard::app.role_permissions')</span></a>
                        @endif
                        @if(Route::has('audit-logs.index'))
                            <a href="{{ route('audit-logs.index') }}" class="sidebar-submenu-item flex min-h-9 items-center gap-2 rounded-xl px-3 text-xs font-extrabold {{ request()->routeIs('audit-logs.*') ? 'bg-green-50 text-green-700' : 'text-slate-500 hover:bg-green-50 hover:text-green-700' }} no-underline" data-sidebar-search-item data-search-label="@lang('Mindigo-dashboard::app.audit_logs')"><x-heroicon-o-clipboard-document-list class="h-4 w-4 shrink-0" /><span>@lang('Mindigo-dashboard::app.audit_logs')</span></a>
                        @endif
                    </div>
                </div>
            @endif
            {{-- ── END role nav ── --}}
            </nav>
        </div>

        {{-- ── Chuông thông báo (luôn hiện số; 0 nếu không có) ── --}}
        @php $unreadCount = $globalUnreadNotifications ?? 0; @endphp
        <div class="relative mt-auto">
            <button id="dashboard-notification-btn" type="button" aria-expanded="false" aria-haspopup="true"
                class="flex min-h-12 w-full items-center gap-3 overflow-hidden rounded-xl border-0 bg-transparent text-left text-slate-700 transition hover:text-green-700" data-sidebar-compact-center>
                <span class="relative grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-600">
                    <x-heroicon-o-bell class="h-5 w-5" />
                    <span class="absolute -right-1 -top-1 grid h-4.5 min-w-4.5 place-items-center rounded-full px-1 text-[10px] font-black text-white {{ $unreadCount > 0 ? 'bg-green-600' : 'bg-slate-300' }}">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                </span>
                <span class="hidden min-w-0 flex-1 whitespace-nowrap" data-sidebar-text>
                    <span class="block truncate text-sm font-black text-slate-900">@lang('notification::app.title')</span>
                    <span class="block text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $unreadCount > 0 ? __('notification::app.unread_count', ['count' => $unreadCount]) : __('notification::app.all_read') }}</span>
                </span>
            </button>

            {{-- Dropdown xem nhanh --}}
            <div id="dashboard-notification-menu" class="absolute bottom-full left-0 z-50 mb-2 hidden w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10">
                <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
                    <span class="text-sm font-black text-slate-900">@lang('notification::app.title')</span>
                    @if($unreadCount > 0)
                        <span class="grid h-5 min-w-5 place-items-center rounded-full bg-green-600 px-1.5 text-[10px] font-black text-white">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                    @endif
                </div>

                <div class="max-h-80 overflow-y-auto">
                    @forelse(($globalRecentNotifications ?? collect()) as $note)
                        @php
                            $d = $note->data;
                            $isUnread = is_null($note->read_at);
                            $icon = match($d['icon'] ?? '') {
                                'megaphone'       => 'heroicon-o-megaphone',
                                'clipboard-check' => 'heroicon-o-clipboard-document-check',
                                default           => 'heroicon-o-bell',
                            };
                            $tone = match($d['tone'] ?? '') {
                                'blue'  => 'bg-blue-50 text-blue-600',
                                'green' => 'bg-green-50 text-green-600',
                                'amber' => 'bg-amber-50 text-amber-600',
                                default => 'bg-slate-100 text-slate-500',
                            };
                        @endphp
                        <a href="{{ route('notifications.read', $note->id) }}"
                           class="flex items-start gap-3 px-4 py-3 no-underline transition hover:bg-slate-50 {{ $isUnread ? 'bg-green-50/40' : '' }}">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl {{ $tone }}">
                                <x-dynamic-component :component="$icon" class="h-4 w-4" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center gap-1.5">
                                    <span class="truncate text-sm font-black text-slate-800">{{ $d['title'] ?? '—' }}</span>
                                    @if($isUnread)<span class="h-1.5 w-1.5 shrink-0 rounded-full bg-green-600"></span>@endif
                                </span>
                                @if(!empty($d['message']))
                                    <span class="line-clamp-1 text-xs font-semibold text-slate-500">{{ $d['message'] }}</span>
                                @endif
                                <span class="text-[10px] font-bold text-slate-400">{{ $note->created_at?->diffForHumans() }}</span>
                            </span>
                        </a>
                    @empty
                        <div class="flex flex-col items-center gap-2 px-4 py-10 text-center">
                            <span class="grid h-12 w-12 place-items-center rounded-full bg-slate-50 text-slate-300"><x-heroicon-o-bell class="h-6 w-6" /></span>
                            <p class="text-xs font-bold text-slate-400">@lang('notification::app.empty_title')</p>
                        </div>
                    @endforelse
                </div>

                <a href="{{ route('notifications.index') }}" class="flex items-center justify-center gap-1.5 border-t border-slate-100 px-4 py-3 text-xs font-black text-green-700 no-underline transition hover:bg-green-50">
                    @lang('notification::app.view_all')
                    <x-heroicon-o-arrow-right class="h-3.5 w-3.5" />
                </a>
            </div>
        </div>

        <button class="flex min-h-12 w-full items-center gap-3 overflow-hidden rounded-xl border-0 bg-transparent text-left" id="sidebar-avatar-btn" type="button" data-sidebar-compact-center>
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
        <button class="ml-1 grid h-9 w-9 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:text-green-700" id="sidebar-toggle" type="button" aria-label="@lang('Mindigo-dashboard::app.expand_sidebar')">
            <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-2 transition" id="sidebar-toggle-icon" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
    </aside>

    <main class="min-w-0 {{ request()->routeIs('dashboard', 'teacher.*', 'student.*', 'reports.*', 'learning-tools.*', 'course-platform.*', 'courses.*', 'admin.course-categories.*', 'admin.course-publication-reviews.*') ? 'p-0' : 'p-6 max-md:p-4' }}">
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
