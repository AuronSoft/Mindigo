@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-subject-management::app.title'))

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css','packages/Mindigo/Dashboard/src/resources/js/app.js','packages/Mindigo/SubjectManagement/src/resources/css/app.css','packages/Mindigo/SubjectManagement/src/resources/js/app.js'])
@endsection

@section('content')
<div class="subject-page mx-auto flex max-w-7xl flex-col gap-6">
    <header class="subject-hero">
        <div>
            <div class="subject-breadcrumb"><a href="{{ route('dashboard') }}">@lang('Mindigo-dashboard::app.dashboard')</a><span>/</span><strong>@lang('Mindigo-subject-management::app.breadcrumb')</strong></div>
            <h1>@lang('Mindigo-subject-management::app.heading')</h1>
            <p>@lang('Mindigo-subject-management::app.description')</p>
        </div>
        @if(auth()->user()?->hasPermissionTo('subjects.create'))
            <a href="{{ route('subjects.create') }}" class="subject-primary-button">
                <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-[2.5]" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                @lang('Mindigo-subject-management::app.create_subject')
            </a>
        @endif
    </header>

    <section class="grid gap-4 md:grid-cols-4">
        @foreach(['total' => 'total_subjects', 'active' => 'active_subjects', 'topics' => 'total_topics', 'inactive' => 'inactive_subjects'] as $key => $label)
            <article class="subject-stat-card"><span>@lang('Mindigo-subject-management::app.' . $label)</span><strong>{{ number_format($stats[$key] ?? 0) }}</strong></article>
        @endforeach
    </section>

    <form method="GET" action="{{ route('subjects.index') }}" class="subject-filter">
        <input name="keyword" value="{{ $filters['keyword'] ?? '' }}" class="subject-input" placeholder="@lang('Mindigo-subject-management::app.search_placeholder')">
        <select name="status" class="subject-select">
            <option value="">@lang('Mindigo-subject-management::app.all_statuses')</option>
            @foreach($statuses as $status)
                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>@lang('Mindigo-subject-management::app.statuses.' . $status)</option>
            @endforeach
        </select>
        <button class="subject-filter-button">@lang('Mindigo-subject-management::app.filter')</button>
        <a href="{{ route('subjects.index') }}" class="subject-secondary-button">@lang('Mindigo-subject-management::app.reset')</a>
    </form>

    <section class="subject-card overflow-hidden">
        @if($subjects->count())
            <div class="overflow-x-auto">
                <table class="subject-table">
                    <thead><tr><th>@lang('Mindigo-subject-management::app.subject')</th><th>@lang('Mindigo-subject-management::app.code')</th><th>@lang('Mindigo-subject-management::app.topics')</th><th>@lang('Mindigo-subject-management::app.status')</th><th></th></tr></thead>
                    <tbody>
                        @foreach($subjects as $subject)
                            <tr>
                                <td><a href="{{ route('subjects.show', $subject) }}" class="block max-w-xl truncate text-sm font-black text-slate-900 no-underline hover:text-green-700">{{ $subject->name }}</a><span class="mt-1 block text-xs font-semibold text-slate-400">{{ $subject->description ?: __('Mindigo-subject-management::app.no_description') }}</span></td>
                                <td><span class="subject-code">{{ $subject->code }}</span></td>
                                <td class="text-sm font-black text-slate-600">{{ $subject->topics_count }}</td>
                                <td><span class="subject-badge subject-status-{{ $subject->status }}">@lang('Mindigo-subject-management::app.statuses.' . $subject->status)</span></td>
                                <td><div class="flex justify-end gap-2">@if(auth()->user()?->hasPermissionTo('subjects.update'))<a href="{{ route('subjects.edit', $subject) }}" class="subject-secondary-button">@lang('Mindigo-subject-management::app.edit')</a>@endif @if(auth()->user()?->hasPermissionTo('subjects.delete'))<form method="POST" action="{{ route('subjects.destroy', $subject) }}" data-mindigo-confirm-title="@lang('Mindigo-subject-management::app.confirm_delete_title')" data-mindigo-confirm-message="@lang('Mindigo-subject-management::app.confirm_delete_message')" data-mindigo-confirm-text="@lang('Mindigo-subject-management::app.delete')" data-mindigo-confirm-cancel="@lang('Mindigo-subject-management::app.cancel')" data-mindigo-confirm-type="danger">@csrf @method('DELETE')<button class="subject-danger-button">@lang('Mindigo-subject-management::app.delete')</button></form>@endif</div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-4">{{ $subjects->links() }}</div>
        @else
            <div class="p-6">@include('core::partials.empty-state', ['title' => __('Mindigo-subject-management::app.empty_title'), 'message' => __('Mindigo-subject-management::app.empty_desc')])</div>
        @endif
    </section>
</div>
@endsection
