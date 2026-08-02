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
    <header class="border-b border-slate-200 bg-white px-6 py-4">
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

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <form method="GET" class="grid gap-3 border-b border-slate-200 p-4 md:grid-cols-5">
                <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('teacher-course::publishing.search_placeholder') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-green-500 md:col-span-2">
                <select name="classroom_id" class="rounded-lg border border-slate-200 px-3 py-2 text-sm"><option value="">@lang('teacher-course::publishing.all_classrooms')</option>@foreach($classrooms as $classroom)<option value="{{ $classroom->id }}" @selected(($filters['classroom_id'] ?? null) == $classroom->id)>{{ $classroom->name }}</option>@endforeach</select>
                <select name="status" class="rounded-lg border border-slate-200 px-3 py-2 text-sm"><option value="">@lang('teacher-course::publishing.all_statuses')</option>@foreach(\Mindigo\TeacherCourse\Models\CourseEnrollment::STATUSES as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? null) === $status)>@lang('teacher-course::learning.statuses.'.$status)</option>@endforeach</select>
                <div class="flex gap-2"><button class="flex-1 rounded-lg bg-green-600 px-3 text-xs font-black text-white">@lang('teacher-course::publishing.filter')</button><a href="{{ route('teacher.courses.monitor', $course) }}" class="grid place-items-center rounded-lg border border-slate-200 px-3 text-xs font-black text-slate-600 no-underline">@lang('teacher-course::publishing.clear')</a></div>
            </form>

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
</main>
@endsection
