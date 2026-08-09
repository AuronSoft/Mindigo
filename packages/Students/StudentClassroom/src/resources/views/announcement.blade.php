@extends('Mindigo-dashboard::layouts')
@section('title', $announcement->title . ' · Mindigo LMS')

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
@php
    $type = $announcement->type ?? 'info';
    $typeMeta = match ($type) {
        'warning'    => ['bg-amber-100 text-amber-700', 'heroicon-o-exclamation-triangle'],
        'reminder'   => ['bg-blue-100 text-blue-700', 'heroicon-o-bell-alert'],
        'assignment' => ['bg-violet-100 text-violet-700', 'heroicon-o-clipboard-document-list'],
        default      => ['bg-green-100 text-green-700', 'heroicon-o-information-circle'],
    };
    $pinned = (bool) ($announcement->is_pinned ?? false);
@endphp
<div class="flex min-h-screen flex-col bg-slate-50">

    {{-- Header --}}
    <header class="sticky top-0 z-10 flex items-center gap-4 border-b border-slate-200 bg-white/90 px-6 py-4 backdrop-blur">
        <a href="{{ route('student.classrooms.show', $classroom) }}"
           class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50">
            <x-heroicon-o-arrow-left class="h-5 w-5" />
        </a>
        <div class="min-w-0">
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">
                @lang('student-classroom::app.section_announce') · {{ $classroom->name }}
            </p>
            <h1 class="mt-0.5 truncate text-lg font-black text-slate-950">{{ $announcement->title }}</h1>
        </div>
    </header>

    <div class="mx-auto w-full max-w-3xl flex-1 p-6">

        {{-- Card chi tiết thông báo --}}
        <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 px-6 py-4">
                <span class="inline-flex items-center gap-1.5 rounded-full {{ $typeMeta[0] }} px-3 py-1 text-xs font-black">
                    <x-dynamic-component :component="$typeMeta[1]" class="h-3.5 w-3.5" />
                    @lang('student-classroom::app.type_' . $type)
                </span>
                @if($pinned)
                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">
                        <x-heroicon-o-bookmark class="h-3.5 w-3.5" />
                        @lang('student-classroom::app.pinned')
                    </span>
                @endif
                <span class="ml-auto text-xs font-bold text-slate-400">{{ $announcement->published_at?->format('d/m/Y H:i') }}</span>
            </div>

            <div class="px-6 py-6">
                <h2 class="text-xl font-black leading-snug text-slate-900">{{ $announcement->title }}</h2>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    @lang('student-classroom::app.posted_by') {{ $announcement->teacher?->name ?? __('student-classroom::app.no_teacher') }}
                </p>

                <div class="prose prose-sm prose-slate mt-5 max-w-none whitespace-pre-line text-[15px] font-semibold leading-relaxed text-slate-700">
                    {{ $announcement->content }}
                </div>
            </div>
        </article>

        {{-- Nút quay lại --}}
        <div class="mt-5 flex justify-center">
            <a href="{{ route('student.classrooms.show', $classroom) }}"
               class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-black text-slate-600 ring-1 ring-slate-200 no-underline transition hover:bg-slate-50">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
                @lang('student-classroom::app.back')
            </a>
        </div>
    </div>
</div>
@endsection
