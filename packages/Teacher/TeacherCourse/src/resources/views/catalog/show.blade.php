@extends('Mindigo-dashboard::layouts')

@section('title', $course->name.' - Mindigo LMS')
@section('meta_description', $course->description ?: __('teacher-course::catalog.no_description'))
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
@php
    $isTeacherPreview = auth()->user()?->isTeacher() && (int) $course->teacher_id === (int) auth()->id();
    $backUrl = $isTeacherPreview
        ? route('teacher.courses.show', $course)
        : route('courses.index');
@endphp
<div class="min-h-screen bg-slate-50">
    <header class="sticky top-0 z-10 border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur sm:px-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="min-w-0">
                <p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('teacher-course::catalog.course_detail')</p>
                <h1 class="mt-0.5 truncate text-lg font-black text-slate-950">{{ $course->name }}</h1>
                <p class="text-xs font-semibold text-slate-400">@lang('teacher-course::catalog.detail_subtitle')</p>
            </div>
            <a href="{{ $backUrl }}" aria-label="@lang('teacher-course::catalog.back_to_catalog')" class="grid h-10 w-10 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline hover:text-green-700">
                <x-heroicon-o-arrow-left class="h-5 w-5" />
            </a>
        </div>
    </header>

    <main class="p-4 sm:p-6">
        <div class="mx-auto grid max-w-7xl gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
            <div class="min-w-0 space-y-5">
                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                    <div class="grid md:grid-cols-[280px_minmax(0,1fr)]">
                        <div class="aspect-video bg-slate-100 md:aspect-auto">
                            @if($course->cover_image)
                                <img src="{{ asset('storage/'.$course->cover_image) }}" alt="{{ $course->name }}" class="h-full w-full object-cover">
                            @else
                                <div class="grid h-full min-h-48 place-items-center text-slate-300"><x-heroicon-o-academic-cap class="h-16 w-16" /></div>
                            @endif
                        </div>
                        <div class="p-5 sm:p-6">
                            <div class="flex flex-wrap gap-2">
                                @if(!$course->isPublished())<span class="rounded-full bg-amber-50 px-3 py-1 text-[10px] font-black uppercase text-amber-700">@lang('teacher-course::catalog.preview_mode')</span>@endif
                                @foreach([$course->subject?->name, $course->category?->name] as $tag)@if($tag)<span class="rounded-full bg-green-50 px-3 py-1 text-[10px] font-black text-green-700">{{ $tag }}</span>@endif @endforeach
                            </div>
                            <h2 class="mt-4 text-2xl font-black tracking-tight text-slate-950">{{ $course->name }}</h2>
                            <p class="mt-3 text-sm font-semibold leading-6 text-slate-500">{{ $course->description ?: __('teacher-course::catalog.no_description') }}</p>
                            <dl class="mt-5 grid grid-cols-3 gap-3 border-t border-slate-100 pt-4 text-center">
                                <div><dt class="text-[10px] font-black uppercase text-slate-400">@lang('teacher-course::catalog.chapters')</dt><dd class="mt-1 font-black text-slate-900">{{ $course->chapters_count }}</dd></div>
                                <div><dt class="text-[10px] font-black uppercase text-slate-400">@lang('teacher-course::catalog.lessons')</dt><dd class="mt-1 font-black text-slate-900">{{ $course->lessons_count }}</dd></div>
                                <div><dt class="text-[10px] font-black uppercase text-slate-400">@lang('teacher-course::catalog.duration')</dt><dd class="mt-1 font-black text-slate-900">{{ $course->durationLabel() }}</dd></div>
                            </dl>
                        </div>
                    </div>
                </section>

                @foreach(['learning_outcomes', 'requirements', 'target_learners'] as $property)
                    @if(filled($course->{$property}))
                        <section class="rounded-xl border border-slate-200 bg-white p-5">
                            <h2 class="text-base font-black text-slate-950">@lang('teacher-course::catalog.'.$property)</h2>
                            <ul class="mt-3 grid gap-2 sm:grid-cols-2">
                                @foreach($course->{$property} as $item)<li class="flex gap-2 text-sm font-semibold leading-5 text-slate-600"><x-heroicon-o-check-circle class="mt-0.5 h-4 w-4 shrink-0 text-green-600" /><span>{{ $item }}</span></li>@endforeach
                            </ul>
                        </section>
                    @endif
                @endforeach

                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                    <div class="border-b border-slate-200 px-5 py-4">
                        <h2 class="text-base font-black text-slate-950">@lang('teacher-course::catalog.curriculum')</h2>
                        <p class="mt-1 text-xs font-semibold text-slate-400">{{ __('teacher-course::catalog.curriculum_summary', ['chapters' => $course->chapters_count, 'lessons' => $course->lessons_count]) }}</p>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($course->chapters as $chapter)
                            <details class="group px-5 py-4" @if($loop->first) open @endif>
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-black text-slate-800"><span>{{ $chapter->name }}</span><span class="flex items-center gap-2 text-xs text-slate-400">{{ trans_choice('teacher-course::catalog.lesson_count', $chapter->lessons->count(), ['count' => $chapter->lessons->count()]) }}<x-heroicon-o-chevron-down class="h-4 w-4 transition group-open:rotate-180" /></span></summary>
                                <ol class="mt-3 space-y-1 border-t border-slate-100 pt-3">
                                    @foreach($chapter->lessons as $lesson)
                                        @php $canOpen = auth()->user()->can('view', $lesson); @endphp
                                        <li>
                                            @if($canOpen)
                                                <a href="{{ route('courses.lessons.show', [$course->slug, $lesson->id]) }}" class="flex items-center gap-3 rounded-lg px-2 py-2 text-sm font-semibold text-slate-600 no-underline hover:bg-green-50 hover:text-green-700"><x-heroicon-o-play-circle class="h-4 w-4 shrink-0 text-green-600" /><span class="min-w-0 flex-1 truncate">{{ $lesson->name }}</span>@if($lesson->is_preview)<span class="text-[10px] font-black uppercase text-green-700">@lang('teacher-course::catalog.preview')</span>@endif</a>
                                            @else
                                                <div class="flex items-center gap-3 px-2 py-2 text-sm font-semibold text-slate-400"><x-heroicon-o-lock-closed class="h-4 w-4 shrink-0" /><span class="min-w-0 flex-1 truncate">{{ $lesson->name }}</span></div>
                                            @endif
                                        </li>
                                    @endforeach
                                </ol>
                            </details>
                        @empty
                            <div class="px-5 py-12 text-center"><x-heroicon-o-book-open class="mx-auto h-9 w-9 text-slate-300" /><p class="mt-3 text-sm font-semibold text-slate-400">@lang('teacher-course::catalog.curriculum_empty')</p></div>
                        @endforelse
                    </div>
                </section>

                @include('teacher-course::catalog.partials.reviews')

                @if($relatedCourses->isNotEmpty())
                    <section><h2 class="mb-3 text-base font-black text-slate-950">@lang('teacher-course::discovery.related')</h2><div class="grid gap-4 md:grid-cols-2">@foreach($relatedCourses->take(4) as $relatedCourse) @include('teacher-course::catalog.partials.course-card', ['course' => $relatedCourse, 'wishlistedIds' => $wishlistedIds]) @endforeach</div></section>
                @endif
            </div>

            <aside class="space-y-5 xl:sticky xl:top-5 xl:self-start">
                @if(auth()->user()->isStudent())
                    @php $currentEnrollment = $course->enrollments->first(); @endphp
                    <section class="rounded-xl border border-green-200 bg-green-50 p-5">
                        @if($currentEnrollment && in_array($currentEnrollment->status, \Mindigo\TeacherCourse\Models\CourseEnrollment::ACTIVE_STATUSES, true))
                            <a href="{{ route('student.courses.show', $course->slug) }}" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-lg bg-green-600 px-4 text-sm font-black text-white no-underline hover:bg-green-700"><x-heroicon-o-play class="h-4 w-4" />@lang('teacher-course::learning.continue_learning')</a>
                        @else
                            <form method="POST" action="{{ route('courses.enroll', $course->slug) }}">@csrf<button type="submit" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-lg bg-green-600 px-4 text-sm font-black text-white hover:bg-green-700"><x-heroicon-o-plus class="h-4 w-4" />@lang('teacher-course::learning.enroll_now')</button></form>
                        @endif
                    </section>
                @endif
                <section class="rounded-xl border border-slate-200 bg-white p-5">
                    <p class="text-[10px] font-black uppercase tracking-wider text-green-700">@lang('teacher-course::catalog.instructor')</p>
                    <div class="mt-3 flex items-center gap-3"><span class="grid h-11 w-11 place-items-center overflow-hidden rounded-full bg-green-50 font-black text-green-700">@if($course->teacher->avatar)<img src="{{ asset('storage/'.$course->teacher->avatar) }}" alt="" class="h-full w-full object-cover">@else{{ str($course->teacher->name)->substr(0, 1)->upper() }}@endif</span><div class="min-w-0"><h2 class="truncate font-black text-slate-900">{{ $course->teacher->name }}</h2><p class="truncate text-xs font-semibold text-slate-400">{{ $course->teacher->teacherProfile?->headline ?? __('teacher-course::catalog.instructor_default') }}</p></div></div>
                    @if($course->teacher->teacherProfile?->is_public && $course->teacher->teacherProfile->biography)<p class="mt-3 text-sm font-semibold leading-5 text-slate-500">{{ $course->teacher->teacherProfile->biography }}</p>@endif
                    @if($course->teacher->teacherProfile?->is_public)<a href="{{ route('teachers.show', $course->teacher) }}" class="mt-4 inline-flex text-xs font-black text-green-700 no-underline">@lang('teacher-course::reviews.view_profile') →</a>@endif
                </section>
                <section class="rounded-xl border border-slate-200 bg-white p-5">
                    <h2 class="text-sm font-black text-slate-950">@lang('teacher-course::catalog.course_information')</h2>
                    <dl class="mt-3 divide-y divide-slate-100 text-xs">
                        @foreach(['subject' => $course->subject?->name, 'category' => $course->category?->name, 'education_level' => __('teacher-course::app.education_levels.'.$course->education_level), 'difficulty' => __('teacher-course::app.difficulties.'.$course->difficulty), 'language' => strtoupper($course->language)] as $label => $value)
                            <div class="flex items-center justify-between gap-4 py-2.5"><dt class="font-bold text-slate-400">@lang('teacher-course::catalog.'.$label)</dt><dd class="text-right font-black text-slate-700">{{ $value ?: '—' }}</dd></div>
                        @endforeach
                    </dl>
                </section>
            </aside>
        </div>
    </main>
</div>
<script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'Course', 'name' => $course->name, 'description' => $course->description, 'provider' => ['@type' => 'Organization', 'name' => 'Mindigo'], 'author' => ['@type' => 'Person', 'name' => $course->teacher->name]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endsection
