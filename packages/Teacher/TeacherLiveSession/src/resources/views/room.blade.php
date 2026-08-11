@extends('Mindigo-dashboard::layouts')
@section('title', $session->title.' — '.__('teacher-live-session::app.room_title'))

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<main class="flex min-h-[calc(100vh-4rem)] items-center justify-center bg-slate-50 p-6">
    <section class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
        <span class="mx-auto grid h-12 w-12 place-items-center rounded-xl bg-green-50 text-green-700"><x-heroicon-o-video-camera class="h-6 w-6" /></span>
        <p class="mt-4 text-xs font-bold uppercase tracking-widest text-green-700">Mindigo Live</p>
        <h1 class="mt-2 text-xl font-black text-slate-950">{{ $session->title }}</h1>
        <p class="mt-1 text-sm font-semibold text-slate-500">{{ $session->classroom->name ?? '' }}</p>
        <div class="mt-6 rounded-xl border border-green-100 bg-green-50 p-4 text-sm font-semibold text-green-900">@lang('teacher-live-session::app.native_room_preparing')</div>
        <div class="mt-6 flex justify-center gap-3">
            <a href="{{ route('teacher.live-sessions.index') }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 no-underline">@lang('teacher-live-session::app.leave_room')</a>
            <form action="{{ route('teacher.live-sessions.end', $session) }}" method="POST">@csrf<button type="submit" class="inline-flex h-10 items-center rounded-xl bg-red-600 px-4 text-sm font-bold text-white">@lang('teacher-live-session::app.end')</button></form>
        </div>
    </section>
</main>
@endsection
