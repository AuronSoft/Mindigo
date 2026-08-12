@extends('Mindigo-dashboard::layouts')
@section('title', $session->title.' — '.__('teacher-live-session::app.room_title'))

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js', 'packages/Teacher/TeacherLiveSession/src/resources/js/room.js'])
@endsection

@section('content')
<main class="flex min-h-[calc(100vh-4rem)] flex-col bg-slate-50">
    <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-white px-6 py-4">
        <div class="min-w-0"><p class="text-xs font-black uppercase tracking-widest text-green-700">Mindigo Live</p><h1 class="truncate text-lg font-black text-slate-950">{{ $session->title }}</h1><p class="text-xs font-semibold text-slate-500">{{ $session->classroom->name ?? '' }}</p></div>
        <div class="flex flex-wrap gap-2">
            <form method="POST" action="{{ route($session->isLocked() ? 'teacher.live-sessions.unlock' : 'teacher.live-sessions.lock', $session) }}">@csrf<button class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 px-4 text-xs font-black text-slate-700"><x-dynamic-component :component="$session->isLocked() ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed'" class="h-4 w-4" />{{ $session->isLocked() ? __('teacher-live-session::app.unlock_room') : __('teacher-live-session::app.lock_room') }}</button></form>
            <a href="{{ route('teacher.live-sessions.index') }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 px-4 text-xs font-black text-slate-700 no-underline">@lang('teacher-live-session::app.leave_room')</a>
            @if($session->isWaiting())
                <form action="{{ route('teacher.live-sessions.start', $session) }}" method="POST">@csrf<button class="inline-flex h-10 items-center gap-2 rounded-xl bg-green-600 px-4 text-xs font-black text-white"><x-heroicon-o-play class="h-4 w-4" />@lang('teacher-live-session::app.start')</button></form>
            @else
                <form action="{{ route('teacher.live-sessions.end', $session) }}" method="POST">@csrf<button class="inline-flex h-10 items-center rounded-xl bg-red-600 px-4 text-xs font-black text-white">@lang('teacher-live-session::app.end')</button></form>
            @endif
        </div>
    </header>

    <div class="grid flex-1 gap-5 p-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        @if($mediaConfig)
            @include('teacher-live-session::partials.media-stage')
        @else
            <section class="grid min-h-120 place-items-center rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                <div class="max-w-lg">
                    <span class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-green-50 text-green-700"><x-heroicon-o-user-group class="h-8 w-8" /></span>
                    <p class="mt-5 text-xs font-black uppercase tracking-widest text-green-700">@lang('teacher-live-session::app.waiting_room')</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-950">@lang('teacher-live-session::app.waiting_room_management_title')</h2>
                    <p class="mt-3 text-sm font-semibold leading-6 text-slate-500">@lang('teacher-live-session::app.waiting_room_management_hint')</p>
                    <form action="{{ route('teacher.live-sessions.start', $session) }}" method="POST" class="mt-6">@csrf<button class="inline-flex h-11 items-center gap-2 rounded-xl bg-green-600 px-5 text-sm font-black text-white"><x-heroicon-o-play class="h-4 w-4" />@lang('teacher-live-session::app.start')</button></form>
                </div>
            </section>
        @endif

        <aside class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-4"><div class="flex items-center justify-between"><h2 class="text-sm font-black text-slate-950">@lang('teacher-live-session::app.waiting_participants')</h2><span class="rounded-full bg-amber-50 px-2 py-1 text-xs font-black text-amber-700">{{ $waitingParticipants->count() + $waitingGuests->count() }}</span></div><p class="mt-1 text-xs font-semibold text-slate-500">@lang('teacher-live-session::app.waiting_participants_hint')</p></div>
            <div class="max-h-[60vh] space-y-2 overflow-y-auto p-3">
                @foreach($waitingParticipants as $waiting)
                    <div class="rounded-xl border border-slate-100 p-3"><p class="truncate text-sm font-black text-slate-900">{{ $waiting->user?->name }}</p><p class="truncate text-xs font-semibold text-slate-500">{{ $waiting->user?->email }}</p><div class="mt-3 grid grid-cols-2 gap-2"><form method="POST" action="{{ route('teacher.live-sessions.participants.admit', [$session, $waiting]) }}">@csrf<button class="h-8 w-full rounded-lg bg-green-600 text-xs font-black text-white">@lang('teacher-live-session::app.admit')</button></form><form method="POST" action="{{ route('teacher.live-sessions.participants.deny', [$session, $waiting]) }}">@csrf<button class="h-8 w-full rounded-lg border border-slate-200 text-xs font-black text-slate-600">@lang('teacher-live-session::app.deny')</button></form></div></div>
                @endforeach
                @foreach($waitingGuests as $guest)
                    <div class="rounded-xl border border-amber-100 bg-amber-50/40 p-3"><div class="flex items-center gap-2"><span class="rounded-full bg-amber-100 px-2 py-1 text-[10px] font-black uppercase text-amber-700">@lang('teacher-live-session::app.external_guest')</span><p class="truncate text-sm font-black text-slate-900">{{ $guest->name }}</p></div><p class="mt-1 truncate text-xs font-semibold text-slate-500">{{ $guest->email ?: __('teacher-live-session::app.no_email') }}</p><div class="mt-3 grid grid-cols-2 gap-2"><form method="POST" action="{{ route('teacher.live-sessions.guests.decision', [$session, $guest]) }}">@csrf<input type="hidden" name="decision" value="admitted"><button class="h-8 w-full rounded-lg bg-green-600 text-xs font-black text-white">@lang('teacher-live-session::app.admit')</button></form><form method="POST" action="{{ route('teacher.live-sessions.guests.decision', [$session, $guest]) }}">@csrf<input type="hidden" name="decision" value="denied"><button class="h-8 w-full rounded-lg border border-slate-200 bg-white text-xs font-black text-slate-600">@lang('teacher-live-session::app.deny')</button></form></div></div>
                @endforeach
                @if($waitingParticipants->isEmpty() && $waitingGuests->isEmpty())
                    <div class="py-12 text-center text-xs font-semibold text-slate-400">@lang('teacher-live-session::app.no_waiting_participants')</div>
                @endif
                @if($admittedGuests->isNotEmpty())<div class="border-t border-slate-100 pt-3"><p class="mb-2 text-[10px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-live-session::app.admitted_guests')</p>@foreach($admittedGuests as $guest)<div class="mb-2 flex items-center justify-between rounded-xl bg-slate-50 p-3"><div class="min-w-0"><p class="truncate text-xs font-black text-slate-800">{{ $guest->name }}</p><p class="text-[10px] font-semibold text-green-700">@lang('teacher-live-session::app.in_room')</p></div><form method="POST" action="{{ route('teacher.live-sessions.guests.decision', [$session, $guest]) }}">@csrf<input type="hidden" name="decision" value="removed"><button class="text-[11px] font-black text-red-600">@lang('teacher-live-session::app.remove_guest')</button></form></div>@endforeach</div>@endif
            </div>
            @if($canManageGuestLinks && ($session->room_settings['guest_access_enabled'] ?? false) === true)
                <div class="border-t border-slate-100 p-3"><h3 class="text-xs font-black uppercase tracking-wider text-slate-500">@lang('teacher-live-session::app.guest_links')</h3>@if(session('guest_link_url'))<div class="mt-2 rounded-xl bg-green-50 p-3"><p class="break-all text-xs font-bold text-green-800">{{ session('guest_link_url') }}</p><p class="mt-1 text-[11px] font-semibold text-green-700">@lang('teacher-live-session::app.copy_guest_link_now')</p></div>@endif
                    @foreach($guestLinks as $guestLink)<div class="mt-2 flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2"><p class="text-[11px] font-bold text-slate-600">{{ $guestLink->uses_count }}{{ $guestLink->max_uses ? '/'.$guestLink->max_uses : '' }} · {{ $guestLink->expires_at->format('d/m H:i') }}</p><form method="POST" action="{{ route('teacher.live-sessions.guest-links.destroy', [$session, $guestLink]) }}">@csrf @method('DELETE')<button class="text-[11px] font-black text-red-600">@lang('teacher-live-session::app.revoke_guest_link')</button></form></div>@endforeach
                    <form method="POST" action="{{ route('teacher.live-sessions.guest-links.store', $session) }}" class="mt-2 grid grid-cols-[1fr_80px_auto] gap-2">@csrf<select name="ttl_minutes" class="h-9 rounded-lg border border-slate-200 px-2 text-xs font-bold"><option value="60">1 giờ</option><option value="240">4 giờ</option><option value="1440">24 giờ</option></select><input name="max_uses" type="number" min="1" max="1000" placeholder="Lượt" class="h-9 rounded-lg border border-slate-200 px-2 text-xs"><button class="rounded-lg bg-green-600 px-3 text-xs font-black text-white">@lang('teacher-live-session::app.create_guest_link')</button></form></div>
            @endif
        </aside>
    </div>
</main>
@endsection
