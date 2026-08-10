@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-calendar::app.title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Teacher/TeacherCalendar/src/resources/css/app.css',
        'packages/Teacher/TeacherCalendar/src/resources/js/app.js',
    ])
@endsection

@section('content')
@php
    $tones = [
        'class_session' => 'border-blue-200 bg-blue-50 text-blue-800',
        'assignment_due' => 'border-orange-200 bg-orange-50 text-orange-800',
        'exam_window' => 'border-violet-200 bg-violet-50 text-violet-800',
        'live_session' => 'border-rose-200 bg-rose-50 text-rose-800',
    ];
    $hours = range(7, 19);
    $query = request()->except('date');
@endphp
<div class="flex min-h-screen flex-col bg-slate-50 text-slate-900">
    <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 bg-white px-5 py-4 lg:px-6">
        <div class="flex items-center gap-3">
            <span class="grid h-11 w-11 place-items-center rounded-xl bg-blue-50 text-blue-700"><x-heroicon-o-calendar-days class="h-6 w-6" /></span>
            <div><h1 class="text-lg font-black">@lang('teacher-calendar::app.title')</h1><p class="text-xs font-semibold text-slate-400">@lang('teacher-calendar::app.subtitle')</p></div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('teacher.calendar.index', [...$query, 'date' => now()->toDateString()]) }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 no-underline hover:bg-slate-50">@lang('teacher-calendar::app.today')</a>
            <a aria-label="Previous week" href="{{ route('teacher.calendar.index', [...$query, 'date' => $start->subWeek()->toDateString()]) }}" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600"><x-heroicon-o-arrow-left class="h-4 w-4" /></a>
            <a aria-label="Next week" href="{{ route('teacher.calendar.index', [...$query, 'date' => $start->addWeek()->toDateString()]) }}" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600"><x-heroicon-o-arrow-right class="h-4 w-4" /></a>
            <strong class="min-w-48 text-center text-sm">{{ $start->format('d/m') }} – {{ $end->subDay()->format('d/m/Y') }}</strong>
            <span class="rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-black text-white">@lang('teacher-calendar::app.week')</span>
            <button type="button" data-calendar-create data-date="{{ now()->toDateString() }}" class="inline-flex h-10 items-center gap-2 rounded-xl bg-green-600 px-4 text-xs font-black text-white hover:bg-green-700"><x-heroicon-o-plus class="h-4 w-4" />@lang('teacher-calendar::app.new_session')</button>
        </div>
    </header>

    <div class="grid flex-1 lg:grid-cols-[16rem_minmax(0,1fr)]">
        <aside class="hidden border-r border-slate-200 bg-white p-4 lg:block">
            <section class="rounded-2xl border border-slate-200 p-4">
                <h2 class="text-sm font-black capitalize">{{ $anchor->translatedFormat('F Y') }}</h2>
                <div class="mt-4 grid grid-cols-7 gap-y-2 text-center text-[10px] font-bold text-slate-400">
                    @foreach(['T2','T3','T4','T5','T6','T7','CN'] as $label)<span>{{ $label }}</span>@endforeach
                    @php $monthStart = $anchor->startOfMonth()->startOfWeek(); @endphp
                    @foreach(range(0, 41) as $offset)
                        @php $date = $monthStart->addDays($offset); @endphp
                        <a href="{{ route('teacher.calendar.index', [...$query, 'date' => $date->toDateString()]) }}" class="grid h-7 w-7 place-items-center rounded-lg text-[11px] no-underline {{ $date->isToday() ? 'bg-blue-600 text-white' : ($date->month === $anchor->month ? 'text-slate-700 hover:bg-slate-100' : 'text-slate-300') }}">{{ $date->day }}</a>
                    @endforeach
                </div>
            </section>

            <form method="GET" class="mt-4 rounded-2xl border border-slate-200 p-4">
                <input type="hidden" name="date" value="{{ $anchor->toDateString() }}">
                <h2 class="text-xs font-black uppercase tracking-wide text-slate-500">@lang('teacher-calendar::app.filters')</h2>
                <select name="classroom_id" onchange="this.form.submit()" class="mt-3 h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold">
                    <option value="">@lang('teacher-calendar::app.all_classrooms')</option>
                    @foreach($classrooms as $classroom)<option value="{{ $classroom->id }}" @selected(($filters['classroom_id'] ?? null) == $classroom->id)>{{ $classroom->name }}</option>@endforeach
                </select>
                <div class="mt-3 space-y-2">
                    @foreach(\Mindigo\AcademicCalendar\Enums\CalendarEventKind::cases() as $kind)
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-600"><input type="checkbox" name="kinds[]" value="{{ $kind->value }}" @checked(empty($filters['kinds']) || in_array($kind->value, $filters['kinds'], true)) onchange="this.form.submit()" class="accent-green-600">@lang('teacher-calendar::app.'.$kind->value)</label>
                    @endforeach
                </div>
            </form>

            <section class="mt-4 rounded-2xl border border-slate-200 p-4">
                <h2 class="text-xs font-black uppercase tracking-wide text-slate-500">@lang('teacher-calendar::app.summary')</h2>
                <dl class="mt-3 space-y-3 text-xs"><div class="flex justify-between"><dt class="font-semibold text-slate-500">@lang('teacher-calendar::app.sessions')</dt><dd class="font-black">{{ $summary['class_sessions'] }}</dd></div><div class="flex justify-between"><dt class="font-semibold text-slate-500">@lang('teacher-calendar::app.hours')</dt><dd class="font-black">{{ $summary['hours'] }}</dd></div><div class="flex justify-between"><dt class="font-semibold text-slate-500">@lang('teacher-calendar::app.events')</dt><dd class="font-black">{{ $summary['count'] }}</dd></div></dl>
            </section>
        </aside>

        <main class="min-w-0 p-3 lg:p-5">
            <div class="calendar-scrollbar overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="min-w-245">
                    <div class="grid grid-cols-[4.5rem_repeat(7,minmax(8rem,1fr))] border-b border-slate-200">
                        <div class="px-3 py-4 text-[10px] font-black text-slate-400">@lang('teacher-calendar::app.timezone')</div>
                        @foreach($days as $day)<div class="border-l border-slate-100 px-3 py-3 text-center"><span class="block text-[10px] font-black uppercase text-slate-400">{{ $day->translatedFormat('D') }}</span><strong class="mt-1 inline-grid h-8 w-8 place-items-center rounded-xl {{ $day->isToday() ? 'bg-blue-600 text-white' : 'text-slate-800' }}">{{ $day->day }}</strong></div>@endforeach
                    </div>
                    <div class="grid grid-cols-[4.5rem_repeat(7,minmax(8rem,1fr))]">
                        <div class="relative h-192">@foreach($hours as $hour)<span class="absolute right-3 -translate-y-2 text-[10px] font-bold text-slate-400" style="top: {{ ($hour - 7) * 64 }}px">{{ sprintf('%02d:00', $hour) }}</span>@endforeach</div>
                        @foreach($days as $day)
                            <div class="relative h-192 border-l border-slate-100 {{ $day->isToday() ? 'bg-blue-50/25' : '' }}">
                                @foreach($hours as $hour)<button type="button" data-calendar-create data-date="{{ $day->toDateString() }}" data-start="{{ sprintf('%02d:00', $hour) }}" data-end="{{ sprintf('%02d:00', min(23, $hour + 1)) }}" aria-label="Create at {{ $day->format('d/m/Y H:00') }}" class="absolute inset-x-0 h-16 border-t border-slate-100 hover:bg-green-50/50" style="top: {{ ($hour - 7) * 64 }}px"></button>@endforeach
                                @foreach(($eventsByDay[$day->toDateString()] ?? collect()) as $event)
                                    @php
                                        $startMinutes = max(0, ($event->startsAt->hour - 7) * 60 + $event->startsAt->minute);
                                        $duration = $event->endsAt ? max(45, $event->startsAt->diffInMinutes($event->endsAt)) : 45;
                                        $payload = ['title' => $event->title, 'kindLabel' => __('teacher-calendar::app.'.$event->kind->value), 'time' => $event->startsAt->format('H:i').($event->endsAt ? ' – '.$event->endsAt->format('H:i') : ''), 'classroom' => $event->metadata['classroom_name'] ?? null, 'url' => $event->url, 'cancelUrl' => $event->kind === \Mindigo\AcademicCalendar\Enums\CalendarEventKind::ClassSession ? route('teacher.calendar.sessions.cancel', $event->sourceId) : null];
                                    @endphp
                                    <button type="button" data-calendar-event='@json($payload)' class="calendar-event calendar-motion absolute inset-x-1 z-10 overflow-hidden rounded-xl border p-2 text-left shadow-sm transition hover:shadow-md {{ $tones[$event->kind->value] }}" style="top: {{ ($startMinutes / 60) * 64 }}px; height: {{ min(180, ($duration / 60) * 64) }}px">
                                        <span class="block truncate text-[10px] font-black">{{ $event->startsAt->format('H:i') }}</span><strong class="mt-0.5 block text-xs leading-tight">{{ $event->title }}</strong><span class="mt-1 block truncate text-[10px] font-semibold opacity-70">{{ $event->metadata['classroom_name'] ?? __('teacher-calendar::app.'.$event->kind->value) }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-3 space-y-2 lg:hidden">
                @foreach($events as $event)
                    @php
                        $mobilePayload = [
                            'title' => $event->title,
                            'kindLabel' => __('teacher-calendar::app.'.$event->kind->value),
                            'time' => $event->startsAt->format('d/m/Y H:i'),
                            'classroom' => $event->metadata['classroom_name'] ?? null,
                            'url' => $event->url,
                            'cancelUrl' => $event->kind === \Mindigo\AcademicCalendar\Enums\CalendarEventKind::ClassSession ? route('teacher.calendar.sessions.cancel', $event->sourceId) : null,
                        ];
                    @endphp
                    <button type="button" data-calendar-event='@json($mobilePayload)' class="flex w-full items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left"><span class="h-9 w-1 rounded-full bg-blue-500"></span><span><strong class="block text-sm">{{ $event->title }}</strong><small class="font-semibold text-slate-400">{{ $event->startsAt->format('d/m H:i') }}</small></span></button>
                @endforeach
            </div>
        </main>
    </div>
</div>

<div id="calendar-detail-drawer" data-calendar-layer aria-hidden="true" class="fixed inset-0 z-60 hidden bg-slate-950/35">
    <aside class="ml-auto flex h-full w-full max-w-md flex-col bg-white shadow-2xl"><div class="flex items-center justify-between border-b border-slate-200 p-5"><h2 class="font-black">@lang('teacher-calendar::app.event_details')</h2><button data-calendar-close class="grid h-9 w-9 place-items-center rounded-xl hover:bg-slate-100"><x-heroicon-o-x-mark class="h-5 w-5" /></button></div><div class="flex-1 space-y-5 p-5"><span data-event-kind class="inline-flex rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-black text-blue-700"></span><h3 data-event-title class="text-xl font-black"></h3><dl class="space-y-3 text-sm"><div><dt class="text-xs font-bold text-slate-400">@lang('teacher-calendar::app.date')</dt><dd data-event-time class="mt-1 font-black"></dd></div><div><dt class="text-xs font-bold text-slate-400">@lang('teacher-calendar::app.classroom')</dt><dd data-event-classroom class="mt-1 font-black"></dd></div></dl><form method="POST" data-event-cancel-form class="hidden rounded-xl border border-red-100 bg-red-50 p-3">@csrf<label class="text-xs font-black text-red-700">@lang('teacher-calendar::app.cancel_reason')<textarea required minlength="10" name="cancel_reason" rows="2" class="mt-1.5 w-full rounded-lg border border-red-200 bg-white p-2 text-slate-800"></textarea></label><button class="mt-2 h-9 rounded-lg bg-red-600 px-3 text-xs font-black text-white">@lang('teacher-calendar::app.cancel_session')</button></form></div><div class="border-t border-slate-200 p-5"><a data-event-link href="#" class="inline-flex h-10 items-center rounded-xl bg-slate-900 px-4 text-xs font-black text-white no-underline">@lang('teacher-calendar::app.view_classroom')</a></div></aside>
</div>

<div id="calendar-create-drawer" data-calendar-layer aria-hidden="true" class="fixed inset-0 z-60 hidden bg-slate-950/35">
    <aside class="ml-auto flex h-full w-full max-w-lg flex-col overflow-y-auto bg-white shadow-2xl"><div class="flex items-center justify-between border-b border-slate-200 p-5"><h2 class="font-black">@lang('teacher-calendar::app.new_session')</h2><button data-calendar-close class="grid h-9 w-9 place-items-center rounded-xl hover:bg-slate-100"><x-heroicon-o-x-mark class="h-5 w-5" /></button></div>
        <form id="calendar-session-form" method="POST" action="#" class="space-y-4 p-5">@csrf
            <label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.classroom')<select required id="calendar-classroom" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3" name="classroom_id"><option value="">@lang('teacher-calendar::app.all_classrooms')</option>@foreach($classrooms as $classroom)<option value="{{ $classroom->id }}" data-type="{{ $classroom->type }}" data-store-url="{{ route('teacher.calendar.sessions.store', $classroom) }}">{{ $classroom->name }}</option>@endforeach</select></label>
            <div id="calendar-session-type-shell" class="hidden"><label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.session_type')<select id="calendar-session-type" name="type" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3"><option value="regular">@lang('teacher-calendar::app.regular')</option><option value="makeup">@lang('teacher-calendar::app.makeup')</option></select></label></div>
            <div id="calendar-makeup-reason-shell" class="hidden"><label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.makeup_reason')<textarea name="makeup_reason" rows="2" class="mt-1.5 w-full rounded-xl border border-amber-200 bg-amber-50 p-3"></textarea></label></div>
            <label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.lesson')<select id="calendar-lesson" name="lesson_id" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3"><option value="">—</option>@foreach($classrooms as $classroom)@foreach($classroom->course?->chapters ?? [] as $chapter)@foreach($chapter->lessons as $lesson)<option hidden data-classroom="{{ $classroom->id }}" value="{{ $lesson->id }}">{{ $chapter->name }} · {{ $lesson->name }}</option>@endforeach @endforeach @endforeach</select></label>
            <label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.title_field')<input required name="title" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 px-3"></label>
            <div class="grid grid-cols-3 gap-3"><label class="text-xs font-black text-slate-600">@lang('teacher-calendar::app.date')<input required type="date" name="session_date" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 px-2"></label><label class="text-xs font-black text-slate-600">@lang('teacher-calendar::app.start')<input required type="time" name="start_time" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 px-2"></label><label class="text-xs font-black text-slate-600">@lang('teacher-calendar::app.end')<input required type="time" name="end_time" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 px-2"></label></div>
            <label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.delivery_mode')<select name="delivery_mode" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3"><option value="offline">@lang('teacher-calendar::app.offline')</option><option value="online">@lang('teacher-calendar::app.online')</option><option value="hybrid">@lang('teacher-calendar::app.hybrid')</option></select></label>
            <label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.location')<input name="location" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 px-3"></label><label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.meeting_url')<input type="url" name="meeting_url" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 px-3"></label><label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.description')<textarea name="description" rows="3" class="mt-1.5 w-full rounded-xl border border-slate-200 p-3"></textarea></label>
            <button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-green-600 text-sm font-black text-white hover:bg-green-700">@lang('teacher-calendar::app.save')</button>
        </form>
    </aside>
</div>
@endsection
