<div id="calendar-detail-drawer" data-calendar-layer aria-hidden="true" class="fixed inset-0 z-60 hidden bg-green-950/20">
    <aside class="ml-auto flex h-full w-full max-w-md flex-col border-l border-green-100 bg-white shadow-2xl">
        <header class="flex items-center justify-between border-b border-slate-200 p-5">
            <h2 class="font-black text-slate-900">@lang('teacher-calendar::app.event_details')</h2>
            <button data-calendar-close class="grid h-9 w-9 place-items-center rounded-xl text-slate-500 transition hover:bg-green-50 hover:text-green-700 focus:outline-none focus:ring-2 focus:ring-green-500/30"><x-heroicon-o-x-mark class="h-5 w-5" /></button>
        </header>
        <div class="flex-1 space-y-5 overflow-y-auto p-5">
            <span data-event-kind class="inline-flex rounded-lg bg-green-50 px-2.5 py-1 text-xs font-black text-green-700"></span>
            <h3 data-event-title class="text-xl font-black text-slate-900"></h3>
            <dl class="space-y-3 text-sm">
                <div><dt class="text-xs font-bold text-slate-400">@lang('teacher-calendar::app.date')</dt><dd data-event-time class="mt-1 font-black text-slate-800"></dd></div>
                <div><dt class="text-xs font-bold text-slate-400">@lang('teacher-calendar::app.classroom')</dt><dd data-event-classroom class="mt-1 font-black text-slate-800"></dd></div>
            </dl>
            <form method="POST" data-event-cancel-form class="hidden rounded-xl border border-red-100 bg-red-50 p-3">
                @csrf
                <label class="text-xs font-black text-red-700">@lang('teacher-calendar::app.cancel_reason')<textarea required minlength="10" name="cancel_reason" rows="2" class="mt-1.5 w-full rounded-lg border border-red-200 bg-white p-2 text-slate-800 focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-500/20"></textarea></label>
                <button class="mt-2 h-9 rounded-lg bg-red-600 px-3 text-xs font-black text-white hover:bg-red-700">@lang('teacher-calendar::app.cancel_session')</button>
            </form>
        </div>
        <footer class="border-t border-slate-200 bg-slate-50/70 p-5">
            <a data-event-link href="#" class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-green-600 px-4 text-sm font-black text-white no-underline shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500/30">@lang('teacher-calendar::app.view_classroom')</a>
        </footer>
    </aside>
</div>

<div id="calendar-create-drawer" data-calendar-layer aria-hidden="true" class="fixed inset-0 z-60 hidden bg-green-950/20">
    <aside class="ml-auto flex h-full w-full max-w-lg flex-col overflow-y-auto border-l border-green-100 bg-white shadow-2xl">
        <header class="flex items-center justify-between border-b border-slate-200 p-5">
            <h2 class="font-black text-slate-900">@lang('teacher-calendar::app.new_session')</h2>
            <button data-calendar-close class="grid h-9 w-9 place-items-center rounded-xl text-slate-500 transition hover:bg-green-50 hover:text-green-700 focus:outline-none focus:ring-2 focus:ring-green-500/30"><x-heroicon-o-x-mark class="h-5 w-5" /></button>
        </header>
        <form id="calendar-session-form" method="POST" action="#" class="space-y-4 p-5">@csrf
            <label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.classroom')<select required id="calendar-classroom" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3" name="classroom_id"><option value="">@lang('teacher-calendar::app.all_classrooms')</option>@foreach($classrooms as $classroom)<option value="{{ $classroom->id }}" data-type="{{ $classroom->type }}" data-store-url="{{ route('teacher.calendar.sessions.store', $classroom) }}">{{ $classroom->name }}</option>@endforeach</select></label>
            <div id="calendar-session-type-shell" class="hidden"><label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.session_type')<select id="calendar-session-type" name="type" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3"><option value="regular">@lang('teacher-calendar::app.regular')</option><option value="makeup">@lang('teacher-calendar::app.makeup')</option></select></label></div>
            <div id="calendar-makeup-reason-shell" class="hidden"><label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.makeup_reason')<textarea name="makeup_reason" rows="2" class="mt-1.5 w-full rounded-xl border border-amber-200 bg-amber-50 p-3"></textarea></label></div>
            <label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.lesson')<select id="calendar-lesson" name="lesson_id" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3"><option value="">—</option>@foreach($classrooms as $classroom)@foreach($classroom->course?->chapters ?? [] as $chapter)@foreach($chapter->lessons as $lesson)<option hidden data-classroom="{{ $classroom->id }}" value="{{ $lesson->id }}">{{ $chapter->name }} · {{ $lesson->name }}</option>@endforeach @endforeach @endforeach</select></label>
            <label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.title_field')<input required name="title" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 px-3"></label>
            <div class="grid grid-cols-3 gap-3"><label class="text-xs font-black text-slate-600">@lang('teacher-calendar::app.date')<input required type="date" name="session_date" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 px-2"></label><label class="text-xs font-black text-slate-600">@lang('teacher-calendar::app.start')<input required type="time" name="start_time" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 px-2"></label><label class="text-xs font-black text-slate-600">@lang('teacher-calendar::app.end')<input required type="time" name="end_time" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 px-2"></label></div>
            <label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.delivery_mode')<select name="delivery_mode" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3"><option value="offline">@lang('teacher-calendar::app.offline')</option><option value="online">@lang('teacher-calendar::app.online')</option><option value="hybrid">@lang('teacher-calendar::app.hybrid')</option></select></label>
            <label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.location')<input name="location" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 px-3"></label>
            <label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.meeting_url')<input type="url" name="meeting_url" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 px-3"></label>
            <label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.description')<textarea name="description" rows="3" class="mt-1.5 w-full rounded-xl border border-slate-200 p-3"></textarea></label>
            <button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-green-600 text-sm font-black text-white hover:bg-green-700">@lang('teacher-calendar::app.save')</button>
        </form>
    </aside>
</div>
