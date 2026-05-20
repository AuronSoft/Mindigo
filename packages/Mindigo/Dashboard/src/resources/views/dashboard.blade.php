@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-dashboard::app.title'))

@section('styles')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
    @php($dashboardUser = Auth::user())

    <section class="min-h-screen bg-[#f7faf7]">
        <header class="flex min-h-[4.25rem] flex-wrap items-center justify-between gap-4 bg-[#f7faf7] px-5 py-3 backdrop-blur max-md:px-4">
            <div class="flex min-w-[16rem] max-w-xl flex-1 items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2">
                <x-heroicon-o-magnifying-glass class="h-5 w-5 shrink-0 text-slate-400" />
                <input type="text" placeholder="@lang('Mindigo-dashboard::app.global_search')" class="min-w-0 flex-1 bg-transparent text-sm font-bold text-slate-700 outline-none placeholder:text-slate-400">
            </div>

            <div class="flex items-center gap-2 rounded-full bg-white/80 p-1.5 shadow-sm ring-1 ring-slate-200">
                <button type="button" class="grid h-10 w-10 place-items-center rounded-full bg-white text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-green-50 hover:text-green-700">
                    <x-heroicon-o-bars-3 class="h-5 w-5" />
                </button>
                <span class="grid h-10 w-10 place-items-center rounded-full bg-gradient-to-br from-amber-200 via-green-100 to-emerald-300 text-xs font-black text-slate-800 shadow-sm ring-2 ring-white">{{ mb_substr($dashboardUser?->name ?? 'A', 0, 1) }}</span>
                <a href="#exams" class="grid h-10 w-10 place-items-center rounded-full bg-green-600 text-white shadow-sm transition hover:bg-green-500">
                    <x-heroicon-o-plus class="h-5 w-5" />
                </a>
            </div>

            <div id="dashboard-notification-menu" class="absolute right-8 top-20 z-40 hidden w-80 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/10">
                <div class="rounded-xl bg-green-50 px-3 py-2.5">
                    <p class="m-0 text-sm font-black text-slate-900">@lang('Mindigo-dashboard::app.notification_title')</p>
                    <p class="m-0 mt-0.5 text-[11px] font-bold text-slate-500">@lang('Mindigo-dashboard::app.notification_subtitle')</p>
                </div>
                <div class="mt-2 grid gap-1">
                    <a href="#question-bank" class="flex gap-3 rounded-xl px-3 py-2.5 text-slate-700 no-underline transition hover:bg-slate-50">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-green-500"></span>
                        <span class="min-w-0">
                            <span class="block text-sm font-black">@lang('Mindigo-dashboard::app.notification_new_questions')</span>
                            <span class="mt-0.5 block text-xs font-semibold leading-5 text-slate-500">@lang('Mindigo-dashboard::app.notification_new_questions_desc')</span>
                        </span>
                    </a>
                    <a href="#exams" class="flex gap-3 rounded-xl px-3 py-2.5 text-slate-700 no-underline transition hover:bg-slate-50">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-amber-500"></span>
                        <span class="min-w-0">
                            <span class="block text-sm font-black">@lang('Mindigo-dashboard::app.notification_exam_ready')</span>
                            <span class="mt-0.5 block text-xs font-semibold leading-5 text-slate-500">@lang('Mindigo-dashboard::app.notification_exam_ready_desc')</span>
                        </span>
                    </a>
                </div>
            </div>
        </header>

        <div class="grid -mt-3 items-start gap-4 px-5 pb-5 pt-0 2xl:grid-cols-[minmax(0,1fr)_23rem] max-md:-mt-2 max-md:px-4 max-md:pb-4">
            <main class="min-w-0 space-y-4">
                <section class="min-h-[18.85rem] rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm" id="overview">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-600">
                                <x-heroicon-o-plus class="h-4 w-4" />
                            </button>
                            @foreach([
                                ['name' => 'Admin A.', 'color' => 'bg-green-100 text-green-700'],
                                ['name' => 'Teacher B.', 'color' => 'bg-amber-100 text-amber-700'],
                                ['name' => 'Student C.', 'color' => 'bg-sky-100 text-sky-700'],
                            ] as $person)
                                <span class="inline-flex h-9 items-center gap-2 rounded-full border border-slate-200 bg-white px-2.5 text-xs font-black text-slate-700">
                                    <span class="grid h-6 w-6 place-items-center rounded-full {{ $person['color'] }}">{{ mb_substr($person['name'], 0, 1) }}</span>
                                    {{ $person['name'] }}
                                </span>
                            @endforeach
                            <span class="grid h-9 w-9 place-items-center rounded-full bg-green-700 text-xs font-black text-white">C</span>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" class="inline-flex h-9 items-center gap-2 rounded-full bg-green-50 px-3 text-xs font-black text-green-700 ring-1 ring-green-100 transition hover:bg-green-100" data-dashboard-timeframe-toggle aria-pressed="true">
                                <span class="h-4 w-7 rounded-full bg-green-600 p-0.5 transition" data-dashboard-timeframe-track>
                                    <span class="block h-3 w-3 translate-x-3 rounded-full bg-white transition" data-dashboard-timeframe-knob></span>
                                </span>
                                <span data-dashboard-timeframe-status>Timeframe</span>
                            </button>
                            <button type="button" class="inline-flex h-9 items-center gap-2 rounded-full bg-white px-3 text-xs font-black text-slate-600 ring-1 ring-slate-200 transition hover:border-green-200 hover:bg-green-50 hover:text-green-700" data-dashboard-date-button>
                                <x-heroicon-o-calendar-days class="h-4 w-4" />
                                <span data-dashboard-date-label>{{ now()->format('d/m/Y') }}</span>
                            </button>
                            <input type="date" value="{{ now()->toDateString() }}" class="sr-only" data-dashboard-date-input>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 xl:grid-cols-[minmax(0,1fr)_31rem]">
                        <div>
                            <p class="inline-flex rounded-full bg-green-50 px-3 py-1 text-xs font-black text-green-700 ring-1 ring-green-100">Báo cáo thi cử</p>
                            <p class="mt-5 text-sm font-black text-slate-700">Tổng lượt làm bài</p>
                            <div class="mt-1 flex flex-wrap items-end gap-2">
                                <h2 class="text-5xl font-black tracking-tight text-slate-950 max-sm:text-4xl">{{ number_format($stats['active_users'] * 128 + 24876) }}</h2>
                                <span class="mb-1 rounded-full bg-green-600 px-2.5 py-1 text-[11px] font-black text-white">+7.9%</span>
                                <span class="mb-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-black text-emerald-700 ring-1 ring-emerald-100">+2,735 lượt</span>
                            </div>
                            <p class="mt-2 text-xs font-bold text-slate-400">So với kỳ trước: {{ number_format($stats['active_users'] * 96 + 22141) }} lượt</p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-5">
                            <article class="rounded-2xl border border-green-100 bg-gradient-to-br from-white to-green-50 p-3 shadow-sm sm:col-span-1">
                                <p class="text-[11px] font-black text-green-700">Top thí sinh</p>
                                <strong class="mt-2 block text-2xl font-black text-slate-950">72</strong>
                                <div class="mt-3 flex items-center justify-between gap-2 text-xs font-bold text-slate-600">
                                    <span class="inline-flex items-center gap-1"><span class="grid h-5 w-5 place-items-center rounded-full bg-sky-100 text-[10px] text-sky-700">M</span>Minh</span>
                                    <x-heroicon-o-chevron-right class="h-4 w-4" />
                                </div>
                            </article>
                            <article class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-green-700 to-emerald-500 p-3 text-white shadow-sm sm:col-span-2">
                                <div class="flex items-center justify-between">
                                    <p class="text-[11px] font-black text-green-100">Đề tốt nhất</p>
                                    <span class="grid h-7 w-7 place-items-center rounded-full bg-white/15">
                                        <x-heroicon-o-star class="h-4 w-4 text-green-100" />
                                    </span>
                                </div>
                                <strong class="mt-2 block text-2xl font-black">8.7 điểm</strong>
                                <p class="mt-2 text-xs font-bold text-green-50">THPT Toán 01</p>
                            </article>
                            <article class="rounded-2xl border border-slate-200 bg-white p-3 text-center shadow-sm">
                                <p class="text-[11px] font-black text-slate-500">Đề thi</p>
                                <strong class="mt-2 block text-xl font-black text-slate-950">128</strong>
                                <span class="mt-2 inline-flex rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-black text-green-700 ring-1 ring-green-100">+5</span>
                            </article>
                            <article class="rounded-2xl border border-emerald-200 bg-emerald-50 p-3 text-center shadow-sm">
                                <p class="text-[11px] font-black text-emerald-700">Tỉ lệ đạt</p>
                                <strong class="mt-2 block text-xl font-black text-emerald-800">74%</strong>
                                <span class="mt-2 inline-flex rounded-full bg-white px-2 py-0.5 text-[10px] font-black text-emerald-700 ring-1 ring-emerald-100">+1.2%</span>
                            </article>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-2 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                        <div class="grid grid-cols-4 overflow-hidden rounded-full border border-green-100 bg-white text-xs font-black text-slate-500 shadow-sm max-lg:grid-cols-1">
                            <span class="flex min-h-10 items-center gap-2 px-3"><span class="grid h-6 w-6 place-items-center rounded-full bg-green-100 text-[10px] text-green-700">A</span> 12,063 <b class="ml-auto text-slate-300">39.63%</b></span>
                            <span class="flex min-h-10 items-center gap-2 border-l border-slate-200 px-3 max-lg:border-l-0 max-lg:border-t"><span class="grid h-6 w-6 place-items-center rounded-full bg-sky-100 text-[10px] text-sky-700">M</span> 5,841 <b class="ml-auto text-slate-300">20.65%</b></span>
                            <span class="flex min-h-10 items-center gap-2 border-l border-slate-200 px-3 max-lg:border-l-0 max-lg:border-t"><span class="grid h-6 w-6 place-items-center rounded-full bg-amber-100 text-[10px] text-amber-700">E</span> 7,115 <b class="ml-auto text-slate-300">22.14%</b></span>
                            <span class="flex min-h-10 items-center gap-2 border-l border-slate-200 px-3 max-lg:border-l-0 max-lg:border-t"><span class="grid h-6 w-6 place-items-center rounded-full bg-emerald-100 text-[10px] text-emerald-700">C</span> 4,386 <b class="ml-auto text-slate-300">8.58%</b></span>
                        </div>
                        <a href="#reports" class="inline-flex min-h-10 items-center justify-center rounded-full bg-green-700 px-5 text-xs font-black text-white no-underline transition hover:bg-green-600">Chi tiết</a>
                    </div>
                </section>

                <section class="grid items-stretch gap-4 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
                    <div class="grid items-stretch gap-4 lg:grid-cols-2">
                        <div class="min-h-[21rem] rounded-[1.75rem] border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="mb-4 flex items-center justify-between gap-3">
                                <button type="button" class="inline-flex h-9 items-center gap-2 rounded-full bg-slate-50 px-3 text-slate-600 ring-1 ring-slate-200">
                                    <x-heroicon-o-list-bullet class="h-5 w-5" />
                                    <x-heroicon-m-chevron-down class="h-3.5 w-3.5" />
                                </button>
                                <button type="button" class="inline-flex h-9 items-center gap-1 rounded-full bg-slate-50 px-3 text-xs font-black text-slate-600 ring-1 ring-slate-200">
                                    Filters
                                    <x-heroicon-o-funnel class="h-4 w-4" />
                                </button>
                            </div>

                            <div class="grid gap-2">
                                @foreach([
                                    ['label' => 'Thi thử THPT', 'value' => '12,459', 'percent' => '43%', 'icon' => 'heroicon-o-academic-cap', 'tone' => 'bg-green-50 text-green-700'],
                                    ['label' => 'Luyện tập nhanh', 'value' => '8,823', 'percent' => '27%', 'icon' => 'heroicon-o-bolt', 'tone' => 'bg-amber-50 text-amber-700'],
                                    ['label' => 'Ngân hàng đề', 'value' => '5,935', 'percent' => '18%', 'icon' => 'heroicon-o-circle-stack', 'tone' => 'bg-sky-50 text-sky-700'],
                                    ['label' => 'Bài giao lớp', 'value' => '3,028', 'percent' => '12%', 'icon' => 'heroicon-o-users', 'tone' => 'bg-emerald-50 text-emerald-700'],
                                ] as $source)
                                    <div class="flex min-h-14 items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50/80 px-3">
                                        <span class="grid h-9 w-9 place-items-center rounded-xl {{ $source['tone'] }}">
                                            <x-dynamic-component :component="$source['icon']" class="h-5 w-5" />
                                        </span>
                                        <span class="min-w-0 flex-1 truncate text-sm font-black text-slate-700">{{ $source['label'] }}</span>
                                        <strong class="text-sm font-black text-slate-950">{{ $source['value'] }}</strong>
                                        <span class="text-xs font-black text-slate-400">{{ $source['percent'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="min-h-[21rem] rounded-[1.75rem] border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="mb-4 flex items-center justify-between gap-3">
                                <button type="button" class="inline-flex h-9 items-center gap-2 rounded-full bg-slate-50 px-3 text-slate-600 ring-1 ring-slate-200">
                                    <x-heroicon-o-chart-bar class="h-5 w-5" />
                                    <x-heroicon-m-chevron-down class="h-3.5 w-3.5" />
                                </button>
                                <button type="button" class="inline-flex h-9 items-center gap-1 rounded-full bg-slate-50 px-3 text-xs font-black text-slate-600 ring-1 ring-slate-200">
                                    Filters
                                    <x-heroicon-o-funnel class="h-4 w-4" />
                                </button>
                            </div>

                            <div class="grid h-52 grid-cols-5 items-end gap-3 rounded-2xl bg-slate-50 px-4 pb-4 pt-6">
                                @foreach([
                                    ['label' => 'THPT', 'height' => 62, 'icon' => 'T', 'tone' => 'bg-green-600'],
                                    ['label' => 'Ôn tập', 'height' => 88, 'icon' => 'Ô', 'tone' => 'bg-emerald-500'],
                                    ['label' => 'Đề lớp', 'height' => 54, 'icon' => 'L', 'tone' => 'bg-sky-500'],
                                    ['label' => 'AI', 'height' => 38, 'icon' => 'AI', 'tone' => 'bg-amber-400'],
                                    ['label' => 'Khác', 'height' => 72, 'icon' => '+', 'tone' => 'bg-white border border-dashed border-slate-300'],
                                ] as $bar)
                                    <div class="flex h-full flex-col items-center justify-end gap-2">
                                        <div class="grid w-full place-items-center rounded-2xl {{ $bar['tone'] }} text-xs font-black {{ str_contains($bar['tone'], 'bg-white') ? 'text-slate-400' : 'text-white' }}" style="height: {{ $bar['height'] }}%">
                                            {{ $bar['icon'] }}
                                        </div>
                                        <span class="text-[10px] font-black text-slate-400">{{ $bar['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-3">
                                <p class="text-sm font-black text-slate-900">Lượt thi theo nguồn đề</p>
                                <p class="mt-0.5 text-xs font-semibold text-slate-400">Phân loại theo referrer/category</p>
                            </div>
                        </div>
                    </div>

                    <div class="min-h-[21rem] rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm" id="ranking">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-black uppercase tracking-wider text-slate-400">Ranking</p>
                                <h3 class="mt-1 text-lg font-black text-slate-950">Top performers</h3>
                            </div>
                            <span class="rounded-full bg-green-50 px-2.5 py-1 text-xs font-black text-green-700">Live</span>
                        </div>
                        <div class="h-72"><canvas id="rankingChart"></canvas></div>
                    </div>
                </section>

                <section class="grid gap-4 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
                    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm" id="question-bank">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="grid h-10 w-10 place-items-center rounded-full bg-green-50 text-green-700 ring-1 ring-green-100">
                                    <x-heroicon-o-chart-pie class="h-5 w-5" />
                                </span>
                                <div>
                                    <p class="text-xs font-black text-slate-400">@lang('Mindigo-dashboard::app.quality')</p>
                                    <h3 class="mt-0.5 text-sm font-black text-slate-950">@lang('Mindigo-dashboard::app.question_quality')</h3>
                                </div>
                            </div>
                            <div class="flex rounded-full bg-slate-100 p-1 text-xs font-black">
                                <span class="rounded-full bg-green-700 px-3 py-1.5 text-white shadow-sm">Revenue</span>
                                <span class="px-3 py-1.5 text-slate-500">Leads</span>
                                <span class="px-3 py-1.5 text-slate-500">W/L</span>
                            </div>
                        </div>
                        <div class="grid gap-4 lg:grid-cols-[14rem_minmax(0,1fr)]">
                            <div class="rounded-[1.5rem] bg-gradient-to-br from-green-700 to-emerald-500 p-5 text-white shadow-sm">
                                <p class="text-xs font-black text-green-100">Giá trị nội dung</p>
                                <h4 class="mt-1 text-lg font-black">MindigoExam</h4>
                                <div class="mt-7 space-y-4">
                                    <div>
                                        <p class="text-xs font-bold text-green-100">Câu hỏi đạt chuẩn</p>
                                        <strong class="mt-0.5 block text-2xl font-black">18,552</strong>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-green-100">Lượt làm bài</p>
                                        <strong class="mt-0.5 block text-xl font-black">373 <span class="text-sm text-green-100">/ 27,278</span></strong>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-green-100">Tỉ lệ thắng</p>
                                        <strong class="mt-0.5 block text-xl font-black">18% <span class="text-sm text-green-100">51 / 318</span></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="relative min-h-64 rounded-[1.5rem] bg-slate-50 p-4">
                                <div class="absolute inset-x-4 top-7 space-y-10 text-right text-[11px] font-black text-slate-300">
                                    <div class="border-t border-slate-200 pt-1">$14,000</div>
                                    <div class="border-t border-slate-200 pt-1">$11,000</div>
                                    <div class="border-t border-slate-200 pt-1">$7,500</div>
                                    <div class="border-t border-slate-200 pt-1">$4,000</div>
                                </div>
                                <div class="relative z-10 grid h-56 grid-cols-7 items-end gap-2 pr-10">
                                    @foreach([
                                        ['height' => 46, 'label' => '$6,901', 'tag' => true],
                                        ['height' => 34, 'label' => '', 'tag' => false],
                                        ['height' => 86, 'label' => '$11,035', 'tag' => true],
                                        ['height' => 57, 'label' => '', 'tag' => false],
                                        ['height' => 49, 'label' => '', 'tag' => false],
                                        ['height' => 74, 'label' => '$9,265', 'tag' => true],
                                        ['height' => 61, 'label' => '', 'tag' => false],
                                    ] as $bar)
                                        <div class="flex h-full flex-col items-center justify-end gap-2">
                                            @if($bar['tag'])
                                                <span class="rounded-lg bg-green-600 px-2 py-1 text-[10px] font-black text-white shadow-sm">{{ $bar['label'] }}</span>
                                            @endif
                                            <span class="w-full rounded-t-2xl {{ $bar['tag'] ? 'bg-[repeating-linear-gradient(135deg,#bbf7d0_0,#bbf7d0_4px,#ffffff_4px,#ffffff_8px)] ring-1 ring-green-100' : 'bg-slate-200' }}" style="height: {{ $bar['height'] }}%"></span>
                                            <span class="grid h-6 w-6 place-items-center rounded-full bg-white text-[10px] font-black text-green-700 ring-1 ring-slate-200">{{ $loop->iteration % 3 === 0 ? 'T' : ($loop->iteration % 2 === 0 ? 'S' : 'A') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm" id="exams">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-black uppercase tracking-wider text-slate-400">@lang('Mindigo-dashboard::app.operations')</p>
                                <h3 class="mt-1 text-lg font-black text-slate-950">@lang('Mindigo-dashboard::app.latest_exams')</h3>
                            </div>
                            <a href="#reports" class="rounded-full bg-slate-950 px-4 py-2 text-xs font-black text-white no-underline">@lang('Mindigo-dashboard::app.view_report')</a>
                        </div>
                        <div class="overflow-hidden rounded-2xl border border-slate-200">
                            <table class="w-full min-w-[620px] text-left">
                                <thead class="bg-slate-50 text-[11px] font-black uppercase text-slate-400">
                                    <tr>
                                        <th class="px-4 py-3">@lang('Mindigo-dashboard::app.exam_name')</th>
                                        <th class="px-4 py-3">@lang('Mindigo-dashboard::app.candidates')</th>
                                        <th class="px-4 py-3">@lang('Mindigo-dashboard::app.avg_score')</th>
                                        <th class="px-4 py-3">@lang('Mindigo-dashboard::app.status')</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm font-bold text-slate-700">
                                    @foreach([
                                        ['exam' => 'Đề luyện thi THPT 2026 - Toán 01', 'candidates' => '1,248', 'score' => '7.4', 'status' => __('Mindigo-dashboard::app.published'), 'tone' => 'bg-green-100 text-green-800'],
                                        ['exam' => 'Kiểm tra Sinh học - Di truyền', 'candidates' => '684', 'score' => '6.9', 'status' => __('Mindigo-dashboard::app.reviewing'), 'tone' => 'bg-amber-100 text-amber-800'],
                                        ['exam' => 'Ngân hàng Anh văn B1 - Reading', 'candidates' => '932', 'score' => '8.1', 'status' => __('Mindigo-dashboard::app.scheduled'), 'tone' => 'bg-sky-100 text-sky-800'],
                                    ] as $exam)
                                        <tr class="bg-white">
                                            <td class="px-4 py-3.5">{{ $exam['exam'] }}</td>
                                            <td class="px-4 py-3.5">{{ $exam['candidates'] }}</td>
                                            <td class="px-4 py-3.5">{{ $exam['score'] }}</td>
                                            <td class="px-4 py-3.5"><span class="rounded-full px-2.5 py-1 text-[11px] font-black {{ $exam['tone'] }}">{{ $exam['status'] }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </main>

            <aside class="space-y-4">
                <section class="min-h-[18.85rem] rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-slate-400">Top sales</p>
                            <h3 class="mt-1 text-lg font-black text-slate-950">Learner flow</h3>
                        </div>
                        <span class="rounded-full bg-green-50 px-2.5 py-1 text-xs font-black text-green-700">+3</span>
                    </div>
                    <div class="mt-5 grid gap-2">
                        @foreach([
                            ['name' => 'Admin A.', 'value' => '$209,633', 'kpi' => 84],
                            ['name' => 'Teacher B.', 'value' => '$156,841', 'kpi' => 103],
                            ['name' => 'Student C.', 'value' => '$45,386', 'kpi' => 41],
                        ] as $row)
                            <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-3">
                                <span class="grid h-9 w-9 place-items-center rounded-full bg-green-100 text-xs font-black text-green-700">{{ mb_substr($row['name'], 0, 1) }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-black text-slate-900">{{ $row['name'] }}</span>
                                    <span class="block text-xs font-bold text-slate-400">{{ $row['value'] }}</span>
                                </span>
                                <b class="rounded-full bg-slate-950 px-2 py-1 text-xs font-black text-white">{{ $row['kpi'] }}</b>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="min-h-[21rem] rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm" id="reports">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-slate-400">@lang('Mindigo-dashboard::app.reports')</p>
                            <h3 class="mt-1 text-lg font-black text-slate-950">@lang('Mindigo-dashboard::app.today_tasks')</h3>
                        </div>
                        <x-heroicon-o-adjustments-horizontal class="h-5 w-5 text-slate-400" />
                    </div>
                    <div class="mt-4 space-y-3">
                        @foreach([
                            ['label' => __('Mindigo-dashboard::app.task_review_questions'), 'count' => 42, 'tone' => 'bg-green-600'],
                            ['label' => __('Mindigo-dashboard::app.task_publish_exam'), 'count' => 8, 'tone' => 'bg-amber-500'],
                            ['label' => __('Mindigo-dashboard::app.task_support'), 'count' => 15, 'tone' => 'bg-slate-950'],
                        ] as $task)
                            <div class="grid grid-cols-[8px_minmax(0,1fr)_auto] items-center gap-3 rounded-2xl bg-slate-50 p-3">
                                <span class="h-10 rounded-full {{ $task['tone'] }}"></span>
                                <span class="truncate text-sm font-black text-slate-800">{{ $task['label'] }}</span>
                                <strong class="text-lg font-black text-slate-950">{{ $task['count'] }}</strong>
                            </div>
                        @endforeach
                    </div>
                </section>
            </aside>
        </div>
    </section>
@endsection
