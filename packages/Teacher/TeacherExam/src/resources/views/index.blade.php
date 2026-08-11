@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-exam::app.title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    <header class="sticky top-0 z-10 border-b border-slate-200 bg-white px-6 py-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('teacher-exam::app.teaching_exam')</p>
                <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('teacher-exam::app.title')</h1>
                <p class="mt-1 text-xs font-semibold text-slate-400">@lang('teacher-exam::app.subtitle')</p>
            </div>
            <a href="{{ route('teacher.exams.create') }}" class="inline-flex h-10 items-center gap-2 rounded-xl bg-green-600 px-4 text-sm font-black text-white no-underline shadow-sm transition hover:bg-green-500">
                <x-heroicon-o-plus class="h-4 w-4" />@lang('teacher-exam::app.create')
            </a>
        </div>
    </header>

    <div class="flex flex-1 flex-col gap-5 p-4 sm:p-6">
        <section aria-labelledby="exam-catalog-title" class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 id="exam-catalog-title" class="text-base font-black text-slate-900">@lang('teacher-exam::app.catalog_title')</h2>
                <p class="mt-1 text-xs font-semibold text-slate-400">{{ __('teacher-exam::app.catalog_count', ['count' => $exams->total()]) }}</p>
            </div>

            <form method="GET" action="{{ route('teacher.exams.index') }}" class="flex w-full flex-wrap items-center gap-2 lg:w-auto" role="search">
                <label class="flex h-10 min-w-56 flex-1 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 transition focus-within:border-green-400 focus-within:ring-2 focus-within:ring-green-50 lg:w-72">
                    <x-heroicon-o-magnifying-glass class="h-4 w-4 shrink-0 text-slate-400" />
                    <span class="sr-only">@lang('teacher-exam::app.search')</span>
                    <input type="search" name="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="@lang('teacher-exam::app.search')" class="min-w-0 flex-1 bg-transparent text-sm font-bold text-slate-700 outline-none placeholder:text-slate-400">
                </label>
                <label class="sr-only" for="exam-status-filter">@lang('teacher-exam::app.all_status')</label>
                <select id="exam-status-filter" name="status" data-mindigo-auto-submit class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-600 outline-none transition focus:border-green-400 focus:ring-2 focus:ring-green-50">
                    <option value="">@lang('teacher-exam::app.all_status')</option>
                    @foreach(['published', 'draft', 'reviewing', 'closed'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>@lang('teacher-exam::app.'.$status)</option>
                    @endforeach
                </select>
                @if(filled($filters['keyword'] ?? null) || filled($filters['status'] ?? null))
                    <a href="{{ route('teacher.exams.index') }}" class="inline-flex h-10 items-center rounded-xl px-3 text-xs font-black text-slate-500 no-underline transition hover:bg-slate-100 hover:text-slate-800">@lang('teacher-exam::app.clear_filter')</a>
                @endif
            </form>
        </section>

        @if($exams->isEmpty())
            <section class="flex flex-1 flex-col items-center justify-center gap-4 rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-20 text-center">
                <span class="grid h-16 w-16 place-items-center rounded-2xl bg-green-50 text-green-600"><x-heroicon-o-document-text class="h-8 w-8" /></span>
                <div>
                    <h2 class="text-base font-black text-slate-800">@lang('teacher-exam::app.empty_title')</h2>
                    <p class="mt-1 max-w-sm text-sm font-semibold leading-6 text-slate-400">@lang('teacher-exam::app.empty_desc')</p>
                </div>
                <a href="{{ route('teacher.exams.create') }}" class="inline-flex h-10 items-center gap-2 rounded-xl bg-green-600 px-5 text-sm font-black text-white no-underline transition hover:bg-green-500">
                    <x-heroicon-o-plus class="h-4 w-4" />@lang('teacher-exam::app.create')
                </a>
            </section>
        @else
            <section aria-label="@lang('teacher-exam::app.catalog_title')" class="grid grid-cols-[repeat(auto-fill,minmax(17rem,20rem))] gap-4">
                @foreach($exams as $exam)
                    @include('teacher-exam::partials.exam-card', ['exam' => $exam])
                @endforeach
            </section>

            @if($exams->hasPages())
                <nav aria-label="@lang('teacher-exam::app.pagination')" class="flex justify-center pt-1">{{ $exams->links() }}</nav>
            @endif
        @endif
    </div>
</div>
@endsection
