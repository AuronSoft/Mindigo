@extends('Mindigo-dashboard::layouts')
@section('title', $session->title.' — '.__('teacher-live-session::app.room_title'))

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<main class="flex min-h-[calc(100vh-4rem)] flex-col bg-slate-50">
    <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-white px-6 py-4">
        <div class="min-w-0"><p class="text-xs font-black uppercase tracking-widest text-green-700">Mindigo Live</p><h1 class="truncate text-lg font-black text-slate-950">{{ $session->title }}</h1><p class="text-xs font-semibold text-slate-500">{{ $session->classroom->name ?? '' }}</p></div>
        <div class="flex flex-wrap gap-2">
            <form method="POST" action="{{ route($session->isLocked() ? 'teacher.live-sessions.unlock' : 'teacher.live-sessions.lock', $session) }}">@csrf<button class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 px-4 text-xs font-black text-slate-700"><x-dynamic-component :component="$session->isLocked() ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed'" class="h-4 w-4" />{{ $session->isLocked() ? __('teacher-live-session::app.unlock_room') : __('teacher-live-session::app.lock_room') }}</button></form>
            <a href="{{ route('teacher.live-sessions.index') }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 px-4 text-xs font-black text-slate-700 no-underline">@lang('teacher-live-session::app.leave_room')</a>
            <form action="{{ route('teacher.live-sessions.end', $session) }}" method="POST">@csrf<button class="inline-flex h-10 items-center rounded-xl bg-red-600 px-4 text-xs font-black text-white">@lang('teacher-live-session::app.end')</button></form>
        </div>
    </header>

    <div class="grid flex-1 gap-5 p-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <section class="flex min-h-105 items-center justify-center rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
            <div class="max-w-lg"><span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-green-50 text-green-700"><x-heroicon-o-video-camera class="h-7 w-7" /></span><h2 class="mt-4 text-xl font-black text-slate-950">@lang('teacher-live-session::app.secure_room_ready')</h2><p class="mt-2 text-sm font-semibold leading-6 text-slate-500">@lang('teacher-live-session::app.native_room_preparing')</p><div class="mt-5 inline-flex items-center gap-2 rounded-full bg-green-50 px-3 py-2 text-xs font-black text-green-800"><x-heroicon-o-shield-check class="h-4 w-4" />@lang('teacher-live-session::app.secure_token_active')</div></div>
        </section>

        <aside class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-4"><div class="flex items-center justify-between"><h2 class="text-sm font-black text-slate-950">@lang('teacher-live-session::app.waiting_participants')</h2><span class="rounded-full bg-amber-50 px-2 py-1 text-xs font-black text-amber-700">{{ $waitingParticipants->count() }}</span></div><p class="mt-1 text-xs font-semibold text-slate-500">@lang('teacher-live-session::app.waiting_participants_hint')</p></div>
            <div class="max-h-[60vh] space-y-2 overflow-y-auto p-3">
                @forelse($waitingParticipants as $waiting)
                    <div class="rounded-xl border border-slate-100 p-3"><p class="truncate text-sm font-black text-slate-900">{{ $waiting->user?->name }}</p><p class="truncate text-xs font-semibold text-slate-500">{{ $waiting->user?->email }}</p><div class="mt-3 grid grid-cols-2 gap-2"><form method="POST" action="{{ route('teacher.live-sessions.participants.admit', [$session, $waiting]) }}">@csrf<button class="h-8 w-full rounded-lg bg-green-600 text-xs font-black text-white">@lang('teacher-live-session::app.admit')</button></form><form method="POST" action="{{ route('teacher.live-sessions.participants.deny', [$session, $waiting]) }}">@csrf<button class="h-8 w-full rounded-lg border border-slate-200 text-xs font-black text-slate-600">@lang('teacher-live-session::app.deny')</button></form></div></div>
                @empty
                    <div class="py-12 text-center text-xs font-semibold text-slate-400">@lang('teacher-live-session::app.no_waiting_participants')</div>
                @endforelse
            </div>
        </aside>
    </div>
</main>
@endsection
