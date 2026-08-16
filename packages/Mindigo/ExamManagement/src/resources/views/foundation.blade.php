@extends('Mindigo-dashboard::layouts')
@section('title', __('Mindigo-exam-management::app.foundation.foundation_title'))
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/ExamManagement/src/resources/css/app.css'])
@endsection
@section('content')
<main class="exam-foundation-shell"><div class="exam-foundation-container">
    <x-exam::page-header :eyebrow="__('Mindigo-exam-management::app.foundation.dashboard.eyebrow')" :title="__('Mindigo-exam-management::app.foundation.foundation_title')" :description="__('Mindigo-exam-management::app.foundation.foundation_subtitle')">
        <x-slot:actions>
            <x-exam::button variant="secondary" :href="route('teacher.exam-templates.index')"><x-heroicon-o-rectangle-stack class="h-4 w-4" />@lang('Mindigo-exam-management::app.foundation.dashboard.manage_templates')</x-exam::button>
            <x-exam::button :href="route('teacher.exam-sessions.create')"><x-heroicon-o-plus class="h-4 w-4" />@lang('Mindigo-exam-management::app.foundation.dashboard.create_session')</x-exam::button>
        </x-slot:actions>
    </x-exam::page-header>

    <section class="exam-dashboard-metrics" aria-label="@lang('Mindigo-exam-management::app.foundation.dashboard.overview')">
        <article class="exam-dashboard-metric exam-dashboard-metric--score">
            <div><p>@lang('Mindigo-exam-management::app.foundation.dashboard.average_score')</p><strong>{{ number_format($metrics['averageScore'], 1) }}%</strong><span>{{ __('Mindigo-exam-management::app.foundation.dashboard.pass_rate', ['rate' => number_format($metrics['passRate'], 1)]) }}</span></div>
            <div class="exam-score-ring" style="--score: {{ min(100, max(0, $metrics['averageScore'])) }}"><span>{{ number_format($metrics['averageScore'], 0) }}%</span></div>
        </article>
        <article class="exam-dashboard-metric">
            <div class="exam-dashboard-metric-heading"><div><p>@lang('Mindigo-exam-management::app.foundation.dashboard.active_students')</p><strong>{{ number_format($metrics['activeStudents']) }}</strong></div><span class="exam-dashboard-metric-icon exam-dashboard-metric-icon--amber"><x-heroicon-o-user-group class="h-5 w-5" /></span></div>
            <div class="exam-mini-bars" aria-hidden="true">@foreach($monthlyActivity as $month)<i style="height: {{ max(16, round($month['count'] / $monthlyMaximum * 100)) }}%"></i>@endforeach</div>
        </article>
        <article class="exam-dashboard-metric">
            <div class="exam-dashboard-metric-heading"><div><p>@lang('Mindigo-exam-management::app.foundation.dashboard.question_bank')</p><strong>{{ number_format($metrics['questions']) }}</strong></div><span class="exam-dashboard-metric-icon exam-dashboard-metric-icon--teal"><x-heroicon-o-question-mark-circle class="h-5 w-5" /></span></div>
            <div class="exam-question-wave" aria-hidden="true">@foreach([45,72,38,84,55,76,44,68,35,79] as $height)<i style="height: {{ $height }}%"></i>@endforeach</div>
        </article>
        <article class="exam-dashboard-metric">
            <div class="exam-dashboard-metric-heading"><div><p>@lang('Mindigo-exam-management::app.foundation.dashboard.pending_grading')</p><strong>{{ number_format($metrics['pendingGrading']) }}</strong></div><span class="exam-dashboard-metric-icon"><x-heroicon-o-pencil-square class="h-5 w-5" /></span></div>
            <div class="mt-5 flex items-center justify-between text-xs font-bold text-slate-500"><span>@lang('Mindigo-exam-management::app.foundation.dashboard.templates')</span><span class="rounded-full bg-green-50 px-3 py-1 text-green-700">{{ number_format($metrics['templates']) }}</span></div>
        </article>
    </section>

    <section class="grid gap-5 xl:grid-cols-[1.05fr_.95fr]">
        <article class="exam-dashboard-card">
            <header class="exam-dashboard-card-header"><div><h2>@lang('Mindigo-exam-management::app.foundation.dashboard.exam_activity')</h2><p>@lang('Mindigo-exam-management::app.foundation.dashboard.exam_activity_desc')</p></div><span class="exam-dashboard-period"><x-heroicon-o-calendar-days class="h-4 w-4" />@lang('Mindigo-exam-management::app.foundation.dashboard.last_12_months')</span></header>
            <div class="exam-activity-chart"><div class="exam-chart-grid" aria-hidden="true"><i></i><i></i><i></i><i></i></div><div class="exam-chart-columns">
                @foreach($monthlyActivity as $month)<div class="exam-chart-column" title="{{ $month['label'] }}: {{ $month['count'] }}"><span class="exam-chart-value">{{ $month['count'] }}</span><i style="height: {{ max(6, round($month['count'] / $monthlyMaximum * 100)) }}%"></i><small>{{ $month['label'] }}</small></div>@endforeach
            </div></div>
        </article>
        <article class="exam-dashboard-card">
            <header class="exam-dashboard-card-header"><div><h2>@lang('Mindigo-exam-management::app.foundation.dashboard.question_difficulty')</h2><p>@lang('Mindigo-exam-management::app.foundation.dashboard.question_difficulty_desc')</p></div></header>
            @php($difficultyTotal = max(1, $difficulty->sum()))
            <div class="exam-difficulty-summary">@foreach(['easy' => 'green', 'medium' => 'teal', 'hard' => 'amber'] as $level => $tone)<div class="exam-difficulty-row"><div class="exam-difficulty-label"><span class="exam-legend-dot" data-tone="{{ $tone }}"></span>@lang('Mindigo-exam-management::app.foundation.dashboard.'.$level)<strong>{{ $difficulty[$level] }}</strong></div><div class="exam-difficulty-track"><i data-tone="{{ $tone }}" style="width: {{ $difficulty[$level] / $difficultyTotal * 100 }}%"></i></div></div>@endforeach</div>
            <div class="exam-dashboard-insight"><x-heroicon-o-chart-bar-square class="h-5 w-5" /><span>{{ __('Mindigo-exam-management::app.foundation.dashboard.question_total', ['count' => number_format($metrics['questions'])]) }}</span></div>
        </article>
    </section>

    <article class="exam-dashboard-card">
        <header class="exam-dashboard-card-header"><div><h2>@lang('Mindigo-exam-management::app.foundation.dashboard.recent_results')</h2><p>@lang('Mindigo-exam-management::app.foundation.dashboard.recent_results_desc')</p></div><x-exam::button variant="secondary" :href="route('teacher.exam-sessions.index')">@lang('Mindigo-exam-management::app.foundation.dashboard.view_sessions')</x-exam::button></header>
        @if($recentAttempts->isEmpty())
            <x-exam::empty-state :title="__('Mindigo-exam-management::app.foundation.dashboard.no_attempts')" :description="__('Mindigo-exam-management::app.foundation.dashboard.no_attempts_desc')" />
        @else
            <div class="overflow-x-auto"><table class="exam-dashboard-table"><thead><tr><th>@lang('Mindigo-exam-management::app.foundation.dashboard.learner')</th><th>@lang('Mindigo-exam-management::app.foundation.dashboard.exam')</th><th>@lang('Mindigo-exam-management::app.foundation.dashboard.score')</th><th>@lang('Mindigo-exam-management::app.foundation.dashboard.time')</th><th>@lang('Mindigo-exam-management::app.status')</th><th class="text-right">@lang('Mindigo-exam-management::app.actions')</th></tr></thead><tbody>
            @foreach($recentAttempts as $attempt)
                @php($learnerName = $attempt->candidate?->name ?? $attempt->user?->name ?? __('Mindigo-exam-management::app.foundation.dashboard.unknown_learner'))
                <tr><td><div class="exam-learner"><span>{{ str($learnerName)->substr(0, 1)->upper() }}</span><div><strong>{{ $learnerName }}</strong><small>{{ $attempt->candidate?->student_code ?? $attempt->user?->email }}</small></div></div></td><td><strong class="text-slate-700">{{ $attempt->session?->title }}</strong></td><td><span class="exam-score-pill" data-passed="{{ $attempt->passed ? 'true' : 'false' }}">{{ $attempt->percentage !== null ? number_format((float) $attempt->percentage, 1).'%' : '—' }}</span></td><td>{{ $attempt->started_at?->diffForHumans($attempt->submitted_at ?? $attempt->updated_at, true) ?? '—' }}</td><td><x-exam::status-badge :status="$attempt->status" :label="__('Mindigo-exam-management::app.foundation.dashboard.statuses.'.$attempt->status)" /></td><td class="text-right"><a class="exam-row-action" href="{{ route('teacher.exam-sessions.monitoring.index', $attempt->session) }}" aria-label="@lang('Mindigo-exam-management::app.foundation.dashboard.open_attempt')"><x-heroicon-o-arrow-up-right class="h-4 w-4" /></a></td></tr>
            @endforeach
            </tbody></table></div>
        @endif
    </article>
</div></main>
@endsection
