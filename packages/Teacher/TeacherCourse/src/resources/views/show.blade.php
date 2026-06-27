@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-course::app.course_management_title') . ' — ' . $course->name)

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
    <style>
        .chapter-block { transition: box-shadow 0.15s; }
        .chapter-block:hover { box-shadow: 0 4px 24px 0 rgba(16,185,129,0.07); }
        .lesson-row { transition: background 0.1s; }
        .modal-bg { backdrop-filter: blur(4px); }
    </style>
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
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">
                    @lang('teacher-course::app.courses') · {{ __('teacher-course::app.' . $course->status) }}
                </p>
                <h1 class="mt-0.5 text-lg font-black text-slate-950">{{ $course->name }}</h1>
            </div>
        </div>
        <div class="flex items-center gap-2">
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

        {{-- Stats bar --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm flex items-center gap-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-green-50 text-green-600">
                    <x-heroicon-o-squares-2x2 class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-course::app.stat_chapters')</p>
                    <p class="text-xl font-black text-slate-900">{{ $course->chapters->count() }}</p>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm flex items-center gap-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-sky-50 text-sky-600">
                    <x-heroicon-o-play class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-course::app.stat_lessons')</p>
                    <p class="text-xl font-black text-slate-900">{{ $course->chapters->flatMap->lessons->count() }}</p>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm flex items-center gap-3">
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
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h2 class="text-sm font-black text-slate-900">@lang('teacher-course::app.curriculum')</h2>
                    <p class="text-xs text-slate-400 font-bold mt-0.5">@lang('teacher-course::app.curriculum_desc')</p>
                </div>
                <button onclick="openModal('add-chapter-modal')"
                        class="inline-flex h-9 items-center gap-2 rounded-full bg-green-600 px-4 text-xs font-black text-white hover:bg-green-500 transition shadow-sm shadow-green-100">
                    <x-heroicon-o-plus class="h-4 w-4" /> @lang('teacher-course::app.add_chapter')
                </button>
            </div>

            @if($course->chapters->isEmpty())
                <div class="flex flex-col items-center justify-center gap-3 py-24">
                    <x-heroicon-o-squares-2x2 class="h-16 w-16 text-slate-200" />
                    <p class="text-base font-black text-slate-600">@lang('teacher-course::app.no_chapters')</p>
                    <p class="text-sm font-bold text-slate-400">@lang('teacher-course::app.no_chapters_desc')</p>
                    <button onclick="openModal('add-chapter-modal')"
                            class="mt-2 inline-flex h-10 items-center gap-2 rounded-full bg-green-600 px-5 text-sm font-black text-white hover:bg-green-500">
                        <x-heroicon-o-plus class="h-4 w-4" /> @lang('teacher-course::app.add_chapter_btn')
                    </button>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($course->chapters as $chapterIndex => $chapter)
                        <div class="chapter-block p-5">
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
                                    <button onclick='openEditChapterModal(@json($chapter))'
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
                                <div class="ml-11 divide-y divide-slate-100 rounded-2xl border border-slate-100 overflow-hidden">
                                    @foreach($chapter->lessons as $lessonIndex => $lesson)
                                        <div class="lesson-row flex items-center gap-3 bg-white px-4 py-3 hover:bg-slate-50">
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
<div id="add-chapter-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 modal-bg p-4" style="display:none">
    <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white shadow-xl overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-6 py-4">
            <h3 class="text-base font-black text-slate-900">@lang('teacher-course::app.add_chapter_modal_title')</h3>
            <button onclick="closeModal('add-chapter-modal')" class="text-slate-400 hover:text-slate-600">
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
                <button type="button" onclick="closeModal('add-chapter-modal')"
                        class="inline-flex h-9 items-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-600 hover:bg-slate-50">@lang('teacher-course::app.cancel')</button>
                <button type="submit"
                        class="inline-flex h-9 items-center rounded-xl bg-green-600 px-5 text-xs font-black text-white hover:bg-green-500 shadow-sm">@lang('teacher-course::app.add_chapter_btn')</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: EDIT CHAPTER --}}
<div id="edit-chapter-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 modal-bg p-4" style="display:none">
    <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white shadow-xl overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-6 py-4">
            <h3 class="text-base font-black text-slate-900">@lang('teacher-course::app.edit_chapter_modal_title')</h3>
            <button onclick="closeModal('edit-chapter-modal')" class="text-slate-400 hover:text-slate-600">
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
                <button type="button" onclick="closeModal('edit-chapter-modal')"
                        class="inline-flex h-9 items-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-600 hover:bg-slate-50">@lang('teacher-course::app.cancel')</button>
                <button type="submit"
                        class="inline-flex h-9 items-center rounded-xl bg-green-600 px-5 text-xs font-black text-white hover:bg-green-500 shadow-sm">@lang('teacher-course::app.save_changes')</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    const el = document.getElementById(id);
    if (el) { el.style.display = 'flex'; }
}
function closeModal(id) {
    const el = document.getElementById(id);
    if (el) { el.style.display = 'none'; }
}
function openEditChapterModal(chapter) {
    const baseUrl = "{{ route('teacher.courses.chapters.update', [$course, ':chapter']) }}";
    document.getElementById('edit-chapter-form').action = baseUrl.replace(':chapter', chapter.id);
    document.getElementById('edit-chapter-name').value = chapter.name;
    openModal('edit-chapter-modal');
}
// Close modals on backdrop click
document.querySelectorAll('[id$="-modal"]').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal(modal.id);
    });
});
</script>
@endsection
