@extends('Mindigo-dashboard::layouts')
@section('title', __('teacher-live-session::app.create'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50 lg:h-screen lg:overflow-hidden">
    <header class="sticky top-0 z-10 flex items-center gap-4 border-b border-slate-200 bg-white/90 px-6 py-3 backdrop-blur">
        <a href="{{ route('teacher.live-sessions.index') }}"
           class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50">
            <x-heroicon-o-arrow-left class="h-5 w-5" />
        </a>
        <div>
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">@lang('teacher-live-session::app.title')</p>
            <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('teacher-live-session::app.create')</h1>
        </div>
    </header>

    <div class="flex min-h-0 flex-1 items-start p-3 sm:p-4">
        <form action="{{ route('teacher.live-sessions.store') }}" method="POST" class="flex w-full flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 lg:h-full">
            @csrf
            @include('teacher-live-session::_form', ['session' => null])

            <div class="flex shrink-0 items-center justify-end gap-2 border-t border-slate-100 pt-3">
                <a href="{{ route('teacher.live-sessions.index') }}"
                   class="inline-flex h-10 items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-600 no-underline transition hover:bg-slate-50">
                    @lang('teacher-live-session::app.cancel')
                </a>
                <button type="button" data-live-session-form-next
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-green-600 px-6 text-sm font-black text-white transition hover:bg-green-500">
                    @lang('teacher-live-session::app.continue') <x-heroicon-o-arrow-right class="h-4 w-4" />
                </button>
                <button type="submit" data-live-session-form-submit hidden
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-green-600 px-6 text-sm font-black text-white transition hover:bg-green-500">
                    <x-heroicon-o-check class="h-4 w-4" /> @lang('teacher-live-session::app.save')
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
