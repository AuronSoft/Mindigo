@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-classroom::app.title') . ' · Auronsoft LMS')
@section('meta_description', __('teacher-classroom::app.subtitle'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Teacher/TeacherClassroom/src/resources/css/app.css',
        'packages/Teacher/TeacherClassroom/src/resources/js/app.js',
    ])
@endsection

@section('content')
@php($activeFilterCount = collect($filters)->filter()->count())
<div class="flex min-h-screen flex-col bg-[#f7f9fc]">
    <header class="sticky top-0 z-10 border-b border-slate-200/80 bg-white/95 px-6 py-4 backdrop-blur">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div><p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-green-700">@lang('teacher-classroom::app.teaching_classroom')</p><h1 class="mt-0.5 text-xl font-black tracking-tight text-slate-950">@lang('teacher-classroom::app.title')</h1><p class="mt-1 text-xs font-medium text-slate-500">@lang('teacher-classroom::app.subtitle')</p></div>
            <div class="flex items-center gap-2">
                <button type="button" data-mindigo-drawer-open="teacher-classroom-filter" class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 transition hover:border-green-200 hover:bg-green-50 hover:text-green-700"><x-heroicon-o-adjustments-horizontal class="h-4 w-4" />@lang('teacher-classroom::app.filters')@if($activeFilterCount)<span class="grid h-5 min-w-5 place-items-center rounded-full bg-green-600 px-1.5 text-[10px] text-white">{{ $activeFilterCount }}</span>@endif</button>
                <a href="{{ route('teacher.classrooms.create') }}" class="inline-flex h-10 items-center gap-2 rounded-xl bg-green-600 px-4 text-sm font-bold text-white no-underline shadow-sm transition hover:bg-green-700"><x-heroicon-o-plus class="h-4 w-4" />@lang('teacher-classroom::app.create')</a>
            </div>
        </div>
    </header>

    <main class="flex flex-1 flex-col gap-5 p-6">
        @if($activeFilterCount)
            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500"><span>@lang('teacher-classroom::app.filtering_by')</span>@if(filled($filters['keyword'] ?? null))<span class="rounded-full border border-slate-200 bg-white px-3 py-1.5">“{{ $filters['keyword'] }}”</span>@endif @if(filled($filters['status'] ?? null))<span class="rounded-full border border-slate-200 bg-white px-3 py-1.5">@lang('teacher-classroom::app.' . $filters['status'])</span>@endif <a href="{{ route('teacher.classrooms.index') }}" class="text-green-700 no-underline hover:text-green-800">@lang('teacher-classroom::app.clear_filter')</a></div>
        @endif

        @if($classrooms->isEmpty())
            <section class="flex flex-1 flex-col items-center justify-center gap-4 rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-20"><span class="grid h-16 w-16 place-items-center rounded-2xl bg-green-50 text-green-600"><x-heroicon-o-user-group class="h-8 w-8" /></span><div class="text-center"><p class="text-lg font-bold text-slate-800">@lang('teacher-classroom::app.empty_title')</p><p class="mt-1 max-w-sm text-sm leading-6 text-slate-500">@lang('teacher-classroom::app.empty_desc')</p></div><a href="{{ route('teacher.classrooms.create') }}" class="inline-flex h-10 items-center gap-2 rounded-xl bg-green-600 px-5 text-sm font-bold text-white no-underline hover:bg-green-700"><x-heroicon-o-plus class="h-4 w-4" />@lang('teacher-classroom::app.create')</a></section>
        @else
            <section aria-label="@lang('teacher-classroom::app.title')" class="grid grid-cols-[repeat(auto-fill,minmax(260px,290px))] gap-5">
                @foreach($classrooms as $classroom)
                    @include('teacher-classroom::partials.classroom-card', ['classroom' => $classroom])
                @endforeach
            </section>
            @if($classrooms->hasPages())<div class="flex justify-center">{{ $classrooms->links() }}</div>@endif
        @endif
    </main>

    @include('teacher-classroom::partials.filter-drawer', ['filters' => $filters])
</div>
@endsection
