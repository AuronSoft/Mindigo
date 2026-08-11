<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>Mindigo Live</title>@vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Teacher/TeacherLiveSession/src/resources/js/room.js'])@if(!$mediaConfig)<meta http-equiv="refresh" content="5">@endif</head>
<body class="min-h-screen bg-slate-50">@if($mediaConfig)<main class="flex min-h-screen p-5">@include('teacher-live-session::partials.media-stage', ['displayName' => $guest->name])</main>@else<main class="grid min-h-screen place-items-center p-5"><section class="w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-xl shadow-slate-200/60">
    @if($guest->admission_status->value === 'waiting' || !$guest->session->isLive())
        <span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-amber-50 text-amber-700"><x-heroicon-o-clock class="h-8 w-8" /></span><h1 class="mt-5 text-xl font-black text-slate-950">@lang('teacher-live-session::app.guest_waiting_title')</h1><p class="mt-2 text-sm font-semibold leading-6 text-slate-500">@lang('teacher-live-session::app.guest_waiting_description')</p><span class="mt-5 inline-flex rounded-full bg-slate-100 px-3 py-2 text-xs font-black text-slate-600">{{ $guest->name }}</span>
    @else
        <span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-green-50 text-green-700"><x-heroicon-o-check-circle class="h-8 w-8" /></span><h1 class="mt-5 text-xl font-black text-slate-950">@lang('teacher-live-session::app.guest_admitted_title')</h1><p class="mt-2 text-sm font-semibold leading-6 text-slate-500">@lang('teacher-live-session::app.guest_admitted_description')</p>
    @endif
</section></main>@endif</body></html>
