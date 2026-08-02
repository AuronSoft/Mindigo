@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-course::learning.my_courses').' - Mindigo LMS')
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="min-h-screen bg-slate-50">
    <header class="border-b border-slate-200 bg-white px-5 py-4 sm:px-6"><p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('teacher-course::learning.student_space')</p><h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('teacher-course::learning.my_courses')</h1><p class="text-xs font-semibold text-slate-400">@lang('teacher-course::learning.my_courses_subtitle')</p></header>
    <main class="p-4 sm:p-6">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($enrollments as $enrollment)
                <article class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                    <div class="aspect-16/7 bg-slate-100">@if($enrollment->course->cover_image)<img src="{{ asset('storage/'.$enrollment->course->cover_image) }}" alt="{{ $enrollment->course->name }}" class="h-full w-full object-cover">@else<div class="grid h-full place-items-center text-slate-300"><x-heroicon-o-academic-cap class="h-10 w-10" /></div>@endif</div>
                    <div class="p-5"><div class="flex items-center justify-between gap-3"><span class="text-[10px] font-black uppercase text-green-700">{{ $enrollment->course->subject?->name ?? __('teacher-course::learning.course') }}</span><span class="rounded-full bg-slate-100 px-2 py-1 text-[9px] font-black text-slate-500">@lang('teacher-course::learning.statuses.'.$enrollment->status)</span></div><h2 class="mt-2 line-clamp-2 text-base font-black text-slate-950">{{ $enrollment->course->name }}</h2><p class="mt-1 text-xs font-semibold text-slate-400">{{ $enrollment->course->teacher->name }}</p><div class="mt-4"><div class="flex justify-between text-[10px] font-black text-slate-500"><span>@lang('teacher-course::learning.progress')</span><span>{{ $enrollment->completion_percentage }}%</span></div><progress value="{{ $enrollment->completion_percentage }}" max="100" class="mt-2 h-1.5 w-full accent-green-600"></progress></div><a href="{{ route('student.courses.show', $enrollment->course->slug) }}" class="mt-4 inline-flex h-9 w-full items-center justify-center gap-2 rounded-lg bg-green-600 px-4 text-xs font-black text-white no-underline hover:bg-green-700"><x-heroicon-o-play class="h-4 w-4" />@lang('teacher-course::learning.continue_learning')</a></div>
                </article>
            @empty
                <section class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"><x-heroicon-o-book-open class="mx-auto h-10 w-10 text-slate-300" /><h2 class="mt-3 font-black text-slate-700">@lang('teacher-course::learning.empty_title')</h2><p class="mt-1 text-sm font-semibold text-slate-400">@lang('teacher-course::learning.empty_description')</p><a href="{{ route('courses.index') }}" class="mt-5 inline-flex h-10 items-center rounded-lg bg-green-600 px-5 text-xs font-black text-white no-underline">@lang('teacher-course::learning.explore_courses')</a></section>
            @endforelse
        </div>
        <div class="mt-5">{{ $enrollments->links() }}</div>
    </main>
</div>
@endsection
