@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-classroom-management::app.title'))

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css','packages/Mindigo/Dashboard/src/resources/js/app.js','packages/Mindigo/ClassroomManagement/src/resources/css/app.css','packages/Mindigo/ClassroomManagement/src/resources/js/app.js'])
@endsection

@section('content')
<div class="classroom-page mx-auto flex max-w-7xl flex-col gap-6">
    <header class="classroom-hero">
        <div>
            <div class="classroom-breadcrumb"><a href="{{ route('dashboard') }}">@lang('Mindigo-dashboard::app.dashboard')</a><span>/</span><strong>@lang('Mindigo-classroom-management::app.breadcrumb')</strong></div>
            <h1>@lang('Mindigo-classroom-management::app.heading')</h1>
            <p>@lang('Mindigo-classroom-management::app.description')</p>
        </div>
        @if(auth()->user()?->hasPermissionTo('classrooms.create'))
            <a href="{{ route('classrooms.create') }}" class="classroom-primary-button">
                <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-[2.5]" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                @lang('Mindigo-classroom-management::app.create_classroom')
            </a>
        @endif
    </header>

    <section class="grid gap-4 md:grid-cols-4">
        @foreach(['total' => 'total_classrooms', 'active' => 'active_classrooms', 'students' => 'total_students', 'inactive' => 'inactive_classrooms'] as $key => $label)
            <article class="classroom-stat-card"><span>@lang('Mindigo-classroom-management::app.' . $label)</span><strong>{{ number_format($stats[$key] ?? 0) }}</strong></article>
        @endforeach
    </section>

    <form method="GET" action="{{ route('classrooms.index') }}" class="classroom-filter">
        <input name="keyword" value="{{ $filters['keyword'] ?? '' }}" class="classroom-input" placeholder="@lang('Mindigo-classroom-management::app.search_placeholder')">
        <select name="status" class="classroom-select">
            <option value="">@lang('Mindigo-classroom-management::app.all_statuses')</option>
            @foreach($statuses as $status)
                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>@lang('Mindigo-classroom-management::app.statuses.' . $status)</option>
            @endforeach
        </select>
        <select name="teacher_id" class="classroom-select">
            <option value="">@lang('Mindigo-classroom-management::app.all_teachers')</option>
            @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected((string) ($filters['teacher_id'] ?? '') === (string) $teacher->id)>{{ $teacher->name }}</option>
            @endforeach
        </select>
        <select name="subject_id" class="classroom-select">
            <option value="">@lang('Mindigo-classroom-management::app.all_subjects')</option>
            @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" @selected((string) ($filters['subject_id'] ?? '') === (string) $subject->id)>{{ $subject->name }}</option>
            @endforeach
        </select>
        <button class="classroom-filter-button">@lang('Mindigo-classroom-management::app.filter')</button>
        <a href="{{ route('classrooms.index') }}" class="classroom-secondary-button">@lang('Mindigo-classroom-management::app.reset')</a>
    </form>

    <section class="classroom-card overflow-hidden">
        @if($classrooms->count())
            <div class="overflow-x-auto">
                <table class="classroom-table">
                    <thead><tr><th>@lang('Mindigo-classroom-management::app.classroom')</th><th>@lang('Mindigo-classroom-management::app.teacher')</th><th>@lang('Mindigo-classroom-management::app.subjects')</th><th>@lang('Mindigo-classroom-management::app.students')</th><th>@lang('Mindigo-classroom-management::app.status')</th><th></th></tr></thead>
                    <tbody>
                        @foreach($classrooms as $classroom)
                            <tr>
                                <td><a href="{{ route('classrooms.show', $classroom) }}" class="block max-w-xl truncate text-sm font-black text-slate-900 no-underline hover:text-green-700">{{ $classroom->name }}</a><span class="mt-1 block text-xs font-semibold text-slate-400">{{ $classroom->code }}{{ $classroom->school_year ? ' / ' . $classroom->school_year : '' }}</span></td>
                                <td class="text-sm font-black text-slate-700">{{ $classroom->teacher?->name ?: __('Mindigo-classroom-management::app.not_set') }}</td>
                                <td><div class="flex max-w-sm flex-wrap gap-1">@forelse($classroom->subjects as $subject)<span class="classroom-pill">{{ $subject->name }}</span>@empty<span class="text-xs font-bold text-slate-400">@lang('Mindigo-classroom-management::app.not_set')</span>@endforelse</div></td>
                                <td class="text-sm font-black text-slate-700">{{ number_format($classroom->students_count) }}</td>
                                <td><span class="classroom-badge classroom-status-{{ $classroom->status }}">@lang('Mindigo-classroom-management::app.statuses.' . $classroom->status)</span></td>
                                <td><div class="flex justify-end gap-2">@if(auth()->user()?->hasPermissionTo('classrooms.update'))<a href="{{ route('classrooms.edit', $classroom) }}" class="classroom-secondary-button">@lang('Mindigo-classroom-management::app.edit')</a>@endif @if(auth()->user()?->hasPermissionTo('classrooms.delete'))<form method="POST" action="{{ route('classrooms.destroy', $classroom) }}" data-mindigo-confirm-title="@lang('Mindigo-classroom-management::app.confirm_delete_title')" data-mindigo-confirm-message="@lang('Mindigo-classroom-management::app.confirm_delete_message')" data-mindigo-confirm-text="@lang('Mindigo-classroom-management::app.delete')" data-mindigo-confirm-cancel="@lang('Mindigo-classroom-management::app.cancel')" data-mindigo-confirm-type="danger">@csrf @method('DELETE')<button class="classroom-danger-button">@lang('Mindigo-classroom-management::app.delete')</button></form>@endif</div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-4">{{ $classrooms->links() }}</div>
        @else
            <div class="p-6">@include('core::partials.empty-state', ['title' => __('Mindigo-classroom-management::app.empty_title'), 'message' => __('Mindigo-classroom-management::app.empty_desc')])</div>
        @endif
    </section>
</div>
@endsection
