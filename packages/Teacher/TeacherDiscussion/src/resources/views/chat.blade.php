@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-discussion::app.title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('scripts')
    @include('teacher-discussion::chat.partials._scripts')
@endsection

@section('content')
@php
    $currentUserId = (int) auth()->id();
    $currentRole = auth()->user()->role;

    $threadName = function ($thread) use ($currentUserId) {
        if ($thread->type === 'class' && $thread->classroom) {
            return $thread->classroom->name;
        }
        if ($thread->name) {
            return $thread->name;
        }
        if ($thread->type === 'direct') {
            $other = $thread->participants
                ->filter(fn ($p) => (int) $p->user_id !== $currentUserId)
                ->first();
            return $other?->user?->name ?? __('teacher-discussion::app.direct_chat');
        }
        return __('teacher-discussion::app.unknown_class');
    };

    $threadSub = function ($thread) use ($currentUserId) {
        if ($thread->type === 'direct') {
            return __('teacher-discussion::app.direct_chat');
        }
        if ($thread->classroom) {
            return number_format($thread->classroom->students_count ?? 0).' '.mb_strtolower(__('teacher-discussion::app.students'));
        }
        $count = $thread->participants_count ?? $thread->participants->count();
        return number_format($count).' '.__('teacher-discussion::app.members');
    };

    $threadInitial = function ($thread) use ($threadName) {
        return mb_strtoupper(mb_substr($threadName($thread), 0, 1));
    };

    $selectedName = $selectedThread ? $threadName($selectedThread) : '';
    $selectedInitial = $selectedThread ? $threadInitial($selectedThread) : '';
    $selectedSub = $selectedThread ? $threadSub($selectedThread) : '';

    $imageAttachments = $attachments->filter(fn ($a) => $a->isImage())->values();
    $fileAttachments = $attachments->reject(fn ($a) => $a->isImage())->values();
    $pinnedMessages = $messages->where('is_pinned', true)->sortByDesc('pinned_at')->values();
@endphp

<div class="h-screen overflow-hidden bg-slate-50">
    <div class="grid h-full grid-cols-[21rem_minmax(0,1fr)_21rem] max-2xl:grid-cols-[19rem_minmax(0,1fr)_19rem] max-xl:grid-cols-[18rem_minmax(0,1fr)] max-lg:grid-cols-1">

        @include('teacher-discussion::chat.partials._sidebar')

        @include('teacher-discussion::chat.partials._chat_panel')

        @if($selectedThread)
            @include('teacher-discussion::chat.partials._info_panel')
        @endif
    </div>
</div>

@include('teacher-discussion::chat.partials._modals')
@endsection