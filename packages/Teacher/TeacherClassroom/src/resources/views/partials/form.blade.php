@php
    $editing  = isset($classroom) && $classroom->exists;
    $sel      = $editing ? $classroom : null;
@endphp

<div class="space-y-5">

    {{-- Row 1: name + code --}}
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="mb-1.5 block text-xs font-black text-slate-600">
                @lang('teacher-classroom::app.name') <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" value="{{ old('name', $sel?->name) }}"
                   placeholder="@lang('teacher-classroom::app.name_ph')" required
                   class="w-full rounded-2xl border {{ $errors->has('name') ? 'border-red-300 bg-red-50' : 'border-slate-200 bg-white' }} px-4 py-2.5 text-sm font-bold text-slate-800 outline-none transition focus:border-green-400 focus:ring-2 focus:ring-green-50">
            @error('name')
                <p class="mt-1.5 flex items-center gap-1 text-xs font-bold text-red-600">
                    <x-heroicon-o-exclamation-circle class="h-3.5 w-3.5" />{{ $message }}
                </p>
            @enderror
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-black text-slate-600">
                @lang('teacher-classroom::app.code') <span class="text-red-500">*</span>
            </label>
            <input type="text" name="code" value="{{ old('code', $sel?->code) }}"
                   placeholder="@lang('teacher-classroom::app.code_ph')" required
                   class="w-full rounded-2xl border {{ $errors->has('code') ? 'border-red-300 bg-red-50' : 'border-slate-200 bg-white' }} px-4 py-2.5 text-sm font-bold text-slate-800 outline-none transition focus:border-green-400 focus:ring-2 focus:ring-green-50">
            @error('code')
                <p class="mt-1.5 flex items-center gap-1 text-xs font-bold text-red-600">
                    <x-heroicon-o-exclamation-circle class="h-3.5 w-3.5" />{{ $message }}
                </p>
            @enderror
        </div>
    </div>

    {{-- Row 2: school year + status --}}
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-classroom::app.school_year')</label>
            <input type="text" name="school_year" value="{{ old('school_year', $sel?->school_year) }}"
                   placeholder="@lang('teacher-classroom::app.school_year_ph')"
                   class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 outline-none transition focus:border-green-400 focus:ring-2 focus:ring-green-50">
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-classroom::app.status') <span class="text-red-500">*</span></label>
            <div class="flex gap-3 pt-1">
                @foreach($statuses as $st)
                    <label class="flex cursor-pointer items-center gap-2">
                        <input type="radio" name="status" value="{{ $st }}"
                               @checked(old('status', $sel?->status ?? 'active') === $st)
                               class="h-4 w-4 accent-green-600">
                        <span class="text-sm font-black {{ $st === 'active' ? 'text-green-700' : 'text-slate-500' }}">
                            @lang('teacher-classroom::app.' . $st)
                        </span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Assistant --}}
    <div>
        <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-classroom::app.assistant')</label>
        <select name="assistant_id"
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 outline-none transition focus:border-green-400 focus:ring-2 focus:ring-green-50">
            <option value="">@lang('teacher-classroom::app.no_assistant')</option>
            @foreach($assistants as $assistant)
                <option value="{{ $assistant->id }}" @selected(old('assistant_id', $sel?->assistant_id) == $assistant->id)>
                    {{ $assistant->name }} ({{ $assistant->email }})
                </option>
            @endforeach
        </select>
        @error('assistant_id')
            <p class="mt-1.5 flex items-center gap-1 text-xs font-bold text-red-600">
                <x-heroicon-o-exclamation-circle class="h-3.5 w-3.5" />{{ $message }}
            </p>
        @enderror
    </div>

    {{-- Subjects --}}
    <div>
        <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-classroom::app.subjects')</label>
        <div class="grid gap-2 sm:grid-cols-2">
            @forelse($subjects as $subject)
                <label class="flex cursor-pointer items-center gap-2.5 rounded-2xl border {{ in_array($subject->id, old('subject_ids', $sel?->subjects->pluck('id')->all() ?? [])) ? 'border-green-300 bg-green-50' : 'border-slate-200 bg-white' }} px-4 py-2.5 transition hover:border-green-300">
                    <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}"
                           @checked(in_array($subject->id, old('subject_ids', $sel?->subjects->pluck('id')->all() ?? [])))
                           class="h-4 w-4 accent-green-600">
                    <span class="flex items-center gap-2 text-sm font-bold text-slate-700">
                        <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $subject->color ?? '#10b981' }}"></span>
                        {{ $subject->name }}
                    </span>
                </label>
            @empty
                <p class="text-sm font-bold text-slate-400">@lang('teacher-classroom::app.no_subjects')</p>
            @endforelse
        </div>
        @error('subject_ids')
            <p class="mt-1.5 flex items-center gap-1 text-xs font-bold text-red-600">
                <x-heroicon-o-exclamation-circle class="h-3.5 w-3.5" />{{ $message }}
            </p>
        @enderror
    </div>

    {{-- Description --}}
    <div>
        <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-classroom::app.description')</label>
        <textarea name="description" rows="3"
                  placeholder="@lang('teacher-classroom::app.description_ph')"
                  class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold leading-relaxed text-slate-800 outline-none transition focus:border-green-400 focus:ring-2 focus:ring-green-50 resize-none">{{ old('description', $sel?->description) }}</textarea>
    </div>

</div>

