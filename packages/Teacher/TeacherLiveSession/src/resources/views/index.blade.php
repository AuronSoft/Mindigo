@extends('Mindigo-dashboard::layouts')
@section('title', __('teacher-live-session::app.title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
    @php
        $selectedClassroom = request('classroom_id')
            ? $classrooms->firstWhere('id', (int) request('classroom_id'))
            : null;
    @endphp

    <div class="flex min-h-screen flex-col bg-slate-50">

        {{-- Header --}}
        <header
            class="sticky top-0 z-10 border-b border-slate-200 bg-white/95 px-6 py-4 backdrop-blur">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-widest text-green-700">
                        @lang('teacher-live-session::app.teaching_live')
                    </p>
                    <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('teacher-live-session::app.title')</h1>
                    <p class="mt-1 text-xs font-semibold text-slate-400">@lang('teacher-live-session::app.subtitle')</p>
                </div>
                <div class="flex flex-wrap gap-2">
                <a href="{{ route('teacher.live-sessions.reports.index') }}" class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 no-underline transition hover:bg-slate-50"><x-heroicon-o-chart-bar-square class="h-4 w-4" />@lang('teacher-live-session::app.analytics_report')</a>
                <button type="button" data-mindigo-drawer-open="teacher-live-filter"
                    class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 no-underline hover:bg-slate-50 transition">
                    <x-heroicon-o-adjustments-horizontal class="h-4 w-4" />
                    Bộ lọc
                    @if($selectedClassroom)
                        <span class="grid h-5 min-w-5 place-items-center rounded-full bg-green-600 px-1.5 text-[10px] text-white">1</span>
                    @endif
                </button>
                <a href="{{ route('teacher.live-sessions.create') }}"
                    class="inline-flex h-10 items-center gap-2 rounded-xl bg-green-600 px-4 text-sm font-black text-white no-underline shadow-sm transition hover:bg-green-500">
                    <x-heroicon-o-plus class="h-4 w-4" />
                    @lang('teacher-live-session::app.create')
                </a>
                </div>
            </div>
        </header>

        <div class="flex flex-1 flex-col gap-5 p-6">
            @if($selectedClassroom)
                <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-green-100 bg-green-50 px-4 py-3">
                    <span class="text-xs font-black uppercase tracking-wider text-green-700">Đang lọc</span>
                    <span class="inline-flex min-h-8 items-center gap-2 rounded-full bg-white px-3 text-xs font-black text-slate-700 ring-1 ring-green-100">
                        <x-heroicon-o-user-group class="h-4 w-4 text-green-600" />
                        {{ $selectedClassroom->name }}
                        <a href="{{ route('teacher.live-sessions.index') }}" class="grid h-5 w-5 place-items-center rounded-full text-slate-400 no-underline transition hover:bg-slate-100 hover:text-red-500" title="{{ __('teacher-live-session::app.clear_filter') }}">
                            <x-heroicon-o-x-mark class="h-3.5 w-3.5" />
                        </a>
                    </span>
                </div>
            @endif

            @if($sessions->isEmpty())
                <div class="flex min-h-107.5 flex-col items-center justify-center gap-4 rounded-3xl border border-dashed border-slate-200 bg-white px-6 py-16">
                    <span class="grid h-20 w-20 place-items-center rounded-full bg-slate-50 text-slate-300">
                        <x-heroicon-o-video-camera class="h-10 w-10" />
                    </span>
                    <div class="text-center">
                        <p class="text-lg font-black text-slate-700">@lang('teacher-live-session::app.empty_title')</p>
                        <p class="mt-1 max-w-xs text-sm font-semibold leading-relaxed text-slate-400">
                            @lang('teacher-live-session::app.empty_desc')
                        </p>
                    </div>
                    <a href="{{ route('teacher.live-sessions.create') }}"
                        class="mt-2 inline-flex h-10 items-center gap-2 rounded-full bg-green-600 px-6 text-sm font-black text-white no-underline shadow-sm transition hover:bg-green-500">
                        <x-heroicon-o-plus class="h-4 w-4" /> @lang('teacher-live-session::app.create')
                    </a>
                </div>
            @else
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50/70 text-xs font-black uppercase tracking-wider text-slate-400">
                                    <th class="px-6 py-4">@lang('teacher-live-session::app.col_number')</th>
                                    <th class="px-6 py-4">@lang('teacher-live-session::app.col_title')</th>
                                    <th class="px-6 py-4">@lang('teacher-live-session::app.col_classroom')</th>
                                    <th class="px-6 py-4">@lang('teacher-live-session::app.col_schedule')</th>
                                    <th class="px-6 py-4">@lang('teacher-live-session::app.col_status')</th>
                                    <th class="px-6 py-4 text-center">@lang('teacher-live-session::app.col_actions')</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm font-semibold text-slate-600">
                                @foreach($sessions as $i => $s)
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="px-6 py-4 text-slate-400">{{ $sessions->firstItem() + $i }}</td>
                                        <td class="px-6 py-4 font-black text-slate-900">{{ $s->title }}@if($s->recordings->isNotEmpty())<div class="mt-2 flex flex-wrap gap-1">@foreach($s->recordings as $recording)<a href="{{ route('live-recordings.stream', $recording) }}" class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-1 text-[10px] font-black text-green-700 no-underline"><x-heroicon-o-play-circle class="h-3.5 w-3.5" />@lang('teacher-live-session::app.watch_recording')</a>@endforeach</div>@endif</td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-bold text-slate-600">
                                                {{ $s->classroom->name ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $s->scheduled_start?->format('d/m/Y H:i') }}
                                            @if($s->scheduled_end)
                                                <span class="text-slate-400">– {{ $s->scheduled_end->format('H:i') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($s->isLive())
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-bold text-red-700">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-red-600 animate-pulse"></span>
                                                    @lang('teacher-live-session::app.status_live')
                                                </span>
                                            @elseif($s->isScheduled())
                                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-700">@lang('teacher-live-session::app.status_scheduled')</span>
                                            @elseif($s->isWaiting())
                                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-700">@lang('teacher-live-session::app.status_waiting')</span>
                                            @elseif($s->status === 'cancelled')
                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-500">@lang('teacher-live-session::app.status_cancelled')</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-500">@lang('teacher-live-session::app.status_ended')</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-center gap-1">
                                                {{-- Start (scheduled) --}}
                                                @if($s->isScheduled())
                                                    <form action="{{ route('teacher.live-sessions.open', $s) }}" method="POST" class="inline">@csrf<button type="submit" class="inline-flex h-8 items-center gap-1 rounded-xl border border-green-200 bg-green-50 px-3 text-xs font-black text-green-700"><x-heroicon-o-user-plus class="h-4 w-4" /> @lang('teacher-live-session::app.open_waiting_room')</button></form>
                                                    <form action="{{ route('teacher.live-sessions.start', $s) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="inline-flex h-8 items-center gap-1 rounded-xl bg-green-600 px-3 text-xs font-black text-white hover:bg-green-500">
                                                            <x-heroicon-o-play class="h-4 w-4" /> @lang('teacher-live-session::app.start')
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($s->isWaiting())
                                                    <a href="{{ route('teacher.live-sessions.room', $s) }}" class="inline-flex h-8 items-center gap-1 rounded-xl border border-green-200 px-3 text-xs font-black text-green-700"><x-heroicon-o-user-group class="h-4 w-4" /> @lang('teacher-live-session::app.manage_waiting_room')</a>
                                                    <form action="{{ route('teacher.live-sessions.start', $s) }}" method="POST" class="inline">@csrf<button type="submit" class="inline-flex h-8 items-center gap-1 rounded-xl bg-green-600 px-3 text-xs font-black text-white"><x-heroicon-o-play class="h-4 w-4" /> @lang('teacher-live-session::app.start')</button></form>
                                                @endif

                                                {{-- Join + End (live) --}}
                                                @if($s->isLive())
                                                    <a href="{{ route('teacher.live-sessions.room', $s) }}"
                                                        class="inline-flex h-8 items-center gap-1 rounded-xl bg-red-600 px-3 text-xs font-black text-white hover:bg-red-500">
                                                        <x-heroicon-o-video-camera class="h-4 w-4" /> @lang('teacher-live-session::app.join_room')
                                                    </a>
                                                    <form action="{{ route('teacher.live-sessions.end', $s) }}" method="POST" class="inline"
                                                        data-mindigo-confirm-title="{{ __('teacher-live-session::app.end') }}"
                                                        data-mindigo-confirm-message="{{ __('teacher-live-session::app.subtitle') }}"
                                                        data-mindigo-confirm-type="danger">
                                                        @csrf
                                                        <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 hover:bg-red-50 hover:text-red-600" title="{{ __('teacher-live-session::app.end') }}">
                                                            <x-heroicon-o-stop class="h-4 w-4" />
                                                        </button>
                                                    </form>
                                                @endif

                                                @if(in_array($s->status, ['live', 'ended'], true))
                                                    <a href="{{ route('teacher.live-sessions.attendance.show', $s) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 hover:bg-green-50 hover:text-green-700" title="@lang('teacher-live-session::app.attendance_report')"><x-heroicon-o-chart-bar-square class="h-4 w-4" /></a>
                                                @endif

                                                {{-- Edit (not ended) --}}
                                                @if(in_array($s->status, ['draft', 'scheduled'], true))
                                                    @if($s->provider->isExternal())
                                                        <form action="{{ route('teacher.live-sessions.fallback-native', $s) }}" method="POST" class="inline"
                                                            data-mindigo-confirm-title="@lang('teacher-live-session::app.fallback_native')"
                                                            data-mindigo-confirm-message="@lang('teacher-live-session::app.fallback_native_confirm')">
                                                            @csrf
                                                            <button type="submit" class="inline-flex h-8 items-center gap-1 rounded-xl border border-green-200 bg-green-50 px-3 text-xs font-black text-green-700" title="@lang('teacher-live-session::app.fallback_native')">
                                                                <x-heroicon-o-arrow-path class="h-4 w-4" /> @lang('teacher-live-session::app.fallback_native')
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <a href="{{ route('teacher.live-sessions.edit', $s) }}"
                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                                                        title="{{ __('teacher-live-session::app.edit') }}">
                                                        <x-heroicon-o-pencil-square class="h-4 w-4" />
                                                    </a>
                                                @endif

                                                {{-- Delete --}}
                                                <form action="{{ route('teacher.live-sessions.destroy', $s) }}" method="POST" class="inline"
                                                    data-mindigo-confirm-title="{{ __('teacher-live-session::app.delete_confirm_title') }}"
                                                    data-mindigo-confirm-message="{{ __('teacher-live-session::app.delete_confirm_message') }}"
                                                    data-mindigo-confirm-text="{{ __('teacher-live-session::app.delete') }}"
                                                    data-mindigo-confirm-cancel="{{ __('teacher-live-session::app.cancel') }}"
                                                    data-mindigo-confirm-type="danger">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 hover:bg-red-50 hover:text-red-600" title="{{ __('teacher-live-session::app.delete') }}">
                                                        <x-heroicon-o-trash class="h-4 w-4" />
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($sessions->hasPages())
                    <div class="flex justify-center mt-4">{{ $sessions->links() }}</div>
                @endif
            @endif
        </div>

        <div data-mindigo-drawer="teacher-live-filter" class="fixed inset-0 z-40 hidden bg-slate-950/45 opacity-0 backdrop-blur-sm transition-opacity duration-200"></div>
        <aside data-mindigo-drawer-panel="teacher-live-filter"
            class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col border-l border-slate-200 bg-white shadow-2xl shadow-slate-950/20 transition-transform duration-200"
            style="transform: translateX(100%);">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-wider text-green-700">Bộ lọc</p>
                    <h2 class="mt-1 text-lg font-black text-slate-950">Lọc buổi học trực tuyến</h2>
                    <p class="mt-1 text-sm font-semibold text-slate-400">Chọn lớp để thu gọn danh sách buổi học.</p>
                </div>
                <button type="button" data-mindigo-drawer-close="teacher-live-filter"
                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-900">
                    <x-heroicon-o-x-mark class="h-5 w-5" />
                </button>
            </div>

            <form action="{{ route('teacher.live-sessions.index') }}" method="GET" class="flex flex-1 flex-col">
                <div class="flex-1 space-y-5 overflow-y-auto px-5 py-5">
                    <div class="space-y-2">
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-500">@lang('teacher-live-session::app.filter_classroom_label')</label>
                        <select name="classroom_id" class="block h-12 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700 outline-none transition focus:border-green-300 focus:ring-4 focus:ring-green-100">
                            <option value="">-- @lang('teacher-live-session::app.all_classrooms') --</option>
                            @foreach($classrooms as $class)
                                <option value="{{ $class->id }}" {{ request('classroom_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }} ({{ $class->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <div class="flex items-center gap-3">
                            <span class="grid h-10 w-10 place-items-center rounded-xl bg-green-100 text-green-700">
                                <x-heroicon-o-information-circle class="h-5 w-5" />
                            </span>
                            <div>
                                <p class="text-sm font-black text-slate-800">Gợi ý</p>
                                <p class="mt-0.5 text-xs font-semibold leading-5 text-slate-500">Bộ lọc được đặt trong drawer để khu vực dữ liệu chính luôn rộng và dễ nhìn.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 border-t border-slate-100 p-5">
                    <a href="{{ route('teacher.live-sessions.index') }}"
                        class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-600 no-underline transition hover:bg-slate-50">
                        Xóa lọc
                    </a>
                    <button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-green-600 px-4 text-sm font-black text-white shadow-sm transition hover:bg-green-500">
                        <x-heroicon-o-funnel class="h-4 w-4" />
                        @lang('teacher-live-session::app.filter_submit')
                    </button>
                </div>
            </form>
        </aside>
    </div>
@endsection
