@extends('Mindigo-dashboard::layouts')
@section('title', __('Mindigo-exam-management::app.session_builder.title'))
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/ExamManagement/src/resources/css/app.css'])
@endsection
@section('content')
<main class="exam-foundation-shell"><div class="exam-foundation-container">
    <x-exam::page-header :eyebrow="__('Mindigo-exam-management::app.session_builder.workspace')" :title="__('Mindigo-exam-management::app.session_builder.title')" :description="__('Mindigo-exam-management::app.session_builder.description')"><x-slot:actions><x-exam::button :href="route('teacher.exam-sessions.create')"><x-heroicon-o-calendar-days class="h-4 w-4" />@lang('Mindigo-exam-management::app.session_builder.create')</x-exam::button></x-slot:actions></x-exam::page-header>
    @if(session('success'))<div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-bold text-green-700">{{ session('success') }}</div>@endif
    <x-exam::panel :title="__('Mindigo-exam-management::app.session_builder.list')" :description="__('Mindigo-exam-management::app.session_builder.list_description')">
        @if($sessions->isEmpty())<x-exam::empty-state :title="__('Mindigo-exam-management::app.session_builder.empty')" :description="__('Mindigo-exam-management::app.session_builder.empty_description')" />
        @else<div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">@foreach($sessions as $session)
            <article class="rounded-2xl border border-slate-200 bg-white p-5"><div class="flex items-start justify-between gap-3"><div><p class="text-xs font-bold text-slate-400">{{ $session->version->template->title }} · v{{ $session->version->version }}</p><h2 class="mt-1 text-base font-black text-slate-900">{{ $session->title }}</h2></div><x-exam::status-badge :status="$session->status" :label="__('Mindigo-exam-management::app.session_builder.statuses.'.$session->status)" /></div><p class="mt-4 text-sm font-semibold text-slate-500">{{ $session->starts_at?->format('d/m/Y H:i') }}</p><p class="mt-1 text-xs font-bold text-slate-400">{{ __('Mindigo-exam-management::app.session_builder.summary', ['candidates' => $session->candidates_count, 'minutes' => $session->duration_minutes]) }}</p><div class="mt-4"><x-exam::button variant="secondary" :href="route('teacher.exam-sessions.grading.index', $session)">@lang('Mindigo-exam-management::app.grading.open_queue')</x-exam::button></div></article>
        @endforeach</div><div class="mt-5">{{ $sessions->links() }}</div>@endif
    </x-exam::panel>
</div></main>
@endsection
