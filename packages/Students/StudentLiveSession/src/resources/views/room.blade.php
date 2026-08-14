@extends('Mindigo-dashboard::layouts')
@section('title', $session->title.' — '.__('student-live-session::app.room_title'))

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js', 'packages/Teacher/TeacherLiveSession/src/resources/js/room.js'])
@endsection

@section('content')
<main class="flex min-h-[calc(100vh-4rem)] flex-col bg-slate-50">
    <header class="flex items-center justify-between gap-4 border-b border-slate-200 bg-white px-6 py-4"><div class="min-w-0"><p class="text-xs font-black uppercase tracking-widest text-green-700">Auronsoft Live</p><h1 class="truncate text-lg font-black text-slate-950">{{ $session->title }}</h1><p class="text-xs font-semibold text-slate-500">{{ $session->classroom->name ?? '' }}</p></div><a href="{{ route('student.live-sessions.index') }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 px-4 text-xs font-black text-slate-700 no-underline">@lang('student-live-session::app.leave_room')</a></header>
    <div class="flex min-h-0 flex-1 p-5">@include('teacher-live-session::partials.media-stage')</div>
</main>
@endsection
