@extends('Mindigo-dashboard::layouts')
@php
    $editing = $template->exists;
    $version = $editing ? $template->versions->first() : null;
    $selected = collect(old('sections.0.questions', $version?->questions?->map(fn ($question) => ['id' => $question->source_question_id, 'points' => $question->points])->all() ?? []))->keyBy('id');
@endphp
@section('title', $editing ? __('Mindigo-exam-management::app.template_builder.edit_title') : __('Mindigo-exam-management::app.template_builder.create_title'))
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/ExamManagement/src/resources/css/app.css'])
@endsection
@section('content')
<main class="exam-foundation-shell">
<form method="POST" action="{{ $editing ? route('teacher.exam-templates.update', $template) : route('teacher.exam-templates.store') }}" class="exam-foundation-container">@csrf @if($editing) @method('PUT') @endif
    <x-exam::page-header :eyebrow="__('Mindigo-exam-management::app.template_builder.builder')" :title="$editing ? $template->title : __('Mindigo-exam-management::app.template_builder.create_title')" :description="__('Mindigo-exam-management::app.template_builder.form_description')"><x-slot:actions><x-exam::button type="submit">@lang('Mindigo-exam-management::app.template_builder.save_draft')</x-exam::button></x-slot:actions></x-exam::page-header>
    @if($errors->any())<div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-700">{{ $errors->first() }}</div>@endif
    @if(session('success'))<div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-bold text-green-700">{{ session('success') }}</div>@endif
    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(420px,1.4fr)]">
        <x-exam::panel :title="__('Mindigo-exam-management::app.template_builder.information')" :description="__('Mindigo-exam-management::app.template_builder.information_description')"><div class="grid gap-4">
            <label class="text-sm font-bold text-slate-700">@lang('Mindigo-exam-management::app.template_builder.name')<input class="exam-input mt-1" name="title" value="{{ old('title', $template->title) }}" required></label>
            <div class="grid gap-4 sm:grid-cols-2"><label class="text-sm font-bold text-slate-700">@lang('Mindigo-exam-management::app.template_builder.subject')<input class="exam-input mt-1" name="subject" value="{{ old('subject', $template->subject) }}"></label><label class="text-sm font-bold text-slate-700">@lang('Mindigo-exam-management::app.template_builder.topic')<input class="exam-input mt-1" name="topic" value="{{ old('topic', $template->topic) }}"></label></div>
            <label class="text-sm font-bold text-slate-700">@lang('Mindigo-exam-management::app.template_builder.description')<textarea class="exam-textarea mt-1" name="description">{{ old('description', $template->description) }}</textarea></label>
            <label class="text-sm font-bold text-slate-700">@lang('Mindigo-exam-management::app.template_builder.instructions')<textarea class="exam-textarea mt-1" name="instructions">{{ old('instructions', $template->instructions) }}</textarea></label>
            <label class="flex items-center gap-2 text-sm font-bold text-slate-700"><input type="hidden" name="settings[shuffle_questions]" value="0"><input type="checkbox" name="settings[shuffle_questions]" value="1" @checked(old('settings.shuffle_questions', data_get($version, 'settings.shuffle_questions'))) class="rounded border-slate-300 text-green-600">@lang('Mindigo-exam-management::app.template_builder.shuffle_questions')</label>
        </div></x-exam::panel>
        <x-exam::panel :title="__('Mindigo-exam-management::app.template_builder.questions_title')" :description="__('Mindigo-exam-management::app.template_builder.questions_description')">
            <input type="hidden" name="sections[0][title]" value="{{ old('sections.0.title', data_get($version, 'sections.0.title', __('Mindigo-exam-management::app.template_builder.default_section'))) }}">
            <div class="max-h-155 space-y-3 overflow-y-auto pr-1">@forelse($questions as $question) @php($item = $selected->get($question->id))
                <label class="grid grid-cols-[auto_minmax(0,1fr)_90px] items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-green-300"><input type="checkbox" name="sections[0][questions][{{ $question->id }}][id]" value="{{ $question->id }}" @checked($item) data-template-question class="mt-1 rounded border-slate-300 text-green-600"><span><strong class="block text-sm text-slate-900">{{ $question->content }}</strong><small class="mt-1 block font-bold text-slate-400">@lang('Mindigo-exam-management::app.question_types.'.$question->type) · @lang('Mindigo-exam-management::app.difficulties.'.$question->difficulty) · @lang('Mindigo-exam-management::app.template_builder.question_statuses.'.$question->status)</small></span><span><small class="font-bold text-slate-500">@lang('Mindigo-exam-management::app.template_builder.points')</small><input type="number" min="0.25" step="0.25" name="sections[0][questions][{{ $question->id }}][points]" value="{{ data_get($item, 'points', 1) }}" @disabled(!$item) data-template-points class="exam-input mt-1"></span></label>
            @empty <x-exam::empty-state :title="__('Mindigo-exam-management::app.template_builder.no_questions')" :description="__('Mindigo-exam-management::app.template_builder.no_questions_description')" /> @endforelse</div>
        </x-exam::panel>
    </div>
</form>
@if($editing && $template->total_questions > 0)<form method="POST" action="{{ route('teacher.exam-templates.ready', $template) }}" class="mx-auto mt-5 flex w-full max-w-375 justify-end">@csrf<x-exam::button type="submit" variant="secondary">{{ __('Mindigo-exam-management::app.template_builder.mark_ready', ['version' => $template->current_version]) }}</x-exam::button></form>@endif
</main>
@endsection
@section('scripts')
<script>
document.querySelectorAll('[data-template-question]').forEach((checkbox) => {
    const points = checkbox.closest('label').querySelector('[data-template-points]');
    checkbox.addEventListener('change', () => { points.disabled = ! checkbox.checked; });
});
</script>
@endsection
