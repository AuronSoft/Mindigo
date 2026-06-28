@extends('Mindigo-dashboard::layouts')
@section('title', __('student-live-session::app.title') . ' · Mindigo LMS')
@section('meta_description', __('student-live-session::app.subtitle'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
    <div class="flex min-h-screen flex-col bg-slate-50">

        {{-- Header --}}
        <header class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white/90 px-6 py-4 backdrop-blur">
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">@lang('student-live-session::app.area')</p>
                <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('student-live-session::app.title')</h1>
                <p class="text-xs font-semibold text-slate-400">@lang('student-live-session::app.subtitle')</p>
            </div>
            <span class="hidden sm:grid h-11 w-11 place-items-center rounded-2xl bg-rose-50 text-rose-500">
                <x-heroicon-o-video-camera class="h-6 w-6" />
            </span>
        </header>

        <div class="flex flex-1 flex-col gap-5 p-6">

            {{-- Filter --}}
            <form action="{{ route('student.live-sessions.index') }}" method="GET"
                class="grid grid-cols-1 gap-4 sm:grid-cols-3 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm items-end">
                <div class="space-y-1 sm:col-span-2">
                    <label class="text-xs font-bold text-slate-500 block">@lang('student-live-session::app.filter_classroom_label')</label>
                    <select name="classroom_id" class="block h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700 outline-none transition focus:border-green-300">
                        <option value="">-- @lang('student-live-session::app.all_classrooms') --</option>
                        @foreach($classrooms as $class)
                            <option value="{{ $class->id }}" {{ request('classroom_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} ({{ $class->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 inline-flex h-10 items-center justify-center gap-1.5 rounded-xl bg-green-600 px-4 text-xs font-black text-white shadow-sm transition hover:bg-green-500">
                        @lang('student-live-session::app.filter_submit')
                    </button>
                    @if(request('classroom_id'))
                        <button type="button" onclick="window.location.href='{{ route('student.live-sessions.index') }}'"
                            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-500 hover:bg-slate-100"
                            title="{{ __('student-live-session::app.clear_filter') }}">
                            <x-heroicon-o-x-mark class="h-4 w-4" />
                        </button>
                    @endif
                </div>
            </form>

            @if($sessions->isEmpty())
                <div class="flex flex-1 flex-col items-center justify-center gap-4 rounded-3xl border border-dashed border-slate-200 bg-white py-20">
                    <span class="grid h-20 w-20 place-items-center rounded-full bg-slate-50 text-slate-300">
                        <x-heroicon-o-video-camera class="h-10 w-10" />
                    </span>
                    <div class="text-center">
                        <p class="text-lg font-black text-slate-700">@lang('student-live-session::app.empty_title')</p>
                        <p class="mt-1 max-w-xs text-sm font-semibold leading-relaxed text-slate-400">@lang('student-live-session::app.empty_desc')</p>
                    </div>
                </div>
            @else
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50/70 text-xs font-black uppercase tracking-wider text-slate-400">
                                    <th class="px-6 py-4">@lang('student-live-session::app.col_number')</th>
                                    <th class="px-6 py-4">@lang('student-live-session::app.col_title')</th>
                                    <th class="px-6 py-4">@lang('student-live-session::app.col_classroom')</th>
                                    <th class="px-6 py-4">@lang('student-live-session::app.col_teacher')</th>
                                    <th class="px-6 py-4">@lang('student-live-session::app.col_schedule')</th>
                                    <th class="px-6 py-4">@lang('student-live-session::app.col_status')</th>
                                    <th class="px-6 py-4 text-center">@lang('student-live-session::app.col_actions')</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm font-semibold text-slate-600">
                                @foreach($sessions as $i => $s)
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="px-6 py-4 text-slate-400">{{ $sessions->firstItem() + $i }}</td>
                                        <td class="px-6 py-4 font-black text-slate-900">{{ $s->title }}</td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-bold text-slate-600">
                                                {{ $s->classroom->name ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">{{ $s->teacher->name ?? '—' }}</td>
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
                                                    @lang('student-live-session::app.status_live')
                                                </span>
                                            @elseif($s->isScheduled())
                                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-700">@lang('student-live-session::app.status_scheduled')</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-500">@lang('student-live-session::app.status_ended')</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-center">
                                                @if($s->isLive())
                                                    <a href="{{ route('student.live-sessions.room', $s) }}"
                                                        class="inline-flex h-8 items-center gap-1 rounded-full bg-red-600 px-3 text-xs font-black text-white no-underline hover:bg-red-500">
                                                        <x-heroicon-o-video-camera class="h-4 w-4" /> @lang('student-live-session::app.join_room')
                                                    </a>
                                                @elseif($s->isScheduled())
                                                    <span class="inline-flex h-8 items-center gap-1 rounded-full bg-slate-100 px-3 text-xs font-black text-slate-400">
                                                        <x-heroicon-o-clock class="h-4 w-4" /> @lang('student-live-session::app.not_started')
                                                    </span>
                                                @else
                                                    <span class="text-xs font-bold text-slate-300">—</span>
                                                @endif
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
    </div>
@endsection
