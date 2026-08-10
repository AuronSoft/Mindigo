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
                <div><dt class="text-xs font-bold text-slate-400">@lang('teacher-calendar::app.lifecycle_status')</dt><dd data-event-status class="mt-1 font-black text-green-700"></dd></div>
                <div data-event-reason-shell class="hidden rounded-xl border border-amber-100 bg-amber-50 p-3"><dt class="text-xs font-bold text-amber-700">@lang('teacher-calendar::app.lifecycle_reason')</dt><dd data-event-reason class="mt-1 text-xs font-semibold leading-5 text-amber-900"></dd></div>
            </dl>
            <form method="POST" data-event-cancel-form class="hidden rounded-xl border border-red-100 bg-red-50 p-3">
                @csrf
                <label class="text-xs font-black text-red-700">@lang('teacher-calendar::app.cancel_reason')<textarea required minlength="10" name="cancel_reason" rows="2" class="mt-1.5 w-full rounded-lg border border-red-200 bg-white p-2 text-slate-800 focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-500/20"></textarea></label>
                <button class="mt-2 h-9 rounded-lg bg-red-600 px-3 text-xs font-black text-white hover:bg-red-700">@lang('teacher-calendar::app.cancel_session')</button>
            </form>
        </div>
        <footer class="border-t border-slate-200 bg-slate-50/70 p-5">
            <div class="grid grid-cols-2 gap-2">
                <button type="button" data-event-edit class="hidden h-10 items-center justify-center rounded-xl border border-green-200 bg-white text-xs font-black text-green-700 hover:bg-green-50"><x-heroicon-o-pencil-square class="mr-1.5 inline h-4 w-4" />@lang('teacher-calendar::app.edit_session')</button>
                <button type="button" data-event-reschedule class="hidden h-10 items-center justify-center rounded-xl border border-amber-200 bg-white text-xs font-black text-amber-700 hover:bg-amber-50"><x-heroicon-o-arrow-path class="mr-1.5 inline h-4 w-4" />@lang('teacher-calendar::app.reschedule_session')</button>
                <form method="POST" data-event-complete-form class="hidden">@csrf<button class="h-10 w-full rounded-xl bg-green-600 text-xs font-black text-white hover:bg-green-700"><x-heroicon-o-check-circle class="mr-1.5 inline h-4 w-4" />@lang('teacher-calendar::app.complete_session')</button></form>
                <a data-event-link href="#" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-black text-slate-700 no-underline hover:bg-slate-100">@lang('teacher-calendar::app.view_classroom')</a>
            </div>
        </footer>
    </aside>
</div>

<div id="calendar-edit-drawer" data-calendar-layer aria-hidden="true" class="fixed inset-0 z-60 hidden bg-green-950/20">
    <aside class="ml-auto flex h-full w-full max-w-lg flex-col border-l border-green-100 bg-white shadow-2xl">
        <header class="flex items-center justify-between border-b border-slate-200 p-5"><div><p class="text-xs font-black uppercase tracking-wider text-green-700">@lang('teacher-calendar::app.session_lifecycle')</p><h2 class="mt-1 font-black text-slate-900">@lang('teacher-calendar::app.edit_session')</h2></div><button type="button" data-calendar-close class="grid h-9 w-9 place-items-center rounded-xl text-slate-500 hover:bg-green-50 hover:text-green-700"><x-heroicon-o-x-mark class="h-5 w-5" /></button></header>
        <form method="POST" data-calendar-edit-form class="flex flex-1 flex-col">@csrf @method('PUT')
            <div class="flex-1 space-y-4 overflow-y-auto p-5">
                <label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.title_field')<input required name="title" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 px-3 focus:border-green-400 focus:outline-none focus:ring-4 focus:ring-green-50"></label>
                <label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.delivery_mode')<select name="delivery_mode" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3"><option value="offline">@lang('teacher-calendar::app.offline')</option><option value="online">@lang('teacher-calendar::app.online')</option><option value="hybrid">@lang('teacher-calendar::app.hybrid')</option></select></label>
                <label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.location')<input name="location" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 px-3"></label>
                <label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.meeting_url')<input type="url" name="meeting_url" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 px-3"></label>
                <label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.description')<textarea name="description" rows="4" class="mt-1.5 w-full rounded-xl border border-slate-200 p-3"></textarea></label>
            </div>
            <footer class="border-t border-slate-100 p-5"><button class="h-11 w-full rounded-xl bg-green-600 text-sm font-black text-white hover:bg-green-700">@lang('teacher-calendar::app.save_changes')</button></footer>
        </form>
    </aside>
</div>

<div id="calendar-reschedule-drawer" data-calendar-layer aria-hidden="true" class="fixed inset-0 z-60 hidden bg-green-950/20">
    <aside class="ml-auto flex h-full w-full max-w-lg flex-col border-l border-amber-100 bg-white shadow-2xl">
        <header class="flex items-center justify-between border-b border-slate-200 p-5"><div><p class="text-xs font-black uppercase tracking-wider text-amber-700">@lang('teacher-calendar::app.session_lifecycle')</p><h2 class="mt-1 font-black text-slate-900">@lang('teacher-calendar::app.reschedule_session')</h2></div><button type="button" data-calendar-close class="grid h-9 w-9 place-items-center rounded-xl text-slate-500 hover:bg-amber-50 hover:text-amber-700"><x-heroicon-o-x-mark class="h-5 w-5" /></button></header>
        <form method="POST" data-calendar-reschedule-form class="flex flex-1 flex-col">@csrf
            <div class="flex-1 space-y-4 overflow-y-auto p-5">
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs font-semibold leading-5 text-amber-800">@lang('teacher-calendar::app.reschedule_hint')</div>
                <input type="hidden" name="type"><input type="hidden" name="lesson_id"><input type="hidden" name="delivery_mode"><input type="hidden" name="title"><input type="hidden" name="location"><input type="hidden" name="meeting_url"><input type="hidden" name="description"><input type="hidden" name="makeup_reason">
                <div class="grid grid-cols-3 gap-3"><label class="text-xs font-black text-slate-600">@lang('teacher-calendar::app.date')<input required type="date" name="session_date" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 px-2"></label><label class="text-xs font-black text-slate-600">@lang('teacher-calendar::app.start')<input required type="time" name="start_time" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 px-2"></label><label class="text-xs font-black text-slate-600">@lang('teacher-calendar::app.end')<input required type="time" name="end_time" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 px-2"></label></div>
                <label class="block text-xs font-black text-slate-600">@lang('teacher-calendar::app.reschedule_reason')<textarea required minlength="10" maxlength="1000" name="reschedule_reason" rows="4" class="mt-1.5 w-full rounded-xl border border-amber-200 bg-amber-50/50 p-3 focus:border-amber-400 focus:outline-none focus:ring-4 focus:ring-amber-50"></textarea></label>
            </div>
            <footer class="border-t border-slate-100 p-5"><button class="h-11 w-full rounded-xl bg-amber-500 text-sm font-black text-white hover:bg-amber-600">@lang('teacher-calendar::app.confirm_reschedule')</button></footer>
        </form>
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
