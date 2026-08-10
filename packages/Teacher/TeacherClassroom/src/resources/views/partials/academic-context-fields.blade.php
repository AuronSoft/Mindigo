@php
    $selectedType = old('type', $classroom?->type ?? \Mindigo\TeacherClassroom\Models\Classroom::TYPE_STANDALONE);
    $selectedCourseId = old('course_id', $classroom?->course_id);
    $selectedSubjectId = old('subject_id', $classroom?->subject_id ?? $classroom?->subjects->first()?->id);
@endphp

<fieldset class="space-y-4" data-classroom-academic-context>
    <div>
        <legend class="text-xs font-black text-slate-600">@lang('teacher-classroom::app.classroom_type') <span class="text-red-500">*</span></legend>
        <p class="mt-1 text-xs font-medium text-slate-400">@lang('teacher-classroom::app.classroom_type_hint')</p>
        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            @foreach([\Mindigo\TeacherClassroom\Models\Classroom::TYPE_STANDALONE, \Mindigo\TeacherClassroom\Models\Classroom::TYPE_COURSE] as $type)
                <label class="cursor-pointer rounded-2xl border border-slate-200 p-4 transition has-checked:border-green-500 has-checked:bg-green-50 has-checked:ring-1 has-checked:ring-green-500">
                    <span class="flex items-start gap-3">
                        <input type="radio" name="type" value="{{ $type }}" @checked($selectedType === $type) class="mt-0.5 h-4 w-4 accent-green-600" data-classroom-type>
                        <span><strong class="block text-sm font-black text-slate-800">@lang('teacher-classroom::app.type_' . $type)</strong><span class="mt-1 block text-xs font-medium leading-5 text-slate-500">@lang('teacher-classroom::app.type_' . $type . '_hint')</span></span>
                    </span>
                </label>
            @endforeach
        </div>
        @error('type')<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
    </div>

    <div data-context-field="standalone" @class(['hidden' => $selectedType !== 'standalone'])>
        <label for="classroom-subject" class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-classroom::app.primary_subject') <span class="text-red-500">*</span></label>
        @php($selectedSubject = $subjects->firstWhere('id', (int) $selectedSubjectId))
        <div class="relative" data-classroom-subject-picker>
            <select id="classroom-subject" name="subject_id" required @disabled($selectedType !== 'standalone') data-classroom-subject-select class="sr-only" tabindex="-1" aria-hidden="true">
                <option value="">@lang('teacher-classroom::app.choose_subject')</option>
                @foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected((string) $selectedSubjectId === (string) $subject->id)>{{ $subject->name }}</option>@endforeach
            </select>
            <button type="button" data-classroom-subject-trigger aria-haspopup="listbox" aria-expanded="false" class="flex h-12 w-full items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 text-left text-sm font-bold text-slate-700 outline-none transition hover:border-green-300 focus:border-green-400 focus:ring-2 focus:ring-green-50">
                <span data-classroom-subject-label class="truncate {{ $selectedSubject ? '' : 'text-slate-400' }}">{{ $selectedSubject?->name ?? __('teacher-classroom::app.choose_subject') }}</span>
                <x-heroicon-o-chevron-down class="h-4 w-4 shrink-0 text-slate-400" />
            </button>
            <div data-classroom-subject-panel class="absolute left-0 right-0 top-full z-40 mt-2 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10">
                <div class="border-b border-slate-100 p-2.5">
                    <div class="relative"><x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" /><input type="search" data-classroom-subject-search autocomplete="off" placeholder="@lang('teacher-classroom::app.search_subject')" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 text-sm font-semibold outline-none focus:border-green-400 focus:bg-white"></div>
                </div>
                <div role="listbox" class="max-h-56 overflow-y-auto p-1.5">
                    @foreach($subjects as $subject)<button type="button" role="option" data-classroom-subject-option data-value="{{ $subject->id }}" data-label="{{ $subject->name }}" class="flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-left text-sm font-bold text-slate-700 transition hover:bg-green-50 hover:text-green-700"><span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $subject->color ?? '#16a34a' }}"></span>{{ $subject->name }}</button>@endforeach
                    <p data-classroom-subject-empty class="hidden px-3 py-6 text-center text-sm font-semibold text-slate-400">@lang('teacher-classroom::app.subject_search_empty')</p>
                </div>
            </div>
        </div>
        <p class="mt-1.5 text-xs font-medium text-slate-400">@lang('teacher-classroom::app.single_subject_hint')</p>
        @error('subject_id')<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
    </div>

    <div data-context-field="course" @class(['hidden' => $selectedType !== 'course'])>
        <label for="classroom-course" class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-classroom::app.linked_course') <span class="text-red-500">*</span></label>
        <select id="classroom-course" name="course_id" @disabled($selectedType !== 'course') class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-800 outline-none transition focus:border-green-400 focus:ring-2 focus:ring-green-50">
            <option value="">@lang('teacher-classroom::app.choose_course')</option>
            @foreach($courses as $course)<option value="{{ $course->id }}" @selected((string) $selectedCourseId === (string) $course->id)>{{ $course->name }} · {{ $course->subject->name }}</option>@endforeach
        </select>
        <p class="mt-1.5 text-xs font-medium text-slate-400">@lang('teacher-classroom::app.course_subject_inherited')</p>
        @if($courses->isEmpty())
            <p class="mt-2 rounded-xl bg-amber-50 px-3 py-2 text-xs font-semibold leading-5 text-amber-700">@lang('teacher-classroom::app.no_eligible_courses')</p>
        @endif
        @error('course_id')<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
    </div>
</fieldset>

@once
    <script>
        document.addEventListener('change', (event) => {
            if (!event.target.matches('[data-classroom-type]')) return;

            const context = event.target.closest('[data-classroom-academic-context]');
            context?.querySelectorAll('[data-context-field]').forEach((field) => {
                const active = field.dataset.contextField === event.target.value;
                field.classList.toggle('hidden', !active);
                field.querySelectorAll('select, input').forEach((control) => control.disabled = !active);
            });
        });
    </script>
@endonce
