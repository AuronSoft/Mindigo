@extends('Mindigo-dashboard::layouts')
@section('title', __('Mindigo-exam-management::app.grading.title'))
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/ExamManagement/src/resources/css/app.css'])
@endsection
@section('content')
<main class="exam-foundation-shell">
    <div class="exam-foundation-container">
        <x-exam::page-header :eyebrow="__('Mindigo-exam-management::app.grading.workspace')" :title="__('Mindigo-exam-management::app.grading.title')" :description="__('Mindigo-exam-management::app.grading.description', ['session' => $session->title])">
            <x-slot:actions><x-exam::button variant="secondary" :href="route('teacher.exam-sessions.grading.export.excel', $session)">@lang('Mindigo-exam-management::app.grading.export_excel')</x-exam::button><x-exam::button variant="secondary" :href="route('teacher.exam-sessions.grading.export.pdf', $session)">@lang('Mindigo-exam-management::app.grading.export_pdf')</x-exam::button><x-exam::button variant="secondary" :href="route('teacher.exam-sessions.index')">@lang('Mindigo-exam-management::app.grading.back_sessions')</x-exam::button></x-slot:actions>
        </x-exam::page-header>
        @if(session('success'))<div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-bold text-green-700">{{ session('success') }}</div>@endif
        <div class="grid gap-4 md:grid-cols-5">
            <x-exam::stat-card :label="__('Mindigo-exam-management::app.grading.submissions')" :value="$summary['submissions']" />
            <x-exam::stat-card :label="__('Mindigo-exam-management::app.grading.pending')" :value="$summary['pending']" tone="amber" />
            <x-exam::stat-card :label="__('Mindigo-exam-management::app.grading.released_count')" :value="$summary['released']" tone="green" />
            <x-exam::stat-card :label="__('Mindigo-exam-management::app.grading.completed_count')" :value="$summary['completed']" tone="green" />
            <x-exam::stat-card :label="__('Mindigo-exam-management::app.grading.appeals')" :value="$summary['appeals']" tone="amber" />
        </div>
        <div class="grid gap-4 lg:grid-cols-2"><x-exam::panel :title="__('Mindigo-exam-management::app.grading.grade_by_question')"><div class="flex flex-wrap gap-2">@foreach($questions as $question)<x-exam::button variant="secondary" :href="route('teacher.exam-sessions.grading.question', [$session, $question])">{{ __('Mindigo-exam-management::app.grading.question', ['number' => $loop->iteration]) }}</x-exam::button>@endforeach</div></x-exam::panel><x-exam::panel :title="__('Mindigo-exam-management::app.grading.tools')"><div class="space-y-3"><form method="POST" action="{{ route('teacher.exam-sessions.grading.regrade', $session) }}">@csrf<x-exam::button type="submit" variant="secondary">@lang('Mindigo-exam-management::app.grading.regrade')</x-exam::button></form>@if($isOrganizer)<form method="POST" action="{{ route('teacher.exam-sessions.grading.assign', $session) }}" class="flex gap-2">@csrf<select name="grader_id" class="exam-select" required><option value="">@lang('Mindigo-exam-management::app.grading.select_grader')</option>@foreach($availableGraders as $grader)<option value="{{ $grader->id }}">{{ $grader->name }} · {{ $grader->email }}</option>@endforeach</select><x-exam::button type="submit">@lang('Mindigo-exam-management::app.grading.assign')</x-exam::button></form><p class="text-xs font-semibold text-slate-500">{{ $assignments->pluck('grader.name')->join(', ') }}</p>@endif</div></x-exam::panel></div>
        <x-exam::panel :title="__('Mindigo-exam-management::app.grading.queue')" :description="__('Mindigo-exam-management::app.grading.queue_description')">
            @if($attempts->isEmpty())
                <x-exam::empty-state :title="__('Mindigo-exam-management::app.grading.empty')" :description="__('Mindigo-exam-management::app.grading.empty_description')" />
            @else
                <div class="overflow-x-auto"><table class="w-full min-w-180 text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs font-black uppercase tracking-wider text-slate-400"><tr><th class="px-4 py-3">@lang('Mindigo-exam-management::app.grading.candidate')</th><th class="px-4 py-3">@lang('Mindigo-exam-management::app.grading.submitted_at')</th><th class="px-4 py-3">@lang('Mindigo-exam-management::app.grading.score')</th><th class="px-4 py-3">@lang('Mindigo-exam-management::app.grading.state')</th><th class="px-4 py-3 text-right">@lang('Mindigo-exam-management::app.grading.action')</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">@foreach($attempts as $attempt)<tr>
                        <td class="px-4 py-4"><p class="font-black text-slate-900">{{ $session->anonymous_grading ? $attempt->anonymous_code : ($attempt->candidate?->name ?? $attempt->user?->name) }}</p>@unless($session->anonymous_grading)<p class="mt-0.5 text-xs font-semibold text-slate-400">{{ $attempt->candidate?->email ?? $attempt->user?->email }}</p>@endunless</td>
                        <td class="px-4 py-4 font-semibold text-slate-500">{{ $attempt->submitted_at?->format('d/m/Y H:i') }}</td><td class="px-4 py-4 font-black text-slate-800">{{ $attempt->score }}/{{ $attempt->max_score }}</td>
                        <td class="px-4 py-4"><x-exam::status-badge :status="$attempt->released_at ? 'completed' : ($attempt->needs_review ? 'grading' : 'scheduled')" :label="__('Mindigo-exam-management::app.grading.statuses.'.($attempt->released_at ? 'released' : ($attempt->needs_review ? 'pending_manual' : $attempt->grading_status)))" />@if($attempt->appeals->where('status', 'open')->isNotEmpty())<span class="ml-2 text-xs font-black text-amber-700">@lang('Mindigo-exam-management::app.grading.appeal_open')</span>@endif</td>
                        <td class="px-4 py-4 text-right"><x-exam::button :href="route('teacher.exam-sessions.grading.show', [$session, $attempt])">@lang('Mindigo-exam-management::app.grading.open')</x-exam::button></td>
                    </tr>@endforeach</tbody>
                </table></div><div class="mt-5">{{ $attempts->links() }}</div>
            @endif
        </x-exam::panel>
    </div>
</main>
@endsection
