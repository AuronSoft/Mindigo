@extends('Mindigo-dashboard::layouts')
@section('title', __('student-live-session::app.waiting_room_title'))

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<main class="flex min-h-[calc(100vh-4rem)] items-center justify-center bg-slate-50 p-6">
    <section class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
        <span class="mx-auto grid h-12 w-12 place-items-center rounded-xl bg-amber-50 text-amber-700"><x-heroicon-o-clock class="h-6 w-6" /></span>
        <p class="mt-4 text-xs font-black uppercase tracking-widest text-green-700">Auronsoft Live</p>
        <h1 class="mt-2 text-xl font-black text-slate-950">{{ $session->title }}</h1>
        <p class="mt-2 text-sm font-semibold text-slate-500">@lang('student-live-session::app.waiting_room_message')</p>
        <div class="mt-5 rounded-xl bg-slate-50 p-3 text-xs font-bold text-slate-600">@lang('student-live-session::app.waiting_room_refresh')</div>
        <div class="mt-6 flex justify-center gap-3">
            <button type="button" onclick="window.location.reload()" class="inline-flex h-10 items-center rounded-xl bg-green-600 px-4 text-sm font-bold text-white">@lang('student-live-session::app.check_admission')</button>
            <a href="{{ route('student.live-sessions.index') }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 no-underline">@lang('student-live-session::app.leave_room')</a>
        </div>
    </section>
</main>
@endsection
