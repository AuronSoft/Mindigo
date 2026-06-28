@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-course::app.title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Teacher/TeacherCourse/src/resources/css/app.css',
        'packages/Teacher/TeacherCourse/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">

    {{-- Header --}}
    <header class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white/90 px-6 py-4 backdrop-blur">
        <div>
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">@lang('teacher-course::app.teaching_content')</p>
            <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('teacher-course::app.title')</h1>
        </div>
        <a href="{{ route('teacher.courses.create') }}"
           class="inline-flex h-9 items-center gap-2 rounded-full bg-green-600 px-5 text-xs font-black text-white shadow-sm shadow-green-200 transition hover:bg-green-500">
            <x-heroicon-o-plus class="h-4 w-4" /> @lang('teacher-course::app.create_course_btn')
        </a>
    </header>

    <div class="flex flex-1 flex-col gap-5 p-6">

        {{-- Filter bar --}}
        <form method="GET" action="{{ route('teacher.courses.index') }}" class="flex flex-wrap items-center gap-3">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                   placeholder="{{ __('teacher-course::app.search_placeholder') }}"
                   class="h-9 rounded-full border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none transition focus:border-green-400 focus:ring-2 focus:ring-green-50 min-w-[220px]">
            <select name="status" data-mindigo-auto-submit
                    class="h-9 rounded-full border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none">
                <option value="">@lang('teacher-course::app.all_status')</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>@lang('teacher-course::app.active')</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>@lang('teacher-course::app.inactive')</option>
            </select>
            <button type="submit" class="h-9 rounded-full border border-slate-200 bg-white px-4 text-sm font-bold text-slate-600 transition hover:bg-slate-50">
                <x-heroicon-o-magnifying-glass class="h-4 w-4" />
            </button>
        </form>

        @if($courses->isEmpty())
            <div class="flex flex-col items-center justify-center gap-4 rounded-3xl border border-dashed border-slate-300 bg-white py-24">
                <x-heroicon-o-book-open class="h-16 w-16 text-slate-200" />
                <p class="text-lg font-black text-slate-600">@lang('teacher-course::app.no_courses')</p>
                <p class="text-sm font-bold text-slate-400">@lang('teacher-course::app.create_first_course')</p>
                <a href="{{ route('teacher.courses.create') }}"
                   class="mt-2 inline-flex h-10 items-center gap-2 rounded-full bg-green-600 px-6 text-sm font-black text-white shadow-sm hover:bg-green-500">
                    <x-heroicon-o-plus class="h-4 w-4" /> @lang('teacher-course::app.create_course')
                </a>
            </div>
        @else
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($courses as $course)
                    <article class="group rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden transition hover:shadow-md hover:-translate-y-0.5">
                        {{-- Cover --}}
                        <div class="relative h-40 bg-gradient-to-br from-green-50 to-green-100 overflow-hidden">
                            @if($course->cover_image)
                                <img src="{{ asset('storage/' . $course->cover_image) }}"
                                     alt="{{ $course->name }}"
                                     class="h-full w-full object-cover transition group-hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    <x-heroicon-o-book-open class="h-16 w-16 text-green-200" />
                                </div>
                            @endif
                            <span class="absolute right-3 top-3 inline-flex rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider
                                {{ $course->status === 'active' ? 'bg-green-600 text-white' : 'bg-slate-600 text-white' }}">
                                {{ __('teacher-course::app.' . $course->status) }}
                            </span>
                        </div>

                        <div class="p-5">
                            <h2 class="text-base font-black text-slate-900 leading-snug line-clamp-2">
                                <a href="{{ route('teacher.courses.show', $course) }}" class="hover:text-green-700 no-underline">
                                    {{ $course->name }}
                                </a>
                            </h2>
                            @if($course->description)
                                <p class="mt-1.5 text-xs font-semibold text-slate-500 line-clamp-2 leading-relaxed">
                                    {{ strip_tags($course->description) }}
                                </p>
                            @endif

                            <div class="mt-4 flex items-center gap-4 text-xs font-bold text-slate-400">
                                <span class="flex items-center gap-1.5">
                                    <x-heroicon-o-squares-2x2 class="h-3.5 w-3.5" />
                                    {{ __('teacher-course::app.chapters_count', ['count' => $course->chapters_count]) }}
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <x-heroicon-o-play class="h-3.5 w-3.5" />
                                    {{ __('teacher-course::app.lessons_count', ['count' => $course->lessons_count]) }}
                                </span>
                            </div>

                            <div class="mt-4 flex items-center gap-2 border-t border-slate-100 pt-4">
                                <a href="{{ route('teacher.courses.show', $course) }}"
                                   class="inline-flex h-8 items-center gap-1.5 rounded-xl bg-green-50 px-3 text-xs font-black text-green-700 hover:bg-green-100 no-underline">
                                    <x-heroicon-o-eye class="h-3.5 w-3.5" /> @lang('teacher-course::app.manage')
                                </a>
                                <a href="{{ route('teacher.courses.edit', $course) }}"
                                   class="inline-flex h-8 items-center gap-1.5 rounded-xl border border-slate-200 px-3 text-xs font-black text-slate-600 hover:bg-slate-50 no-underline">
                                    <x-heroicon-o-pencil class="h-3.5 w-3.5" /> @lang('teacher-course::app.edit')
                                </a>
                                <form method="POST" action="{{ route('teacher.courses.destroy', $course) }}" class="ml-auto"
                                      data-mindigo-confirm-title="{{ __('teacher-course::app.delete_course_title') }}"
                                      data-mindigo-confirm-message="{{ __('teacher-course::app.delete_course_confirm', ['name' => $course->name]) }}"
                                      data-mindigo-confirm-text="{{ __('teacher-course::app.delete') }}"
                                      data-mindigo-confirm-cancel="{{ __('teacher-course::app.cancel') }}"
                                      data-mindigo-confirm-type="danger">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-xl border border-red-100 px-3 text-xs font-black text-red-500 hover:bg-red-50">
                                        <x-heroicon-o-trash class="h-3.5 w-3.5" />
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-2">
                {{ $courses->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
