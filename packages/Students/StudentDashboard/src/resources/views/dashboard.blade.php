@extends('Mindigo-dashboard::layouts')

@section('title', __('student-dashboard::app.meta_title'))
@section('meta_description', __('student-dashboard::app.meta_description'))

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<main class="min-h-screen bg-stone-50 p-4 lg:h-screen lg:overflow-hidden lg:p-5">
    <div class="grid h-full w-full gap-4 lg:grid-cols-12">
        <aside class="flex min-h-0 flex-col gap-4 lg:col-span-3">
            <section class="rounded-2xl border border-slate-200 bg-white p-4">
                <div class="flex items-center justify-between">
                    <div><p class="text-[10px] font-black uppercase tracking-[0.18em] text-green-500">@lang('student-dashboard::app.calendar')</p><h2 class="mt-1 text-base font-black text-slate-950">{{ now()->translatedFormat('F Y') }}</h2></div>
                    @if(Route::has('student.schedule.index'))<a href="{{ route('student.schedule.index') }}" class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 text-slate-500 no-underline hover:border-green-200 hover:text-green-600"><x-heroicon-o-calendar-days class="h-4 w-4" /></a>@endif
                </div>
                <div class="mt-4 grid grid-cols-7 text-center text-[10px] font-black uppercase text-slate-400">@foreach(['d_mon','d_tue','d_wed','d_thu','d_fri','d_sat','d_sun'] as $day)<span>{{ __('student-dashboard::app.'.$day) }}</span>@endforeach</div>
                <div class="mt-2 space-y-1">@foreach($monthCalendar as $week)<div class="grid grid-cols-7 gap-1">@foreach($week as $day)<div class="relative grid aspect-square place-items-center rounded-lg text-xs font-bold transition-colors {{ $day['is_today'] ? 'bg-green-600 text-white' : ($day['is_current_month'] ? 'text-slate-700 hover:bg-green-50' : 'text-slate-300') }}"><span>{{ $day['day'] }}</span>@if($day['task_count'])<span class="absolute bottom-1 h-1 w-1 rounded-full {{ $day['is_today'] ? 'bg-white' : 'bg-green-500' }}"></span>@endif</div>@endforeach</div>@endforeach</div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-4">
                <div class="flex items-center justify-between"><h2 class="text-sm font-black text-slate-950">@lang('teacher-course::learning.dashboard_title')</h2>@if(Route::has('student.courses.index'))<a href="{{ route('student.courses.index') }}" class="text-[10px] font-black text-green-600 no-underline">@lang('teacher-course::learning.view_all')</a>@endif</div>
                <div class="mt-2 grid gap-2 sm:grid-cols-2">@forelse($activeCourses->take(2) as $enrollment)<a href="{{ route('student.courses.show', $enrollment->course->slug) }}" class="rounded-xl border border-slate-100 px-3 py-2 no-underline hover:border-green-200"><div class="flex items-center justify-between gap-2"><span class="truncate text-[10px] font-black text-slate-700">{{ $enrollment->course->name }}</span><span class="text-[9px] font-black text-green-700">{{ $enrollment->completion_percentage }}%</span></div><progress value="{{ $enrollment->completion_percentage }}" max="100" class="mt-1 h-1 w-full accent-green-600"></progress></a>@empty<p class="col-span-full py-3 text-center text-xs font-semibold text-slate-400">@lang('teacher-course::learning.dashboard_empty')</p>@endforelse</div>
            </section>

            <section class="min-h-0 flex-1 rounded-2xl border border-slate-200 bg-white p-4">
                <div class="flex items-center justify-between"><h2 class="text-sm font-black text-slate-950">@lang('student-dashboard::app.upcoming_exams')</h2><span class="rounded-full bg-green-50 px-2.5 py-1 text-[10px] font-black text-green-600">{{ $upcomingExams->count() }}</span></div>
                <div class="mt-3 space-y-2">@forelse($upcomingExams as $exam)<article class="group flex items-center gap-3 rounded-2xl border border-slate-100 p-3 transition hover:border-green-200 hover:bg-green-50/40"><span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-green-100 text-green-600"><x-heroicon-o-document-text class="h-5 w-5" /></span><div class="min-w-0 flex-1"><h3 class="truncate text-xs font-black text-slate-800">{{ $exam->title }}</h3><p class="mt-1 truncate text-[10px] font-bold text-slate-400">{{ $exam->at?->translatedFormat('d M · H:i') }}</p></div><x-heroicon-o-arrow-right class="h-4 w-4 text-green-500" /></article>@empty<p class="py-8 text-center text-xs font-semibold text-slate-400">@lang('student-dashboard::app.no_upcoming_exams')</p>@endforelse</div>
            </section>
        </aside>

        <section class="flex min-h-0 flex-col gap-4 lg:col-span-6">
            <article class="flex items-center justify-between gap-5 rounded-2xl border border-green-100 bg-linear-to-r from-green-50 to-white px-6 py-5">
                <div><p class="text-[10px] font-black uppercase tracking-[0.18em] text-green-600">@lang('student-dashboard::app.welcome_back')</p><h1 class="mt-1 text-xl font-black text-slate-950">{{ __('student-dashboard::app.hello_name', ['name' => $student->name]) }}</h1><p class="mt-1 text-xs font-semibold text-slate-500">@lang('student-dashboard::app.banner_subtitle')</p></div>
                <div class="flex shrink-0 gap-2">@if(Route::has('student.practice.analytics.index'))<a href="{{ route('student.practice.analytics.index') }}" class="rounded-lg border border-green-200 bg-white px-4 py-2.5 text-xs font-bold text-green-700 no-underline transition-colors hover:bg-green-50">@lang('student-dashboard::app.practice_analytics')</a>@endif @if(Route::has('student.progress.index'))<a href="{{ route('student.progress.index') }}" class="rounded-lg bg-green-600 px-5 py-2.5 text-xs font-bold text-white no-underline transition-colors hover:bg-green-700">@lang('student-dashboard::app.view_progress')</a>@endif</div>
            </article>

            <section>
                <div class="mb-3 flex items-center justify-between"><h2 class="text-base font-black text-slate-950">@lang('student-dashboard::app.my_progress')</h2><span class="text-[10px] font-black uppercase tracking-widest text-slate-400">@lang('student-dashboard::app.this_week')</span></div>
                <div class="grid grid-cols-2 gap-3">
                    <article class="rounded-2xl border border-slate-200 bg-white p-4">
                        <p class="text-[10px] font-bold text-slate-400">@lang('student-dashboard::app.card_avg')</p>
                        <div class="mt-3 flex items-end justify-between gap-4">
                            <div><strong class="block text-4xl font-black text-slate-700">{{ number_format($stats['avg_score']['percent'], 1) }}</strong><span class="mt-3 inline-flex items-center gap-1 rounded-lg bg-green-100 px-2.5 py-1 text-[10px] font-black text-green-700"><x-heroicon-o-arrow-trending-up class="h-3 w-3" />@lang('student-dashboard::app.scale_100')</span></div>
                            <div class="flex h-20 items-end gap-1.5" aria-hidden="true">
                                <span class="h-7 w-6 rounded-t-lg bg-slate-100"></span>
                                <span class="h-12 w-6 rounded-t-lg bg-green-100"></span>
                                <span class="relative h-16 w-6 rounded-t-lg bg-green-300"><span class="absolute bottom-full left-1/2 mb-1 -translate-x-1/2 whitespace-nowrap rounded bg-green-700 px-2 py-1 text-[8px] font-bold leading-none text-white">Mindigo</span></span>
                                <span class="h-8 w-6 rounded-t-lg bg-slate-100"></span>
                            </div>
                        </div>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="flex h-full items-center gap-4">
                            <div class="relative h-16 w-16 shrink-0"><svg class="h-16 w-16 -rotate-90" viewBox="0 0 64 64" aria-hidden="true"><circle cx="32" cy="32" r="25" fill="none" stroke="currentColor" stroke-width="6" class="text-slate-100"/><circle cx="32" cy="32" r="25" fill="none" stroke="currentColor" stroke-width="6" stroke-linecap="round" pathLength="100" stroke-dasharray="{{ $stats['weekly']['percent'] }} 100" class="text-green-500"/></svg><strong class="absolute inset-0 grid place-items-center text-xs font-black text-slate-800">{{ $stats['weekly']['percent'] }}%</strong></div>
                            <div class="min-w-0"><p class="text-[10px] font-bold text-slate-400">@lang('student-dashboard::app.card_weekly')</p><h3 class="mt-1 text-sm font-black leading-snug text-slate-900">@lang('student-dashboard::app.keep_learning')</h3><p class="mt-4 text-[10px] font-semibold leading-relaxed text-slate-400">{{ __('student-dashboard::app.of_count', ['done' => $stats['assignments']['done'], 'total' => $stats['assignments']['total'], 'unit' => __('student-dashboard::app.unit_assignments')]) }}</p></div>
                        </div>
                    </article>
                </div>
            </section>

            <section class="min-h-0 flex-1 rounded-2xl border border-slate-200 bg-white p-4">
                <div class="flex items-center justify-between"><div><h2 class="text-base font-black text-slate-950">@lang('student-dashboard::app.assignments')</h2><p class="text-[10px] font-semibold text-slate-400">@lang('student-dashboard::app.assignments_subtitle')</p></div>@if(Route::has('student.assignments.index'))<a href="{{ route('student.assignments.index') }}" class="text-xs font-black text-green-600 no-underline">@lang('student-dashboard::app.see_all')</a>@endif</div>
                <div class="mt-3 divide-y divide-slate-100">@forelse($assignments as $assignment)<article class="grid grid-cols-12 items-center gap-4 py-3"><div class="col-span-7 flex min-w-0 items-center gap-3"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-600"><x-heroicon-o-book-open class="h-4 w-4" /></span><div class="min-w-0"><h3 class="truncate text-xs font-black text-slate-800">{{ $assignment->title }}</h3><p class="mt-0.5 truncate text-[10px] font-semibold text-slate-400">{{ $assignment->status }}</p></div></div><span class="col-span-2 text-[10px] font-bold text-slate-400">{{ $assignment->at?->format('d/m') }}</span><div class="col-span-3"><progress value="{{ max(0, 100 - now()->diffInHours($assignment->at, false)) }}" max="100" class="h-1.5 w-full accent-green-600"></progress><p class="mt-1 text-right text-[9px] font-black text-green-600">{{ $assignment->time_left }}</p></div></article>@empty<p class="py-12 text-center text-xs font-semibold text-slate-400">@lang('student-dashboard::app.empty_tasks')</p>@endforelse</div>
            </section>
        </section>

        <aside class="flex min-h-0 flex-col gap-4 lg:col-span-3">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 text-center"><div class="flex justify-between"><h2 class="text-sm font-black text-slate-950">@lang('student-dashboard::app.my_profile')</h2>@if(Route::has('profile.index'))<a href="{{ route('profile.index') }}" class="text-slate-400 hover:text-green-600"><x-heroicon-o-pencil-square class="h-4 w-4" /></a>@endif</div><img src="{{ $student->avatar_url }}" alt="{{ $student->name }}" class="mx-auto mt-4 h-20 w-20 rounded-full object-cover ring-4 ring-green-50"><h3 class="mt-3 text-lg font-black text-slate-950">{{ $student->name }}</h3><p class="text-xs font-semibold text-slate-400">{{ '@'.str($student->email)->before('@') }}</p><div class="mt-4 grid grid-cols-3 divide-x divide-green-100 rounded-2xl bg-green-50 py-3"><div><strong class="block text-sm font-black text-slate-800">{{ $classroomIds->count() }}</strong><span class="text-[9px] font-bold text-slate-400">@lang('student-dashboard::app.classes')</span></div><div><strong class="block text-sm font-black text-slate-800">{{ $stats['exams']['done'] }}</strong><span class="text-[9px] font-bold text-slate-400">@lang('student-dashboard::app.exams')</span></div><div><strong class="block text-sm font-black text-slate-800">{{ $stats['avg_score']['percent'] }}</strong><span class="text-[9px] font-bold text-slate-400">@lang('student-dashboard::app.average')</span></div></div></section>

            <section class="min-h-0 flex-1 rounded-2xl border border-slate-200 bg-white p-4"><div class="flex items-center justify-between"><h2 class="text-sm font-black text-slate-950">@lang('student-dashboard::app.planned_today')</h2><span class="grid h-8 w-8 place-items-center rounded-full bg-slate-900 text-white"><x-heroicon-o-plus class="h-4 w-4" /></span></div><div class="mt-3 space-y-2">@forelse(($todayTasks->isNotEmpty() ? $todayTasks : $activeTasks->take(3)) as $task)<article class="flex items-center gap-3 rounded-2xl border border-slate-100 p-3"><span class="text-xs font-black text-slate-800">{{ $task->at?->format('H:i') }}</span><div class="min-w-0 border-l border-slate-200 pl-3"><h3 class="truncate text-xs font-black text-slate-800">{{ $task->title }}</h3><p class="mt-0.5 text-[10px] font-semibold text-green-500">{{ $task->status }}</p></div></article>@empty<p class="py-8 text-center text-xs font-semibold text-slate-400">@lang('student-dashboard::app.nothing_today')</p>@endforelse</div>
                <div class="mt-4 border-t border-slate-100 pt-4"><div class="mb-3 flex items-center justify-between"><h3 class="text-xs font-black text-slate-800">@lang('student-dashboard::app.recent_activity')</h3>@if(Route::has('student.history.index'))<a href="{{ route('student.history.index') }}" class="text-[10px] font-black text-green-600 no-underline">@lang('student-dashboard::app.see_all')</a>@endif</div><div class="space-y-3">@forelse($recentActivity->take(3) as $item)<div class="flex items-center gap-2"><span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-50 text-emerald-600"><x-heroicon-o-check class="h-3.5 w-3.5" /></span><div class="min-w-0 flex-1"><p class="truncate text-[10px] font-black text-slate-700">{{ $item->action }}</p><p class="truncate text-[9px] font-semibold text-slate-400">{{ $item->text }}</p></div></div>@empty<p class="py-4 text-center text-xs font-semibold text-slate-400">@lang('student-dashboard::app.empty_activity')</p>@endforelse</div></div>
            </section>
        </aside>
    </div>
</main>
@endsection
