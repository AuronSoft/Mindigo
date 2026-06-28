@extends('Mindigo-dashboard::layouts')
@section('title', __('student-schedule::app.title') . ' · Mindigo LMS')
@section('meta_description', __('student-schedule::app.subtitle'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
@php
    $tones = [
        'indigo' => ['chip' => 'bg-indigo-50 text-indigo-700', 'dot' => 'bg-indigo-500'],
        'amber'  => ['chip' => 'bg-amber-50 text-amber-700',   'dot' => 'bg-amber-500'],
        'violet' => ['chip' => 'bg-violet-50 text-violet-700', 'dot' => 'bg-violet-500'],
        'rose'   => ['chip' => 'bg-rose-50 text-rose-700',     'dot' => 'bg-rose-500'],
    ];
    $weekdays = ['d_mon','d_tue','d_wed','d_thu','d_fri','d_sat','d_sun'];
    $legend = [
        'class'      => 'indigo',
        'assignment' => 'amber',
        'exam'       => 'violet',
        'live'       => 'rose',
    ];
@endphp
<div class="flex min-h-screen flex-col bg-slate-50">

    {{-- Header --}}
    <header class="sticky top-0 z-10 flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 bg-white/90 px-6 py-4 backdrop-blur">
        <div>
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">@lang('student-schedule::app.area')</p>
            <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('student-schedule::app.title')</h1>
            <p class="text-xs font-semibold text-slate-400">@lang('student-schedule::app.subtitle')</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('student.schedule.index', ['month' => $prevMonth]) }}" title="@lang('student-schedule::app.prev_month')"
               class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 no-underline transition hover:bg-slate-50">
                <x-heroicon-o-chevron-left class="h-5 w-5" />
            </a>
            <span class="min-w-40 text-center text-sm font-black capitalize text-slate-800">{{ $month->translatedFormat('F Y') }}</span>
            <a href="{{ route('student.schedule.index', ['month' => $nextMonth]) }}" title="@lang('student-schedule::app.next_month')"
               class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 no-underline transition hover:bg-slate-50">
                <x-heroicon-o-chevron-right class="h-5 w-5" />
            </a>
            <a href="{{ route('student.schedule.index') }}"
               class="inline-flex h-10 items-center rounded-xl bg-green-600 px-4 text-sm font-black text-white no-underline shadow-sm transition hover:bg-green-500">
                @lang('student-schedule::app.today')
            </a>
        </div>
    </header>

    <div class="grid flex-1 grid-cols-1 gap-5 p-6 xl:grid-cols-[minmax(0,1fr)_22rem]">

        {{-- ── Calendar ── --}}
        <div class="flex flex-col gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            {{-- Legend --}}
            <div class="flex flex-wrap items-center gap-3 px-1">
                @foreach($legend as $type => $tone)
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500">
                        <span class="h-2.5 w-2.5 rounded-full {{ $tones[$tone]['dot'] }}"></span>
                        @lang('student-schedule::app.type_' . $type)
                    </span>
                @endforeach
            </div>

            {{-- Weekday header --}}
            <div class="grid grid-cols-7 gap-1 border-b border-slate-100 pb-2">
                @foreach($weekdays as $wd)
                    <div class="text-center text-[11px] font-black uppercase tracking-wider text-slate-400">@lang('student-schedule::app.' . $wd)</div>
                @endforeach
            </div>

            {{-- Weeks --}}
            <div class="flex flex-col gap-1">
                @foreach($calendar as $week)
                    <div class="grid grid-cols-7 gap-1">
                        @foreach($week as $day)
                            <div class="min-h-24 rounded-xl border p-1.5 {{ $day->in_month ? 'border-slate-100 bg-white' : 'border-transparent bg-slate-50/50' }}">
                                <div class="mb-1 flex items-center justify-between">
                                    <span class="grid h-6 w-6 place-items-center rounded-full text-xs font-black
                                        {{ $day->is_today ? 'bg-green-600 text-white' : ($day->in_month ? 'text-slate-600' : 'text-slate-300') }}">
                                        {{ $day->date->day }}
                                    </span>
                                </div>
                                <div class="flex flex-col gap-1">
                                    @foreach($day->events->take(3) as $event)
                                        @php $t = $tones[$event->tone] ?? $tones['indigo']; @endphp
                                        @if($event->url)
                                            <a href="{{ $event->url }}" title="{{ $event->title }}" class="block truncate rounded-md px-1.5 py-0.5 text-[10px] font-black no-underline {{ $t['chip'] }}">
                                                {{ $event->at->format('H:i') }} {{ $event->title }}
                                            </a>
                                        @else
                                            <span title="{{ $event->title }}" class="block truncate rounded-md px-1.5 py-0.5 text-[10px] font-black {{ $t['chip'] }}">
                                                {{ $event->at->format('H:i') }} {{ $event->title }}
                                            </span>
                                        @endif
                                    @endforeach
                                    @if($day->events->count() > 3)
                                        <span class="px-1.5 text-[10px] font-bold text-slate-400">{{ __('student-schedule::app.more_count', ['count' => $day->events->count() - 3]) }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ── Upcoming ── --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 flex items-center gap-2 text-base font-black text-slate-800">
                <x-heroicon-o-clock class="h-5 w-5 text-green-600" />
                @lang('student-schedule::app.upcoming')
            </h2>
            <div class="flex flex-col">
                @forelse($upcoming as $event)
                    @php $t = $tones[$event->tone] ?? $tones['indigo']; @endphp
                    <div class="flex items-start gap-3 border-t border-slate-100 py-3 first:border-t-0">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $t['chip'] }}">
                            <x-dynamic-component :component="$event->icon" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-black text-slate-800">{{ $event->title }}</p>
                            <p class="text-xs font-bold text-slate-400">
                                {{ $event->at->translatedFormat('D, d/m') }} · {{ $event->at->format('H:i') }}
                                @if($event->classroom) · {{ $event->classroom }} @endif
                            </p>
                            <span class="mt-1 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-black {{ $t['chip'] }}">
                                @lang('student-schedule::app.type_' . $event->type)
                            </span>
                        </div>
                        @if($event->url)
                            <a href="{{ $event->url }}" class="grid h-7 w-7 shrink-0 place-items-center rounded-lg text-slate-300 no-underline transition hover:bg-slate-100 hover:text-green-600">
                                <x-heroicon-o-arrow-right class="h-4 w-4" />
                            </a>
                        @endif
                    </div>
                @empty
                    <p class="py-10 text-center text-sm font-semibold text-slate-400">@lang('student-schedule::app.empty_upcoming')</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
