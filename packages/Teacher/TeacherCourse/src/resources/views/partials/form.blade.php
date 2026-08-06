@php
    $editing = isset($course) && $course->exists;
    $selectedScheduleDays = old('schedule_days', $course->schedule_days ?? []);
    $tabs = [
        'overview' => __('teacher-course::app.form_tabs.overview'),
        'schedule' => __('teacher-course::app.form_tabs.schedule'),
        'content' => __('teacher-course::app.form_tabs.content'),
        'media' => __('teacher-course::app.form_tabs.media'),
    ];
    $timeRange = old('study_time', $course->study_time ?? '');
    [$studyTimeStart, $studyTimeEnd] = str_contains($timeRange, ' - ')
        ? explode(' - ', $timeRange, 2)
        : ['', ''];
    $timeOptions = [];
    for ($hour = 5; $hour <= 23; $hour++) {
        foreach ([0, 30] as $minute) {
            $timeOptions[] = sprintf('%02d:%02d', $hour, $minute);
        }
    }
@endphp

<div data-course-form-tabs data-course-create-wizard="{{ $editing ? '0' : '1' }}" class="min-h-0 flex-1 space-y-4">
    <nav class="flex gap-2 overflow-x-auto rounded-2xl border border-slate-200 bg-slate-50 p-1" aria-label="@lang('teacher-course::app.form_navigation')">
        @foreach($tabs as $tabKey => $tabLabel)
            <button type="button"
                    data-course-form-tab="{{ $tabKey }}"
                    data-course-form-tab-index="{{ $loop->index }}"
                    @if(! $editing && ! $loop->first) disabled @endif
                    class="shrink-0 rounded-xl px-4 py-2 text-xs font-black transition {{ $loop->first ? 'bg-white text-green-700 shadow-sm' : 'text-slate-500 hover:text-slate-800' }} {{ ! $editing && ! $loop->first ? 'cursor-not-allowed opacity-50' : '' }}">
                {{ $tabLabel }}
            </button>
        @endforeach
    </nav>

    <section data-course-form-panel="overview" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4">
        <div>
            <label class="mb-1.5 block text-xs font-black text-slate-600">
                @lang('teacher-course::app.course_name_field') <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" value="{{ old('name', $course->name ?? '') }}"
                   placeholder="@lang('teacher-course::app.course_name_ph')" required
                   class="w-full rounded-2xl border {{ $errors->has('name') ? 'border-red-300 bg-red-50' : 'border-slate-200 bg-white' }} px-4 py-2.5 text-sm font-bold text-slate-800 outline-none transition focus:border-green-400 focus:ring-2 focus:ring-green-50">
            @error('name')<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-3 md:grid-cols-2">
            <div>
                @include('teacher-course::partials.master-data-picker', ['name' => 'subject_id', 'label' => __('teacher-course::app.subject_field'), 'searchPlaceholder' => __('teacher-course::app.subject_search_placeholder'), 'items' => $subjects, 'selected' => $course->subject_id ?? ''])
                @error('subject_id')<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                @include('teacher-course::partials.master-data-picker', ['name' => 'category_id', 'label' => __('teacher-course::app.category_field'), 'searchPlaceholder' => __('teacher-course::app.category_search_placeholder'), 'items' => $categories, 'selected' => $course->category_id ?? ''])
                @error('category_id')<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div>
                <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::app.education_level_field')</label>
                <select name="education_level" class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700">
                    <option value="">@lang('teacher-course::app.not_selected')</option>
                    @foreach(\Mindigo\TeacherCourse\Models\Course::EDUCATION_LEVELS as $level)
                        <option value="{{ $level }}" @selected(old('education_level', $course->education_level ?? '') === $level)>@lang('teacher-course::app.education_levels.'.$level)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::app.difficulty_field')</label>
                <select name="difficulty" required class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700">
                    @foreach(\Mindigo\TeacherCourse\Models\Course::DIFFICULTIES as $difficulty)
                        <option value="{{ $difficulty }}" @selected(old('difficulty', $course->difficulty ?? 'beginner') === $difficulty)>@lang('teacher-course::app.difficulties.'.$difficulty)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::app.language_field')</label>
                <select name="language" required class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700">
                    <option value="vi" @selected(old('language', $course->language ?? 'vi') === 'vi')>@lang('teacher-course::app.languages.vi')</option>
                    <option value="en" @selected(old('language', $course->language ?? 'vi') === 'en')>@lang('teacher-course::app.languages.en')</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::app.status_field') <span class="text-red-500">*</span></label>
                <div class="flex h-11 items-center gap-4">
                    <label class="flex cursor-pointer items-center gap-2">
                        <input type="radio" name="status" value="active" @checked(old('status', $course->status ?? 'active') === 'active') class="h-4 w-4 accent-green-600">
                        <span class="text-sm font-black text-green-700">@lang('teacher-course::app.active')</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2">
                        <input type="radio" name="status" value="inactive" @checked(old('status', $course->status ?? '') === 'inactive') class="h-4 w-4 accent-slate-500">
                        <span class="text-sm font-black text-slate-500">@lang('teacher-course::app.inactive')</span>
                    </label>
                </div>
            </div>
        </div>
    </section>

    <section data-course-form-panel="schedule" hidden class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4">
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::app.duration_field')</label>
                <div class="grid grid-cols-[minmax(0,1fr)_7.5rem] gap-2">
                    <input type="number" step="0.25" name="duration_value" min="0.25" max="525600" value="{{ old('duration_value', $course->duration_value ?? $course->estimated_duration_minutes ?? '') }}" class="h-11 min-w-0 rounded-xl border border-slate-200 px-3 text-sm font-bold text-slate-700">
                    <select name="duration_unit" class="h-11 min-w-0 rounded-xl border border-slate-200 bg-white px-2 text-sm font-bold text-slate-700">
                        @foreach(\Mindigo\TeacherCourse\Models\Course::DURATION_UNITS as $unit)
                            <option value="{{ $unit }}" @selected(old('duration_unit', $course->duration_unit ?? 'hour') === $unit)>@lang('teacher-course::app.duration_units.'.$unit)</option>
                        @endforeach
                    </select>
                </div>
                @error('duration_value')<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
                @error('duration_unit')<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::app.access_type_field')</label>
                <select name="access_type" class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700">
                    @foreach(\Mindigo\TeacherCourse\Models\Course::ACCESS_TYPES as $accessType)
                        <option value="{{ $accessType }}" @selected(old('access_type', $course->access_type ?? 'free') === $accessType)>@lang('teacher-course::app.access_types.'.$accessType)</option>
                    @endforeach
                </select>
                @error('access_type')<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::app.price_field')</label>
                <div class="grid grid-cols-[minmax(0,1fr)_5rem] gap-2">
                    <input type="number" name="price" min="0" step="1000" value="{{ old('price', $course->price ?? 0) }}" class="h-11 min-w-0 rounded-xl border border-slate-200 px-3 text-sm font-bold text-slate-700">
                    <select name="currency" class="h-11 min-w-0 rounded-xl border border-slate-200 bg-white px-2 text-sm font-bold text-slate-700">
                        <option value="VND" @selected(old('currency', $course->currency ?? 'VND') === 'VND')>VND</option>
                    </select>
                </div>
                @error('price')<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::app.starts_at_field')</label>
                <div class="relative">
                    <input type="text" name="starts_at" inputmode="numeric" value="{{ old('starts_at', isset($course) && $course->starts_at ? $course->starts_at->format('d/m/Y') : '') }}" placeholder="@lang('teacher-course::app.date_placeholder')" class="h-11 w-full rounded-xl border border-slate-200 px-3 pr-11 text-sm font-bold text-slate-700">
                    <input type="date" data-course-date-picker value="{{ isset($course) && $course->starts_at ? $course->starts_at->format('Y-m-d') : '' }}" class="pointer-events-none absolute inset-y-0 right-0 w-11 cursor-pointer opacity-0">
                    <button type="button" data-course-date-trigger class="absolute inset-y-0 right-0 grid w-11 place-items-center text-slate-500 transition hover:text-green-700" aria-label="@lang('teacher-course::app.pick_date')">
                        <x-heroicon-o-calendar-days class="h-4 w-4" />
                    </button>
                </div>
                @error('starts_at')<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid gap-3 xl:grid-cols-[1fr_1fr]">
            <div>
                <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::app.schedule_days_field')</label>
                <div class="flex flex-wrap gap-2 rounded-xl border border-slate-200 bg-slate-50 p-2">
                    @foreach(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'] as $day)
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-600 transition has-checked:border-green-300 has-checked:bg-green-50 has-checked:text-green-700">
                            <input type="checkbox" name="schedule_days[]" value="{{ $day }}" @checked(in_array($day, $selectedScheduleDays ?? [], true)) class="h-3.5 w-3.5 accent-green-600">
                            @lang('teacher-course::app.schedule_days.'.$day)
                        </label>
                    @endforeach
                </div>
                @error('schedule_days')<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::app.study_time_field')</label>
                <input type="hidden" name="study_time" value="{{ $timeRange }}">
                <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-2">
                    <select name="study_time_start" class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700">
                        <option value="">@lang('teacher-course::app.start_time')</option>
                        @foreach($timeOptions as $timeOption)
                            <option value="{{ $timeOption }}" @selected(old('study_time_start', $studyTimeStart) === $timeOption)>{{ $timeOption }}</option>
                        @endforeach
                    </select>
                    <span class="text-xs font-black text-slate-400">—</span>
                    <select name="study_time_end" class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700">
                        <option value="">@lang('teacher-course::app.end_time')</option>
                        @foreach($timeOptions as $timeOption)
                            <option value="{{ $timeOption }}" @selected(old('study_time_end', $studyTimeEnd) === $timeOption)>{{ $timeOption }}</option>
                        @endforeach
                    </select>
                </div>
                @error('study_time_start')<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
                @error('study_time_end')<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
                @error('study_time')<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section data-course-form-panel="content" hidden class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4">
        <p class="text-xs font-semibold text-slate-400">@lang('teacher-course::app.content_tab_hint')</p>
        <div class="grid gap-4 xl:grid-cols-3">
            @foreach(['learning_outcomes', 'requirements', 'target_learners'] as $metadataField)
                <div>
                    <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::app.'.$metadataField.'_field')</label>
                    <textarea name="{{ $metadataField }}" rows="12" placeholder="@lang('teacher-course::app.'.$metadataField.'_placeholder')" class="min-h-72 w-full resize-y rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold leading-7 text-slate-800 outline-none focus:border-green-400 focus:ring-2 focus:ring-green-50">{{ old($metadataField, isset($course) ? implode("\n", $course->{$metadataField} ?? []) : '') }}</textarea>
                    @error($metadataField)<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
                </div>
            @endforeach
        </div>
    </section>

    <section data-course-form-panel="media" hidden class="rounded-2xl border border-slate-200 bg-white p-4">
        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::app.cover_image_field')</label>
                    <input type="file" name="cover_image" accept="image/*"
                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 outline-none file:mr-3 file:rounded-full file:border-0 file:bg-green-50 file:px-3 file:py-1 file:text-xs file:font-black file:text-green-700">
                    @error('cover_image')<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::app.description_field')</label>
                    <textarea name="description" rows="10"
                              placeholder="@lang('teacher-course::app.description_ph')"
                              class="min-h-72 w-full resize-y rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold leading-7 text-slate-800 outline-none transition focus:border-green-400 focus:ring-2 focus:ring-green-50">{{ old('description', $course->description ?? '') }}</textarea>
                    @error('description')<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <aside class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                <p class="mb-2 text-[10px] font-black uppercase tracking-widest text-green-700">@lang('teacher-course::app.preview_card')</p>
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                    <div class="aspect-video bg-slate-950">
                        @if($editing && $course->cover_image)
                            <img src="{{ asset('storage/' . $course->cover_image) }}" alt="@lang('teacher-course::app.current_cover_image')" class="h-full w-full object-contain">
                        @else
                            <div class="grid h-full place-items-center text-slate-400"><x-heroicon-o-academic-cap class="h-12 w-12" /></div>
                        @endif
                    </div>
                    <div class="space-y-3 p-4">
                        <h3 class="line-clamp-2 text-lg font-black text-slate-950">{{ old('name', $course->name ?? __('teacher-course::app.course_name_field')) }}</h3>
                        <p class="line-clamp-3 text-xs font-semibold leading-5 text-slate-500">{{ old('description', $course->description ?? __('teacher-course::app.description_ph')) }}</p>
                        <div class="rounded-xl bg-green-50 px-3 py-2 text-center text-base font-black text-green-700">
                            {{ old('access_type', $course->access_type ?? 'free') === 'free' ? __('teacher-course::catalog.free') : number_format((float) old('price', $course->price ?? 0)).' VND' }}
                        </div>
                        <dl class="divide-y divide-slate-100 text-xs">
                            <div class="flex justify-between gap-3 py-2"><dt class="font-bold text-slate-400">@lang('teacher-course::app.starts_at_field')</dt><dd class="font-black text-slate-700">{{ old('starts_at', isset($course) && $course->starts_at ? $course->starts_at->format('d/m/Y') : '—') }}</dd></div>
                            <div class="flex justify-between gap-3 py-2"><dt class="font-bold text-slate-400">@lang('teacher-course::app.study_time_field')</dt><dd class="font-black text-slate-700">{{ $timeRange ?: '—' }}</dd></div>
                        </dl>
                    </div>
                </div>
            </aside>
        </div>
    </section>
</div>

<script>
    document.querySelectorAll('[data-course-form-tabs]').forEach((root) => {
        const tabs = root.querySelectorAll('[data-course-form-tab]');
        const panels = root.querySelectorAll('[data-course-form-panel]');
        const form = root.closest('form');
        const isCreateWizard = root.dataset.courseCreateWizard === '1';
        const nextButton = form?.querySelector('[data-course-form-next]');
        const submitButton = form?.querySelector('[data-course-form-submit]');
        let currentIndex = 0;
        let unlockedIndex = isCreateWizard ? 0 : tabs.length - 1;
        const activeClass = ['bg-white', 'text-green-700', 'shadow-sm'];
        const inactiveClass = ['text-slate-500', 'hover:text-slate-800'];
        const dateInput = root.querySelector('[name="starts_at"]');
        const datePicker = root.querySelector('[data-course-date-picker]');
        const dateTrigger = root.querySelector('[data-course-date-trigger]');

        const refreshFooter = () => {
            if (! isCreateWizard || ! nextButton || ! submitButton) {
                return;
            }

            const isLast = currentIndex === tabs.length - 1;
            nextButton.hidden = isLast;
            submitButton.hidden = ! isLast;
        };

        const activateTab = (tab, index) => {
            if (isCreateWizard && index > unlockedIndex) {
                return;
            }

            currentIndex = index;
            const target = tab.dataset.courseFormTab;

            tabs.forEach((item) => {
                    const active = item === tab;
                    item.classList.toggle(activeClass[0], active);
                    item.classList.toggle(activeClass[1], active);
                    item.classList.toggle(activeClass[2], active);
                    item.classList.toggle(inactiveClass[0], ! active);
                    item.classList.toggle(inactiveClass[1], ! active);
            });

            panels.forEach((panel) => {
                    panel.hidden = panel.dataset.courseFormPanel !== target;
            });

            refreshFooter();
        };

        const unlockThrough = (index) => {
            unlockedIndex = Math.max(unlockedIndex, index);
            tabs.forEach((tab, tabIndex) => {
                const locked = isCreateWizard && tabIndex > unlockedIndex;
                tab.disabled = locked;
                tab.classList.toggle('cursor-not-allowed', locked);
                tab.classList.toggle('opacity-50', locked);
            });
        };

        tabs.forEach((tab, index) => {
            tab.addEventListener('click', () => activateTab(tab, index));
        });

        nextButton?.addEventListener('click', () => {
            const nextIndex = Math.min(currentIndex + 1, tabs.length - 1);
            unlockThrough(nextIndex);
            activateTab(tabs[nextIndex], nextIndex);
        });

        dateInput?.addEventListener('input', () => {
            const digits = dateInput.value.replace(/\D/g, '').slice(0, 8);
            const parts = [digits.slice(0, 2), digits.slice(2, 4), digits.slice(4, 8)].filter(Boolean);
            dateInput.value = parts.join('/');
        });

        dateTrigger?.addEventListener('click', () => {
            if (typeof datePicker.showPicker === 'function') {
                datePicker.showPicker();
                return;
            }

            datePicker.click();
        });

        datePicker?.addEventListener('change', () => {
            if (! datePicker.value) {
                dateInput.value = '';
                return;
            }

            const [year, month, day] = datePicker.value.split('-');
            dateInput.value = `${day}/${month}/${year}`;
        });

        refreshFooter();
    });
</script>
