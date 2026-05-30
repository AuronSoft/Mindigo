@extends('Mindigo-dashboard::layouts')

@section('title', $subject->name)

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css','packages/Mindigo/Dashboard/src/resources/js/app.js','packages/Mindigo/SubjectManagement/src/resources/css/app.css','packages/Mindigo/SubjectManagement/src/resources/js/app.js'])
@endsection

@section('content')
<div class="subject-page mx-auto flex max-w-7xl flex-col gap-6">
    <header class="subject-hero">
        <div>
            <div class="subject-breadcrumb"><a href="{{ route('dashboard') }}">@lang('Mindigo-dashboard::app.dashboard')</a><span>/</span><a href="{{ route('subjects.index') }}">@lang('Mindigo-subject-management::app.breadcrumb')</a><span>/</span><strong>{{ $subject->name }}</strong></div>
            <h1>{{ $subject->name }}</h1>
            <p>{{ $subject->description ?: __('Mindigo-subject-management::app.no_description') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if(auth()->user()?->hasPermissionTo('subjects.update'))<a href="{{ route('subjects.edit', $subject) }}" class="subject-secondary-button">@lang('Mindigo-subject-management::app.edit')</a>@endif
        </div>
    </header>

    <section class="grid gap-4 md:grid-cols-4">
        <article class="subject-stat-card"><span>@lang('Mindigo-subject-management::app.status')</span><strong class="text-xl">@lang('Mindigo-subject-management::app.statuses.' . $subject->status)</strong></article>
        <article class="subject-stat-card"><span>@lang('Mindigo-subject-management::app.topics')</span><strong>{{ $subject->topics->count() }}</strong></article>
        <article class="subject-stat-card"><span>@lang('Mindigo-subject-management::app.questions')</span><strong>{{ number_format($usage['questions'] ?? 0) }}</strong></article>
        <article class="subject-stat-card"><span>@lang('Mindigo-subject-management::app.exams')</span><strong>{{ number_format($usage['exams'] ?? 0) }}</strong></article>
    </section>

    <section class="grid gap-5 lg:grid-cols-[0.78fr_1.22fr]">
        @if(auth()->user()?->hasPermissionTo('subjects.create'))
            <form method="POST" action="{{ route('subjects.topics.store', $subject) }}" class="subject-card p-5" data-mindigo-confirm-title="@lang('Mindigo-subject-management::app.confirm_topic_title')" data-mindigo-confirm-message="@lang('Mindigo-subject-management::app.confirm_topic_message')" data-mindigo-confirm-text="@lang('Mindigo-subject-management::app.save_topic')" data-mindigo-confirm-cancel="@lang('Mindigo-subject-management::app.cancel')">
                @csrf
                <div class="subject-section-head"><span>02</span><div><h2>@lang('Mindigo-subject-management::app.create_topic')</h2><p>@lang('Mindigo-subject-management::app.create_topic_desc')</p></div></div>
                <div class="mt-5 grid gap-4">
                    <label class="subject-field"><span>@lang('Mindigo-subject-management::app.topic_name')</span><input name="name" class="subject-input" required></label>
                    <label class="subject-field"><span>@lang('Mindigo-subject-management::app.topic_description')</span><textarea name="description" class="subject-textarea"></textarea></label>
                    <div class="grid gap-3 md:grid-cols-2">
                        <label class="subject-field"><span>@lang('Mindigo-subject-management::app.status')</span><select name="status" class="subject-select">@foreach($topicStatuses as $status)<option value="{{ $status }}">@lang('Mindigo-subject-management::app.statuses.' . $status)</option>@endforeach</select></label>
                        <label class="subject-field"><span>@lang('Mindigo-subject-management::app.sort_order')</span><input type="number" min="0" name="sort_order" value="0" class="subject-input"></label>
                    </div>
                    <button class="subject-primary-button">@lang('Mindigo-subject-management::app.save_topic')</button>
                </div>
            </form>
        @endif

        <article class="subject-card overflow-hidden">
            <div class="subject-card-head"><h2>@lang('Mindigo-subject-management::app.topic_list')</h2><p>@lang('Mindigo-subject-management::app.topic_list_desc')</p></div>
            <div class="divide-y divide-slate-100">
                @forelse($subject->topics as $topic)
                    <form method="POST" action="{{ route('subjects.topics.update', $topic) }}" class="subject-topic-row" data-mindigo-confirm-title="@lang('Mindigo-subject-management::app.confirm_topic_title')" data-mindigo-confirm-message="@lang('Mindigo-subject-management::app.confirm_topic_update_message')" data-mindigo-confirm-text="@lang('Mindigo-subject-management::app.save_topic')" data-mindigo-confirm-cancel="@lang('Mindigo-subject-management::app.cancel')">
                        @csrf @method('PUT')
                        <div class="grid min-w-0 flex-1 gap-3 md:grid-cols-[1fr_8rem_6rem]">
                            <input name="name" value="{{ $topic->name }}" class="subject-input" @disabled(!auth()->user()?->hasPermissionTo('subjects.update'))>
                            <select name="status" class="subject-select" @disabled(!auth()->user()?->hasPermissionTo('subjects.update'))>@foreach($topicStatuses as $status)<option value="{{ $status }}" @selected($topic->status === $status)>@lang('Mindigo-subject-management::app.statuses.' . $status)</option>@endforeach</select>
                            <input type="number" min="0" name="sort_order" value="{{ $topic->sort_order }}" class="subject-input" @disabled(!auth()->user()?->hasPermissionTo('subjects.update'))>
                        </div>
                        <input type="hidden" name="description" value="{{ $topic->description }}">
                        <div class="flex gap-2">@if(auth()->user()?->hasPermissionTo('subjects.update'))<button class="subject-secondary-button">@lang('Mindigo-subject-management::app.save')</button>@endif</div>
                    </form>
                    @if(auth()->user()?->hasPermissionTo('subjects.delete'))
                        <form method="POST" action="{{ route('subjects.topics.destroy', $topic) }}" class="px-5 pb-4 -mt-2" data-mindigo-confirm-title="@lang('Mindigo-subject-management::app.confirm_delete_topic_title')" data-mindigo-confirm-message="@lang('Mindigo-subject-management::app.confirm_delete_topic_message')" data-mindigo-confirm-text="@lang('Mindigo-subject-management::app.delete')" data-mindigo-confirm-cancel="@lang('Mindigo-subject-management::app.cancel')" data-mindigo-confirm-type="danger">
                            @csrf @method('DELETE')
                            <button class="subject-danger-button">@lang('Mindigo-subject-management::app.delete_topic')</button>
                        </form>
                    @endif
                @empty
                    <div class="p-5 text-sm font-bold text-slate-400">@lang('Mindigo-subject-management::app.no_topics')</div>
                @endforelse
            </div>
        </article>
    </section>
</div>
@endsection
