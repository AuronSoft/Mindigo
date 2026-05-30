@extends('Mindigo-dashboard::layouts')

@section('title', $classroom->name)

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css','packages/Mindigo/Dashboard/src/resources/js/app.js','packages/Mindigo/ClassroomManagement/src/resources/css/app.css','packages/Mindigo/ClassroomManagement/src/resources/js/app.js'])
@endsection

@section('content')
<div class="classroom-page mx-auto flex max-w-7xl flex-col gap-6">
    <header class="classroom-hero">
        <div>
            <div class="classroom-breadcrumb"><a href="{{ route('dashboard') }}">@lang('Mindigo-dashboard::app.dashboard')</a><span>/</span><a href="{{ route('classrooms.index') }}">@lang('Mindigo-classroom-management::app.breadcrumb')</a><span>/</span><strong>{{ $classroom->name }}</strong></div>
            <h1>{{ $classroom->name }}</h1>
            <p>{{ $classroom->description ?: __('Mindigo-classroom-management::app.no_description') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if(auth()->user()?->hasPermissionTo('classrooms.update'))<a href="{{ route('classrooms.edit', $classroom) }}" class="classroom-secondary-button">@lang('Mindigo-classroom-management::app.edit')</a>@endif
            <a href="{{ route('classrooms.index') }}" class="classroom-secondary-button">@lang('Mindigo-classroom-management::app.back')</a>
        </div>
    </header>

    <section class="grid gap-4 md:grid-cols-4">
        <article class="classroom-stat-card"><span>@lang('Mindigo-classroom-management::app.teacher')</span><strong class="text-xl">{{ $classroom->teacher?->name ?: __('Mindigo-classroom-management::app.not_set') }}</strong></article>
        <article class="classroom-stat-card"><span>@lang('Mindigo-classroom-management::app.students')</span><strong>{{ number_format($classroom->students->count()) }}</strong></article>
        <article class="classroom-stat-card"><span>@lang('Mindigo-classroom-management::app.subjects')</span><strong>{{ number_format($classroom->subjects->count()) }}</strong></article>
        <article class="classroom-stat-card"><span>@lang('Mindigo-classroom-management::app.status')</span><strong class="text-xl">@lang('Mindigo-classroom-management::app.statuses.' . $classroom->status)</strong></article>
    </section>

    <section class="grid gap-5 lg:grid-cols-[1fr_1fr]">
        <article class="classroom-card overflow-hidden">
            <div class="classroom-card-head"><h2>@lang('Mindigo-classroom-management::app.roster')</h2><p>@lang('Mindigo-classroom-management::app.roster_desc')</p></div>
            @if(auth()->user()?->hasPermissionTo('classrooms.manage_students'))
                <form method="POST" action="{{ route('classrooms.students.sync', $classroom) }}" class="p-5" data-mindigo-confirm-title="@lang('Mindigo-classroom-management::app.confirm_roster_title')" data-mindigo-confirm-message="@lang('Mindigo-classroom-management::app.confirm_roster_message')" data-mindigo-confirm-text="@lang('Mindigo-classroom-management::app.save')" data-mindigo-confirm-cancel="@lang('Mindigo-classroom-management::app.cancel')">
                    @csrf
                    <div class="classroom-check-grid">
                        @foreach($students as $student)
                            <label class="classroom-check"><input type="checkbox" name="student_ids[]" value="{{ $student->id }}" @checked($classroom->students->contains('id', $student->id))><span>{{ $student->name }}<small>{{ $student->email }}</small></span></label>
                        @endforeach
                    </div>
                    <button class="classroom-primary-button mt-4">@lang('Mindigo-classroom-management::app.save_roster')</button>
                </form>
            @else
                <div class="divide-y divide-slate-100">@forelse($classroom->students as $student)<div class="classroom-list-row"><strong>{{ $student->name }}</strong><span>{{ $student->email }}</span></div>@empty<div class="p-5 text-sm font-bold text-slate-400">@lang('Mindigo-classroom-management::app.no_students')</div>@endforelse</div>
            @endif
        </article>

        <article class="classroom-card overflow-hidden">
            <div class="classroom-card-head"><h2>@lang('Mindigo-classroom-management::app.subjects')</h2><p>@lang('Mindigo-classroom-management::app.subjects_desc')</p></div>
            @if(auth()->user()?->hasPermissionTo('classrooms.update'))
                <form method="POST" action="{{ route('classrooms.subjects.sync', $classroom) }}" class="p-5" data-mindigo-confirm-title="@lang('Mindigo-classroom-management::app.confirm_subjects_title')" data-mindigo-confirm-message="@lang('Mindigo-classroom-management::app.confirm_subjects_message')" data-mindigo-confirm-text="@lang('Mindigo-classroom-management::app.save')" data-mindigo-confirm-cancel="@lang('Mindigo-classroom-management::app.cancel')">
                    @csrf
                    <div class="classroom-check-grid">
                        @foreach($subjects as $subject)
                            <label class="classroom-check"><input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" @checked($classroom->subjects->contains('id', $subject->id))><span>{{ $subject->name }}</span></label>
                        @endforeach
                    </div>
                    <button class="classroom-primary-button mt-4">@lang('Mindigo-classroom-management::app.save_subjects')</button>
                </form>
            @else
                <div class="flex flex-wrap gap-2 p-5">@forelse($classroom->subjects as $subject)<span class="classroom-pill">{{ $subject->name }}</span>@empty<span class="text-sm font-bold text-slate-400">@lang('Mindigo-classroom-management::app.no_subjects')</span>@endforelse</div>
            @endif
        </article>
    </section>
</div>
@endsection
