<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@lang('teacher-live-session::app.guest_join_title') — Auronsoft Live</title>
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-950">
<main class="grid min-h-screen place-items-center p-5">
    <section class="w-full max-w-md overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/60">
        <div class="border-b border-slate-100 p-6"><span class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-green-700"><x-heroicon-o-video-camera class="h-5 w-5" />Auronsoft Live</span><h1 class="mt-4 text-2xl font-black">{{ $link->session->title }}</h1><p class="mt-1 text-sm font-semibold text-slate-500">{{ $link->session->classroom?->name }}</p><p class="mt-4 text-xs font-semibold text-slate-500">{{ $link->session->scheduled_start?->format('d/m/Y H:i') }} · @lang('teacher-live-session::app.guest_link_expires') {{ $link->expires_at->format('d/m/Y H:i') }}</p></div>
        <form method="POST" action="{{ route('live-guest.join', $token) }}" class="space-y-4 p-6">@csrf
            <div><label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">@lang('teacher-live-session::app.guest_name')</label><input name="name" value="{{ old('name') }}" required maxlength="120" autofocus class="h-12 w-full rounded-xl border border-slate-200 px-4 text-sm font-bold outline-none focus:border-green-400 focus:ring-4 focus:ring-green-100">@error('name')<p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p>@enderror</div>
            <div><label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">@lang('teacher-live-session::app.guest_email_optional')</label><input type="email" name="email" value="{{ old('email') }}" maxlength="255" class="h-12 w-full rounded-xl border border-slate-200 px-4 text-sm font-bold outline-none focus:border-green-400 focus:ring-4 focus:ring-green-100">@error('email')<p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p>@enderror</div>
            <div class="rounded-xl bg-amber-50 p-3 text-xs font-semibold leading-5 text-amber-900">@lang('teacher-live-session::app.guest_waiting_notice')</div>
            <button class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-green-600 px-5 text-sm font-black text-white hover:bg-green-500">@lang('teacher-live-session::app.request_to_join')</button>
        </form>
    </section>
</main>
</body></html>
