{{-- Shared Phase 3 creation workflow. Expects $session, $classrooms and $providerCapabilities. --}}
@php
    $editing = $session?->exists === true;
    $tabs = [
        'academic' => __('teacher-live-session::app.form_tabs.academic'),
        'content' => __('teacher-live-session::app.form_tabs.content'),
        'provider' => __('teacher-live-session::app.form_tabs.provider'),
        'permissions' => __('teacher-live-session::app.form_tabs.permissions'),
    ];
    $errorTab = collect([
        'academic' => ['classroom_id', 'classroom_schedule_id', 'session_type'],
        'content' => ['title', 'description', 'scheduled_start', 'scheduled_end'],
        'provider' => ['provider'],
        'permissions' => ['room_settings'],
    ])->first(fn (array $fields) => collect($fields)->contains(fn (string $field) => $errors->has($field) || $errors->has($field.'.*')), 'academic');
    $initialTabIndex = max(0, array_search($errorTab, array_keys($tabs), true));
    $fmt = fn ($date) => $date ? $date->format('Y-m-d\TH:i') : null;
    $settings = old('room_settings', $session?->room_settings ?? [
        'waiting_room_enabled' => true,
        'guest_access_enabled' => false,
        'chat_enabled' => true,
        'private_chat_enabled' => false,
        'student_microphone_enabled' => true,
        'student_camera_enabled' => true,
        'student_screen_share_enabled' => false,
        'recording_enabled' => false,
    ]);
    $selectedClassroom = (string) old('classroom_id', $session?->classroom_id);
    $selectedSchedule = (string) old('classroom_schedule_id', $session?->classroom_schedule_id);
    $selectedProvider = old('provider', $session?->provider?->value ?? 'native');
    $nativeCapabilities = $providerCapabilities['native'] ?? null;
    $guestAccessDisabled = ! ($nativeCapabilities?->guestLinks ?? false);
    $recordingDisabled = ! ($nativeCapabilities?->recording ?? false);
    $classroomContext = $classrooms->mapWithKeys(fn ($classroom) => [(string) $classroom->id => [
        'type' => $classroom->type,
        'courseName' => $classroom->course?->name,
        'schedules' => $classroom->schedules->map(fn ($schedule) => [
            'id' => (string) $schedule->id,
            'type' => $schedule->type,
            'title' => $schedule->title,
            'lesson' => $schedule->lesson?->name,
            'start' => $schedule->session_date->format('Y-m-d').'T'.substr($schedule->start_time, 0, 5),
            'end' => $schedule->session_date->format('Y-m-d').'T'.substr($schedule->end_time, 0, 5),
            'label' => $schedule->session_date->format('d/m/Y').' · '.substr($schedule->start_time, 0, 5).'–'.substr($schedule->end_time, 0, 5).' · '.$schedule->title,
        ])->values(),
    ]]);
@endphp

<div data-live-session-form-tabs data-create-wizard="{{ $editing ? '0' : '1' }}" data-initial-tab-index="{{ $initialTabIndex }}" class="flex min-h-0 flex-1 flex-col gap-4">
<nav role="tablist" class="flex shrink-0 gap-2 overflow-x-auto rounded-2xl border border-slate-200 bg-slate-50 p-1" aria-label="@lang('teacher-live-session::app.form_navigation')">
    @foreach($tabs as $tabKey => $tabLabel)
        <button type="button" role="tab" aria-selected="{{ $loop->index === $initialTabIndex ? 'true' : 'false' }}" aria-controls="live-session-panel-{{ $tabKey }}" data-live-session-form-tab="{{ $tabKey }}" data-tab-index="{{ $loop->index }}"
                @disabled(! $editing && $loop->index > $initialTabIndex)
                class="shrink-0 rounded-xl px-4 py-2.5 text-xs font-black transition {{ $loop->index === $initialTabIndex ? 'bg-white text-green-700 shadow-sm' : 'text-slate-500 hover:text-slate-800' }} {{ ! $editing && $loop->index > $initialTabIndex ? 'cursor-not-allowed opacity-50' : '' }}">
            <span class="mr-1.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-current/10">{{ $loop->iteration }}</span>{{ $tabLabel }}
        </button>
    @endforeach
</nav>

<section id="live-session-panel-academic" role="tabpanel" data-live-session-form-panel="academic" @if($initialTabIndex !== 0) hidden @endif class="min-h-0 flex-1 space-y-5 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start gap-3 border-b border-slate-100 pb-4">
        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-green-50 text-sm font-black text-green-700">1</span>
        <div><h2 class="text-base font-black text-slate-950">@lang('teacher-live-session::app.creation_academic_title')</h2><p class="mt-1 text-xs font-semibold text-slate-500">@lang('teacher-live-session::app.creation_academic_desc')</p></div>
    </div>

    <div class="space-y-1.5">
        <label for="live-classroom" class="text-sm font-bold text-slate-700">@lang('teacher-live-session::app.field_classroom') <span class="text-red-500">*</span></label>
        <select id="live-classroom" name="classroom_id" required class="block h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none focus:border-green-400 focus:ring-2 focus:ring-green-50">
            <option value="">@lang('teacher-live-session::app.classroom_select_placeholder')</option>
            @foreach($classrooms as $classroom)
                <option value="{{ $classroom->id }}" @selected($selectedClassroom === (string) $classroom->id)>{{ __('teacher-live-session::app.classroom_option', ['name' => $classroom->name, 'code' => $classroom->code, 'count' => $classroom->students_count]) }}</option>
            @endforeach
        </select>
        @error('classroom_id')<p class="text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
    </div>

    <div id="academic-context" class="hidden rounded-xl border border-green-100 bg-green-50 p-4">
        <div class="flex items-start gap-3"><x-heroicon-o-academic-cap class="mt-0.5 h-5 w-5 shrink-0 text-green-700" /><div><p id="academic-context-title" class="text-sm font-black text-green-950"></p><p id="academic-context-desc" class="mt-1 text-xs font-semibold leading-5 text-green-700"></p></div></div>
    </div>

    <div id="session-type-wrap" class="hidden space-y-2">
        <span class="text-sm font-bold text-slate-700">@lang('teacher-live-session::app.field_session_type') <span class="text-red-500">*</span></span>
        <div class="grid gap-3 sm:grid-cols-2">
            @foreach(['regular' => 'regular', 'makeup' => 'makeup'] as $value => $label)
                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-3 transition has-checked:border-green-500 has-checked:bg-green-50">
                    <input type="radio" name="session_type" value="{{ $value }}" class="mt-1 text-green-600 focus:ring-green-500" @checked(old('session_type', $session?->session_type?->value ?? 'regular') === $value)>
                    <span><strong class="block text-sm text-slate-900">{{ __('teacher-live-session::app.session_type_'.$label) }}</strong><small class="mt-1 block font-semibold text-slate-500">{{ __('teacher-live-session::app.session_type_'.$label.'_hint') }}</small></span>
                </label>
            @endforeach
        </div>
        @error('session_type')<p class="text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
    </div>
    <input id="standalone-session-type" type="hidden" name="session_type" value="flexible" disabled>

    <div class="space-y-1.5">
        <div class="flex items-center justify-between"><label for="live-schedule" class="text-sm font-bold text-slate-700">@lang('teacher-live-session::app.field_schedule_link')</label><span id="schedule-requirement" class="text-[11px] font-bold text-slate-400"></span></div>
        <select id="live-schedule" name="classroom_schedule_id" class="block h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none focus:border-green-400">
            <option value="">@lang('teacher-live-session::app.schedule_select_placeholder')</option>
        </select>
        <p id="schedule-hint" class="text-xs font-semibold leading-5 text-slate-500">@lang('teacher-live-session::app.schedule_initial_hint')</p>
        @error('classroom_schedule_id')<p class="text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
    </div>
</section>

<section id="live-session-panel-content" role="tabpanel" data-live-session-form-panel="content" @if($initialTabIndex !== 1) hidden @endif class="min-h-0 flex-1 space-y-5 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start gap-3 border-b border-slate-100 pb-4">
        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-green-50 text-sm font-black text-green-700">2</span>
        <div><h2 class="text-base font-black text-slate-950">@lang('teacher-live-session::app.creation_content_title')</h2><p class="mt-1 text-xs font-semibold text-slate-500">@lang('teacher-live-session::app.creation_content_desc')</p></div>
    </div>
    <div class="space-y-1.5"><label for="live-title" class="text-sm font-bold text-slate-700">@lang('teacher-live-session::app.field_title') <span class="text-red-500">*</span></label><input id="live-title" type="text" name="title" value="{{ old('title', $session?->title) }}" maxlength="255" required class="block w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-800 outline-none focus:border-green-400 focus:ring-2 focus:ring-green-50" placeholder="{{ __('teacher-live-session::app.title_placeholder') }}">@error('title')<p class="text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
        <div class="space-y-1.5"><label for="live-start" class="text-sm font-bold text-slate-700">@lang('teacher-live-session::app.field_start') <span class="text-red-500">*</span></label><input id="live-start" type="datetime-local" name="scheduled_start" value="{{ old('scheduled_start', $fmt($session?->scheduled_start)) }}" required class="block w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-800 outline-none focus:border-green-400">@error('scheduled_start')<p class="text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
        <div class="space-y-1.5"><label for="live-end" class="text-sm font-bold text-slate-700">@lang('teacher-live-session::app.field_end') <span class="text-red-500">*</span></label><input id="live-end" type="datetime-local" name="scheduled_end" value="{{ old('scheduled_end', $fmt($session?->scheduled_end)) }}" required class="block w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-800 outline-none focus:border-green-400">@error('scheduled_end')<p class="text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
    </div>
    <p id="time-lock-hint" class="hidden rounded-lg bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600">@lang('teacher-live-session::app.course_time_locked')</p>
    <div class="space-y-1.5"><label for="live-description" class="text-sm font-bold text-slate-700">@lang('teacher-live-session::app.field_desc')</label><textarea id="live-description" name="description" rows="4" maxlength="2000" class="block w-full resize-none rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 outline-none focus:border-green-400" placeholder="{{ __('teacher-live-session::app.desc_placeholder') }}">{{ old('description', $session?->description) }}</textarea>@error('description')<p class="text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
</section>

<section id="live-session-panel-provider" role="tabpanel" data-live-session-form-panel="provider" @if($initialTabIndex !== 2) hidden @endif class="min-h-0 flex-1 space-y-5 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start gap-3 border-b border-slate-100 pb-4"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-green-50 text-sm font-black text-green-700">3</span><div><h2 class="text-base font-black text-slate-950">@lang('teacher-live-session::app.creation_provider_title')</h2><p class="mt-1 text-xs font-semibold text-slate-500">@lang('teacher-live-session::app.creation_provider_desc')</p></div></div>
    <div class="grid gap-3 md:grid-cols-3">
        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-green-500 bg-green-50 p-4">
            <input type="radio" name="provider" value="native" class="mt-1 text-green-600 focus:ring-green-500" @checked($selectedProvider === 'native')>
            <span><strong class="block text-sm text-slate-950">Mindigo Live</strong><small class="mt-1 block font-semibold leading-5 text-slate-600">@lang('teacher-live-session::app.provider_native_hint')</small><em class="mt-2 inline-flex rounded-full bg-green-100 px-2 py-1 text-[10px] font-black not-italic text-green-800">@lang('teacher-live-session::app.provider_recommended')</em></span>
        </label>
        @foreach(['google_meet' => 'Google Meet', 'zoom' => 'Zoom'] as $provider => $name)
            <div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 opacity-70"><input type="radio" disabled class="mt-1"><span><strong class="block text-sm text-slate-700">{{ $name }}</strong><small class="mt-1 block font-semibold leading-5 text-slate-500">@lang('teacher-live-session::app.provider_not_connected')</small><em class="mt-2 inline-flex rounded-full bg-slate-200 px-2 py-1 text-[10px] font-black not-italic text-slate-600">@lang('teacher-live-session::app.provider_coming')</em></span></div>
        @endforeach
    </div>
    @error('provider')<p class="text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
</section>

<section id="live-session-panel-permissions" role="tabpanel" data-live-session-form-panel="permissions" @if($initialTabIndex !== 3) hidden @endif class="min-h-0 flex-1 space-y-5 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start gap-3 border-b border-slate-100 pb-4"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-green-50 text-sm font-black text-green-700">4</span><div><h2 class="text-base font-black text-slate-950">@lang('teacher-live-session::app.creation_permissions_title')</h2><p class="mt-1 text-xs font-semibold text-slate-500">@lang('teacher-live-session::app.creation_permissions_desc')</p></div></div>
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            'waiting_room_enabled' => ['waiting_room', false],
            'chat_enabled' => ['chat', false],
            'private_chat_enabled' => ['private_chat', false],
            'student_microphone_enabled' => ['student_microphone', false],
            'student_camera_enabled' => ['student_camera', false],
            'student_screen_share_enabled' => ['student_screen_share', false],
            'guest_access_enabled' => ['guest_access', $guestAccessDisabled],
            'recording_enabled' => ['recording', $recordingDisabled],
        ] as $key => [$translation, $disabled])
            <label class="flex items-start justify-between gap-3 rounded-xl border border-slate-200 p-3 {{ $disabled ? 'bg-slate-50 opacity-60' : 'cursor-pointer' }}">
                <span><strong class="block text-sm text-slate-800">{{ __('teacher-live-session::app.permission_'.$translation) }}</strong><small class="mt-1 block font-semibold leading-5 text-slate-500">{{ __('teacher-live-session::app.permission_'.$translation.'_hint') }}</small></span>
                <span class="shrink-0"><input type="hidden" name="room_settings[{{ $key }}]" value="0"><input type="checkbox" name="room_settings[{{ $key }}]" value="1" class="h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500" @checked((bool) ($settings[$key] ?? false)) @disabled($disabled)></span>
            </label>
            @error('room_settings.'.$key)<p class="text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
        @endforeach
    </div>
</section>
</div>

<script>
(() => {
    const contexts = @json($classroomContext);
    const selectedSchedule = @json($selectedSchedule);
    const classroom = document.getElementById('live-classroom');
    const schedule = document.getElementById('live-schedule');
    const typeWrap = document.getElementById('session-type-wrap');
    const standaloneType = document.getElementById('standalone-session-type');
    const contextBox = document.getElementById('academic-context');
    const start = document.getElementById('live-start');
    const end = document.getElementById('live-end');
    const title = document.getElementById('live-title');
    const timeHint = document.getElementById('time-lock-hint');
    const tabRoot = document.querySelector('[data-live-session-form-tabs]');
    const form = tabRoot.closest('form');
    const tabs = [...tabRoot.querySelectorAll('[data-live-session-form-tab]')];
    const panels = [...tabRoot.querySelectorAll('[data-live-session-form-panel]')];
    const isCreateWizard = tabRoot.dataset.createWizard === '1';
    const nextButton = form.querySelector('[data-live-session-form-next]');
    const submitButton = form.querySelector('[data-live-session-form-submit]');
    let currentIndex = Number(tabRoot.dataset.initialTabIndex || 0);
    let unlockedIndex = isCreateWizard ? currentIndex : tabs.length - 1;

    const refreshNavigation = () => {
        tabs.forEach((tab, index) => {
            const active = index === currentIndex;
            const locked = isCreateWizard && index > unlockedIndex;
            tab.disabled = locked;
            tab.setAttribute('aria-selected', String(active));
            tab.setAttribute('aria-disabled', String(locked));
            tab.classList.toggle('bg-white', active);
            tab.classList.toggle('text-green-700', active);
            tab.classList.toggle('shadow-sm', active);
            tab.classList.toggle('text-slate-500', ! active);
            tab.classList.toggle('cursor-not-allowed', locked);
            tab.classList.toggle('opacity-50', locked);
        });
        panels.forEach((panel, index) => { panel.hidden = index !== currentIndex; });
        if (isCreateWizard && nextButton && submitButton) {
            const last = currentIndex === tabs.length - 1;
            nextButton.hidden = last;
            submitButton.hidden = ! last;
        }
    };

    const activateTab = index => {
        if (index < 0 || index >= tabs.length || (isCreateWizard && index > unlockedIndex)) return;
        currentIndex = index;
        refreshNavigation();
        tabRoot.scrollIntoView({behavior: 'smooth', block: 'start'});
    };

    const validatePanel = panel => {
        const invalid = [...panel.querySelectorAll('input, select, textarea')]
            .find(control => ! control.disabled && ! control.checkValidity());
        if (! invalid) return true;
        invalid.reportValidity();
        invalid.focus({preventScroll: true});
        invalid.scrollIntoView({behavior: 'smooth', block: 'center'});
        return false;
    };

    tabs.forEach((tab, index) => tab.addEventListener('click', () => activateTab(index)));
    nextButton?.addEventListener('click', () => {
        if (! validatePanel(panels[currentIndex])) return;
        unlockedIndex = Math.max(unlockedIndex, currentIndex + 1);
        activateTab(currentIndex + 1);
    });
    form.addEventListener('submit', event => {
        const invalid = [...form.elements].find(control => typeof control.checkValidity === 'function' && ! control.disabled && ! control.checkValidity());
        if (! invalid) return;
        event.preventDefault();
        const panelIndex = panels.findIndex(panel => panel.contains(invalid));
        unlockedIndex = Math.max(unlockedIndex, panelIndex);
        activateTab(panelIndex);
        window.setTimeout(() => invalid.reportValidity());
    });

    const render = (preserveSchedule = false) => {
        const context = contexts[classroom.value];
        const previous = preserveSchedule ? selectedSchedule : schedule.value;
        schedule.innerHTML = `<option value="">${@json(__('teacher-live-session::app.schedule_select_placeholder'))}</option>`;
        if (!context) {
            contextBox.classList.add('hidden'); typeWrap.classList.add('hidden'); standaloneType.disabled = false; schedule.required = false; return;
        }
        const courseClass = context.type === 'course';
        contextBox.classList.remove('hidden');
        document.getElementById('academic-context-title').textContent = courseClass ? @json(__('teacher-live-session::app.course_class_label')) : @json(__('teacher-live-session::app.standalone_class_label'));
        document.getElementById('academic-context-desc').textContent = courseClass ? `${context.courseName || ''} · ${@json(__('teacher-live-session::app.course_class_rule'))}` : @json(__('teacher-live-session::app.standalone_class_rule'));
        typeWrap.classList.toggle('hidden', !courseClass); standaloneType.disabled = courseClass;
        schedule.required = courseClass;
        document.querySelectorAll('[name="session_type"][type="radio"]').forEach(input => input.disabled = !courseClass);
        document.getElementById('schedule-requirement').textContent = courseClass ? @json(__('teacher-live-session::app.required_label')) : @json(__('teacher-live-session::app.optional_label'));
        document.getElementById('schedule-hint').textContent = courseClass ? @json(__('teacher-live-session::app.course_schedule_hint')) : @json(__('teacher-live-session::app.standalone_schedule_hint'));
        context.schedules.forEach(item => schedule.add(new Option(item.label, item.id)));
        if ([...schedule.options].some(option => option.value === previous)) schedule.value = previous;
        start.readOnly = courseClass; end.readOnly = courseClass; timeHint.classList.toggle('hidden', !courseClass);
        applySchedule();
    };

    const applySchedule = () => {
        const context = contexts[classroom.value];
        const item = context?.schedules.find(entry => entry.id === schedule.value);
        if (!item) {
            if (context?.type === 'course') { start.value = ''; end.value = ''; }
            return;
        }
        if (!title.value.trim()) title.value = item.lesson || item.title;
        start.value = item.start; end.value = item.end;
        if (context.type === 'course') {
            const type = document.querySelector(`[name="session_type"][value="${item.type}"]`);
            if (type) type.checked = true;
        }
    };

    classroom.addEventListener('change', () => render(false));
    schedule.addEventListener('change', applySchedule);
    render(true);
})();
</script>
