@extends('core::layouts.home')

@section('content')
<div class="min-h-screen bg-slate-50 text-slate-900">
    @include('core::partials.home.navbar')

    <main>
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:px-10">
                <nav class="mb-5 flex flex-wrap items-center gap-2 text-xs font-bold text-slate-400" aria-label="@lang('teacher-course::catalog.breadcrumb')">
                    <a href="{{ route('home') }}" class="text-slate-500 no-underline hover:text-green-700">@lang('teacher-course::catalog.home')</a>
                    <x-heroicon-o-chevron-right class="h-3.5 w-3.5" />
                    <a href="{{ route('courses.index') }}" class="text-slate-500 no-underline hover:text-green-700">@lang('teacher-course::catalog.title')</a>
                    <x-heroicon-o-chevron-right class="h-3.5 w-3.5" />
                    <span class="max-w-64 truncate text-green-700">{{ $course->name }}</span>
                </nav>
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-green-700">{{ $course->subject?->name ?? __('teacher-course::catalog.course_detail') }}</p>
                <h1 class="mt-1 max-w-4xl text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">{{ $course->name }}</h1>
                <p class="mt-3 max-w-3xl text-sm font-semibold leading-6 text-slate-500">{{ $course->description ?: __('teacher-course::catalog.no_description') }}</p>
            </div>
        </header>

        <div class="mx-auto grid max-w-7xl gap-6 px-5 py-7 sm:px-8 lg:grid-cols-[1fr_340px] lg:px-10">
            <div class="space-y-6">
                @foreach([
                    'learning_outcomes' => 'learning_outcomes',
                    'requirements' => 'requirements',
                    'target_learners' => 'target_learners',
                ] as $property => $translation)
                    @if(filled($course->{$property}))
                        <section class="rounded-xl border border-slate-200 bg-white p-5">
                            <h2 class="text-base font-black text-slate-900">@lang('teacher-course::catalog.'.$translation)</h2>
                            <ul class="mt-3 grid gap-2 sm:grid-cols-2">
                                @foreach($course->{$property} as $item)
                                    <li class="flex gap-2 text-sm font-semibold leading-5 text-slate-600"><x-heroicon-o-check-circle class="mt-0.5 h-4 w-4 shrink-0 text-green-600" /><span>{{ $item }}</span></li>
                                @endforeach
                            </ul>
                        </section>
                    @endif
                @endforeach

                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                    <div class="border-b border-slate-200 px-5 py-4">
                        <h2 class="text-base font-black text-slate-900">@lang('teacher-course::catalog.curriculum')</h2>
                        <p class="mt-1 text-xs font-semibold text-slate-400">{{ trans_choice('teacher-course::catalog.curriculum_summary', $course->lessons_count, ['chapters' => $course->chapters->count(), 'lessons' => $course->lessons_count]) }}</p>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($course->chapters as $chapter)
                            <details class="group px-5 py-4" @if($loop->first) open @endif>
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-black text-slate-800">
                                    <span>{{ $chapter->name }}</span>
                                    <span class="flex items-center gap-2 text-xs text-slate-400">{{ trans_choice('teacher-course::catalog.lesson_count', $chapter->lessons->count(), ['count' => $chapter->lessons->count()]) }}<x-heroicon-o-chevron-down class="h-4 w-4 transition group-open:rotate-180" /></span>
                                </summary>
                                <ol class="mt-3 space-y-2 border-t border-slate-100 pt-3">
                                    @foreach($chapter->lessons as $lesson)
                                        <li class="flex items-center gap-2 text-sm font-semibold text-slate-500"><x-heroicon-o-play-circle class="h-4 w-4 text-green-600" /><span>{{ $lesson->name }}</span></li>
                                    @endforeach
                                </ol>
                            </details>
                        @empty
                            <p class="px-5 py-10 text-center text-sm font-semibold text-slate-400">@lang('teacher-course::catalog.curriculum_empty')</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">
                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                    <div class="aspect-video bg-slate-100">
                        @if($course->cover_image)<img src="{{ asset('storage/'.$course->cover_image) }}" alt="{{ $course->name }}" class="h-full w-full object-cover">@else<div class="grid h-full place-items-center text-slate-300"><x-heroicon-o-academic-cap class="h-14 w-14" /></div>@endif
                    </div>
                    <dl class="grid grid-cols-2 gap-px bg-slate-100 text-center">
                        <div class="bg-white p-3"><dt class="text-[10px] font-black uppercase text-slate-400">@lang('teacher-course::catalog.duration')</dt><dd class="mt-1 text-sm font-black">{{ $course->estimated_duration_minutes ? __('teacher-course::catalog.minutes', ['count' => $course->estimated_duration_minutes]) : '—' }}</dd></div>
                        <div class="bg-white p-3"><dt class="text-[10px] font-black uppercase text-slate-400">@lang('teacher-course::catalog.lessons')</dt><dd class="mt-1 text-sm font-black">{{ $course->lessons_count }}</dd></div>
                        <div class="bg-white p-3"><dt class="text-[10px] font-black uppercase text-slate-400">@lang('teacher-course::catalog.students')</dt><dd class="mt-1 text-sm font-black">{{ number_format($course->enrollment_count) }}</dd></div>
                        <div class="bg-white p-3"><dt class="text-[10px] font-black uppercase text-slate-400">@lang('teacher-course::catalog.rating')</dt><dd class="mt-1 text-sm font-black text-amber-600">{{ $course->rating_count ? number_format($course->rating_average, 1) : '—' }}</dd></div>
                    </dl>
                    <div class="p-5">
                        <p class="text-xl font-black text-slate-950">{{ $course->access_type === 'free' ? __('teacher-course::catalog.free') : number_format((float) $course->price).' '.$course->currency }}</p>
                        <p class="mt-1 text-xs font-semibold text-slate-400">@lang('teacher-course::catalog.enrollment_phase_notice')</p>
                    </div>
                </section>

                <section class="rounded-xl border border-slate-200 bg-white p-5">
                    <p class="text-[10px] font-black uppercase tracking-wider text-green-700">@lang('teacher-course::catalog.instructor')</p>
                    <div class="mt-3 flex items-center gap-3">
                        <span class="grid h-11 w-11 place-items-center rounded-full bg-green-50 font-black text-green-700">{{ str($course->teacher->name)->substr(0, 1)->upper() }}</span>
                        <div><h2 class="font-black text-slate-900">{{ $course->teacher->name }}</h2><p class="text-xs font-semibold text-slate-400">{{ $course->teacher->teacherProfile?->headline ?? __('teacher-course::catalog.instructor_default') }}</p></div>
                    </div>
                    @if($course->teacher->teacherProfile?->is_public && $course->teacher->teacherProfile->biography)
                        <p class="mt-3 text-sm font-semibold leading-5 text-slate-500">{{ $course->teacher->teacherProfile->biography }}</p>
                    @endif
                </section>
            </aside>
        </div>
    </main>
</div>
@endsection
