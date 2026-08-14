@extends('Mindigo-dashboard::layouts')
@section('title', __('Mindigo-exam-management::app.template_builder.title'))
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/ExamManagement/src/resources/css/app.css'])
@endsection
@section('content')
<main class="exam-foundation-shell"><div class="exam-foundation-container">
    <x-exam::page-header :eyebrow="__('Mindigo-exam-management::app.template_builder.workspace')" :title="__('Mindigo-exam-management::app.template_builder.title')" :description="__('Mindigo-exam-management::app.template_builder.index_description')"><x-slot:actions><x-exam::button :href="route('teacher.exam-templates.create')"><x-heroicon-o-plus class="h-4 w-4" />@lang('Mindigo-exam-management::app.template_builder.create')</x-exam::button></x-slot:actions></x-exam::page-header>
    <x-exam::panel :title="__('Mindigo-exam-management::app.template_builder.library')" :description="__('Mindigo-exam-management::app.template_builder.library_description')">
        @if($templates->isEmpty())
            <x-exam::empty-state :title="__('Mindigo-exam-management::app.template_builder.empty_title')" :description="__('Mindigo-exam-management::app.template_builder.empty_description')"><x-slot:actions><x-exam::button :href="route('teacher.exam-templates.create')">@lang('Mindigo-exam-management::app.template_builder.create_first')</x-exam::button></x-slot:actions></x-exam::empty-state>
        @else
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">@foreach($templates as $template)
                <article class="rounded-2xl border border-slate-200 bg-white p-5"><div class="flex items-start justify-between gap-3"><div><p class="text-xs font-bold text-slate-400">{{ $template->subject ?: __('Mindigo-exam-management::app.template_builder.uncategorized') }}</p><h2 class="mt-1 text-base font-black text-slate-900">{{ $template->title }}</h2></div><x-exam::status-badge :status="$template->status" :label="__('Mindigo-exam-management::app.template_builder.statuses.'.$template->status)" /></div><p class="mt-4 text-sm font-semibold text-slate-500">{{ __('Mindigo-exam-management::app.template_builder.summary', ['questions' => $template->total_questions, 'points' => $template->total_points, 'versions' => $template->versions_count]) }}</p><div class="mt-5"><x-exam::button variant="secondary" :href="route('teacher.exam-templates.edit', $template)">@lang('Mindigo-exam-management::app.template_builder.open_builder')</x-exam::button></div></article>
            @endforeach</div><div class="mt-5">{{ $templates->links() }}</div>
        @endif
    </x-exam::panel>
</div></main>
@endsection
