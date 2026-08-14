@extends('Mindigo-dashboard::layouts')
@section('title', __('Mindigo-exam-management::app.monitoring.title'))
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/ExamManagement/src/resources/css/app.css'])
@endsection
@section('content')
<main class="exam-foundation-shell" data-exam-monitor data-session-id="{{ $session->id }}" data-refresh-url="{{ route('teacher.exam-sessions.monitoring.data', ['session' => $session, 'status' => request('status')]) }}"><div class="exam-foundation-container max-w-7xl">
    <x-exam::page-header :eyebrow="__('Mindigo-exam-management::app.monitoring.workspace')" :title="$session->title" :description="__('Mindigo-exam-management::app.monitoring.description')"><x-slot:actions><x-exam::button variant="secondary" :href="route('teacher.exam-sessions.index')">@lang('Mindigo-exam-management::app.monitoring.back')</x-exam::button></x-slot:actions></x-exam::page-header>
    @if(session('success'))<div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-bold text-green-700">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-700">{{ $errors->first() }}</div>@endif
    <div class="grid gap-3 sm:grid-cols-3 xl:grid-cols-6">@foreach(['not_started', 'in_progress', 'disconnected', 'paused', 'submitted', 'terminated'] as $state)<a href="{{ route('teacher.exam-sessions.monitoring.index', ['session' => $session, 'status' => $state]) }}" class="rounded-2xl border border-slate-200 bg-white p-4 no-underline shadow-sm"><span class="text-xs font-black text-slate-500">{{ __('Mindigo-exam-management::app.monitoring.statuses.'.$state) }}</span><strong class="mt-2 block text-2xl text-slate-950" data-summary="{{ $state }}">{{ $summary[$state] }}</strong></a>@endforeach</div>
    <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="flex items-center justify-between border-b border-slate-100 px-5 py-4"><div><h2 class="font-black text-slate-900">@lang('Mindigo-exam-management::app.monitoring.candidates')</h2><p class="text-xs font-semibold text-slate-400">@lang('Mindigo-exam-management::app.monitoring.realtime_hint')</p></div><span class="flex items-center gap-2 text-xs font-black text-green-700"><i class="h-2 w-2 rounded-full bg-green-500"></i>@lang('Mindigo-exam-management::app.monitoring.live')</span></div><div class="overflow-x-auto"><table class="w-full text-left"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr>@foreach(['candidate', 'status', 'time_left', 'last_activity', 'current_question', 'warnings', 'integrity_score', 'actions'] as $column)<th class="px-4 py-3 font-black">{{ __('Mindigo-exam-management::app.monitoring.'.$column) }}</th>@endforeach</tr></thead><tbody data-monitor-rows>@include('Mindigo-exam-management::monitoring.partials.candidates')</tbody></table></div></div>
</div></main>
@endsection
@section('scripts')
<script>
(() => {
    const monitor = document.querySelector('[data-exam-monitor]'); if (! monitor) return;
    let refreshing = false;
    const refresh = async () => { if (refreshing) return; refreshing = true; try { const response = await fetch(monitor.dataset.refreshUrl, { headers: { Accept: 'application/json' } }); if (! response.ok) return; const data = await response.json(); document.querySelector('[data-monitor-rows]').innerHTML = data.html; Object.entries(data.summary).forEach(([key, value]) => { const item = document.querySelector(`[data-summary="${key}"]`); if (item) item.textContent = value; }); } finally { refreshing = false; } };
    window.Echo?.private(`exam-session.${monitor.dataset.sessionId}`).listen('.exam.monitoring.updated', refresh);
    window.setInterval(refresh, 15000);
    window.setInterval(() => document.querySelectorAll('[data-remaining]').forEach((item) => { const value = Number(item.dataset.remaining); if (! Number.isFinite(value) || value <= 0) return; const next = value - 1; item.dataset.remaining = next; item.textContent = `${String(Math.floor(next / 60)).padStart(2, '0')}:${String(next % 60).padStart(2, '0')}`; }), 1000);
})();
</script>
@endsection
