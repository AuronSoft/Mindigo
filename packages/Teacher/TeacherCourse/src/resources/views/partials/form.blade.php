@php
    $editing = isset($course) && $course->exists;
    $selectedScheduleDays = old('schedule_days', $course->schedule_days ?? []);
    $tabs = [
        'overview' => __('teacher-course::app.form_tabs.overview'),
        'schedule' => __('teacher-course::app.form_tabs.schedule'),
        'content' => __('teacher-course::app.form_tabs.content'),
        'media' => __('teacher-course::app.form_tabs.media'),
    ];
@endphp

<div x-data="{ tab: 'overview' }" class="min-h-0 flex-1 space-y-4">
    <nav class="flex gap-2 overflow-x-auto rounded-2xl border border-slate-200 bg-slate-50 p-1" aria-label="@lang('teacher-course::app.form_navigation')">
        @foreach($tabs as $tabKey => $tabLabel)
            <button type="button"
                    @click="tab = '{{ $tabKey }}'"
                    :class="tab === '{{ $tabKey }}' ? 'bg-white text-green-700 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                    class="shrink-0 rounded-xl px-4 py-2 text-xs font-black transition">
                {{ $tabLabel }}
            </button>
        @endforeach
    </nav>

    <section x-show="tab === 'overview'" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4">
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

    <section x-show="tab === 'schedule'" x-cloak class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4">
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
                <input type="date" name="starts_at" value="{{ old('starts_at', isset($course) && $course->starts_at ? $course->starts_at->format('Y-m-d') : '') }}" class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm font-bold text-slate-700">
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
                <input type="text" name="study_time" value="{{ old('study_time', $course->study_time ?? '') }}" placeholder="@lang('teacher-course::app.study_time_placeholder')" class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm font-bold text-slate-700">
                @error('study_time')<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section x-show="tab === 'content'" x-cloak class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4">
        <div class="grid gap-3 lg:grid-cols-3">
            @foreach(['learning_outcomes', 'requirements', 'target_learners'] as $metadataField)
                <div>
                    <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::app.'.$metadataField.'_field')</label>
                    <textarea name="{{ $metadataField }}" rows="6" placeholder="@lang('teacher-course::app.'.$metadataField.'_placeholder')" class="w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold leading-relaxed text-slate-800 outline-none focus:border-green-400 focus:ring-2 focus:ring-green-50">{{ old($metadataField, isset($course) ? implode("\n", $course->{$metadataField} ?? []) : '') }}</textarea>
                    @error($metadataField)<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
                </div>
            @endforeach
        </div>
    </section>

    <section x-show="tab === 'media'" x-cloak class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4">
        <div>
            <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::app.cover_image_field')</label>
            @if($editing && $course->cover_image)
                <div class="mb-3 overflow-hidden rounded-xl border border-slate-200 bg-slate-950">
                    <img src="{{ asset('storage/' . $course->cover_image) }}" alt="@lang('teacher-course::app.current_cover_image')" class="h-36 w-full object-contain">
                </div>
                <p class="mb-2 text-[11px] font-bold text-slate-400">@lang('teacher-course::app.current_cover_image')</p>
            @endif
            <input type="file" name="cover_image" accept="image/*"
                   class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 outline-none file:mr-3 file:rounded-full file:border-0 file:bg-green-50 file:px-3 file:py-1 file:text-xs file:font-black file:text-green-700">
            @error('cover_image')<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::app.description_field')</label>
            <textarea name="description" rows="5"
                      placeholder="@lang('teacher-course::app.description_ph')"
                      class="w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold leading-relaxed text-slate-800 outline-none transition focus:border-green-400 focus:ring-2 focus:ring-green-50">{{ old('description', $course->description ?? '') }}</textarea>
            @error('description')<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
        </div>
    </section>
</div>
