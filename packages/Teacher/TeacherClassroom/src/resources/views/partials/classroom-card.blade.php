@php
    $isActive = $classroom->status === 'active';
    $isCourseClass = $classroom->type === \Mindigo\TeacherClassroom\Models\Classroom::TYPE_COURSE;
@endphp

<article class="group flex min-h-80 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:border-green-300 hover:shadow-lg">
    <a href="{{ route('teacher.classrooms.show', $classroom) }}" class="block no-underline">
        <div class="relative h-28 overflow-hidden bg-[#173f35] p-4">
            <span aria-hidden="true" class="absolute -right-5 -top-5 h-20 w-20 rounded-full border-16 border-white/10"></span>
            <span aria-hidden="true" class="absolute bottom-0 right-16 h-12 w-12 bg-green-500/70"></span>
            <span aria-hidden="true" class="absolute -bottom-5 right-5 h-14 w-14 rotate-45 rounded-xl bg-lime-300"></span>
            <span aria-hidden="true" class="absolute left-0 top-0 h-full w-1 bg-green-400"></span>

            <div class="relative flex h-full flex-col justify-between">
                <div class="flex items-center justify-between gap-3">
                    <span class="rounded-md bg-white/10 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-white/80">{{ $classroom->code }}</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-2.5 py-1 text-[10px] font-bold {{ $isActive ? 'text-green-700' : 'text-slate-500' }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $isActive ? 'bg-green-500' : 'bg-slate-400' }}"></span>
                        @lang('teacher-classroom::app.' . $classroom->status)
                    </span>
                </div>
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-white text-[#173f35] shadow-sm">
                    <x-heroicon-o-academic-cap class="h-5 w-5" />
                </span>
            </div>
        </div>

        <div class="p-4">
            <h2 class="truncate text-base font-extrabold tracking-tight text-slate-950 transition group-hover:text-green-700">{{ $classroom->name }}</h2>
            <p class="mt-1 flex items-center gap-1.5 text-xs font-medium text-slate-400">
                <x-heroicon-o-calendar-days class="h-3.5 w-3.5" />
                {{ $classroom->school_year ?: __('teacher-classroom::app.school_year_unset') }}
            </p>

            <div class="mt-4 flex min-h-7 items-center gap-1.5 overflow-hidden">
                <span class="shrink-0 rounded-md {{ $isCourseClass ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600' }} px-2 py-1 text-[10px] font-bold">@lang('teacher-classroom::app.type_' . $classroom->type)</span>
                <span class="truncate rounded-md bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-600">{{ $classroom->subject?->name ?? __('teacher-classroom::app.no_subjects') }}</span>
            </div>

            @if($isCourseClass && $classroom->course)
                <p class="mt-2 flex items-center gap-1.5 truncate text-[11px] font-semibold text-slate-500"><x-heroicon-o-link class="h-3.5 w-3.5 shrink-0 text-green-600" /><span class="truncate">{{ $classroom->course->name }}</span></p>
            @endif
            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                <span class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500">
                    <span class="grid h-7 w-7 place-items-center rounded-full bg-green-50 text-green-700"><x-heroicon-o-user-group class="h-3.5 w-3.5" /></span>
                    @lang('teacher-classroom::app.students_count_badge', ['count' => $classroom->students_count])
                </span>
                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-green-700">@lang('teacher-classroom::app.open_class')<x-heroicon-o-arrow-up-right class="h-3.5 w-3.5" /></span>
            </div>
        </div>
    </a>

    <div class="mt-auto flex items-center justify-end gap-1 border-t border-slate-100 px-3 py-2">
        <a href="{{ route('teacher.classrooms.edit', $classroom) }}" aria-label="@lang('teacher-classroom::app.edit_short')" class="grid h-8 w-8 place-items-center rounded-lg text-slate-400 no-underline transition hover:bg-slate-100 hover:text-slate-700"><x-heroicon-o-pencil-square class="h-4 w-4" /></a>
        <form method="POST" action="{{ route('teacher.classrooms.destroy', $classroom) }}" data-mindigo-confirm-title="@lang('teacher-classroom::app.delete_title')" data-mindigo-confirm-message="@lang('teacher-classroom::app.delete_confirm')" data-mindigo-confirm-text="@lang('teacher-classroom::app.delete')" data-mindigo-confirm-cancel="{{ __('teacher-classroom::app.cancel') }}" data-mindigo-confirm-type="danger">
            @csrf
            @method('DELETE')
            <button type="submit" aria-label="@lang('teacher-classroom::app.delete')" class="grid h-8 w-8 place-items-center rounded-lg text-slate-400 transition hover:bg-red-50 hover:text-red-600"><x-heroicon-o-trash class="h-4 w-4" /></button>
        </form>
    </div>
</article>
