<div data-mindigo-drawer="teacher-calendar-filter" class="fixed inset-0 z-40 hidden bg-slate-950/45 opacity-0 backdrop-blur-sm transition-opacity duration-200"></div>
<aside data-mindigo-drawer-panel="teacher-calendar-filter" aria-label="@lang('teacher-calendar::app.filter_title')" class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col border-l border-slate-200 bg-white shadow-2xl transition-transform duration-200" style="transform: translateX(100%);">
    <header class="flex items-start justify-between gap-4 border-b border-slate-100 p-5">
        <div>
            <p class="text-xs font-black uppercase tracking-wider text-green-700">@lang('teacher-calendar::app.filters')</p>
            <h2 class="mt-1 text-xl font-black text-slate-950">@lang('teacher-calendar::app.filter_title')</h2>
            <p class="mt-1 text-sm font-medium leading-6 text-slate-500">@lang('teacher-calendar::app.filter_description')</p>
        </div>
        <button type="button" aria-label="@lang('teacher-calendar::app.close')" data-mindigo-drawer-close="teacher-calendar-filter" class="grid h-10 w-10 shrink-0 place-items-center rounded-full border border-slate-200 text-slate-500 transition hover:border-green-200 hover:bg-green-50 hover:text-green-700"><x-heroicon-o-x-mark class="h-5 w-5" /></button>
    </header>
    <form action="{{ route('teacher.calendar.index') }}" method="GET" class="flex flex-1 flex-col">
        <input type="hidden" name="date" value="{{ $anchor->toDateString() }}">
        <input type="hidden" name="view" value="{{ $viewMode }}">
        <div class="flex-1 space-y-5 overflow-y-auto p-5">
            <label class="block space-y-2">
                <span class="block text-xs font-black uppercase tracking-wider text-slate-500">@lang('teacher-calendar::app.classroom')</span>
                <select name="classroom_id" class="h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-green-400 focus:ring-4 focus:ring-green-50">
                    <option value="">@lang('teacher-calendar::app.all_classrooms')</option>
                    @foreach($classrooms as $classroom)<option value="{{ $classroom->id }}" @selected(($filters['classroom_id'] ?? null) == $classroom->id)>{{ $classroom->name }}</option>@endforeach
                </select>
            </label>
            <fieldset class="space-y-3">
                <legend class="text-xs font-black uppercase tracking-wider text-slate-500">@lang('teacher-calendar::app.event_types')</legend>
                @foreach(\Mindigo\AcademicCalendar\Enums\CalendarEventKind::cases() as $kind)
                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-green-200 hover:bg-green-50/60">
                        <input type="checkbox" name="kinds[]" value="{{ $kind->value }}" @checked(in_array($kind->value, $filters['kinds'] ?? [], true)) class="h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500">
                        <span>@lang('teacher-calendar::app.'.$kind->value)</span>
                    </label>
                @endforeach
            </fieldset>
        </div>
        <footer class="grid grid-cols-2 gap-3 border-t border-slate-100 p-5">
            <a href="{{ route('teacher.calendar.index', ['date' => $anchor->toDateString(), 'view' => $viewMode]) }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 text-sm font-bold text-slate-600 no-underline hover:bg-slate-50">@lang('teacher-calendar::app.clear_filter')</a>
            <button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-green-600 text-sm font-bold text-white hover:bg-green-700"><x-heroicon-o-funnel class="h-4 w-4" />@lang('teacher-calendar::app.apply_filter')</button>
        </footer>
    </form>
</aside>
