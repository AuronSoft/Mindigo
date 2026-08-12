@extends('Mindigo-dashboard::layouts')
@section('title', __('teacher-live-session::app.attendance_report'))

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    <header class="border-b border-slate-200 bg-white px-6 py-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('teacher.live-sessions.index') }}" class="grid h-9 w-9 place-items-center rounded-xl border border-slate-200 text-slate-500 no-underline hover:bg-slate-50"><x-heroicon-o-arrow-left class="h-4 w-4" /></a>
                <div><p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('teacher-live-session::app.attendance_report')</p><h1 class="text-lg font-black text-slate-950">{{ $session->title }}</h1><p class="text-xs font-semibold text-slate-400">{{ $session->classroom?->name }} · {{ $session->scheduled_start?->format('d/m/Y H:i') }}</p></div>
            </div>
            <a href="{{ route('teacher.live-sessions.attendance.export', $session) }}" class="inline-flex h-10 items-center gap-2 rounded-xl bg-green-600 px-4 text-sm font-black text-white no-underline hover:bg-green-500"><x-heroicon-o-arrow-down-tray class="h-4 w-4" />@lang('teacher-live-session::app.export_csv')</a>
        </div>
    </header>

    <main class="flex-1 space-y-4 p-4 sm:p-6">
        <dl class="grid overflow-hidden rounded-2xl border border-slate-200 bg-white sm:grid-cols-5">
            @foreach(['total', 'present', 'late', 'absent', 'average_minutes'] as $metric)
                <div class="border-b border-slate-100 px-5 py-4 last:border-0 sm:border-b-0 sm:border-r"><dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-live-session::app.report_'.$metric)</dt><dd class="mt-1 text-xl font-black text-slate-950">{{ $summary[$metric] }}{{ $metric === 'average_minutes' ? 'p' : '' }}</dd></div>
            @endforeach
        </dl>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="overflow-x-auto"><table class="w-full min-w-250 text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3">@lang('teacher-live-session::app.student')</th><th class="px-5 py-3">@lang('teacher-live-session::app.report_status')</th><th class="px-5 py-3">@lang('teacher-live-session::app.report_joined')</th><th class="px-5 py-3">@lang('teacher-live-session::app.report_duration')</th><th class="px-5 py-3">@lang('teacher-live-session::app.report_rejoins')</th><th class="px-5 py-3">@lang('teacher-live-session::app.report_engagement')</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rows as $row)<tr><td class="px-5 py-4"><p class="font-black text-slate-900">{{ $row['name'] }}</p><p class="text-xs font-semibold text-slate-400">{{ $row['email'] }}</p></td><td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-black {{ match($row['status']) {'present' => 'bg-green-50 text-green-700', 'late' => 'bg-amber-50 text-amber-700', 'partial' => 'bg-violet-50 text-violet-700', default => 'bg-rose-50 text-rose-700'} }}">@lang('teacher-live-session::app.attendance_'.$row['status'])</span>@if($row['late_minutes'])<p class="mt-2 text-xs font-semibold text-amber-600">+{{ $row['late_minutes'] }} @lang('teacher-live-session::app.minutes')</p>@endif</td><td class="px-5 py-4 font-semibold text-slate-600">{{ $row['joined_at']?->format('H:i:s') ?? '—' }}</td><td class="px-5 py-4 font-black text-slate-800">{{ intdiv($row['total_seconds'], 60) }} @lang('teacher-live-session::app.minutes')</td><td class="px-5 py-4 font-bold text-slate-600">{{ $row['join_count'] }}</td><td class="px-5 py-4"><div class="flex items-center gap-3"><div class="h-2 w-24 overflow-hidden rounded-full bg-slate-100"><span class="block h-full rounded-full bg-green-500" style="width: {{ $row['engagement_score'] }}%"></span></div><strong class="text-xs text-slate-700">{{ $row['engagement_score'] }}</strong></div><p class="mt-2 text-[10px] font-semibold text-slate-400">{{ $row['chat_messages_count'] }} chat · {{ $row['poll_votes_count'] }} poll · {{ $row['hands_raised_count'] }} hand</p></td></tr>@empty<tr><td colspan="6" class="px-5 py-16 text-center text-sm font-semibold text-slate-400">@lang('teacher-live-session::app.no_attendance_data')</td></tr>@endforelse
                </tbody>
            </table></div>
        </section>
    </main>
</div>
@endsection
