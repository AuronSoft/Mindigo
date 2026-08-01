@extends('Mindigo-dashboard::layouts')
@section('title', __('student-leaderboard::app.title') . ' · Mindigo LMS')
@section('meta_description', __('student-leaderboard::app.subtitle'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
@php
    $rows  = $ranking['rows'];
    $me    = $ranking['me'];
    $total = $ranking['total'];
    $top3  = $rows->take(3);

    $medal = [
        1 => ['ring' => 'ring-amber-300',  'bg' => 'bg-amber-50',  'text' => 'text-amber-600',  'grad' => 'from-amber-400 to-yellow-500',  'label' => '🥇'],
        2 => ['ring' => 'ring-slate-300',  'bg' => 'bg-slate-50',  'text' => 'text-slate-500',  'grad' => 'from-slate-300 to-slate-400',   'label' => '🥈'],
        3 => ['ring' => 'ring-orange-300', 'bg' => 'bg-orange-50', 'text' => 'text-orange-600', 'grad' => 'from-orange-400 to-amber-600',  'label' => '🥉'],
    ];

    $initial = function ($name) {
        $name = trim((string) $name);
        return $name === '' ? '?' : mb_strtoupper(mb_substr($name, 0, 1));
    };
@endphp

<div class="flex min-h-screen flex-col bg-slate-50">

    {{-- Header --}}
    <header class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white/90 px-6 py-4 backdrop-blur">
        <div>
            <p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('student-leaderboard::app.area')</p>
            <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('student-leaderboard::app.title')</h1>
            <p class="text-xs font-semibold text-slate-400">@lang('student-leaderboard::app.subtitle')</p>
        </div>

        {{-- Filter --}}
        <form action="{{ route('student.leaderboard.index') }}" method="GET" class="flex items-end gap-2">
            <select name="classroom_id" data-mindigo-auto-submit
                class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700 outline-none transition focus:border-green-300">
                <option value="">@lang('student-leaderboard::app.all_classrooms')</option>
                @foreach($classrooms as $class)
                    <option value="{{ $class->id }}" {{ $classroomId == $class->id ? 'selected' : '' }}>{{ $class->name }} ({{ $class->code }})</option>
                @endforeach
            </select>
            @if($classroomId)
                <a href="{{ route('student.leaderboard.index') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-500 no-underline hover:bg-slate-100" title="{{ __('student-leaderboard::app.clear_filter') }}">
                    <x-heroicon-o-x-mark class="h-4 w-4" />
                </a>
            @endif
        </form>
    </header>

    <div class="flex flex-1 flex-col gap-5 p-6">

        @if($total === 0 || ($rows->count() === 1 && $me && ! $me->has_data))
            {{-- Empty state --}}
            <div class="flex flex-1 flex-col items-center justify-center gap-4 rounded-3xl border border-dashed border-slate-200 bg-white py-20">
                <span class="grid h-20 w-20 place-items-center rounded-full bg-slate-50 text-slate-300">
                    <x-heroicon-o-trophy class="h-10 w-10" />
                </span>
                <div class="text-center">
                    <p class="text-lg font-black text-slate-700">@lang('student-leaderboard::app.empty_title')</p>
                    <p class="mt-1 max-w-xs text-sm font-semibold leading-relaxed text-slate-400">@lang('student-leaderboard::app.empty_desc')</p>
                </div>
            </div>
        @else
            {{-- Podium: top 3 --}}
            @if($top3->isNotEmpty())
                <section class="grid grid-cols-3 gap-4 max-md:grid-cols-1">
                    @foreach($top3 as $row)
                        @php $m = $medal[$row->rank] ?? $medal[3]; @endphp
                        <div class="relative overflow-hidden rounded-3xl bg-white p-5 text-center shadow-sm ring-1 {{ $row->is_me ? 'ring-green-300' : $m['ring'] }}">
                            <div class="absolute inset-x-0 top-0 h-1.5 bg-linear-to-r {{ $m['grad'] }}"></div>
                            <div class="mx-auto mb-2 mt-1 text-3xl">{{ $m['label'] }}</div>
                            <div class="mx-auto grid h-14 w-14 place-items-center rounded-full {{ $m['bg'] }} text-xl font-black {{ $m['text'] }}">
                                {{ $initial($row->name) }}
                            </div>
                            <p class="mt-2 flex items-center justify-center gap-1.5 truncate font-black text-slate-800">
                                {{ $row->name }}
                                @if($row->is_me)
                                    <span class="rounded-full bg-green-100 px-1.5 py-0.5 text-[10px] font-black text-green-700">@lang('student-leaderboard::app.you_badge')</span>
                                @endif
                            </p>
                            <p class="mt-1 text-2xl font-black text-slate-900">{{ $row->score }}<span class="text-sm font-bold text-slate-400">/100</span></p>
                            <p class="text-[11px] font-bold text-slate-400">{{ $row->completed }} @lang('student-leaderboard::app.completed_unit')</p>
                        </div>
                    @endforeach
                </section>
            @endif

            {{-- Ranked list --}}
            <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                    <h2 class="text-base font-black text-slate-800">@lang('student-leaderboard::app.title')</h2>
                    <span class="text-xs font-bold text-slate-400">@lang('student-leaderboard::app.of_total', ['total' => $total])</span>
                </div>

                {{-- Column headers --}}
                <div class="grid grid-cols-[3rem_minmax(0,1fr)_6rem_12rem] items-center gap-3 border-b border-slate-100 px-5 py-2 text-[11px] font-black uppercase tracking-wide text-slate-400 max-sm:grid-cols-[2.5rem_minmax(0,1fr)_9rem]">
                    <span class="text-center">@lang('student-leaderboard::app.col_rank')</span>
                    <span>@lang('student-leaderboard::app.col_student')</span>
                    <span class="text-center max-sm:hidden">@lang('student-leaderboard::app.col_completed')</span>
                    <span class="text-right">@lang('student-leaderboard::app.col_score')</span>
                </div>

                @foreach($rows as $row)
                    @php $m = $medal[$row->rank] ?? null; @endphp
                    <div class="grid grid-cols-[3rem_minmax(0,1fr)_6rem_12rem] items-center gap-3 border-b border-slate-50 px-5 py-3 last:border-b-0 {{ $row->is_me ? 'bg-green-50/70' : '' }} max-sm:grid-cols-[2.5rem_minmax(0,1fr)_9rem]">
                        {{-- Rank --}}
                        <div class="text-center">
                            @if($m)
                                <span class="text-lg leading-none">{{ $m['label'] }}</span>
                            @else
                                <span class="text-sm font-black text-slate-400">{{ $row->rank }}</span>
                            @endif
                        </div>

                        {{-- Student --}}
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full {{ $row->is_me ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }} text-sm font-black">
                                {{ $initial($row->name) }}
                            </span>
                            <p class="flex min-w-0 items-center gap-1.5">
                                <span class="truncate font-black text-slate-700">{{ $row->name }}</span>
                                @if($row->is_me)
                                    <span class="shrink-0 rounded-full bg-green-100 px-1.5 py-0.5 text-[10px] font-black text-green-700">@lang('student-leaderboard::app.you_badge')</span>
                                @endif
                            </p>
                        </div>

                        {{-- Completed --}}
                        <div class="text-center text-sm font-bold text-slate-500 max-sm:hidden">{{ $row->completed }}</div>

                        {{-- Score + bar --}}
                        <div class="flex items-center gap-2">
                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full {{ $row->is_me ? 'bg-green-500' : 'bg-slate-400' }}" style="width: {{ $row->score }}%"></div>
                            </div>
                            @if($row->has_data)
                                <span class="w-12 shrink-0 text-right text-sm font-black text-slate-700">{{ $row->score }}</span>
                            @else
                                <span class="w-12 shrink-0 text-right text-[10px] font-bold text-slate-300">@lang('student-leaderboard::app.no_score')</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </section>
        @endif
    </div>

    {{-- Sticky "your rank" card --}}
    @if($me)
        <div class="sticky bottom-0 z-10 border-t border-slate-200 bg-white/95 px-6 py-3 backdrop-blur">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-green-50 text-green-600">
                        <x-heroicon-o-trophy class="h-5 w-5" />
                    </span>
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">@lang('student-leaderboard::app.your_rank')</p>
                        <p class="font-black text-slate-800">
                            #{{ $me->rank }}
                            <span class="text-xs font-bold text-slate-400">@lang('student-leaderboard::app.of_total', ['total' => $total])</span>
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    @if($me->has_data)
                        <p class="text-xl font-black text-slate-900">{{ $me->score }}<span class="text-xs font-bold text-slate-400">/100</span></p>
                        <p class="text-[11px] font-bold text-slate-400">{{ $me->completed }} @lang('student-leaderboard::app.completed_unit')</p>
                    @else
                        <p class="text-sm font-bold text-slate-300">@lang('student-leaderboard::app.no_score')</p>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
