@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-course::app.course_management_title') . ' — ' . $course->name)

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
        <div class="flex items-center gap-3">
            <a href="{{ route('teacher.courses.index') }}"
               class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline transition hover:bg-green-50 hover:text-green-700">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
            </a>
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest text-green-700">
                    @lang('teacher-course::app.teaching_content')
                </p>
                <h1 class="mt-0.5 text-lg font-black text-slate-950">{{ $course->name }}</h1>
                <p class="mt-1 text-xs font-semibold text-slate-400">@lang('teacher-course::app.detail_subtitle')</p>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-black {{ $course->is_active ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500' }}">{{ __('teacher-course::app.'.($course->is_active ? 'active' : 'inactive')) }}</span>
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black text-slate-600">@lang('teacher-course::app.publication_statuses.'.$course->publication_status)</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('courses.show', $course->slug) }}" target="_blank" class="inline-flex h-9 items-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 no-underline hover:bg-slate-50"><x-heroicon-o-eye class="h-4 w-4" />@lang('teacher-course::publishing.preview')</a>
            <a href="{{ route('teacher.courses.monitor', $course) }}" class="inline-flex h-9 items-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 no-underline hover:bg-slate-50"><x-heroicon-o-chart-bar class="h-4 w-4" />@lang('teacher-course::publishing.monitor')</a>
            <form method="POST" action="{{ route('teacher.courses.duplicate', $course) }}">@csrf<button class="inline-flex h-9 items-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 hover:bg-slate-50"><x-heroicon-o-document-duplicate class="h-4 w-4" />@lang('teacher-course::publishing.duplicate')</button></form>
            <a href="{{ route('teacher.courses.edit', $course) }}"
               class="inline-flex h-9 items-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 no-underline transition hover:bg-slate-50">
                <x-heroicon-o-pencil-square class="h-4 w-4" /> @lang('teacher-course::app.edit_course_btn')
            </a>
            <form method="POST" action="{{ route('teacher.courses.destroy', $course) }}"
                  data-mindigo-confirm-title="{{ __('teacher-course::app.delete_course_title') }}"
                  data-mindigo-confirm-message="{{ __('teacher-course::app.delete_course_permanent_confirm') }}"
                  data-mindigo-confirm-text="{{ __('teacher-course::app.delete_permanent') }}"
                  data-mindigo-confirm-cancel="{{ __('teacher-course::app.cancel') }}"
                  data-mindigo-confirm-type="danger">
                @csrf @method('DELETE')
                <button type="submit"
                        class="inline-flex h-9 items-center gap-2 rounded-full border border-red-200 bg-red-50 px-4 text-xs font-black text-red-600 transition hover:bg-red-100">
                    <x-heroicon-o-trash class="h-4 w-4" /> @lang('teacher-course::app.delete_permanent')
                </button>
            </form>
        </div>
    </header>

    <div class="flex flex-1 flex-col gap-6 p-6">

        @if(auth()->user()->isAdmin())
            <section class="rounded-xl border border-slate-200 bg-white p-5">
                <form method="POST" action="{{ route('admin.courses.featured', $course) }}" class="flex flex-wrap items-end justify-between gap-4">
                    @csrf @method('PATCH')
                    <div><h2 class="text-sm font-black text-slate-900">@lang('teacher-course::discovery.featured')</h2><p class="mt-1 text-xs font-semibold text-slate-400">@lang('teacher-course::discovery.featured_description')</p></div>
                    <div class="flex items-end gap-3"><label class="flex items-center gap-2 pb-2 text-xs font-black text-slate-600"><input type="hidden" name="is_featured" value="0"><input type="checkbox" name="is_featured" value="1" @checked($course->is_featured) class="h-4 w-4 accent-green-600">@lang('teacher-course::discovery.featured')</label><label><span class="mb-1 block text-[10px] font-black uppercase text-slate-400">@lang('teacher-course::discovery.featured_order')</span><input type="number" name="featured_order" min="0" value="{{ $course->featured_order }}" class="h-9 w-24 rounded-lg border border-slate-200 px-3 text-sm"></label><button class="h-9 rounded-lg bg-green-600 px-4 text-xs font-black text-white">@lang('teacher-course::discovery.save')</button></div>
                </form>
            </section>
        @endif

        <section class="rounded-xl border border-slate-200 bg-white p-5">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div><h2 class="text-sm font-black text-slate-900">@lang('teacher-course::publishing.workflow')</h2><p class="mt-1 text-xs font-semibold text-slate-400">@lang('teacher-course::publishing.workflow_description')</p></div>
                <div class="flex flex-wrap gap-2">
                    @if(auth()->user()->can('submitForReview', $course))<form method="POST" action="{{ route('teacher.courses.publication.update', $course) }}">@csrf @method('PATCH')<input type="hidden" name="publication_status" value="pending_review"><button class="rounded-lg bg-green-600 px-4 py-2 text-xs font-black text-white">@lang('teacher-course::publishing.submit_review')</button></form>@endif
                    @if(auth()->user()->can('withdrawReview', $course))<form method="POST" action="{{ route('teacher.courses.publication.update', $course) }}">@csrf @method('PATCH')<input type="hidden" name="publication_status" value="draft"><button class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-black text-slate-700">@lang('teacher-course::publishing.withdraw_review')</button></form>@endif
                    @if(auth()->user()->can('publish', $course))<form method="POST" action="{{ route('teacher.courses.publication.update', $course) }}">@csrf @method('PATCH')<input type="hidden" name="publication_status" value="published"><button class="rounded-lg bg-green-600 px-4 py-2 text-xs font-black text-white">@lang('teacher-course::publishing.publish')</button></form>@endif
                    @if($course->publication_status === \Mindigo\TeacherCourse\Models\Course::PUBLICATION_PUBLISHED && auth()->user()->can('update', $course))<form method="POST" action="{{ route('teacher.courses.publication.update', $course) }}">@csrf @method('PATCH')<input type="hidden" name="publication_status" value="unlisted"><button class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-black text-slate-700">@lang('teacher-course::publishing.unlist')</button></form>@endif
                    @if(auth()->user()->can('archive', $course))<form method="POST" action="{{ route('teacher.courses.publication.update', $course) }}">@csrf @method('PATCH')<input type="hidden" name="publication_status" value="archived"><button class="rounded-lg border border-red-200 px-4 py-2 text-xs font-black text-red-600">@lang('teacher-course::publishing.archive')</button></form>@endif
                </div>
            </div>
        </section>

        @if($course->isPublished())
            <section class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div><h2 class="text-sm font-black text-slate-900">@lang('teacher-course::learning.assign_title')</h2><p class="mt-1 text-xs font-semibold text-slate-400">@lang('teacher-course::learning.assign_description')</p></div>
                </div>
                <form method="POST" action="{{ route('teacher.courses.assign', $course) }}" class="mt-4 grid items-end gap-3 lg:grid-cols-6">
                    @csrf
                    <label class="min-w-64 flex-1"><span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::learning.classrooms')</span><select name="classroom_ids[]" multiple required class="min-h-24 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 outline-none focus:border-green-400">@foreach($classrooms as $classroom)<option value="{{ $classroom->id }}">{{ $classroom->name }} · {{ trans_choice('teacher-course::learning.student_count', $classroom->students_count, ['count' => $classroom->students_count]) }}</option>@endforeach</select></label>
                    <label><span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::publishing.starts_at')</span><input type="date" name="starts_at" class="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm"></label>
                    <label><span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::publishing.due_at')</span><input type="date" name="due_at" class="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm"></label>
                    <label><span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::publishing.visibility')</span><select name="visibility" class="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm"><option value="visible">@lang('teacher-course::publishing.visible')</option><option value="hidden">@lang('teacher-course::publishing.hidden')</option></select></label>
                    <div><label class="mb-2 flex items-center gap-2 text-xs font-black text-slate-600"><input type="hidden" name="is_mandatory" value="0"><input type="checkbox" name="is_mandatory" value="1" checked class="h-4 w-4 accent-green-600">@lang('teacher-course::publishing.mandatory')</label><button type="submit" class="inline-flex h-10 items-center gap-2 rounded-lg bg-green-600 px-5 text-xs font-black text-white hover:bg-green-700"><x-heroicon-o-paper-airplane class="h-4 w-4" />@lang('teacher-course::learning.assign_action')</button></div>
                </form>
                @if($classrooms->isEmpty())<p class="mt-3 text-xs font-semibold text-slate-400">@lang('teacher-course::learning.no_classrooms')</p>@endif
            </section>
        @endif

        {{-- Stats bar --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-green-50 text-green-600">
                    <x-heroicon-o-squares-2x2 class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-course::app.stat_chapters')</p>
                    <p class="text-xl font-black text-slate-900">{{ $course->chapters->count() }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-sky-50 text-sky-600">
                    <x-heroicon-o-play class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-course::app.stat_lessons')</p>
                    <p class="text-xl font-black text-slate-900">{{ $course->chapters->flatMap->lessons->count() }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-violet-50 text-violet-600">
                    <x-heroicon-o-paper-clip class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-course::app.stat_assignments')</p>
                    <p class="text-xl font-black text-slate-900">
                        {{ $course->chapters->flatMap->lessons->filter(fn($l) => $l->assignment_id)->count() }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Curriculum --}}
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h2 class="text-sm font-black text-slate-900">@lang('teacher-course::app.curriculum')</h2>
                    <p class="text-xs text-slate-400 font-bold mt-0.5">@lang('teacher-course::app.curriculum_desc')</p>
                </div>
                <button type="button" data-mindigo-modal-open="add-chapter-modal"
                        class="inline-flex h-9 items-center gap-2 rounded-full bg-green-600 px-4 text-xs font-black text-white hover:bg-green-500 transition shadow-sm shadow-green-100">
                    <x-heroicon-o-plus class="h-4 w-4" /> @lang('teacher-course::app.add_chapter')
                </button>
            </div>

            @if($course->chapters->isEmpty())
                <div class="flex flex-col items-center justify-center gap-3 py-24">
                    <x-heroicon-o-squares-2x2 class="h-16 w-16 text-slate-200" />
                    <p class="text-base font-black text-slate-600">@lang('teacher-course::app.no_chapters')</p>
                    <p class="text-sm font-bold text-slate-400">@lang('teacher-course::app.no_chapters_desc')</p>
                    <button type="button" data-mindigo-modal-open="add-chapter-modal"
                            class="mt-2 inline-flex h-10 items-center gap-2 rounded-full bg-green-600 px-5 text-sm font-black text-white hover:bg-green-500">
                        <x-heroicon-o-plus class="h-4 w-4" /> @lang('teacher-course::app.add_chapter_btn')
                    </button>
                </div>
            @else
                <div class="divide-y divide-slate-100" data-course-curriculum data-reorder-url="{{ route('teacher.courses.curriculum.reorder', $course) }}" data-order-error="{{ __('teacher-course::publishing.order_failed') }}">
                    @foreach($course->chapters as $chapterIndex => $chapter)
                        <div class="chapter-block p-5" draggable="true" data-chapter-id="{{ $chapter->id }}">
                            {{-- Chapter header --}}
                            <div class="flex items-center justify-between gap-4 mb-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-green-100 text-xs font-black text-green-700">
                                        {{ $chapterIndex + 1 }}
                                    </span>
                                    <div>
                                        <h3 class="text-sm font-black text-slate-900">{{ $chapter->name }}</h3>
                                        <p class="text-[11px] text-slate-400 font-bold">{{ __('teacher-course::app.lessons_count', ['count' => $chapter->lessons->count()]) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button"
                                            data-course-chapter-edit
                                            data-chapter-id="{{ $chapter->id }}"
                                            data-chapter-name="{{ $chapter->name }}"
                                            data-update-url="{{ route('teacher.courses.chapters.update', [$course, ':chapter']) }}"
                                            class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50">
                                        <x-heroicon-o-pencil class="h-3.5 w-3.5" />
                                    </button>
                                    <a href="{{ route('teacher.courses.lessons.create', [$course, $chapter]) }}"
                                       class="inline-flex h-8 items-center gap-1 rounded-lg border border-green-200 bg-green-50 px-3 text-[11px] font-black text-green-700 no-underline hover:bg-green-100">
                                        <x-heroicon-o-plus class="h-3 w-3" /> @lang('teacher-course::app.add_lesson')
                                    </a>
                                    <form method="POST" action="{{ route('teacher.courses.chapters.destroy', [$course, $chapter]) }}"
                                          data-mindigo-confirm-title="{{ __('teacher-course::app.delete_chapter_title') }}"
                                          data-mindigo-confirm-message="{{ __('teacher-course::app.delete_chapter_confirm', ['name' => $chapter->name]) }}"
                                          data-mindigo-confirm-text="{{ __('teacher-course::app.delete_chapter_title') }}"
                                          data-mindigo-confirm-cancel="{{ __('teacher-course::app.cancel') }}"
                                          data-mindigo-confirm-type="danger">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="grid h-8 w-8 place-items-center rounded-lg border border-red-100 text-red-500 hover:bg-red-50">
                                            <x-heroicon-o-trash class="h-3.5 w-3.5" />
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Lessons list --}}
                            @if($chapter->lessons->isEmpty())
                                <div class="ml-11 rounded-xl border border-dashed border-slate-200 bg-slate-50 py-4 text-center">
                                    <p class="text-xs font-bold text-slate-400">@lang('teacher-course::app.no_lessons_in_chapter')</p>
                                </div>
                            @else
                                <div class="ml-11 divide-y divide-slate-100 rounded-2xl border border-slate-100 overflow-hidden" data-lesson-list>
                                    @foreach($chapter->lessons as $lessonIndex => $lesson)
                                        <div class="lesson-row flex items-center gap-3 bg-white px-4 py-3 hover:bg-slate-50" draggable="true" data-lesson-id="{{ $lesson->id }}">
                                            <span class="text-xs font-black text-slate-400 w-5 text-right">{{ $lessonIndex + 1 }}</span>
                                            <div class="flex flex-1 items-center gap-2 min-w-0">
                                                {{-- Type icons --}}
                                                @if($lesson->video_path)
                                                    <x-heroicon-o-play-circle class="h-4 w-4 shrink-0 text-sky-500" />
                                                @else
                                                    <x-heroicon-o-document-text class="h-4 w-4 shrink-0 text-slate-300" />
                                                @endif
                                                <span class="text-sm font-black text-slate-800 truncate">{{ $lesson->name }}</span>

                                                {{-- Badges --}}
                                                @if($lesson->video_path)
                                                    <span class="hidden sm:inline-flex rounded-full bg-sky-50 px-2 py-0.5 text-[10px] font-black text-sky-600">Video</span>
                                                @endif
                                                @if($lesson->attachment_paths && count($lesson->attachment_paths) > 0)
                                                    <span class="hidden sm:inline-flex rounded-full bg-orange-50 px-2 py-0.5 text-[10px] font-black text-orange-600">
                                                        {{ __('teacher-course::app.attachments_count', ['count' => count($lesson->attachment_paths)]) }}
                                                    </span>
                                                @endif
                                                @if($lesson->assignment)
                                                    <span class="hidden sm:inline-flex rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-black text-indigo-600">
                                                        @lang('teacher-course::app.assignment')
                                                    </span>
                                                @endif
                                                @if($lesson->prerequisite_lesson_id)
                                                    <span class="hidden sm:inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black text-slate-500">
                                                        <x-heroicon-o-lock-closed class="h-2.5 w-2.5" /> @lang('teacher-course::app.conditional')
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-1 shrink-0">
                                                <a href="{{ route('teacher.courses.lessons.edit', $lesson) }}"
                                                   class="grid h-7 w-7 place-items-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-100 no-underline">
                                                    <x-heroicon-o-pencil class="h-3 w-3" />
                                                </a>
                                                <form method="POST" action="{{ route('teacher.courses.lessons.destroy', $lesson) }}"
                                                      data-mindigo-confirm-title="{{ __('teacher-course::app.delete_lesson_title') }}"
                                                      data-mindigo-confirm-message="{{ __('teacher-course::app.delete_lesson_confirm', ['name' => $lesson->name]) }}"
                                                      data-mindigo-confirm-text="{{ __('teacher-course::app.delete') }}"
                                                      data-mindigo-confirm-cancel="{{ __('teacher-course::app.cancel') }}"
                                                      data-mindigo-confirm-type="danger">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                            class="grid h-7 w-7 place-items-center rounded-lg border border-red-100 text-red-400 hover:bg-red-50">
                                                        <x-heroicon-o-trash class="h-3 w-3" />
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

{{-- MODAL: ADD CHAPTER --}}
<div id="add-chapter-modal" data-mindigo-modal aria-hidden="true" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 modal-bg p-4">
    <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white shadow-xl overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-6 py-4">
            <h3 class="text-base font-black text-slate-900">@lang('teacher-course::app.add_chapter_modal_title')</h3>
            <button type="button" data-mindigo-modal-close="add-chapter-modal" class="text-slate-400 hover:text-slate-600">
                <x-heroicon-o-x-mark class="h-6 w-6" />
            </button>
        </div>
        <form method="POST" action="{{ route('teacher.courses.chapters.store', $course) }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::app.chapter_name_field') <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="{{ __('teacher-course::app.chapter_name_ph') }}"
                       class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 outline-none transition focus:border-green-400">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" data-mindigo-modal-close="add-chapter-modal"
                        class="inline-flex h-9 items-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-600 hover:bg-slate-50">@lang('teacher-course::app.cancel')</button>
                <button type="submit"
                        class="inline-flex h-9 items-center rounded-xl bg-green-600 px-5 text-xs font-black text-white hover:bg-green-500 shadow-sm">@lang('teacher-course::app.add_chapter_btn')</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: EDIT CHAPTER --}}
<div id="edit-chapter-modal" data-mindigo-modal aria-hidden="true" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 modal-bg p-4">
    <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white shadow-xl overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-6 py-4">
            <h3 class="text-base font-black text-slate-900">@lang('teacher-course::app.edit_chapter_modal_title')</h3>
            <button type="button" data-mindigo-modal-close="edit-chapter-modal" class="text-slate-400 hover:text-slate-600">
                <x-heroicon-o-x-mark class="h-6 w-6" />
            </button>
        </div>
        <form id="edit-chapter-form" method="POST" action="" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::app.chapter_name_field') <span class="text-red-500">*</span></label>
                <input type="text" id="edit-chapter-name" name="name" required
                       class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 outline-none transition focus:border-green-400">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" data-mindigo-modal-close="edit-chapter-modal"
                        class="inline-flex h-9 items-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-600 hover:bg-slate-50">@lang('teacher-course::app.cancel')</button>
                <button type="submit"
                        class="inline-flex h-9 items-center rounded-xl bg-green-600 px-5 text-xs font-black text-white hover:bg-green-500 shadow-sm">@lang('teacher-course::app.save_changes')</button>
            </div>
        </form>
    </div>
</div>

@endsection
