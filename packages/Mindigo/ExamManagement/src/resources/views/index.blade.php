@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-exam-management::app.title'))

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css','packages/Mindigo/Dashboard/src/resources/js/app.js','packages/Mindigo/ExamManagement/src/resources/css/app.css','packages/Mindigo/ExamManagement/src/resources/js/app.js'])
@endsection

@section('content')
<div class="exam-page mx-auto flex max-w-7xl flex-col gap-6">
    <header class="exam-hero">
        <div><div class="exam-breadcrumb"><a href="{{ route('dashboard') }}">@lang('Mindigo-dashboard::app.dashboard')</a><span>/</span><strong>@lang('Mindigo-exam-management::app.breadcrumb')</strong></div><h1>@lang('Mindigo-exam-management::app.heading')</h1><p>@lang('Mindigo-exam-management::app.description')</p></div>
        @if(auth()->user()?->hasPermissionTo('exams.create'))<a href="{{ route('exams.create') }}" class="exam-primary-button"><svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-[2.5]" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>@lang('Mindigo-exam-management::app.create_exam')</a>@endif
    </header>

    <section class="grid gap-4 md:grid-cols-4">
        @foreach(['total' => 'total_exams', 'published' => 'published_exams', 'draft' => 'draft_exams', 'closed' => 'closed_exams'] as $key => $label)
            <article class="exam-stat-card"><span>@lang('Mindigo-exam-management::app.' . $label)</span><strong>{{ number_format($stats[$key] ?? 0) }}</strong></article>
        @endforeach
    </section>

    <form method="GET" action="{{ route('exams.index') }}" class="exam-filter">
        <input name="keyword" value="{{ $filters['keyword'] ?? '' }}" class="exam-input" placeholder="@lang('Mindigo-exam-management::app.search_placeholder')">
        <input name="subject" value="{{ $filters['subject'] ?? '' }}" class="exam-input" placeholder="@lang('Mindigo-exam-management::app.subject')" list="exam-subject-filter">
        <datalist id="exam-subject-filter">@foreach($subjects as $subject)<option value="{{ $subject }}"></option>@endforeach</datalist>
        <select name="status" class="exam-select"><option value="">@lang('Mindigo-exam-management::app.all_statuses')</option>@foreach($statuses as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>@lang('Mindigo-exam-management::app.statuses.' . $status)</option>@endforeach</select>
        <button class="exam-filter-button">@lang('Mindigo-exam-management::app.filter')</button>
        <a href="{{ route('exams.index') }}" class="exam-secondary-button">@lang('Mindigo-exam-management::app.reset')</a>
    </form>

    <section class="exam-card overflow-hidden">
        @if($exams->count())
            <div class="overflow-x-auto">
                <table class="exam-table">
                    <thead><tr><th>@lang('Mindigo-exam-management::app.exam')</th><th>@lang('Mindigo-exam-management::app.status')</th><th>@lang('Mindigo-exam-management::app.duration')</th><th>@lang('Mindigo-exam-management::app.questions')</th><th>@lang('Mindigo-exam-management::app.attempts')</th><th></th></tr></thead>
                    <tbody>
                    @foreach($exams as $exam)
                        <tr>
                            <td><a href="{{ route('exams.show', $exam) }}" class="block max-w-xl truncate text-sm font-black text-slate-900 no-underline hover:text-green-700">{{ $exam->title }}</a><span class="mt-1 block text-xs font-semibold text-slate-400">{{ $exam->subject ?: '-' }} / {{ $exam->topic ?: '-' }}</span></td>
                            <td><span class="exam-badge exam-status-{{ $exam->status }}">@lang('Mindigo-exam-management::app.statuses.' . $exam->status)</span></td>
                            <td class="text-sm font-bold text-slate-500">{{ $exam->duration_minutes }} @lang('Mindigo-exam-management::app.minutes')</td>
                            <td class="text-sm font-bold text-slate-500">{{ $exam->total_questions }} / {{ number_format((float) $exam->total_points, 2) }}</td>
                            <td class="text-sm font-bold text-slate-500">{{ $exam->attempts_count }}</td>
                            <td><div class="flex justify-end gap-2">@if(auth()->user()?->hasPermissionTo('exams.update'))<a href="{{ route('exams.edit', $exam) }}" class="exam-secondary-button">@lang('Mindigo-exam-management::app.edit')</a>@endif @if(auth()->user()?->hasPermissionTo('exams.delete'))<form method="POST" action="{{ route('exams.destroy', $exam) }}" data-mindigo-confirm-title="@lang('Mindigo-exam-management::app.confirm_delete_title')" data-mindigo-confirm-message="@lang('Mindigo-exam-management::app.confirm_delete_message')" data-mindigo-confirm-text="@lang('Mindigo-exam-management::app.delete')" data-mindigo-confirm-cancel="@lang('Mindigo-exam-management::app.cancel')" data-mindigo-confirm-type="danger">@csrf @method('DELETE')<button class="exam-danger-button">@lang('Mindigo-exam-management::app.delete')</button></form>@endif</div></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-4">{{ $exams->links() }}</div>
        @else
            <div class="p-6">@include('core::partials.empty-state', ['title' => __('Mindigo-exam-management::app.empty_title'), 'message' => __('Mindigo-exam-management::app.empty_desc')])</div>
        @endif
    </section>
</div>
@endsection
