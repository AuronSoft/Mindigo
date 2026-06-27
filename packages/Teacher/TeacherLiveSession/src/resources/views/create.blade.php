@extends('Mindigo-dashboard::layouts')
@section('title', __('teacher-live-session::app.create'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    <header class="sticky top-0 z-10 flex items-center gap-4 border-b border-slate-200 bg-white/90 px-6 py-4 backdrop-blur">
        <a href="{{ route('teacher.live-sessions.index') }}"
           class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50">
            <x-heroicon-o-arrow-left class="h-5 w-5" />
        </a>
        <div>
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">@lang('teacher-live-session::app.title')</p>
            <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('teacher-live-session::app.create')</h1>
        </div>
    </header>

    <div class="mx-auto w-full max-w-4xl px-6 py-4">
        <form action="{{ route('teacher.live-sessions.store') }}" method="POST" class="grid grid-cols-1 gap-5 lg:grid-cols-12">
            @csrf
            @include('teacher-live-session::_form', ['session' => null])

            <div class="flex items-center justify-end gap-3 lg:col-span-12">
                <a href="{{ route('teacher.live-sessions.index') }}"
                   class="inline-flex h-11 items-center justify-center rounded-full border border-slate-200 bg-white px-6 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50">
                    @lang('teacher-live-session::app.cancel')
                </a>
                <button type="submit"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-green-600 px-8 text-sm font-black text-white shadow-sm shadow-green-200 transition hover:bg-green-500">
                    @lang('teacher-live-session::app.save')
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
