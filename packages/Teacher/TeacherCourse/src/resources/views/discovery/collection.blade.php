@extends('Mindigo-dashboard::layouts')

@section('title', $title.' - Auronsoft LMS')
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="min-h-screen bg-slate-50">
    <header class="border-b border-slate-200 bg-white px-5 py-4 sm:px-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('courses.index') }}" aria-label="@lang('teacher-course::discovery.back_catalog')" class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 text-slate-600 no-underline hover:text-green-700"><x-heroicon-o-arrow-left class="h-4 w-4" /></a>
            <div><p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('teacher-course::catalog.eyebrow')</p><h1 class="text-lg font-black text-slate-950">{{ $title }}</h1><p class="text-xs font-semibold text-slate-400">{{ $description }}</p></div>
        </div>
    </header>
    <main class="p-4 sm:p-6">
        @if($courses->isEmpty())
            <div class="grid min-h-72 place-items-center rounded-xl border border-dashed border-slate-300 bg-white text-sm font-bold text-slate-400">@lang('teacher-course::discovery.empty')</div>
        @else
            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">@foreach($courses as $course) @include('teacher-course::catalog.partials.course-card', ['course' => $course, 'wishlistedIds' => $wishlistedIds ?? []]) @endforeach</section>
            @if(method_exists($courses, 'links'))<div class="mt-6">{{ $courses->links() }}</div>@endif
        @endif
    </main>
</div>
@endsection
