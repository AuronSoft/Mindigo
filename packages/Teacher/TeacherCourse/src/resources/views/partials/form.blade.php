@php $editing = isset($course) && $course->exists; @endphp

{{-- Tên khóa học --}}
<div>
    <label class="mb-1.5 block text-xs font-black text-slate-600">
        Tên khóa học <span class="text-red-500">*</span>
    </label>
    <input type="text" name="name" value="{{ old('name', $course->name ?? '') }}"
           placeholder="VD: Lập trình Python từ cơ bản đến nâng cao" required
           class="w-full rounded-2xl border {{ $errors->has('name') ? 'border-red-300 bg-red-50' : 'border-slate-200 bg-white' }} px-4 py-2.5 text-sm font-bold text-slate-800 outline-none transition focus:border-green-400 focus:ring-2 focus:ring-green-50">
    @error('name')
        <p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>
    @enderror
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
        <label class="mb-1.5 block whitespace-nowrap text-xs font-black text-slate-600">@lang('teacher-course::app.duration_field')</label>
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
</div>

{{-- Trạng thái --}}
<div>
    <label class="mb-1.5 block text-xs font-black text-slate-600">Trạng thái <span class="text-red-500">*</span></label>
    <div class="flex gap-4 pt-1">
        <label class="flex cursor-pointer items-center gap-2">
            <input type="radio" name="status" value="active" @checked(old('status', $course->status ?? 'active') === 'active') class="h-4 w-4 accent-green-600">
            <span class="text-sm font-black text-green-700">Đang hoạt động</span>
        </label>
        <label class="flex cursor-pointer items-center gap-2">
            <input type="radio" name="status" value="inactive" @checked(old('status', $course->status ?? '') === 'inactive') class="h-4 w-4 accent-slate-500">
            <span class="text-sm font-black text-slate-500">Tạm dừng</span>
        </label>
    </div>
</div>

<div class="grid gap-3 lg:grid-cols-3">
@foreach(['learning_outcomes', 'requirements', 'target_learners'] as $metadataField)
    <div>
        <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::app.'.$metadataField.'_field')</label>
        <textarea name="{{ $metadataField }}" rows="2" placeholder="@lang('teacher-course::app.'.$metadataField.'_placeholder')" class="w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold leading-relaxed text-slate-800 outline-none focus:border-green-400 focus:ring-2 focus:ring-green-50">{{ old($metadataField, isset($course) ? implode("\n", $course->{$metadataField} ?? []) : '') }}</textarea>
        @error($metadataField)<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
    </div>
@endforeach
</div>

{{-- Ảnh bìa --}}
<div>
    <label class="mb-1.5 block text-xs font-black text-slate-600">Ảnh bìa khóa học</label>
    @if($editing && $course->cover_image)
        <div class="mb-3">
            <img src="{{ asset('storage/' . $course->cover_image) }}" alt="Ảnh bìa hiện tại"
                 class="h-16 w-full rounded-xl border border-slate-200 object-cover">
            <p class="mt-1 text-[11px] text-slate-400 font-bold">Ảnh bìa hiện tại. Upload ảnh mới để thay thế.</p>
        </div>
    @endif
    <input type="file" name="cover_image" accept="image/*"
           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 outline-none file:mr-3 file:rounded-full file:border-0 file:bg-green-50 file:px-3 file:py-1 file:text-xs file:font-black file:text-green-700">
    @error('cover_image')
        <p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>
    @enderror
</div>

{{-- Mô tả --}}
<div>
    <label class="mb-1.5 block text-xs font-black text-slate-600">Mô tả khóa học</label>
    <textarea name="description" rows="2"
              placeholder="Mô tả ngắn gọn về nội dung và mục tiêu khóa học..."
              class="w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold leading-relaxed text-slate-800 outline-none transition focus:border-green-400 focus:ring-2 focus:ring-green-50">{{ old('description', $course->description ?? '') }}</textarea>
    @error('description')
        <p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>
    @enderror
</div>
