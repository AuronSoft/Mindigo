@extends('Mindigo-dashboard::layouts')
@section('title', __('teacher-live-session::app.provider_health_title'))
@section('content')
<div class="min-h-screen bg-slate-50 p-6"><div class="mx-auto max-w-6xl">
    <p class="text-xs font-black uppercase tracking-wider text-green-700">Mindigo Live</p>
    <h1 class="mt-1 text-2xl font-black text-slate-950">@lang('teacher-live-session::app.provider_health_title')</h1>
    <p class="mt-1 text-sm font-semibold text-slate-500">@lang('teacher-live-session::app.provider_health_subtitle')</p>
    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        @foreach($health as $item)
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3"><div><h2 class="font-black text-slate-900">{{ str($item['provider']->value)->replace('_', ' ')->title() }}</h2><p class="text-xs font-bold text-slate-400">{{ $item['provider']->value === 'native' ? __('teacher-live-session::app.default_provider') : __('teacher-live-session::app.external_provider') }}</p></div><span class="rounded-full px-2.5 py-1 text-xs font-black {{ $item['available'] ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">{{ $item['available'] ? __('teacher-live-session::app.healthy') : __('teacher-live-session::app.unavailable') }}</span></div>
            <dl class="mt-5 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-sm"><div><dt class="text-xs font-bold text-slate-400">@lang('teacher-live-session::app.pending_sync')</dt><dd class="font-black">{{ $item['pending'] }}</dd></div><div><dt class="text-xs font-bold text-slate-400">@lang('teacher-live-session::app.failed_sync')</dt><dd class="font-black">{{ $item['failed'] }}</dd></div><div><dt class="text-xs font-bold text-slate-400">@lang('teacher-live-session::app.failures')</dt><dd class="font-black">{{ $item['circuit']['failures'] }}</dd></div><div><dt class="text-xs font-bold text-slate-400">Circuit</dt><dd class="font-black {{ $item['circuit']['available'] ? 'text-green-700' : 'text-red-700' }}">{{ $item['circuit']['available'] ? 'Closed' : 'Open' }}</dd></div></dl>
            @if($item['provider']->isExternal() && !$item['circuit']['available'])<form method="POST" action="{{ route('admin.live-providers.health.reset', $item['provider']->value) }}" class="mt-4">@csrf<button class="h-9 w-full rounded-xl border border-slate-200 text-xs font-black text-slate-700">@lang('teacher-live-session::app.reset_circuit')</button></form>@endif
        </article>
        @endforeach
    </div>
</div></div>
@endsection
