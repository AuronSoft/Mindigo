@extends('Mindigo-dashboard::layouts')
@section('title', $classroom->name . ' · Mindigo LMS')

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
@php
    $hasAssignmentRoute = Route::has('student.assignments.index');
@endphp
<div class="flex min-h-screen flex-col bg-slate-50">

    {{-- Header --}}
    <header class="sticky top-0 z-10 flex items-center gap-4 border-b border-slate-200 bg-white/90 px-6 py-4 backdrop-blur">
        <a href="{{ route('student.classrooms.index') }}"
           class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50">
            <x-heroicon-o-arrow-left class="h-5 w-5" />
        </a>
        <div class="min-w-0">
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">{{ $classroom->code }}</p>
            <h1 class="mt-0.5 truncate text-lg font-black text-slate-950">{{ $classroom->name }}</h1>
        </div>
    </header>

    <div class="grid flex-1 grid-cols-1 gap-5 p-6 lg:grid-cols-3">

        {{-- ── Cột chính ── --}}
        <div class="flex flex-col gap-5 lg:col-span-2">

            {{-- Lịch học sắp tới --}}
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-3 flex items-center gap-2 text-sm font-black text-slate-800">
                    <x-heroicon-o-calendar-days class="h-5 w-5 text-green-600" />
                    @lang('student-classroom::app.section_schedule')
                </h2>
                @forelse($schedules as $s)
                    <div class="flex items-center gap-3 border-t border-slate-100 py-3 first:border-t-0">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-green-50 text-center leading-none text-green-700">
                            <span class="text-base font-black">{{ $s->session_date?->format('d') }}</span>
                            <span class="text-[9px] font-bold uppercase">{{ $s->session_date?->format('M') }}</span>
                        </span>
                        <div class="min-w-0">
                            <p class="truncate font-black text-slate-700">{{ $s->title }}</p>
                            <p class="text-xs font-semibold text-slate-400">
                                {{ \Illuminate\Support\Str::of($s->start_time)->substr(0,5) }}
                                @if($s->end_time) – {{ \Illuminate\Support\Str::of($s->end_time)->substr(0,5) }} @endif
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="py-4 text-center text-sm font-semibold text-slate-400">@lang('student-classroom::app.empty_schedule')</p>
                @endforelse
            </section>

            {{-- Bài tập --}}
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-3 flex items-center gap-2 text-sm font-black text-slate-800">
                    <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-amber-500" />
                    @lang('student-classroom::app.section_assignments')
                </h2>
                @forelse($assignments as $a)
                    <div class="flex items-center justify-between gap-3 border-t border-slate-100 py-3 first:border-t-0">
                        <div class="min-w-0">
                            <p class="truncate font-black text-slate-700">{{ $a->title }}</p>
                            <p class="text-xs font-semibold {{ $a->isOverdue() ? 'text-red-500' : 'text-slate-400' }}">
                                @lang('student-classroom::app.due_date'): {{ $a->due_date?->format('d/m/Y H:i') }}
                                @if($a->isOverdue())
                                    · <span class="font-black uppercase">@lang('student-classroom::app.overdue')</span>
                                @endif
                            </p>
                        </div>
                        @if($hasAssignmentRoute)
                            <a href="{{ route('student.assignments.index') }}"
                               class="inline-flex h-8 shrink-0 items-center gap-1 rounded-full bg-slate-100 px-3 text-xs font-black text-slate-600 no-underline transition hover:bg-green-50 hover:text-green-700">
                                @lang('student-classroom::app.open_assignment')
                            </a>
                        @endif
                    </div>
                @empty
                    <p class="py-4 text-center text-sm font-semibold text-slate-400">@lang('student-classroom::app.empty_assignments')</p>
                @endforelse
            </section>

            {{-- Học trực tuyến --}}
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-3 flex items-center gap-2 text-sm font-black text-slate-800">
                    <x-heroicon-o-video-camera class="h-5 w-5 text-rose-500" />
                    @lang('student-classroom::app.section_live')
                </h2>
                @forelse($liveSessions as $live)
                    <div class="flex items-center justify-between gap-3 border-t border-slate-100 py-3 first:border-t-0">
                        <div class="min-w-0">
                            <p class="truncate font-black text-slate-700">{{ $live->title }}</p>
                            <p class="text-xs font-semibold text-slate-400">{{ $live->scheduled_start?->format('d/m/Y H:i') }}</p>
                        </div>
                        @if($live->status === 'live')
                            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-bold text-red-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-red-600 animate-pulse"></span>
                                @lang('student-classroom::app.status_live')
                            </span>
                        @elseif($live->status === 'scheduled')
                            <span class="inline-flex shrink-0 items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-700">@lang('student-classroom::app.status_scheduled')</span>
                        @else
                            <span class="inline-flex shrink-0 items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-500">@lang('student-classroom::app.status_ended')</span>
                        @endif
                    </div>
                @empty
                    <p class="py-4 text-center text-sm font-semibold text-slate-400">@lang('student-classroom::app.empty_live')</p>
                @endforelse
            </section>
        </div>

        {{-- ── Cột phụ ── --}}
        <div class="flex flex-col gap-5">

            {{-- Thông tin lớp --}}
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-3 text-sm font-black text-slate-800">@lang('student-classroom::app.section_info')</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-full bg-slate-100 text-slate-500"><x-heroicon-o-academic-cap class="h-4 w-4" /></span>
                        <div><dt class="text-[11px] font-bold uppercase text-slate-400">@lang('student-classroom::app.teacher')</dt>
                            <dd class="font-black text-slate-700">{{ $classroom->teacher->name ?? __('student-classroom::app.no_teacher') }}</dd></div>
                    </div>
                    @if($classroom->assistant)
                        <div class="flex items-center gap-3">
                            <span class="grid h-9 w-9 place-items-center rounded-full bg-slate-100 text-slate-500"><x-heroicon-o-user class="h-4 w-4" /></span>
                            <div><dt class="text-[11px] font-bold uppercase text-slate-400">@lang('student-classroom::app.assistant')</dt>
                                <dd class="font-black text-slate-700">{{ $classroom->assistant->name }}</dd></div>
                        </div>
                    @endif
                    @if($classroom->school_year)
                        <div class="flex items-center gap-3">
                            <span class="grid h-9 w-9 place-items-center rounded-full bg-slate-100 text-slate-500"><x-heroicon-o-calendar class="h-4 w-4" /></span>
                            <div><dt class="text-[11px] font-bold uppercase text-slate-400">@lang('student-classroom::app.school_year')</dt>
                                <dd class="font-black text-slate-700">{{ $classroom->school_year }}</dd></div>
                        </div>
                    @endif
                </dl>

                @if($classroom->subjects->isNotEmpty())
                    <div class="mt-4 flex flex-wrap gap-1.5 border-t border-slate-100 pt-4">
                        @foreach($classroom->subjects as $subject)
                            <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-bold text-green-700">{{ $subject->name }}</span>
                        @endforeach
                    </div>
                @endif

                @if($classroom->description)
                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <p class="text-[11px] font-bold uppercase text-slate-400">@lang('student-classroom::app.description')</p>
                        <p class="mt-1 text-sm font-semibold leading-relaxed text-slate-600">{{ $classroom->description }}</p>
                    </div>
                @endif
            </section>

            {{-- Thông báo --}}
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-3 flex items-center gap-2 text-sm font-black text-slate-800">
                    <x-heroicon-o-megaphone class="h-5 w-5 text-blue-500" />
                    @lang('student-classroom::app.section_announce')
                </h2>
                @forelse($announcements as $note)
                    <div class="border-t border-slate-100 py-3 first:border-t-0">
                        <p class="font-black text-slate-700">{{ $note->title }}</p>
                        <p class="text-xs font-semibold text-slate-400">{{ $note->created_at?->format('d/m/Y') }}</p>
                    </div>
                @empty
                    <p class="py-4 text-center text-sm font-semibold text-slate-400">@lang('student-classroom::app.empty_announce')</p>
                @endforelse
            </section>

            {{-- Thành viên --}}
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-3 flex items-center gap-2 text-sm font-black text-slate-800">
                    <x-heroicon-o-users class="h-5 w-5 text-slate-400" />
                    @lang('student-classroom::app.section_members')
                    <span class="ml-auto rounded-full bg-slate-100 px-2 py-0.5 text-xs font-black text-slate-500">{{ $members->count() }}</span>
                </h2>
                <div class="max-h-72 space-y-1 overflow-y-auto">
                    @foreach($members as $member)
                        <div class="flex items-center gap-2 rounded-xl px-2 py-1.5 {{ $member->id === auth()->id() ? 'bg-green-50' : '' }}">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-slate-100 text-xs font-black text-slate-500">
                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($member->name, 0, 1)) }}
                            </span>
                            <span class="truncate text-sm font-bold text-slate-600">{{ $member->name }}</span>
                            @if($member->id === auth()->id())
                                <span class="ml-auto rounded-full bg-green-600 px-2 py-0.5 text-[10px] font-black text-white">@lang('student-classroom::app.you')</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
