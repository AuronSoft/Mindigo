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
    use Mindigo\AcademicCalendar\Enums\CalendarEventKind;
    use Mindigo\AcademicCalendar\Enums\CalendarEventStatus;
    $tones = [
        'class_session' => 'calendar-tone-blue',
        'assignment_due' => 'calendar-tone-orange',
        'exam_window' => 'calendar-tone-rose',
        'live_session' => 'calendar-tone-violet',
    ];
    $eventPayload = function ($event) {
        $isSession = $event->kind === CalendarEventKind::ClassSession;
        $lifecycleStatus = $event->metadata['lifecycle_status'] ?? $event->status->value;
        $locked = in_array($lifecycleStatus, ['cancelled', 'rescheduled', 'completed'], true);

        return [
            'title' => $event->title,
            'kindLabel' => __('teacher-calendar::app.'.$event->kind->value),
            'time' => $event->startsAt->format('d/m/Y H:i').($event->endsAt ? ' – '.$event->endsAt->format('H:i') : ''),
            'classroom' => $event->metadata['classroom_name'] ?? null,
            'url' => $event->url,
            'cancelUrl' => $isSession && ! $locked ? route('teacher.calendar.sessions.cancel', $event->sourceId) : null,
            'updateUrl' => $isSession && ! $locked ? ($event->metadata['update_url'] ?? null) : null,
            'rescheduleUrl' => $isSession && ! $locked ? ($event->metadata['reschedule_url'] ?? null) : null,
            'completeUrl' => $isSession && $lifecycleStatus === 'scheduled' ? ($event->metadata['complete_url'] ?? null) : null,
            'date' => $event->startsAt->toDateString(),
            'start' => $event->startsAt->format('H:i'),
            'end' => $event->endsAt?->format('H:i'),
            'deliveryMode' => $event->metadata['delivery_mode'] ?? 'offline',
            'location' => $event->metadata['location'] ?? '',
            'meetingUrl' => $event->metadata['meeting_url'] ?? '',
            'description' => $event->metadata['description'] ?? '',
            'type' => $event->metadata['session_type'] ?? 'regular',
            'lessonId' => $event->lessonId,
            'makeupReason' => $event->metadata['makeup_reason'] ?? '',
            'lifecycleStatus' => $lifecycleStatus,
            'statusLabel' => __('teacher-calendar::app.status_'.$lifecycleStatus),
            'reason' => $event->metadata['cancel_reason'] ?? $event->metadata['reschedule_reason'] ?? '',
            'attendanceStatus' => $event->metadata['attendance_status'] ?? null,
            'attendanceStatusLabel' => isset($event->metadata['attendance_status']) ? __('teacher-calendar::app.attendance_'.$event->metadata['attendance_status']) : null,
            'attendanceCode' => $event->metadata['attendance_code'] ?? null,
            'attendanceExpiresAt' => $event->metadata['attendance_expires_at'] ?? null,
            'attendanceOpenUrl' => $event->metadata['attendance_open_url'] ?? null,
            'attendanceCloseUrl' => $event->metadata['attendance_close_url'] ?? null,
            'attendanceUrl' => $event->metadata['attendance_url'] ?? null,
        ];
    };
    $query = request()->except('date');
    $previousDate = match ($viewMode) { 'day' => $anchor->subDay(), 'month' => $anchor->subMonth(), 'schedule' => $anchor->subDays(30), default => $anchor->subWeek() };
    $nextDate = match ($viewMode) { 'day' => $anchor->addDay(), 'month' => $anchor->addMonth(), 'schedule' => $anchor->addDays(30), default => $anchor->addWeek() };
    $regular = $events->filter(fn ($event) => $event->kind === CalendarEventKind::ClassSession && ($event->metadata['session_type'] ?? 'regular') === 'regular' && $event->status !== CalendarEventStatus::Cancelled)->count();
    $makeup = $events->filter(fn ($event) => $event->kind === CalendarEventKind::ClassSession && ($event->metadata['session_type'] ?? null) === 'makeup')->count();
    $cancelled = $events->where('status', CalendarEventStatus::Cancelled)->count();
    $workload = min(100, (int) round(($summary['hours'] / 40) * 100));
    $activeFilterCount = collect($filters)->only(['classroom_id', 'kinds'])->filter()->count();
@endphp

<div class="teacher-calendar-shell" data-calendar-workspace>
    <header class="teacher-calendar-header">
        <div class="teacher-calendar-brand">
            <span class="teacher-calendar-brand-icon"><x-heroicon-o-calendar-days class="h-5 w-5" /></span>
            <span><strong>@lang('teacher-calendar::app.title')</strong><small>{{ $summary['count'] }} @lang('teacher-calendar::app.events')</small></span>
        </div>

        <div class="teacher-calendar-navigation">
            <a href="{{ route('teacher.calendar.index', [...$query, 'date' => now()->toDateString()]) }}" class="calendar-control calendar-today">@lang('teacher-calendar::app.today')</a>
            <a aria-label="Previous period" href="{{ route('teacher.calendar.index', [...$query, 'date' => $previousDate->toDateString()]) }}" class="calendar-control calendar-arrow"><x-heroicon-o-arrow-left class="h-4 w-4" /></a>
            <a aria-label="Next period" href="{{ route('teacher.calendar.index', [...$query, 'date' => $nextDate->toDateString()]) }}" class="calendar-control calendar-arrow"><x-heroicon-o-arrow-right class="h-4 w-4" /></a>
            <span class="teacher-calendar-period"><strong class="capitalize">{{ $anchor->translatedFormat('F') }}</strong><small>{{ $anchor->year }}</small></span>
        </div>

        <div class="teacher-calendar-view-switch" aria-label="Calendar view">
            @foreach(['day', 'week', 'month', 'schedule'] as $mode)
                <a href="{{ route('teacher.calendar.index', [...request()->except(['view']), 'view' => $mode]) }}" class="{{ $viewMode === $mode ? 'is-active' : '' }}">@lang('teacher-calendar::app.'.$mode)</a>
            @endforeach
        </div>

        <div class="teacher-calendar-actions">
            <button type="button" data-mindigo-drawer-open="teacher-calendar-filter" class="teacher-calendar-filter-trigger"><x-heroicon-o-adjustments-horizontal class="h-4 w-4" /><span>@lang('teacher-calendar::app.filters')</span>@if($activeFilterCount)<em>{{ $activeFilterCount }}</em>@endif</button>
            <button type="button" data-calendar-create data-date="{{ now()->toDateString() }}" class="teacher-calendar-add"><x-heroicon-o-plus class="h-4 w-4" /><span>@lang('teacher-calendar::app.new_session')</span></button>
        </div>
    </header>

    <div class="teacher-calendar-body">
        <aside class="teacher-calendar-sidebar">
            <section class="calendar-side-card calendar-mini-month">
                <div class="calendar-side-heading"><strong class="capitalize">{{ $anchor->translatedFormat('M Y') }}</strong><span><a href="{{ route('teacher.calendar.index', [...$query, 'date' => $anchor->subMonth()->toDateString()]) }}">‹</a><a href="{{ route('teacher.calendar.index', [...$query, 'date' => $anchor->addMonth()->toDateString()]) }}">›</a></span></div>
                <div class="calendar-mini-grid">
                    @foreach(['T2','T3','T4','T5','T6','T7','CN'] as $label)<small>{{ $label }}</small>@endforeach
                    @php $monthStart = $anchor->startOfMonth()->startOfWeek(); @endphp
                    @foreach(range(0, 41) as $offset)
                        @php $date = $monthStart->addDays($offset); @endphp
                        <a href="{{ route('teacher.calendar.index', [...$query, 'date' => $date->toDateString()]) }}" class="{{ $date->isSameDay($anchor) ? 'is-selected' : '' }} {{ $date->month !== $anchor->month ? 'is-outside' : '' }}">{{ $date->day }}</a>
                    @endforeach
                </div>
            </section>

            <section class="calendar-side-card calendar-summary-card">
                <div class="calendar-side-heading"><span><strong>@lang('teacher-calendar::app.summary')</strong><small>{{ $start->format('d/m') }} – {{ $end->subDay()->format('d/m/Y') }}</small></span></div>
                <div class="calendar-donut" style="--calendar-progress: {{ min(100, $summary['class_sessions'] * 10) }}">
                    <svg viewBox="0 0 44 44" aria-hidden="true"><circle cx="22" cy="22" r="17" pathLength="100" /><circle class="calendar-donut-value" cx="22" cy="22" r="17" pathLength="100" /></svg>
                    <span><strong>{{ $summary['class_sessions'] }}</strong><small>@lang('teacher-calendar::app.sessions')</small></span>
                </div>
                <dl class="calendar-legend"><div><dt><i class="bg-blue-500"></i>@lang('teacher-calendar::app.regular')</dt><dd>{{ $regular }}</dd></div><div><dt><i class="bg-amber-400"></i>@lang('teacher-calendar::app.makeup')</dt><dd>{{ $makeup }}</dd></div><div><dt><i class="bg-rose-400"></i>@lang('teacher-calendar::app.cancelled')</dt><dd>{{ $cancelled }}</dd></div></dl>
            </section>

            <section class="calendar-side-card calendar-workload-card">
                <div class="calendar-side-heading"><span><strong>@lang('teacher-calendar::app.workload')</strong><small>{{ $summary['class_sessions'] }} @lang('teacher-calendar::app.sessions') · {{ $summary['hours'] }}h</small></span><em>{{ $workload }}%</em></div>
                <div class="calendar-progress"><span style="width: {{ $workload }}%"></span></div>
                <p><x-heroicon-o-bell-alert class="h-4 w-4" />@lang('teacher-calendar::app.workload_hint')</p>
            </section>

        </aside>

        <main class="teacher-calendar-main">
            @if(in_array($viewMode, ['day', 'week'], true))
            <div class="teacher-calendar-grid {{ $viewMode === 'day' ? 'is-day-view' : '' }}">
                <div class="teacher-calendar-days">
                    <div class="calendar-timezone">GMT+07</div>
                    @foreach($days as $day)<div class="calendar-day-heading"><small>{{ $day->translatedFormat('D') }}</small><strong class="{{ $day->isToday() ? 'is-today' : '' }}">{{ $day->day }}</strong></div>@endforeach
                </div>

                <div class="teacher-calendar-timeline">
                    <div class="calendar-time-axis">
                        @foreach(range(7, 19) as $hour)<span style="top: {{ (($hour - 7) / 12) * 100 }}%">{{ $hour > 12 ? $hour - 12 : $hour }} {{ $hour >= 12 ? 'PM' : 'AM' }}</span>@endforeach
                    </div>
                    @foreach($days as $day)
                        <div class="calendar-day-column {{ $day->isToday() ? 'is-today-column' : '' }}">
                            @foreach(range(7, 18) as $hour)<button type="button" data-calendar-create data-date="{{ $day->toDateString() }}" data-start="{{ sprintf('%02d:00', $hour) }}" data-end="{{ sprintf('%02d:00', $hour + 1) }}" aria-label="Create at {{ $day->format('d/m/Y H:00') }}" class="calendar-hour-slot" style="top: {{ (($hour - 7) / 12) * 100 }}%"></button>@endforeach
                            @foreach(($eventsByDay[$day->toDateString()] ?? collect()) as $event)
                                @php
                                    $startOffset = max(0, (($event->startsAt->hour - 7) * 60 + $event->startsAt->minute) / 60);
                                    $duration = $event->endsAt ? max(.75, $event->startsAt->diffInMinutes($event->endsAt) / 60) : .75;
                                    $eventTop = min(100, ($startOffset / 12) * 100);
                                    $eventHeight = min(100 - $eventTop, ($duration / 12) * 100);
                                    $eventTone = $event->status === CalendarEventStatus::Cancelled ? 'calendar-tone-cancelled' : (($event->metadata['session_type'] ?? null) === 'makeup' ? 'calendar-tone-orange' : ($tones[$event->kind->value] ?? 'calendar-tone-blue'));
                                    $payload = $eventPayload($event);
                                @endphp
                                <button type="button" data-calendar-event='@json($payload)' class="teacher-calendar-event {{ $eventTone }}" style="top: {{ $eventTop }}%; height: {{ $eventHeight }}%">
                                    <span class="calendar-event-meta"><x-dynamic-component :component="$event->kind === CalendarEventKind::LiveSession ? 'heroicon-o-video-camera' : 'heroicon-o-building-library'" class="h-3.5 w-3.5" /><i>{{ strtoupper(substr($event->metadata['classroom_name'] ?? 'LMS', 0, 2)) }}</i></span>
                                    <strong>{{ $event->title }}</strong><small>{{ $event->startsAt->format('H:i') }} – {{ $event->endsAt?->format('H:i') }}</small>
                                </button>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            @elseif($viewMode === 'month')
                <section class="teacher-calendar-month">
                    <header>@foreach(['T2','T3','T4','T5','T6','T7','CN'] as $label)<span>{{ $label }}</span>@endforeach</header>
                    <div class="teacher-calendar-month-grid">
                        @foreach($days as $day)
                            <article class="{{ $day->month !== $anchor->month ? 'is-outside' : '' }}">
                                <a href="{{ route('teacher.calendar.index', [...$query, 'view' => 'day', 'date' => $day->toDateString()]) }}" class="{{ $day->isToday() ? 'is-today' : '' }}">{{ $day->day }}</a>
                                @foreach(($eventsByDay[$day->toDateString()] ?? collect())->take(3) as $event)
                                    @php
                                        $monthPayload = $eventPayload($event);
                                    @endphp
                                    <button type="button" data-calendar-event='@json($monthPayload)'>{{ $event->startsAt->format('H:i') }} · {{ $event->title }}</button>
                                @endforeach
                            </article>
                        @endforeach
                    </div>
                </section>
            @else
                <section class="teacher-calendar-agenda">
                    @forelse($events->groupBy(fn ($event) => $event->startsAt->toDateString()) as $date => $dateEvents)
                        <div class="teacher-calendar-agenda-day"><time>{{ $dateEvents->first()->startsAt->translatedFormat('l, d/m/Y') }}</time>
                            <div>
                                @foreach($dateEvents as $event)
                                    @php
                                        $agendaPayload = $eventPayload($event);
                                    @endphp
                                    <button type="button" data-calendar-event='@json($agendaPayload)'><strong>{{ $event->startsAt->format('H:i') }}</strong><span>{{ $event->title }}<small>{{ $event->metadata['classroom_name'] ?? __('teacher-calendar::app.'.$event->kind->value) }}</small></span></button>
                                @endforeach
                            </div>
                        </div>
                    @empty<p class="calendar-empty">@lang('teacher-calendar::app.empty')</p>@endforelse
                </section>
            @endif

            <div class="teacher-calendar-mobile-list">
                @forelse($events as $event)
                    @php
                        $mobilePayload = $eventPayload($event);
                    @endphp
                    <button type="button" data-calendar-event='@json($mobilePayload)'><span>{{ $event->startsAt->format('d/m') }}<strong>{{ $event->startsAt->format('H:i') }}</strong></span><p><strong>{{ $event->title }}</strong><small>{{ $event->metadata['classroom_name'] ?? __('teacher-calendar::app.'.$event->kind->value) }}</small></p><x-heroicon-o-chevron-right class="h-4 w-4" /></button>
                @empty
                    <p class="calendar-empty">@lang('teacher-calendar::app.empty')</p>
                @endforelse
            </div>
        </main>
    </div>
</div>

@include('teacher-calendar::partials.drawers')
@include('teacher-calendar::partials.filter-drawer')
@endsection
