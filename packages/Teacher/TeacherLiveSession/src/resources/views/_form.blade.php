{{-- Shared form fields for create/edit. Expects $session (nullable) + $classrooms --}}
@php
    $fmt = fn ($dt) => $dt ? $dt->format('Y-m-d\TH:i') : null;
@endphp

{{-- ① Thông tin cơ bản --}}
<section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-4 lg:col-span-7">
    <h2 class="text-sm font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 pb-3">@lang('teacher-live-session::app.section_basic_info')</h2>

    <div class="space-y-1">
        <label class="text-sm font-bold text-slate-700">@lang('teacher-live-session::app.field_title') <span class="text-red-500">*</span></label>
        <input type="text" name="title" value="{{ old('title', $session->title ?? '') }}"
               class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 outline-none focus:border-green-300 focus:ring-2 focus:ring-green-50"
               placeholder="{{ __('teacher-live-session::app.title_placeholder') }}">
        @error('title')<p class="text-xs font-semibold text-red-500 mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="space-y-1">
        <label class="text-sm font-bold text-slate-700">@lang('teacher-live-session::app.field_desc')</label>
        <textarea name="description" rows="5"
                  class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 outline-none focus:border-green-300 focus:ring-2 focus:ring-green-50"
                  placeholder="{{ __('teacher-live-session::app.desc_placeholder') }}">{{ old('description', $session->description ?? '') }}</textarea>
    </div>
</section>

{{-- ② Lớp học & thời gian --}}
<section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-4 lg:col-span-5">
    <h2 class="text-sm font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 pb-3">@lang('teacher-live-session::app.section_schedule')</h2>

    <div class="space-y-1">
        <label class="text-sm font-bold text-slate-700">@lang('teacher-live-session::app.field_classroom') <span class="text-red-500">*</span></label>
        <select name="classroom_id"
                class="block w-full h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none focus:border-green-300">
            <option value="">@lang('teacher-live-session::app.classroom_select_placeholder')</option>
            @foreach($classrooms as $class)
                <option value="{{ $class->id }}" {{ old('classroom_id', $session->classroom_id ?? '') == $class->id ? 'selected' : '' }}>
                    {{ __('teacher-live-session::app.classroom_option', ['name' => $class->name, 'code' => $class->code, 'count' => $class->students_count]) }}
                </option>
            @endforeach
        </select>
        @error('classroom_id')<p class="text-xs font-semibold text-red-500 mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="space-y-1">
        <label class="text-sm font-bold text-slate-700">@lang('teacher-live-session::app.field_start') <span class="text-red-500">*</span></label>
        <input type="datetime-local" name="scheduled_start" value="{{ old('scheduled_start', $fmt($session->scheduled_start ?? null)) }}"
               class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 outline-none focus:border-green-300 focus:ring-2 focus:ring-green-50">
        @error('scheduled_start')<p class="text-xs font-semibold text-red-500 mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="space-y-1">
        <label class="text-sm font-bold text-slate-700">@lang('teacher-live-session::app.field_end')</label>
        <input type="datetime-local" name="scheduled_end" value="{{ old('scheduled_end', $fmt($session->scheduled_end ?? null)) }}"
               class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 outline-none focus:border-green-300 focus:ring-2 focus:ring-green-50">
        @error('scheduled_end')<p class="text-xs font-semibold text-red-500 mt-1">{{ $message }}</p>@enderror
    </div>
</section>
