@extends('Mindigo-dashboard::layouts')
@section('title', __('teacher-assignment::app.assignment.edit'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Teacher/TeacherAssignment/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">

    {{-- Header --}}
    <header class="sticky top-0 z-10 flex items-center gap-4 border-b border-slate-200 bg-white/90 px-6 py-4 backdrop-blur">
        <a href="{{ route('teacher.assignments.index') }}"
           class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50">
            <x-heroicon-o-arrow-left class="h-5 w-5" />
        </a>
        <div>
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">@lang('teacher-assignment::app.assignment.title')</p>
            <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('teacher-assignment::app.assignment.edit')</h1>
        </div>
    </header>

    <div class="mx-auto w-full max-w-7xl px-6 py-4">
        <form action="{{ route('teacher.assignments.update', $assignment) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 gap-5 lg:grid-cols-12">
            @csrf
            @method('PUT')

            {{-- Thông tin cơ bản --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-4 lg:col-span-7">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 pb-3">@lang('teacher-assignment::app.assignment.section_basic_info')</h2>

                <div class="space-y-1">
                    <label class="text-sm font-bold text-slate-700">@lang('teacher-assignment::app.assignment.field_title') <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $assignment->title) }}"
                           class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 outline-none focus:border-green-300 focus:ring-2 focus:ring-green-50"
                           placeholder="{{ __('teacher-assignment::app.assignment.title_placeholder') }}">
                    @error('title')<p class="text-xs font-semibold text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-bold text-slate-700">@lang('teacher-assignment::app.assignment.field_desc')</label>
                    <textarea name="description" rows="5"
                              class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 outline-none focus:border-green-300 focus:ring-2 focus:ring-green-50"
                              placeholder="{{ __('teacher-assignment::app.assignment.description_placeholder') }}">{{ old('description', $assignment->description) }}</textarea>
                </div>

                <div class="space-y-3">
                    <label class="text-sm font-bold text-slate-700 block">@lang('teacher-assignment::app.assignment.field_file')</label>
                    @if($assignment->file_path)
                        <div class="space-y-2 rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <p class="text-xs font-black uppercase tracking-wider text-slate-400">@lang('teacher-assignment::app.assignment.current_file')</p>
                            <div class="grid gap-2 sm:grid-cols-2">
                                @foreach($assignment->file_path as $fileIndex => $filePath)
                                    <div data-existing-file-card class="relative min-w-0 rounded-xl border border-slate-200 bg-white px-3 py-2 pr-9 text-sm font-bold text-slate-700 shadow-xs">
                                        <span class="block truncate">{{ basename($filePath) }}</span>
                                        <a href="{{ route('teacher.assignments.files.show', [$assignment, $fileIndex]) }}" target="_blank" class="mt-1 inline-block text-xs font-black text-green-700 underline hover:text-green-800">
                                            @lang('teacher-assignment::app.assignment.view_file')
                                        </a>
                                        <input type="checkbox" name="remove_files[]" value="{{ $filePath }}" class="hidden">
                                        <button type="button" data-remove-existing-file class="absolute right-2 top-2 grid h-5 w-5 place-items-center rounded-full bg-red-50 text-xs font-black text-red-600 hover:bg-red-100" aria-label="Xoa file">&times;</button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <div class="rounded-xl border border-slate-200 bg-white p-3">
                        <input type="file" name="files[]" multiple
                               class="js-assignment-files block w-full text-sm font-bold text-slate-700 file:mr-4 file:rounded-full file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-xs file:font-black file:text-slate-700 hover:file:bg-slate-200"
                               accept=".pdf,.docx,.doc,.zip,.rar,.xlsx,.xls,.pptx,.ppt,.jpg,.jpeg,.png"
                               data-preview="selected-assignment-files">
                        <div id="selected-assignment-files" class="mt-3 hidden grid gap-2 sm:grid-cols-2"></div>
                    </div>
                    
                    @error('files')<p class="text-xs font-semibold text-red-500 mt-1">{{ $message }}</p>@enderror
                    @error('files.*')<p class="text-xs font-semibold text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </section>

            {{-- Lớp học & Điểm & Hạn nộp --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-4 lg:col-span-5">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 pb-3">@lang('teacher-assignment::app.assignment.section_classroom_deadline')</h2>

                <div class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-1">
                            <label class="text-sm font-bold text-slate-700">@lang('teacher-assignment::app.assignment.field_classroom') <span class="text-red-500">*</span></label>
                            <select name="classroom_id"
                                    class="block w-full h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none focus:border-green-300">
                                <option value="">@lang('teacher-assignment::app.assignment.classroom_select_placeholder')</option>
                                @foreach($classrooms as $class)
                                    <option value="{{ $class->id }}" {{ old('classroom_id', $assignment->classroom_id) == $class->id ? 'selected' : '' }}>
                                        {{ __('teacher-assignment::app.assignment.classroom_option', ['name' => $class->name, 'code' => $class->code, 'count' => $class->students_count]) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('classroom_id')<p class="text-xs font-semibold text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-bold text-slate-700">@lang('teacher-assignment::app.assignment.field_max_score') <span class="text-red-500">*</span></label>
                            <input type="number" name="max_score" value="{{ old('max_score', $assignment->max_score) }}"
                                   class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 outline-none focus:border-green-300 focus:ring-2 focus:ring-green-50"
                                   min="1" max="10" step="1">
                            @error('max_score')<p class="text-xs font-semibold text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-bold text-slate-700">@lang('teacher-assignment::app.assignment.field_due_date') <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="due_date" value="{{ old('due_date', $assignment->due_date ? $assignment->due_date->format('Y-m-d\TH:i') : '') }}"
                               class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 outline-none focus:border-green-300 focus:ring-2 focus:ring-green-50">
                        @error('due_date')<p class="text-xs font-semibold text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700 block">@lang('teacher-assignment::app.assignment.late_submission_label')</label>
                        <div class="flex items-center gap-4 mt-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="allow_late" id="allowLate" value="1"
                                       {{ old('allow_late', $assignment->allow_late) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500"
                                       data-assignment-late-toggle="#lateDaysWrap">
                                <span class="text-sm font-bold text-slate-600">@lang('teacher-assignment::app.assignment.allow_late')</span>
                            </label>

                            <div id="lateDaysWrap" class="flex items-center gap-2 {{ old('allow_late', $assignment->allow_late) ? '' : 'hidden' }}">
                                <input type="number" name="late_days" value="{{ old('late_days', $assignment->late_days ?? 3) }}"
                                       class="h-9 w-20 rounded-xl border border-slate-200 text-center text-sm font-bold text-slate-700 outline-none focus:border-green-300" min="1" max="30">
                                <span class="text-xs font-bold text-slate-400">@lang('teacher-assignment::app.assignment.late_days')</span>
                            </div>
                        </div>
                        @error('late_days')<p class="text-xs font-semibold text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            {{-- Hình thức nộp bài --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-4 lg:col-span-12">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 pb-3">@lang('teacher-assignment::app.assignment.section_submission_type')</h2>

                <div class="grid gap-6 md:grid-cols-3">
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-sm font-bold text-slate-700">@lang('teacher-assignment::app.assignment.submission_question') <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-3 gap-3 mt-1">
                            <label class="submission-type-card flex flex-col items-center justify-center rounded-xl border-2 p-4 text-center cursor-pointer transition hover:border-green-500 hover:bg-green-50/50 {{ old('submission_type', $assignment->submission_type) === 'file' ? 'border-green-600 bg-green-50' : 'border-slate-200 bg-white' }}" data-value="file">
                                <input type="radio" name="submission_type" value="file" class="hidden" {{ old('submission_type', $assignment->submission_type) === 'file' ? 'checked' : '' }}>
                                <span class="mt-1 block text-sm font-black text-slate-900">@lang('teacher-assignment::app.assignment.sub_type_file')</span>
                                <span class="text-[10px] font-bold text-slate-400 leading-normal">@lang('teacher-assignment::app.assignment.sub_type_file_desc')</span>
                            </label>

                            <label class="submission-type-card flex flex-col items-center justify-center rounded-xl border-2 p-4 text-center cursor-pointer transition hover:border-green-500 hover:bg-green-50/50 {{ old('submission_type', $assignment->submission_type) === 'text' ? 'border-green-600 bg-green-50' : 'border-slate-200 bg-white' }}" data-value="text">
                                <input type="radio" name="submission_type" value="text" class="hidden" {{ old('submission_type', $assignment->submission_type) === 'text' ? 'checked' : '' }}>
                                <span class="mt-1 block text-sm font-black text-slate-900">@lang('teacher-assignment::app.assignment.sub_type_text')</span>
                                <span class="text-[10px] font-bold text-slate-400 leading-normal">@lang('teacher-assignment::app.assignment.sub_type_text_desc')</span>
                            </label>

                            <label class="submission-type-card flex flex-col items-center justify-center rounded-xl border-2 p-4 text-center cursor-pointer transition hover:border-green-500 hover:bg-green-50/50 {{ old('submission_type', $assignment->submission_type) === 'both' ? 'border-green-600 bg-green-50' : 'border-slate-200 bg-white' }}" data-value="both">
                                <input type="radio" name="submission_type" value="both" class="hidden" {{ old('submission_type', $assignment->submission_type) === 'both' ? 'checked' : '' }}>
                                <span class="mt-1 block text-sm font-black text-slate-900">@lang('teacher-assignment::app.assignment.sub_type_both')</span>
                                <span class="text-[10px] font-bold text-slate-400 leading-normal">@lang('teacher-assignment::app.assignment.sub_type_both_desc')</span>
                            </label>
                        </div>
                        @error('submission_type')<p class="text-xs font-semibold text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-bold text-slate-700">@lang('teacher-assignment::app.assignment.status_field_label')</label>
                        <select name="status"
                                class="block w-full h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none focus:border-green-300">
                            <option value="draft"     {{ old('status', $assignment->status) === 'draft'     ? 'selected' : '' }}>@lang('teacher-assignment::app.assignment.status_draft')</option>
                            <option value="published" {{ old('status', $assignment->status) === 'published' ? 'selected' : '' }}>@lang('teacher-assignment::app.assignment.status_published')</option>
                        </select>
                    </div>
                </div>
            </section>

            <div class="flex items-center justify-end gap-3 lg:col-span-12">
                <a href="{{ route('teacher.assignments.index') }}"
                   class="inline-flex h-11 items-center justify-center rounded-full border border-slate-200 bg-white px-6 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50">
                    @lang('teacher-assignment::app.cancel')
                </a>
                <button type="submit"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-green-600 px-8 text-sm font-black text-white shadow-sm shadow-green-200 transition hover:bg-green-500">
                    @lang('teacher-assignment::app.assignment.save_assignment')
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
