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
    <header class="sticky top-0 z-10 border-b border-slate-200 bg-white/95 px-6 py-4 backdrop-blur">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('teacher-course::app.teaching_content')</p>
                <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('teacher-course::app.title')</h1>
                <p class="mt-1 text-xs font-semibold text-slate-400">@lang('teacher-course::app.index_subtitle')</p>
            </div>
            <div class="flex flex-wrap gap-2">@if(auth()->user()->isTeacher())<a href="{{ route('teacher.profile.edit') }}" class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 no-underline hover:bg-slate-50"><x-heroicon-o-user-circle class="h-4 w-4" />@lang('teacher-course::reviews.edit_profile')</a>@endif<a href="{{ route('teacher.courses.create') }}" class="inline-flex h-10 items-center gap-2 rounded-xl bg-green-600 px-4 text-sm font-black text-white no-underline shadow-sm transition hover:bg-green-500">
                <x-heroicon-o-plus class="h-4 w-4" />@lang('teacher-course::app.create_course_btn')
            </a></div>
        </div>
    </header>

    <main class="flex flex-1 flex-col gap-5 p-6">
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <form method="GET" action="{{ route('teacher.courses.index') }}" class="flex flex-wrap items-center gap-3 border-b border-slate-200 px-5 py-4" role="search">
                <label class="relative min-w-64 flex-1">
                    <span class="sr-only">@lang('teacher-course::app.search_placeholder')</span>
                    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="@lang('teacher-course::app.search_placeholder')" class="h-10 w-full rounded-lg border border-slate-300 bg-white pl-9 pr-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100">
                </label>
                <label>
                    <span class="sr-only">@lang('teacher-course::app.all_status')</span>
                    <select name="status" data-mindigo-auto-submit class="h-10 min-w-44 rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 outline-none focus:border-green-500">
                        <option value="">@lang('teacher-course::app.all_status')</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>@lang('teacher-course::app.active')</option>
                        <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>@lang('teacher-course::app.inactive')</option>
                    </select>
                </label>
                <label>
                    <span class="sr-only">@lang('teacher-course::app.publication_status_field')</span>
                    <select name="publication_status" data-mindigo-auto-submit class="h-10 min-w-44 rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 outline-none focus:border-green-500">
                        <option value="">@lang('teacher-course::app.all_publication_statuses')</option>
                        @foreach(\Mindigo\TeacherCourse\Models\Course::PUBLICATION_STATUSES as $publicationStatus)
                            <option value="{{ $publicationStatus }}" @selected(($filters['publication_status'] ?? '') === $publicationStatus)>@lang('teacher-course::app.publication_statuses.'.$publicationStatus)</option>
                        @endforeach
                    </select>
                </label>
                <button type="submit" class="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 text-sm font-black text-slate-700 transition hover:border-green-300 hover:text-green-700">
                    <x-heroicon-o-funnel class="h-4 w-4" />@lang('teacher-course::app.filter')
                </button>
            </form>

            @if($courses->isEmpty())
                <div class="flex min-h-80 flex-col items-center justify-center px-6 py-16 text-center">
                    <span class="grid h-12 w-12 place-items-center rounded-xl bg-slate-100 text-slate-400"><x-heroicon-o-book-open class="h-6 w-6" /></span>
                    <h2 class="mt-4 text-base font-black text-slate-800">@lang('teacher-course::app.no_courses')</h2>
                    <p class="mt-1 max-w-lg text-sm font-semibold text-slate-400">@lang('teacher-course::app.create_first_course')</p>
                    <a href="{{ route('teacher.courses.create') }}" class="mt-5 inline-flex h-10 items-center gap-2 rounded-lg bg-green-600 px-5 text-sm font-black text-white no-underline hover:bg-green-500">
                        <x-heroicon-o-plus class="h-4 w-4" />@lang('teacher-course::app.create_course')
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[860px] border-collapse text-left">
                        <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-5 py-3">@lang('teacher-course::app.course')</th>
                                <th class="px-5 py-3">@lang('teacher-course::app.curriculum')</th>
                                <th class="px-5 py-3">@lang('teacher-course::app.status_field')</th>
                                <th class="px-5 py-3">@lang('teacher-course::app.updated_at')</th>
                                <th class="px-5 py-3 text-right">@lang('teacher-course::app.actions')</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($courses as $course)
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="grid h-12 w-16 shrink-0 place-items-center overflow-hidden rounded-lg border border-slate-200 bg-slate-100 text-slate-400">
                                                @if($course->cover_image)
                                                    <img src="{{ asset('storage/'.$course->cover_image) }}" alt="" class="h-full w-full object-cover">
                                                @else
                                                    <x-heroicon-o-book-open class="h-5 w-5" />
                                                @endif
                                            </span>
                                            <span class="min-w-0">
                                                <a href="{{ route('teacher.courses.show', $course) }}" class="block max-w-md truncate text-sm font-black text-slate-900 no-underline hover:text-green-700">{{ $course->name }}</a>
                                                <span class="mt-1 block max-w-md truncate text-xs font-semibold text-slate-400">{{ $course->description ? strip_tags($course->description) : __('teacher-course::app.no_description') }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-sm font-bold text-slate-600">
                                        <span>{{ __('teacher-course::app.chapters_count', ['count' => $course->chapters_count]) }}</span>
                                        <span class="mx-1 text-slate-300">·</span>
                                        <span>{{ __('teacher-course::app.lessons_count', ['count' => $course->lessons_count]) }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap gap-1.5">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-black {{ $course->is_active ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500' }}">{{ __('teacher-course::app.'.($course->is_active ? 'active' : 'inactive')) }}</span>
                                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-600">@lang('teacher-course::app.publication_statuses.'.$course->publication_status)</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-xs font-semibold text-slate-500">{{ $course->updated_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('teacher.courses.show', $course) }}" class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 px-3 text-xs font-black text-slate-700 no-underline hover:border-green-200 hover:text-green-700"><x-heroicon-o-eye class="h-4 w-4" />@lang('teacher-course::app.manage')</a>
                                            <a href="{{ route('teacher.courses.edit', $course) }}" aria-label="@lang('teacher-course::app.edit')" class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 text-slate-500 no-underline hover:text-green-700"><x-heroicon-o-pencil class="h-4 w-4" /></a>
                                            <form method="POST" action="{{ route('teacher.courses.destroy', $course) }}" data-mindigo-confirm-title="{{ __('teacher-course::app.delete_course_title') }}" data-mindigo-confirm-message="{{ __('teacher-course::app.delete_course_confirm', ['name' => $course->name]) }}" data-mindigo-confirm-text="{{ __('teacher-course::app.delete') }}" data-mindigo-confirm-cancel="{{ __('teacher-course::app.cancel') }}" data-mindigo-confirm-type="danger">
                                                @csrf @method('DELETE')
                                                <button type="submit" aria-label="@lang('teacher-course::app.delete')" class="grid h-9 w-9 place-items-center rounded-lg border border-red-100 text-red-500 transition hover:bg-red-50"><x-heroicon-o-trash class="h-4 w-4" /></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($courses->hasPages())
                    <div class="border-t border-slate-100 px-5 py-4">{{ $courses->links() }}</div>
                @endif
            @endif
        </section>
    </main>
</div>
@endsection
