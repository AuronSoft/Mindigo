@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-course::publishing.monitor_title').' — '.$course->name)
@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
<main class="min-h-screen bg-slate-50">
    <header class="sticky top-0 z-10 border-b border-slate-200 bg-white/95 px-6 py-4 backdrop-blur">
        <div class="flex items-center gap-3">
            <a href="{{ route('teacher.courses.show', $course) }}" class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 text-slate-600 no-underline hover:bg-green-50 hover:text-green-700" aria-label="{{ __('teacher-course::publishing.back') }}"><x-heroicon-o-arrow-left class="h-4 w-4" /></a>
            <div><p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('teacher-course::app.teaching_content')</p><h1 class="text-lg font-black text-slate-950">@lang('teacher-course::publishing.monitor_title')</h1><p class="text-xs font-semibold text-slate-400">{{ $course->name }} · @lang('teacher-course::publishing.monitor_description')</p></div>
        </div>
    </header>

    <div class="space-y-5 p-6">
        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['label' => __('teacher-course::publishing.assigned'), 'value' => (int) $summary->assigned_count],
                ['label' => __('teacher-course::publishing.started'), 'value' => (int) $summary->started_count],
                ['label' => __('teacher-course::publishing.completed'), 'value' => (int) $summary->completed_count],
                ['label' => __('teacher-course::publishing.average_progress'), 'value' => round((float) $summary->average_progress).'%'],
            ] as $stat)
                <div class="rounded-xl border border-slate-200 bg-white p-4"><p class="text-[11px] font-black uppercase tracking-wider text-slate-400">{{ $stat['label'] }}</p><p class="mt-1 text-2xl font-black text-slate-950">{{ $stat['value'] }}</p></div>
            @endforeach
        </section>

        @php($activeFilterCount = collect($filters)->only(['classroom_id', 'status'])->filter()->count())
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3">
                <form method="GET" action="{{ route('teacher.courses.monitor', $course) }}" class="min-w-0 flex-1" role="search">
                    @if(filled($filters['classroom_id'] ?? null))<input type="hidden" name="classroom_id" value="{{ $filters['classroom_id'] }}">@endif
                    @if(filled($filters['status'] ?? null))<input type="hidden" name="status" value="{{ $filters['status'] }}">@endif
                    <label class="relative block">
                        <span class="sr-only">@lang('teacher-course::publishing.search_placeholder')</span>
                        <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="@lang('teacher-course::publishing.search_placeholder')" class="h-10 w-full rounded-lg border border-slate-300 bg-white pl-9 pr-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100">
                    </label>
                </form>
                <button type="button" data-mindigo-drawer-open="course-monitor-filter" class="inline-flex h-10 shrink-0 items-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 shadow-sm transition hover:border-green-200 hover:bg-green-50 hover:text-green-700">
                    <x-heroicon-o-adjustments-horizontal class="h-4 w-4" />
                    @lang('teacher-course::publishing.filter')
                    @if($activeFilterCount)<span class="grid h-5 min-w-5 place-items-center rounded-full bg-green-600 px-1.5 text-[11px] text-white">{{ $activeFilterCount }}</span>@endif
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px] text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-[11px] font-black uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3">@lang('teacher-course::publishing.student')</th><th class="px-5 py-3">@lang('teacher-course::publishing.classroom')</th><th class="px-5 py-3">@lang('teacher-course::publishing.lessons')</th><th class="px-5 py-3">@lang('teacher-course::publishing.progress')</th><th class="px-5 py-3">@lang('teacher-course::publishing.status')</th><th class="px-5 py-3">@lang('teacher-course::publishing.last_activity')</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($enrollments as $enrollment)
                            <tr><td class="px-5 py-4"><p class="font-black text-slate-900">{{ $enrollment->student->name }}</p><p class="text-xs text-slate-400">{{ $enrollment->student->email }}</p></td><td class="px-5 py-4 font-semibold text-slate-600">{{ $enrollment->classroom?->name }}</td><td class="px-5 py-4 font-bold text-slate-700">{{ $enrollment->completed_lessons_count }}/{{ $totalLessons }}</td><td class="px-5 py-4"><div class="flex items-center gap-2"><div class="h-1.5 w-24 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-green-500" style="width: {{ $enrollment->completion_percentage }}%"></div></div><span class="text-xs font-black text-slate-600">{{ $enrollment->completion_percentage }}%</span></div></td><td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-black text-slate-600">@lang('teacher-course::learning.statuses.'.$enrollment->status)</span></td><td class="px-5 py-4 text-xs font-semibold text-slate-500">{{ $enrollment->last_activity_at?->diffForHumans() ?? __('teacher-course::publishing.never') }}</td></tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-16 text-center text-sm font-semibold text-slate-400">@lang('teacher-course::publishing.no_students')</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($enrollments->hasPages())<div class="border-t border-slate-200 px-5 py-4">{{ $enrollments->links() }}</div>@endif
        </section>
    </div>

    <div data-mindigo-drawer="course-monitor-filter" class="fixed inset-0 z-40 hidden bg-slate-950/45 opacity-0 backdrop-blur-sm transition-opacity duration-200"></div>
    <aside data-mindigo-drawer-panel="course-monitor-filter" aria-label="@lang('teacher-course::publishing.monitor_filter_title')" class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col border-l border-slate-200 bg-white shadow-2xl shadow-slate-950/20 transition-transform duration-200" style="transform: translateX(100%);">
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4">
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-green-700">@lang('teacher-course::app.teaching_content')</p>
                <h2 class="mt-1 text-xl font-black text-slate-950">@lang('teacher-course::publishing.monitor_filter_title')</h2>
                <p class="mt-1 text-sm font-semibold leading-relaxed text-slate-500">@lang('teacher-course::publishing.monitor_filter_description')</p>
            </div>
            <button type="button" aria-label="@lang('teacher-course::app.close')" data-mindigo-drawer-close="course-monitor-filter" class="grid h-10 w-10 shrink-0 place-items-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-800"><x-heroicon-o-x-mark class="h-5 w-5" /></button>
        </div>
        <form action="{{ route('teacher.courses.monitor', $course) }}" method="GET" class="flex min-h-0 flex-1 flex-col">
            @if(filled($filters['search'] ?? null))<input type="hidden" name="search" value="{{ $filters['search'] }}">@endif
            <div class="flex-1 space-y-5 overflow-y-auto px-5 py-5">
                @php($drawerSelectClass = 'block h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none transition focus:border-green-300 focus:ring-4 focus:ring-green-50')
                <label class="block space-y-2">
                    <span class="block text-xs font-black uppercase tracking-wider text-slate-500">@lang('teacher-course::publishing.classroom')</span>
                    <select name="classroom_id" class="{{ $drawerSelectClass }}"><option value="">@lang('teacher-course::publishing.all_classrooms')</option>@foreach($classrooms as $classroom)<option value="{{ $classroom->id }}" @selected(($filters['classroom_id'] ?? null) == $classroom->id)>{{ $classroom->name }}</option>@endforeach</select>
                </label>
                <label class="block space-y-2">
                    <span class="block text-xs font-black uppercase tracking-wider text-slate-500">@lang('teacher-course::publishing.status')</span>
                    <select name="status" class="{{ $drawerSelectClass }}"><option value="">@lang('teacher-course::publishing.all_statuses')</option>@foreach(\Mindigo\TeacherCourse\Models\CourseEnrollment::STATUSES as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? null) === $status)>@lang('teacher-course::learning.statuses.'.$status)</option>@endforeach</select>
                </label>
            </div>
            <div class="grid grid-cols-2 gap-3 border-t border-slate-100 p-5">
                <a href="{{ route('teacher.courses.monitor', $course) }}" class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-600 no-underline transition hover:bg-slate-50">@lang('teacher-course::publishing.clear')</a>
                <button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-green-600 px-4 text-sm font-black text-white shadow-sm shadow-green-200 transition hover:bg-green-500"><x-heroicon-o-funnel class="h-4 w-4" />@lang('teacher-course::app.apply_filter')</button>
            </div>
        </form>
    </aside>
</main>
@endsection
