@php $ann = $announcement ?? null; @endphp
<div class="space-y-4">

    {{-- Title --}}
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <label class="mb-1.5 block text-xs font-black text-slate-600">
            @lang('teacher-announcement::app.field_title') <span class="text-red-500">*</span>
        </label>
        <input type="text" name="title" value="{{ old('title', $ann?->title) }}"
               placeholder="@lang('teacher-announcement::app.field_title_ph')" required
               class="w-full rounded-2xl border {{ $errors->has('title') ? 'border-red-300 bg-red-50' : 'border-slate-200 bg-white' }} px-4 py-2.5 text-sm font-bold text-slate-800 outline-none transition focus:border-green-400 focus:ring-2 focus:ring-green-50">
        @error('title')<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Content --}}
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <label class="mb-1.5 block text-xs font-black text-slate-600">
            @lang('teacher-announcement::app.field_content') <span class="text-red-500">*</span>
        </label>
        <textarea name="content" rows="6" required
                  placeholder="@lang('teacher-announcement::app.field_content_ph')"
                  class="w-full resize-none rounded-2xl border {{ $errors->has('content') ? 'border-red-300 bg-red-50' : 'border-slate-200 bg-white' }} px-4 py-3 text-sm font-bold leading-relaxed text-slate-800 outline-none transition focus:border-green-400 focus:ring-2 focus:ring-green-50">{{ old('content', $ann?->content) }}</textarea>
        @error('content')<p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Type + Pin --}}
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <label class="mb-2 block text-xs font-black text-slate-600">@lang('teacher-announcement::app.field_type')</label>
            @php
                $typeConfig = [
                    'info'       => ['dot'=>'bg-sky-400',    'label'=>__('teacher-announcement::app.type_info')],
                    'warning'    => ['dot'=>'bg-amber-400',  'label'=>__('teacher-announcement::app.type_warning')],
                    'reminder'   => ['dot'=>'bg-violet-400', 'label'=>__('teacher-announcement::app.type_reminder')],
                    'assignment' => ['dot'=>'bg-green-500',  'label'=>__('teacher-announcement::app.type_assignment')],
                ];
                $selected = old('type', $ann?->type ?? 'info');
            @endphp
            <div class="space-y-1.5">
                @foreach($types as $t)
                    <label class="flex cursor-pointer items-center gap-2.5 rounded-xl px-3 py-2 transition hover:bg-slate-50 has-[:checked]:bg-slate-50">
                        <input type="radio" name="type" value="{{ $t }}" @checked($selected === $t) class="accent-green-600">
                        <span class="h-2 w-2 rounded-full {{ $typeConfig[$t]['dot'] }}"></span>
                        <span class="text-sm font-bold text-slate-700">{{ $typeConfig[$t]['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <label class="mb-2 block text-xs font-black text-slate-600">@lang('teacher-announcement::app.field_classrooms')</label>
            <p class="mb-2 text-xs font-semibold text-slate-400">@lang('teacher-announcement::app.field_classrooms_desc')</p>
            <div class="space-y-1.5">
                @foreach($classrooms as $cls)
                    <label class="flex cursor-pointer items-center gap-2.5 rounded-xl px-3 py-2 transition hover:bg-slate-50 has-[:checked]:bg-green-50">
                        <input type="checkbox" name="classroom_ids[]" value="{{ $cls->id }}"
                               @checked(in_array($cls->id, old('classroom_ids', $ann?->classrooms->pluck('id')->toArray() ?? [])))
                               class="accent-green-600">
                        <span class="text-sm font-bold text-slate-700">{{ $cls->name }}</span>
                        <span class="ml-auto text-xs font-bold text-slate-400">{{ $cls->students_count }} hs</span>
                    </label>
                @endforeach
                @if($classrooms->isEmpty())
                    <p class="text-sm font-bold text-slate-400">@lang('teacher-classroom::app.no_classrooms')</p>
                @endif
            </div>

            <label class="mt-3 flex cursor-pointer items-center gap-2.5 border-t border-slate-100 pt-3">
                <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned', $ann?->is_pinned)) class="accent-amber-500">
                <x-heroicon-s-map-pin class="h-4 w-4 text-amber-500" />
                <span class="text-sm font-bold text-slate-700">@lang('teacher-announcement::app.field_pinned')</span>
            </label>
        </div>
    </div>
</div>
