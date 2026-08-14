@extends('Mindigo-dashboard::layouts')

@section('title', $assignment->title . ' - Auronsoft LMS')

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="min-h-screen bg-slate-50">
    <header class="flex items-center gap-4 border-b border-slate-200 bg-white px-7 py-5">
        <a href="{{ route('student.assignments.index') }}" class="grid h-10 w-10 shrink-0 place-items-center rounded-lg border border-slate-200 text-slate-500 hover:text-green-700" aria-label="{{ __('student-assignment::app.back') }}">
            <x-heroicon-o-arrow-left class="h-5 w-5" />
        </a>
        <div class="min-w-0">
            <p class="text-xs font-black uppercase text-green-600">{{ $assignment->classroom->name }}</p>
            <h1 class="truncate text-2xl font-black text-slate-950">{{ $assignment->title }}</h1>
        </div>
    </header>

    @if(session('success'))
        <div class="mx-auto max-w-7xl px-7 pt-4 max-md:px-4">
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-bold text-green-700">
                {{ session('success') }}
            </div>
        </div>
    @endif

    <div class="mx-auto grid max-w-7xl gap-5 p-7 max-md:p-4 lg:grid-cols-[minmax(0,1fr)_23rem]">
        <section class="space-y-5">
            {{-- Đề bài --}}
            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-black uppercase text-slate-400">{{ __('student-assignment::app.section.assignment_brief') }}</h2>
                <div class="mt-4 flex flex-wrap gap-3 border-b border-slate-100 pb-5 text-xs font-black">
                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-600">{{ __('student-assignment::app.due_date') }}: {{ $assignment->due_date->format('d/m/Y H:i') }}</span>
                    <span class="rounded-full bg-blue-50 px-3 py-1.5 text-blue-700">{{ __('student-assignment::app.max_score') }}: {{ $assignment->max_score }}/10</span>
                    @if($assignment->allow_late)<span class="rounded-full bg-amber-50 px-3 py-1.5 text-amber-700">{{ __('student-assignment::app.late_allowed') }}</span>@endif
                </div>
                <h3 class="mt-5 text-sm font-black uppercase text-slate-400">{{ __('student-assignment::app.description') }}</h3>
                <div class="mt-3 whitespace-pre-line text-sm font-medium leading-7 text-slate-700">{{ $assignment->description ?: __('student-assignment::app.no_description') }}</div>

                @if(!empty($assignment->file_path))
                    <div class="mt-6 border-t border-slate-100 pt-5">
                        <h3 class="text-sm font-black text-slate-900">{{ __('student-assignment::app.assignment_files') }}</h3>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach($assignment->file_path as $fileIndex => $path)
                                <a target="_blank" href="{{ route('student.assignments.files.show', [$assignment, $fileIndex]) }}" class="flex items-center gap-3 rounded-lg border border-slate-200 p-3 text-sm font-bold text-slate-700 no-underline hover:border-green-300 hover:text-green-700">
                                    <x-heroicon-o-document-text class="h-5 w-5 shrink-0" /><span class="truncate">{{ basename($path) }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Bài làm đã nộp --}}
            @if($submission)
                <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-black uppercase text-slate-400">{{ __('student-assignment::app.section.student_work') }}</h2>
                    <p class="mt-2 text-xs font-bold text-slate-400">
                        {{ __('student-assignment::app.submitted_at') }}: {{ $submission->submitted_at?->format('d/m/Y H:i') }}
                        @if($submission->is_late)
                            <span class="ml-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-black uppercase text-amber-700">{{ __('student-assignment::app.late_submission') }}</span>
                        @endif
                    </p>

                    @if($submission->hasFile())
                        <div class="mt-4">
                            <h3 class="text-sm font-black text-slate-900">{{ __('student-assignment::app.submission_file') }}</h3>
                            <a target="_blank" href="{{ route('student.assignments.submission-file.show', $assignment) }}" class="mt-2 flex items-center gap-3 rounded-lg border border-slate-200 p-3 text-sm font-bold text-slate-700 no-underline hover:border-green-300 hover:text-green-700">
                                <x-heroicon-o-paper-clip class="h-5 w-5 shrink-0" /><span class="truncate">{{ $submission->file_original_name ?: basename($submission->file_path) }}</span>
                            </a>
                        </div>
                    @endif

                    @if($submission->hasText())
                        <div class="mt-4">
                            <h3 class="text-sm font-black text-slate-900">{{ __('student-assignment::app.text_content') }}</h3>
                            <div class="mt-2 whitespace-pre-line rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm font-medium leading-7 text-slate-700">{{ $submission->text_content }}</div>
                        </div>
                    @endif
                </div>
            @endif
        </section>

        <aside class="space-y-5 lg:sticky lg:top-5 lg:self-start">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-black">{{ __('student-assignment::app.submit_assignment') }}</h2>

            @if($canSubmit)
                <form action="{{ route('student.assignments.submit', $assignment) }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4" id="submission-form">
                    @csrf

                    @if($assignment->submission_type === 'both')
                        <div class="space-y-2">
                            <p class="text-sm font-black text-slate-800">{{ __('student-assignment::app.submission_method_label') }}</p>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-slate-200 px-3 py-2.5 text-xs font-black text-slate-600 has-checked:border-green-500 has-checked:bg-green-50 has-checked:text-green-700">
                                    <input type="radio" name="submission_method" value="file" class="sr-only" @checked(old('submission_method', 'file') === 'file')>
                                    <x-heroicon-o-paper-clip class="h-4 w-4" />
                                    {{ __('student-assignment::app.submission_type.file') }}
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-slate-200 px-3 py-2.5 text-xs font-black text-slate-600 has-checked:border-green-500 has-checked:bg-green-50 has-checked:text-green-700">
                                    <input type="radio" name="submission_method" value="text" class="sr-only" @checked(old('submission_method') === 'text')>
                                    <x-heroicon-o-pencil class="h-4 w-4" />
                                    {{ __('student-assignment::app.submission_type.text') }}
                                </label>
                            </div>
                            @error('submission_method')<p class="text-xs font-bold text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @endif

                    @if($assignment->allowsFile())
                        <div data-submission-panel="file" @if($assignment->submission_type === 'both' && old('submission_method', 'file') === 'text') hidden @endif>
                            <label for="submission_file" class="text-sm font-black text-slate-800">{{ __('student-assignment::app.submission_file') }}</label>
                            <input id="submission_file" name="submission_file" type="file" class="mt-2 block w-full rounded-lg border border-slate-200 text-xs font-bold file:mr-3 file:border-0 file:bg-slate-100 file:px-3 file:py-3 file:font-black" accept=".pdf,.doc,.docx,.zip,.rar,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png">
                            @error('submission_file')<p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @endif

                    @if($assignment->allowsText())
                        <div data-submission-panel="text" @if($assignment->submission_type === 'both' && old('submission_method', 'file') !== 'text') hidden @endif>
                            <label for="text_content" class="text-sm font-black text-slate-800">{{ __('student-assignment::app.text_content') }}</label>
                            <textarea id="text_content" name="text_content" rows="8" class="mt-2 w-full resize-y rounded-lg border border-slate-200 p-3 text-sm font-medium outline-none focus:border-green-500" placeholder="{{ __('student-assignment::app.text_placeholder') }}">{{ old('text_content') }}</textarea>
                            @error('text_content')<p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @endif

                    <button type="submit" class="min-h-11 w-full rounded-lg bg-green-600 px-5 text-sm font-black text-white hover:bg-green-700">{{ __('student-assignment::app.submit_now') }}</button>
                </form>
            @else
                <div class="mt-5 rounded-lg bg-slate-100 p-4 text-sm font-bold text-slate-600">
                    @if($submission?->isGraded())
                        {{ __('student-assignment::app.locked_graded') }}
                    @elseif($submission)
                        {{ __('student-assignment::app.locked_submitted') }}
                    @else
                        {{ __('student-assignment::app.closed') }}
                    @endif
                </div>
            @endif
            </div>

            @if($submission?->isGraded())
                <div class="rounded-lg border border-blue-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase text-blue-600">{{ __('student-assignment::app.result') }}</p>
                    <p class="mt-2 text-4xl font-black text-slate-950">{{ rtrim(rtrim(number_format($submission->score, 2), '0'), '.') }}<span class="text-lg text-slate-400">/{{ $assignment->max_score }}</span></p>
                    <p class="mt-5 text-sm font-black text-slate-900">{{ __('student-assignment::app.feedback') }}</p>
                    <p class="mt-2 whitespace-pre-line text-sm font-medium leading-6 text-slate-600">{{ $submission->feedback ?: __('student-assignment::app.no_feedback') }}</p>
                </div>
            @endif
        </aside>
    </div>
</div>

@if($assignment->submission_type === 'both' && $canSubmit)
<script>
document.querySelectorAll('#submission-form input[name="submission_method"]').forEach((radio) => {
    radio.addEventListener('change', () => {
        const method = document.querySelector('#submission-form input[name="submission_method"]:checked')?.value;
        document.querySelectorAll('[data-submission-panel]').forEach((panel) => {
            panel.hidden = panel.dataset.submissionPanel !== method;
        });
        if (method === 'file') {
            document.getElementById('text_content').value = '';
        } else {
            document.getElementById('submission_file').value = '';
        }
    });
});
</script>
@endif
@endsection
