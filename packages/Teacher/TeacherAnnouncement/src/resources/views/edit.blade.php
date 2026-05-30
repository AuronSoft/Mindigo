@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-announcement::app.edit') . ' — ' . $announcement->title)

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    <header class="sticky top-0 z-10 flex items-center gap-3 border-b border-slate-200 bg-white/95 px-6 py-3 backdrop-blur">
        <a href="{{ route('teacher.announcements.show', $announcement) }}"
           class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline transition hover:bg-green-50 hover:text-green-700">
            <x-heroicon-o-arrow-left class="h-4 w-4" />
        </a>
        <div>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ $announcement->title }}</p>
            <h1 class="text-base font-black text-slate-950">@lang('teacher-announcement::app.edit')</h1>
        </div>
    </header>

    <div class="mx-auto w-full max-w-2xl p-6">
        <form method="POST" action="{{ route('teacher.announcements.update', $announcement) }}" class="space-y-4">
            @csrf @method('PUT')
            @include('teacher-announcement::partials.form')

            <div class="flex justify-end gap-2 rounded-3xl border border-slate-200 bg-white p-4">
                <a href="{{ route('teacher.announcements.show', $announcement) }}"
                   class="inline-flex h-10 items-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-600 no-underline transition hover:bg-slate-50">
                    @lang('teacher-announcement::app.back')
                </a>
                <button type="submit"
                        class="inline-flex h-10 items-center gap-2 rounded-2xl bg-green-600 px-6 text-sm font-black text-white shadow-sm transition hover:bg-green-500">
                    <x-heroicon-o-check class="h-4 w-4" />@lang('teacher-announcement::app.save')
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
