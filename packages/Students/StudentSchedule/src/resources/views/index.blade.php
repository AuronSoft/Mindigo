@extends('Mindigo-dashboard::layouts')

@section('title', __('student-schedule::app.title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Students/StudentSchedule/src/resources/css/app.css',
        'packages/Students/StudentSchedule/src/resources/js/app.js',
    ])
@endsection

@section('content')
@php
    use Mindigo\AcademicCalendar\Enums\CalendarEventKind;
    use Mindigo\AcademicCalendar\Enums\CalendarEventStatus;
    $tones = [
        'class_session' => 'border-blue-200 bg-blue-50 text-blue-800',
        'assignment_due' => 'border-orange-200 bg-orange-50 text-orange-800',
        'exam_window' => 'border-violet-200 bg-violet-50 text-violet-800',
        'live_session' => 'border-rose-200 bg-rose-50 text-rose-800',
    ];
    $icons = ['class_session' => 'heroicon-o-academic-cap', 'assignment_due' => 'heroicon-o-clipboard-document-list', 'exam_window' => 'heroicon-o-document-text', 'live_session' => 'heroicon-o-video-camera'];
    $query = request()->except(['date', 'view']);
    $step = $viewMode === 'month' ? 'month' : ($viewMode === 'week' ? 'week' : 'day');
    $previous = match($step) { 'month' => $anchor->subMonth(), 'week' => $anchor->subWeek(), default => $anchor->subDay() };
    $next = match($step) { 'month' => $anchor->addMonth(), 'week' => $anchor->addWeek(), default => $anchor->addDay() };
    $payload = function ($event) {
        $cancelled = $event->status === CalendarEventStatus::Cancelled;
        $action = match($event->kind) {
            CalendarEventKind::AssignmentDue => __('student-schedule::app.submit_assignment'),
            CalendarEventKind::ExamWindow => __('student-schedule::app.take_exam'),
            CalendarEventKind::LiveSession => __('student-schedule::app.join_class'),
            default => ($event->metadata['meeting_url'] ?? null) ? __('student-schedule::app.join_class') : __('student-schedule::app.view_lesson'),
        };
        $url = (!$cancelled && $event->kind === CalendarEventKind::ClassSession && ($event->metadata['meeting_url'] ?? null)) ? $event->metadata['meeting_url'] : $event->url;
        $context = collect([
            isset($event->metadata['session_type']) ? __('student-schedule::app.'.$event->metadata['session_type']) : null,
            isset($event->metadata['delivery_mode']) ? __('student-schedule::app.'.$event->metadata['delivery_mode']) : null,
            $event->metadata['location'] ?? null,
            $event->metadata['cancel_reason'] ?? $event->metadata['makeup_reason'] ?? null,
        ])->filter()->implode(' · ');
        return ['title' => $event->title, 'kind' => __('student-schedule::app.'.$event->kind->value), 'time' => $event->startsAt->format('d/m/Y H:i').($event->endsAt ? ' – '.$event->endsAt->format('H:i') : ''), 'classroom' => $event->metadata['classroom_name'] ?? null, 'context' => $context, 'url' => $url, 'action' => $action, 'cancelled' => $cancelled];
    };
@endphp
<div class="min-h-screen bg-slate-50 text-slate-900">
    <header class="border-b border-slate-200 bg-white px-4 py-4 lg:px-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3"><span class="grid h-11 w-11 place-items-center rounded-xl bg-green-50 text-green-700"><x-heroicon-o-calendar-days class="h-6 w-6" /></span><div><h1 class="text-lg font-black">@lang('student-schedule::app.title')</h1><p class="text-xs font-semibold text-slate-400">@lang('student-schedule::app.subtitle')</p></div></div>
            <nav aria-label="Calendar views" class="flex rounded-xl border border-slate-200 bg-slate-50 p-1">
                @foreach(['today','week','month','schedule'] as $mode)<a href="{{ route('student.schedule.index', [...$query, 'view' => $mode, 'date' => $anchor->toDateString()]) }}" class="rounded-lg px-3 py-2 text-xs font-black no-underline {{ $viewMode === $mode ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:text-slate-900' }}">@lang('student-schedule::app.view_'.$mode)</a>@endforeach
            </nav>
            <div class="flex items-center gap-2"><a href="{{ route('student.schedule.index', [...$query, 'view' => $viewMode, 'date' => now()->toDateString()]) }}" class="h-10 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-700 no-underline">@lang('student-schedule::app.today')</a><a aria-label="Previous" href="{{ route('student.schedule.index', [...$query, 'view' => $viewMode, 'date' => $previous->toDateString()]) }}" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white"><x-heroicon-o-chevron-left class="h-4 w-4" /></a><a aria-label="Next" href="{{ route('student.schedule.index', [...$query, 'view' => $viewMode, 'date' => $next->toDateString()]) }}" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white"><x-heroicon-o-chevron-right class="h-4 w-4" /></a></div>
        </div>
    </header>

    <main class="p-4 lg:p-6">
        <section aria-labelledby="next-actions" class="mb-5"><div class="mb-3 flex items-end justify-between"><div><h2 id="next-actions" class="text-base font-black">@lang('student-schedule::app.next_actions')</h2><p class="text-xs font-semibold text-slate-400">@lang('student-schedule::app.next_actions_hint')</p></div><span class="text-xs font-black text-slate-400">{{ $summary['events'] }} @lang('student-schedule::app.events')</span></div><div class="grid gap-3 md:grid-cols-3">
            @foreach(['session' => CalendarEventKind::ClassSession, 'assignment' => CalendarEventKind::AssignmentDue, 'exam' => CalendarEventKind::ExamWindow] as $slot => $kind)
                @php $event = $priorities[$slot]; @endphp
                <button type="button" @if($event) data-student-event='@json($payload($event))' @endif class="student-calendar-event min-h-28 rounded-2xl border border-slate-200 bg-white p-4 text-left shadow-sm {{ $event ? 'hover:border-green-300 hover:shadow-md' : 'cursor-default' }}"><div class="flex items-start gap-3"><span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $tones[$kind->value] }}"><x-dynamic-component :component="$icons[$kind->value]" class="h-5 w-5" /></span><span class="min-w-0"><small class="font-black uppercase tracking-wide text-slate-400">@lang('student-schedule::app.next_'.$slot)</small>@if($event)<strong class="mt-1 block truncate text-sm">{{ $event->title }}</strong><span class="mt-1 block text-xs font-bold text-slate-500">{{ $event->startsAt->format('d/m/Y · H:i') }} · {{ $event->metadata['classroom_name'] ?? __('student-schedule::app.personal') }}</span>@else<span class="mt-2 block text-xs font-semibold text-slate-400">@lang('student-schedule::app.no_upcoming')</span>@endif</span></div></button>
            @endforeach
        </div></section>

        @if($viewMode === 'month')
            <section class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm"><h2 class="px-2 pb-3 text-base font-black capitalize">{{ $anchor->translatedFormat('F Y') }}</h2><div class="grid grid-cols-7 border-b border-slate-100 pb-2 text-center text-[10px] font-black uppercase text-slate-400">@foreach(['T2','T3','T4','T5','T6','T7','CN'] as $label)<span>{{ $label }}</span>@endforeach</div><div class="grid grid-cols-7">@foreach($days as $day)<div class="min-h-28 border-b border-r border-slate-100 p-1.5 {{ $day->month === $anchor->month ? 'bg-white' : 'bg-slate-50 text-slate-300' }}"><span class="grid h-7 w-7 place-items-center rounded-lg text-xs font-black {{ $day->isToday() ? 'bg-green-600 text-white' : '' }}">{{ $day->day }}</span><div class="mt-1 space-y-1">@foreach(($eventsByDay[$day->toDateString()] ?? collect())->take(3) as $event)<button data-student-event='@json($payload($event))' class="block w-full truncate rounded-md border px-1.5 py-1 text-left text-[10px] font-black {{ $event->status === CalendarEventStatus::Cancelled ? 'border-slate-200 bg-slate-100 text-slate-400 line-through' : $tones[$event->kind->value] }}">{{ $event->startsAt->format('H:i') }} {{ $event->title }}</button>@endforeach</div></div>@endforeach</div></section>
        @elseif($viewMode === 'week')
            <section class="student-calendar-scrollbar overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="min-w-225"><div class="grid grid-cols-7 border-b border-slate-200">@foreach($days as $day)<div class="p-3 text-center"><small class="font-black uppercase text-slate-400">{{ $day->translatedFormat('D') }}</small><strong class="mx-auto mt-1 grid h-8 w-8 place-items-center rounded-xl {{ $day->isToday() ? 'bg-green-600 text-white' : '' }}">{{ $day->day }}</strong></div>@endforeach</div><div class="grid grid-cols-7">@foreach($days as $day)<div class="min-h-120 border-r border-slate-100 p-2 {{ $day->isToday() ? 'bg-green-50/30' : '' }}"><div class="space-y-2">@foreach(($eventsByDay[$day->toDateString()] ?? collect()) as $event)<button data-student-event='@json($payload($event))' class="student-calendar-event w-full rounded-xl border p-3 text-left shadow-sm {{ $event->status === CalendarEventStatus::Cancelled ? 'border-slate-200 bg-slate-100 text-slate-400' : $tones[$event->kind->value] }}"><span class="text-[10px] font-black">{{ $event->startsAt->format('H:i') }}</span><strong class="mt-1 block text-xs leading-tight {{ $event->status === CalendarEventStatus::Cancelled ? 'line-through' : '' }}">{{ $event->title }}</strong><small class="mt-1 block truncate font-semibold opacity-70">{{ $event->metadata['classroom_name'] ?? __('student-schedule::app.'.$event->kind->value) }}</small></button>@endforeach</div></div>@endforeach</div></div></section>
        @else
            <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><h2 class="mb-3 text-base font-black">{{ $viewMode === 'today' ? $anchor->translatedFormat('l, d/m/Y') : __('student-schedule::app.upcoming') }}</h2><div class="space-y-2">@forelse(($viewMode === 'schedule' ? $agenda : $events) as $event)<button data-student-event='@json($payload($event))' class="flex w-full items-center gap-3 rounded-xl border border-slate-200 p-3 text-left hover:border-green-300"><span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl {{ $tones[$event->kind->value] }}"><x-dynamic-component :component="$icons[$event->kind->value]" class="h-5 w-5" /></span><span class="min-w-0 flex-1"><strong class="block truncate text-sm {{ $event->status === CalendarEventStatus::Cancelled ? 'text-slate-400 line-through' : '' }}">{{ $event->title }}</strong><small class="font-bold text-slate-400">{{ $event->startsAt->format('d/m/Y · H:i') }} · {{ $event->metadata['classroom_name'] ?? __('student-schedule::app.'.$event->kind->value) }}</small>@if($event->status === CalendarEventStatus::Cancelled && !empty($event->metadata['cancel_reason']))<small class="mt-1 block font-semibold text-red-500">{{ $event->metadata['cancel_reason'] }}</small>@endif</span>@if($event->status === CalendarEventStatus::Cancelled)<span class="rounded-lg bg-red-50 px-2 py-1 text-[10px] font-black text-red-600">@lang('student-schedule::app.cancelled')</span>@else<x-heroicon-o-chevron-right class="h-4 w-4 text-slate-300" />@endif</button>@empty<p class="py-12 text-center text-sm font-semibold text-slate-400">@lang('student-schedule::app.empty_upcoming')</p>@endforelse</div></section>
        @endif
    </main>
</div>

<div id="student-calendar-detail" data-student-calendar-layer aria-hidden="true" class="fixed inset-0 z-60 hidden bg-slate-950/35"><aside class="ml-auto flex h-full w-full max-w-md flex-col bg-white shadow-2xl"><div class="flex items-center justify-between border-b border-slate-200 p-5"><h2 class="font-black">@lang('student-schedule::app.event_details')</h2><button data-student-calendar-close class="grid h-9 w-9 place-items-center rounded-xl hover:bg-slate-100"><x-heroicon-o-x-mark class="h-5 w-5" /></button></div><div class="flex-1 space-y-5 overflow-y-auto p-5"><span data-detail-kind class="inline-flex rounded-lg bg-green-50 px-2.5 py-1 text-xs font-black text-green-700"></span><h3 data-detail-title class="text-xl font-black"></h3><dl class="space-y-4 text-sm"><div><dt class="text-xs font-bold text-slate-400">@lang('student-schedule::app.time')</dt><dd data-detail-time class="mt-1 font-black"></dd></div><div><dt class="text-xs font-bold text-slate-400">@lang('student-schedule::app.classroom')</dt><dd data-detail-classroom class="mt-1 font-black"></dd></div><div><dt class="text-xs font-bold text-slate-400">@lang('student-schedule::app.context')</dt><dd data-detail-context class="mt-1 font-semibold text-slate-600"></dd></div></dl></div><div class="border-t border-slate-200 p-5"><a data-detail-action href="#" class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-green-600 text-sm font-black text-white no-underline hover:bg-green-700"></a></div></aside></div>
@endsection
