@extends('Mindigo-dashboard::layouts')

@section('title', $lesson->name.' - Auronsoft LMS')
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="min-h-screen bg-slate-50">
    <header class="border-b border-slate-200 bg-white px-5 py-4 sm:px-6">
        <div class="flex items-center justify-between gap-4"><div class="min-w-0"><p class="text-[11px] font-black uppercase tracking-widest text-green-700">{{ $lesson->chapter->course->name }}</p><h1 class="mt-0.5 truncate text-lg font-black text-slate-950">{{ $lesson->name }}</h1><p class="text-xs font-semibold text-slate-400">{{ $lesson->chapter->name }}</p></div><a href="{{ route('courses.show', $lesson->chapter->course->slug) }}" aria-label="@lang('teacher-course::catalog.back_to_course')" class="grid h-10 w-10 shrink-0 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline hover:text-green-700"><x-heroicon-o-arrow-left class="h-5 w-5" /></a></div>
    </header>
    <main class="mx-auto max-w-5xl p-4 sm:p-6">
        @if($lesson->video_path)<section class="overflow-hidden rounded-xl border border-slate-200 bg-slate-950"><video controls preload="metadata" class="aspect-video w-full"><source src="{{ route('courses.lessons.video', [$lesson->chapter->course->slug, $lesson->id]) }}"></video></section>@endif
        <article class="mt-5 rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
            @if($lesson->description)<p class="mb-5 border-b border-slate-100 pb-5 text-sm font-semibold leading-6 text-slate-500">{{ $lesson->description }}</p>@endif
            <div class="prose prose-slate max-w-none text-sm leading-7">{!! $lesson->content !!}</div>
        </article>
        @if(filled($lesson->attachment_paths))<section class="mt-5 rounded-xl border border-slate-200 bg-white p-5"><h2 class="text-sm font-black text-slate-950">@lang('teacher-course::catalog.attachments')</h2><div class="mt-3 divide-y divide-slate-100">@foreach($lesson->attachment_paths as $index => $attachment)<a href="{{ route('courses.lessons.attachments.show', [$lesson->chapter->course->slug, $lesson->id, $index]) }}" class="flex items-center gap-3 py-3 text-sm font-bold text-slate-600 no-underline hover:text-green-700"><x-heroicon-o-paper-clip class="h-4 w-4" /><span class="min-w-0 flex-1 truncate">{{ data_get($attachment, 'original_name', __('teacher-course::catalog.attachment')) }}</span><x-heroicon-o-arrow-down-tray class="h-4 w-4" /></a>@endforeach</div></section>@endif
    </main>
</div>
@endsection
