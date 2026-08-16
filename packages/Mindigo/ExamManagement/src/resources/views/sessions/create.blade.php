@extends('Mindigo-dashboard::layouts')
@section('title', __('Mindigo-exam-management::app.session_builder.create'))
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/ExamManagement/src/resources/css/app.css'])
@endsection
@section('content')
@php
    $formatDateTimeInput = static function ($value): string {
        if (blank($value)) {
            return '';
        }

        if (is_string($value) && preg_match('/^\d{2}\/\d{2}\/\d{4}\s\d{2}:\d{2}$/', $value)) {
            return $value;
        }

        try {
            return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return (string) $value;
        }
    };
    $formatDateTimePicker = static function ($value) use ($formatDateTimeInput): string {
        if (blank($value)) {
            return '';
        }

        try {
            return \Carbon\Carbon::createFromFormat('d/m/Y H:i', $formatDateTimeInput($value))->format('Y-m-d\TH:i');
        } catch (\Throwable) {
            return '';
        }
    };
@endphp
<main class="exam-foundation-shell"><form method="POST" action="{{ route('teacher.exam-sessions.store') }}" class="exam-foundation-container">@csrf
    <x-exam::page-header :eyebrow="__('Mindigo-exam-management::app.session_builder.workspace')" :title="__('Mindigo-exam-management::app.session_builder.create')" :description="__('Mindigo-exam-management::app.session_builder.description')"><x-slot:actions><x-exam::button type="submit">@lang('Mindigo-exam-management::app.session_builder.schedule')</x-exam::button></x-slot:actions></x-exam::page-header>
    @if($errors->any())<div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-700">{{ $errors->first() }}</div>@endif
    <div class="grid gap-5 xl:grid-cols-2">
        <x-exam::panel :title="__('Mindigo-exam-management::app.session_builder.information')"><div class="grid gap-4">
            <label class="text-sm font-bold text-slate-700">@lang('Mindigo-exam-management::app.session_builder.template')<select name="exam_template_version_id" class="exam-select mt-1" required><option value="">@lang('Mindigo-exam-management::app.session_builder.select_template')</option>@foreach($versions as $version)<option value="{{ $version->id }}" @selected(old('exam_template_version_id') == $version->id)>{{ __('Mindigo-exam-management::app.session_builder.version_summary', ['title' => $version->template->title, 'version' => $version->version, 'questions' => $version->total_questions]) }}</option>@endforeach</select></label>
            <label class="text-sm font-bold text-slate-700">@lang('Mindigo-exam-management::app.session_builder.name')<input name="title" value="{{ old('title') }}" class="exam-input mt-1" required></label>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach(['starts_at', 'ends_at'] as $dateTimeField)
                    @php($dateTimeValue = old($dateTimeField))
                    <label class="text-sm font-bold text-slate-700">@lang('Mindigo-exam-management::app.session_builder.'.$dateTimeField)<span class="relative mt-1 block" data-exam-datetime-field><input type="text" name="{{ $dateTimeField }}" value="{{ $formatDateTimeInput($dateTimeValue) }}" class="exam-input cursor-pointer pr-11" placeholder="@lang('Mindigo-exam-management::app.datetime_placeholder')" readonly required data-exam-datetime-display><input type="datetime-local" value="{{ $formatDateTimePicker($dateTimeValue) }}" class="pointer-events-none absolute bottom-0 right-0 h-px w-px opacity-0" tabindex="-1" aria-hidden="true" data-exam-datetime-picker><button type="button" class="absolute inset-y-0 right-0 grid w-11 place-items-center text-slate-400 transition hover:text-green-700" aria-label="@lang('Mindigo-exam-management::app.pick_datetime')" data-exam-datetime-trigger><x-heroicon-o-calendar-days class="h-5 w-5" /></button></span><small class="mt-1 block text-xs font-semibold text-slate-400">@lang('Mindigo-exam-management::app.datetime_hint')</small></label>
                @endforeach
            </div>
            <div class="grid gap-4 sm:grid-cols-3"><label class="text-sm font-bold text-slate-700">@lang('Mindigo-exam-management::app.session_builder.duration')<input type="number" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" class="exam-input mt-1" min="1" required></label><label class="text-sm font-bold text-slate-700">@lang('Mindigo-exam-management::app.session_builder.attempts')<input type="number" name="max_attempts" value="{{ old('max_attempts', 1) }}" class="exam-input mt-1" min="1" required></label><label class="text-sm font-bold text-slate-700">@lang('Mindigo-exam-management::app.session_builder.passing_score')<input type="number" name="passing_score" value="{{ old('passing_score', 0) }}" class="exam-input mt-1" min="0" step="0.25" required></label></div>
            <label class="text-sm font-bold text-slate-700">@lang('Mindigo-exam-management::app.session_builder.result_policy')<select name="result_policy" class="exam-select mt-1">@foreach(['immediately', 'after_end', 'after_release'] as $policy)<option value="{{ $policy }}" @selected(old('result_policy', 'after_release') === $policy)>@lang('Mindigo-exam-management::app.session_builder.'.$policy)</option>@endforeach</select></label>
        </div></x-exam::panel>
        <div class="grid gap-5"><x-exam::panel :title="__('Mindigo-exam-management::app.session_builder.classrooms')" :description="__('Mindigo-exam-management::app.session_builder.classrooms_description')"><div class="grid gap-3">@foreach($classrooms as $classroom)<label class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 p-4"><span class="flex items-center gap-3"><input type="checkbox" name="classroom_ids[]" value="{{ $classroom->id }}" @checked(in_array($classroom->id, old('classroom_ids', []))) class="rounded border-slate-300 text-green-600"><strong class="text-sm text-slate-800">{{ $classroom->name }}</strong></span><small class="font-bold text-slate-400">{{ __('Mindigo-exam-management::app.session_builder.students', ['count' => $classroom->students_count]) }}</small></label>@endforeach</div></x-exam::panel>
        <x-exam::panel :title="__('Mindigo-exam-management::app.session_builder.security')" :description="__('Mindigo-exam-management::app.session_builder.security_description')"><div class="grid gap-3 sm:grid-cols-2">
            @foreach(['shuffle_questions', 'shuffle_answers', 'anonymous_grading'] as $setting)<label class="flex items-center gap-2 text-sm font-bold text-slate-700"><input type="hidden" name="{{ $setting }}" value="0"><input type="checkbox" name="{{ $setting }}" value="1" @checked(old($setting, $setting !== 'anonymous_grading')) class="rounded border-slate-300 text-green-600">@lang('Mindigo-exam-management::app.session_builder.'.$setting)</label>@endforeach
            @foreach(['fullscreen', 'tab_switch_detection', 'clipboard_detection', 'multiple_sessions_detection', 'ip_change_detection', 'device_change_detection', 'heartbeat_detection', 'refresh_detection', 'camera_enabled'] as $setting)<label class="flex items-center gap-2 text-sm font-bold text-slate-700"><input type="hidden" name="security_policy[{{ $setting }}]" value="0"><input type="checkbox" name="security_policy[{{ $setting }}]" value="1" @checked(old('security_policy.'.$setting, $setting !== 'camera_enabled')) class="rounded border-slate-300 text-green-600">@lang('Mindigo-exam-management::app.session_builder.'.$setting)</label>@endforeach
        </div><div class="mt-5 grid gap-4 sm:grid-cols-2">
            @foreach(['heartbeat_interval_seconds' => 30, 'heartbeat_grace_seconds' => 90, 'disconnect_threshold_seconds' => 180, 'camera_capture_interval_seconds' => 120] as $setting => $default)<label class="text-sm font-bold text-slate-700">@lang('Mindigo-exam-management::app.session_builder.'.$setting)<input type="number" name="security_policy[{{ $setting }}]" value="{{ old('security_policy.'.$setting, $default) }}" class="exam-input mt-1"></label>@endforeach
        </div></x-exam::panel></div>
    </div>
</form></main>
@endsection
