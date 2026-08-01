@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-exam::app.monitor_exam'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
<main class="min-h-screen bg-slate-50" data-monitor-url="{{ route('teacher.exams.monitor.data', [$exam] + request()->query()) }}">
    <header class="sticky top-0 z-10 border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur lg:px-7">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex min-w-0 items-center gap-3">
                <a href="{{ route('teacher.exams.show', $exam) }}" class="grid h-9 w-9 shrink-0 place-items-center rounded-full border border-slate-200 text-slate-600 no-underline hover:bg-slate-50">
                    <x-heroicon-o-arrow-left class="h-4 w-4" />
                </a>
                <div class="min-w-0">
                    <p class="text-[11px] font-black uppercase tracking-widest text-green-600">@lang('teacher-exam::app.live_monitoring')</p>
                    <h1 class="truncate text-lg font-black text-slate-950">{{ $exam->title }}</h1>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
                <span class="relative flex h-2.5 w-2.5"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"></span><span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-green-500"></span></span>
                <span>@lang('teacher-exam::app.auto_refresh')</span>
                <span data-refreshed-at>{{ now()->format('H:i:s') }}</span>
            </div>
        </div>
    </header>

    <div class="space-y-5 p-5 lg:p-7">
        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            @foreach([
                ['assigned', 'assigned_students', 'users'],
                ['started', 'started_students', 'play'],
                ['in_progress', 'doing_exam', 'signal'],
                ['submitted', 'submitted_students', 'check'],
                ['online', 'online_students', 'wifi'],
            ] as [$key, $label, $icon])
                <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div><p class="text-[11px] font-black uppercase tracking-wide text-slate-400">{{ __('teacher-exam::app.'.$label) }}</p><strong class="mt-2 block text-2xl font-black text-slate-950" data-summary="{{ $key }}">{{ $summary[$key] }}</strong></div>
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-green-50 text-green-700"><x-dynamic-component :component="'heroicon-o-'.$icon" class="h-4 w-4" /></span>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4"><h2 class="text-sm font-black text-slate-950">@lang('teacher-exam::app.classroom_dashboard')</h2></div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wide text-slate-400"><tr>
                        @foreach(['classroom', 'assigned', 'started', 'submitted', 'completion_rate', 'average_score', 'highest_score', 'lowest_score', 'grading_progress'] as $heading)
                            <th class="px-4 py-3">{{ __('teacher-exam::app.'.$heading) }}</th>
                        @endforeach
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($classroomStats as $stat)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-4 py-3 font-black text-slate-900">{{ $stat['name'] }}</td>
                                <td class="px-4 py-3 font-bold text-slate-600">{{ $stat['assigned'] }}</td>
                                <td class="px-4 py-3 font-bold text-slate-600">{{ $stat['started'] }}</td>
                                <td class="px-4 py-3 font-bold text-slate-600">{{ $stat['submitted'] }}</td>
                                <td class="px-4 py-3 font-black text-green-700">{{ $stat['completion_rate'] }}%</td>
                                <td class="px-4 py-3 font-bold text-slate-600">{{ $stat['average_score'] }}%</td>
                                <td class="px-4 py-3 font-bold text-slate-600">{{ $stat['highest_score'] === null ? '—' : $stat['highest_score'].'%' }}</td>
                                <td class="px-4 py-3 font-bold text-slate-600">{{ $stat['lowest_score'] === null ? '—' : $stat['lowest_score'].'%' }}</td>
                                <td class="px-4 py-3 font-black text-slate-700">{{ $stat['grading_progress'] }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-5 py-10 text-center font-bold text-slate-400">@lang('teacher-exam::app.no_assigned_classrooms')</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                <div><h2 class="text-sm font-black text-slate-950">@lang('teacher-exam::app.student_progress')</h2><p class="mt-0.5 text-xs font-bold text-slate-400">@lang('teacher-exam::app.monitoring_hint')</p></div>
                <form method="GET" class="flex flex-wrap items-center gap-2">
                    <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('teacher-exam::app.search_student') }}" class="h-9 w-52 rounded-xl border-slate-200 text-xs font-bold focus:border-green-500 focus:ring-green-500">
                    <select name="classroom" class="h-9 rounded-xl border-slate-200 py-0 text-xs font-bold focus:border-green-500 focus:ring-green-500">
                        <option value="">@lang('teacher-exam::app.all_classrooms')</option>
                        @foreach($classrooms as $classroom)<option value="{{ $classroom->id }}" @selected(($filters['classroom'] ?? null) == $classroom->id)>{{ $classroom->name }}</option>@endforeach
                    </select>
                    <select name="status" class="h-9 rounded-xl border-slate-200 py-0 text-xs font-bold focus:border-green-500 focus:ring-green-500">
                        <option value="">@lang('teacher-exam::app.all_status')</option>
                        @foreach(['not_started', 'in_progress', 'submitted', 'expired'] as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? null) === $status)>{{ __('teacher-exam::app.'.$status) }}</option>@endforeach
                    </select>
                    <select name="sort" class="h-9 rounded-xl border-slate-200 py-0 text-xs font-bold focus:border-green-500 focus:ring-green-500">
                        @foreach(['name', 'status', 'progress', 'remaining', 'last_activity'] as $sort)<option value="{{ $sort }}" @selected(($filters['sort'] ?? 'name') === $sort)>{{ __('teacher-exam::app.sort_'.$sort) }}</option>@endforeach
                    </select>
                    <select name="direction" class="h-9 rounded-xl border-slate-200 py-0 text-xs font-bold focus:border-green-500 focus:ring-green-500">
                        @foreach(['asc', 'desc'] as $direction)<option value="{{ $direction }}" @selected(($filters['direction'] ?? 'asc') === $direction)>{{ __('teacher-exam::app.'.$direction) }}</option>@endforeach
                    </select>
                    <button class="inline-flex h-9 items-center rounded-xl bg-green-600 px-4 text-xs font-black text-white hover:bg-green-700">@lang('teacher-exam::app.filter')</button>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left">
                    <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wide text-slate-400"><tr>
                        @foreach(['student', 'connection', 'exam_status', 'progress', 'remaining_time', 'last_activity', 'score'] as $heading)<th class="px-5 py-3">{{ __('teacher-exam::app.'.$heading) }}</th>@endforeach
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100" data-student-rows>
                        @forelse($students as $row)
                            <tr data-student-id="{{ $row['id'] }}" class="hover:bg-slate-50/70">
                                <td class="px-5 py-3"><p class="text-sm font-black text-slate-900">{{ $row['name'] }}</p><p class="text-xs font-bold text-slate-400">{{ $row['email'] }}</p></td>
                                <td class="px-5 py-3"><span data-field="connection" class="inline-flex items-center gap-1.5 text-xs font-black {{ $row['online'] ? 'text-green-700' : 'text-slate-400' }}"><span class="h-2 w-2 rounded-full {{ $row['online'] ? 'bg-green-500' : 'bg-slate-300' }}"></span>{{ __('teacher-exam::app.'.($row['online'] ? 'online' : 'offline')) }}</span></td>
                                <td class="px-5 py-3"><span data-field="status" class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-black text-slate-700">{{ __('teacher-exam::app.'.$row['status']) }}</span></td>
                                <td class="px-5 py-3"><div class="flex min-w-36 items-center gap-2"><progress data-field="progress-bar" class="h-1.5 w-24 accent-green-600" max="100" value="{{ $row['progress'] }}"></progress><span data-field="progress" class="text-xs font-black text-slate-600">{{ $row['answered'] }}/{{ $row['total_questions'] }} ({{ $row['progress'] }}%)</span></div></td>
                                <td data-field="remaining" class="px-5 py-3 text-xs font-black text-slate-600">{{ $row['remaining'] === null ? '—' : gmdate('H:i:s', $row['remaining']) }}</td>
                                <td data-field="last_activity" class="px-5 py-3 text-xs font-bold text-slate-400">{{ $row['last_activity_label'] ?: '—' }}</td>
                                <td data-field="score" class="px-5 py-3 text-xs font-black text-slate-700">{{ $row['score'] === null ? '—' : round((float) $row['score'], 1).'%' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-14 text-center font-bold text-slate-400">@lang('teacher-exam::app.no_students_found')</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($students->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $students->links() }}</div>@endif
        </section>
    </div>
</main>
@endsection

@push('scripts')
@php
    $monitorLabels = [
        'online' => __('teacher-exam::app.online'),
        'offline' => __('teacher-exam::app.offline'),
        'not_started' => __('teacher-exam::app.not_started'),
        'in_progress' => __('teacher-exam::app.in_progress'),
        'submitted' => __('teacher-exam::app.submitted'),
        'expired' => __('teacher-exam::app.expired'),
    ];
@endphp
<script>
document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('[data-monitor-url]');
    if (!page) return;
    const labels = {{ Illuminate\Support\Js::from($monitorLabels) }};
    const formatTime = seconds => seconds === null ? '—' : new Date(seconds * 1000).toISOString().slice(11, 19);
    const refresh = async () => {
        try {
            const response = await fetch(page.dataset.monitorUrl, { headers: { Accept: 'application/json' } });
            if (!response.ok) return;
            const data = await response.json();
            Object.entries(data.summary).forEach(([key, value]) => { const element = page.querySelector(`[data-summary="${key}"]`); if (element) element.textContent = value; });
            data.students.forEach(student => {
                const row = page.querySelector(`[data-student-id="${student.id}"]`);
                if (!row) return;
                const connection = row.querySelector('[data-field="connection"]');
                connection.className = `inline-flex items-center gap-1.5 text-xs font-black ${student.online ? 'text-green-700' : 'text-slate-400'}`;
                connection.innerHTML = `<span class="h-2 w-2 rounded-full ${student.online ? 'bg-green-500' : 'bg-slate-300'}"></span>${student.online ? labels.online : labels.offline}`;
                row.querySelector('[data-field="status"]').textContent = labels[student.status];
                row.querySelector('[data-field="progress-bar"]').value = student.progress;
                row.querySelector('[data-field="progress"]').textContent = `${student.answered}/${student.total_questions} (${student.progress}%)`;
                row.querySelector('[data-field="remaining"]').textContent = formatTime(student.remaining);
                row.querySelector('[data-field="last_activity"]').textContent = student.last_activity_label || '—';
                row.querySelector('[data-field="score"]').textContent = student.score === null ? '—' : `${Number(student.score).toFixed(1)}%`;
            });
            page.querySelector('[data-refreshed-at]').textContent = new Date(data.refreshed_at).toLocaleTimeString();
        } catch (_) {}
    };
    window.setInterval(refresh, 10000);
});
</script>
@endpush
