@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-course::analytics.title'))
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="min-h-screen bg-slate-50">
    <header class="sticky top-0 z-10 border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur sm:px-6">
        <div class="flex items-center gap-4">
            <a href="{{ route($isAdminAnalytics ? 'dashboard' : 'teacher.dashboard') }}" aria-label="@lang('teacher-course::analytics.back_to_dashboard')" title="@lang('teacher-course::analytics.back_to_dashboard')" class="grid h-9 w-9 shrink-0 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline transition hover:border-green-200 hover:bg-green-50 hover:text-green-700">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
            </a>
            <div class="min-w-0">
                <p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('teacher-course::app.teaching_content')</p>
                <h1 class="text-lg font-black text-slate-950">@lang('teacher-course::analytics.title')</h1>
                <p class="text-xs font-semibold text-slate-400">{{ __($isAdminAnalytics ? 'teacher-course::analytics.admin_subtitle' : 'teacher-course::analytics.teacher_subtitle') }}</p>
            </div>
        </div>
    </header>
    <main class="space-y-5 p-4 sm:p-6">
        <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 2xl:grid-cols-6">
            @foreach($stats as $key => $value)
                <article class="rounded-xl border border-slate-200 bg-white p-4"><p class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ __('teacher-course::analytics.'.$key) }}</p><p class="mt-2 text-2xl font-black text-slate-950">{{ in_array($key, ['completion_rate','lesson_completion_rate','chapter_completion_rate'], true) ? __('teacher-course::analytics.percent', ['value' => $value]) : ($key === 'average_learning_time' ? __('teacher-course::analytics.minutes', ['value' => $value]) : number_format((float) $value, str_contains($key, 'rating') ? 1 : 0)) }}</p></article>
            @endforeach
        </section>

        @php($trendPanels = $isAdminAnalytics ? [__('teacher-course::analytics.enrollment_growth') => $enrollmentGrowth, __('teacher-course::analytics.platform_growth') => $platformGrowth] : [__('teacher-course::analytics.review_trend') => $reviewTrend, __('teacher-course::analytics.student_activity') => $activityTimeline])
        <section class="grid gap-5 xl:grid-cols-2">
            @foreach($trendPanels as $heading => $points)
                <div class="rounded-xl border border-slate-200 bg-white p-5"><h2 class="text-sm font-black text-slate-950">{{ $heading }}</h2><div class="mt-4 space-y-3" aria-label="{{ $heading }}">@forelse($points as $point)@php($total = (int) ($point['total'] ?? 0))<div class="grid grid-cols-[5.5rem_minmax(0,1fr)_2rem] items-center gap-3"><span class="text-[10px] font-bold text-slate-400">{{ $point['period'] ?? $point['date'] ?? '' }}</span><progress value="{{ $total }}" max="{{ max(1, collect($points)->max('total')) }}" class="h-2 w-full accent-green-600"></progress><span class="text-right text-xs font-black text-slate-700">{{ $total }}</span></div>@empty<p class="py-8 text-center text-sm font-semibold text-slate-400">@lang('teacher-course::analytics.no_data')</p>@endforelse</div></div>
            @endforeach
        </section>

        <section class="grid gap-5 xl:grid-cols-2">
            @if(!$isAdminAnalytics)
                @foreach([__('teacher-course::analytics.top_courses') => $topCourses, __('teacher-course::analytics.lowest_courses') => $lowestCourses] as $heading => $courseRows)
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white"><h2 class="border-b border-slate-100 px-5 py-4 text-sm font-black text-slate-950">{{ $heading }}</h2><div class="divide-y divide-slate-100">@forelse($courseRows as $course)<div class="flex items-center justify-between gap-4 px-5 py-3"><span class="truncate text-sm font-bold text-slate-700">{{ $course->name }}</span><span class="text-xs font-black text-green-700">{{ $course->enrollments_count }} · {{ round((float) $course->enrollments_avg_completion_percentage, 1) }}%</span></div>@empty<p class="p-8 text-center text-sm font-semibold text-slate-400">@lang('teacher-course::analytics.no_data')</p>@endforelse</div></div>
                @endforeach
            @else
                @foreach([__('teacher-course::analytics.top_categories') => $topCategories, __('teacher-course::analytics.top_subjects') => $topSubjects] as $heading => $items)
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white"><h2 class="border-b border-slate-100 px-5 py-4 text-sm font-black text-slate-950">{{ $heading }}</h2><div class="divide-y divide-slate-100">@forelse($items as $item)<div class="flex items-center justify-between px-5 py-3"><span class="text-sm font-bold text-slate-700">{{ $item->name }}</span><span class="text-xs font-black text-green-700">{{ $item->courses_count }} · {{ $item->enrollments_count }}</span></div>@empty<p class="p-8 text-center text-sm font-semibold text-slate-400">@lang('teacher-course::analytics.no_data')</p>@endforelse</div></div>
                @endforeach
            @endif
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-5"><div><h2 class="text-sm font-black text-slate-950">@lang('teacher-course::analytics.reporting')</h2><p class="mt-1 text-xs font-semibold text-slate-400">@lang('teacher-course::analytics.reporting_description')</p></div><form method="GET" action="{{ route('course-platform.reports.export') }}" class="mt-4 grid items-end gap-3 sm:grid-cols-2 lg:grid-cols-4"><label><span class="mb-1.5 block text-xs font-black text-slate-500">@lang('teacher-course::analytics.scope')</span><select name="scope" class="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm font-bold">@foreach(['course','teacher','student','classroom'] as $scope)<option value="{{ $scope }}">@lang('teacher-course::analytics.scopes.'.$scope)</option>@endforeach</select></label><label><span class="mb-1.5 block text-xs font-black text-slate-500">@lang('teacher-course::analytics.entity_id')</span><input name="entity_id" type="number" min="1" class="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm"></label><label><span class="mb-1.5 block text-xs font-black text-slate-500">@lang('teacher-course::analytics.format')</span><select name="format" class="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm font-bold"><option value="csv">CSV</option><option value="xlsx">Excel</option><option value="pdf">PDF</option></select></label><button class="h-10 rounded-lg bg-green-600 px-5 text-sm font-black text-white">@lang('teacher-course::analytics.export')</button></form></section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white"><h2 class="border-b border-slate-100 px-5 py-4 text-sm font-black text-slate-950">@lang('teacher-course::analytics.student_activity')</h2><div class="overflow-x-auto"><table class="w-full min-w-[720px] text-left text-sm"><thead class="bg-slate-50 text-[10px] font-black uppercase text-slate-400"><tr><th class="px-5 py-3">@lang('teacher-course::analytics.student')</th><th class="px-5 py-3">@lang('teacher-course::analytics.course')</th><th class="px-5 py-3">@lang('teacher-course::analytics.status')</th><th class="px-5 py-3">@lang('teacher-course::analytics.progress')</th><th class="px-5 py-3">@lang('teacher-course::analytics.last_activity')</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($activities as $enrollment)<tr><td class="px-5 py-3 font-bold">{{ $enrollment->student?->name }}</td><td class="px-5 py-3">{{ $enrollment->course?->name }}</td><td class="px-5 py-3">{{ $enrollment->status }}</td><td class="px-5 py-3 font-black text-green-700">{{ $enrollment->completion_percentage }}%</td><td class="px-5 py-3 text-slate-500">{{ $enrollment->last_activity_at?->format('d/m/Y H:i') ?? '—' }}</td></tr>@empty<tr><td colspan="5" class="p-10 text-center text-slate-400">@lang('teacher-course::analytics.no_data')</td></tr>@endforelse</tbody></table></div><div class="border-t border-slate-100 px-5 py-4">{{ $activities->links() }}</div></section>
    </main>
</div>
@endsection
