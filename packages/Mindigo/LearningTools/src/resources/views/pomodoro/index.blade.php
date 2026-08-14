@extends('Mindigo-dashboard::layouts')
@section('title', __('learning-tools::app.pomodoro.title') . ' · Auronsoft LMS')
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    @include('learning-tools::partials.header', [
        'eyebrow' => __('learning-tools::app.eyebrow'),
        'title' => __('learning-tools::app.pomodoro.title'),
        'subtitle' => __('learning-tools::app.pomodoro.subtitle'),
    ])

    <div class="grid flex-1 gap-5 p-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @if($activeSession)
                <div class="flex min-h-96 flex-col items-center justify-center text-center"
                    data-pomodoro
                    data-started-at="{{ $activeSession->started_at->toIso8601String() }}"
                    data-minutes="{{ $activeSession->planned_minutes }}">
                    <span class="grid h-16 w-16 place-items-center rounded-2xl bg-green-50 text-green-700">
                        <x-heroicon-o-clock class="h-8 w-8" />
                    </span>
                    <p class="mt-5 text-xs font-black uppercase tracking-widest text-slate-400">
                        {{ $activeSession->subject?->name ?? __('learning-tools::app.pomodoro.general') }}
                    </p>
                    <p class="mt-2 text-6xl font-black tabular-nums tracking-tight text-slate-950" data-pomodoro-clock>
                        {{ sprintf('%02d:00', $activeSession->planned_minutes) }}
                    </p>
                    <p class="mt-3 text-sm font-semibold text-slate-400">@lang('learning-tools::app.pomodoro.focus_message')</p>
                    <div class="mt-8 flex flex-wrap justify-center gap-3">
                        <form method="POST" action="{{ route('learning-tools.pomodoro.complete', $activeSession) }}">
                            @csrf @method('PATCH')
                            <button class="inline-flex h-11 items-center gap-2 rounded-full bg-green-600 px-6 text-sm font-black text-white shadow-sm shadow-green-200 transition hover:bg-green-500">
                                <x-heroicon-o-check class="h-4 w-4" /> @lang('learning-tools::app.pomodoro.complete')
                            </button>
                        </form>
                        <form method="POST" action="{{ route('learning-tools.pomodoro.cancel', $activeSession) }}">
                            @csrf @method('PATCH')
                            <button class="inline-flex h-11 items-center gap-2 rounded-full border border-red-100 bg-red-50 px-6 text-sm font-black text-red-600 transition hover:bg-red-100">
                                <x-heroicon-o-x-mark class="h-4 w-4" /> @lang('learning-tools::app.pomodoro.cancel')
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="mx-auto max-w-xl py-8">
                    <div class="text-center">
                        <span class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-green-50 text-green-700">
                            <x-heroicon-o-play class="h-8 w-8" />
                        </span>
                        <h2 class="mt-4 text-xl font-black text-slate-900">@lang('learning-tools::app.pomodoro.start_title')</h2>
                        <p class="mt-1 text-sm font-semibold text-slate-400">@lang('learning-tools::app.pomodoro.start_description')</p>
                    </div>
                    <form method="POST" action="{{ route('learning-tools.pomodoro.store') }}" class="mt-8 space-y-5">
                        @csrf
                        <label class="block">
                            <span class="text-sm font-black text-slate-700">@lang('learning-tools::app.fields.subject')</span>
                            <select name="subject_id" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none focus:border-green-400 focus:ring-4 focus:ring-green-50">
                                <option value="">@lang('learning-tools::app.pomodoro.general')</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label>
                                <span class="text-sm font-black text-slate-700">@lang('learning-tools::app.pomodoro.focus_minutes')</span>
                                <input type="number" name="planned_minutes" min="5" max="120" value="{{ old('planned_minutes', 25) }}" class="mt-2 h-12 w-full rounded-xl border border-slate-200 px-4 text-sm font-bold outline-none focus:border-green-400 focus:ring-4 focus:ring-green-50">
                            </label>
                            <label>
                                <span class="text-sm font-black text-slate-700">@lang('learning-tools::app.pomodoro.break_minutes')</span>
                                <input type="number" name="break_minutes" min="1" max="30" value="{{ old('break_minutes', 5) }}" class="mt-2 h-12 w-full rounded-xl border border-slate-200 px-4 text-sm font-bold outline-none focus:border-green-400 focus:ring-4 focus:ring-green-50">
                            </label>
                        </div>
                        @if($errors->any())
                            <p class="text-sm font-bold text-red-600">{{ $errors->first() }}</p>
                        @endif
                        <button class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-full bg-green-600 px-6 text-sm font-black text-white shadow-sm shadow-green-200 transition hover:bg-green-500">
                            <x-heroicon-o-play class="h-4 w-4" /> @lang('learning-tools::app.pomodoro.start')
                        </button>
                    </form>
                </div>
            @endif
        </section>

        <aside class="space-y-5">
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">@lang('learning-tools::app.pomodoro.this_week')</p>
                <p class="mt-2 text-3xl font-black text-slate-950">{{ $weeklyMinutes }}</p>
                <p class="text-xs font-bold text-slate-400">@lang('learning-tools::app.pomodoro.minutes_unit')</p>
            </section>
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-black text-slate-900">@lang('learning-tools::app.pomodoro.history')</h2>
                <div class="mt-4 space-y-3">
                    @forelse($sessions as $session)
                        <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3 last:border-0 last:pb-0">
                            <div>
                                <p class="text-sm font-black text-slate-700">{{ $session->subject?->name ?? __('learning-tools::app.pomodoro.general') }}</p>
                                <p class="text-xs font-semibold text-slate-400">{{ $session->started_at->diffForHumans() }}</p>
                            </div>
                            <span class="text-xs font-black {{ $session->status === 'completed' ? 'text-green-700' : 'text-slate-400' }}">
                                @lang('learning-tools::app.statuses.' . $session->status)
                            </span>
                        </div>
                    @empty
                        <p class="text-sm font-semibold text-slate-400">@lang('learning-tools::app.pomodoro.empty_history')</p>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>
</div>

@if($activeSession)
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-pomodoro]');
    const clock = root?.querySelector('[data-pomodoro-clock]');
    if (!root || !clock) return;
    const end = new Date(root.dataset.startedAt).getTime() + Number(root.dataset.minutes) * 60000;
    const render = () => {
        const remaining = Math.max(0, Math.floor((end - Date.now()) / 1000));
        clock.textContent = `${String(Math.floor(remaining / 60)).padStart(2, '0')}:${String(remaining % 60).padStart(2, '0')}`;
    };
    render();
    window.setInterval(render, 1000);
});
</script>
@endif
@endsection
