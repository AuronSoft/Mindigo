@extends('Mindigo-dashboard::layouts')
@section('title', __('student-exam::app.exams') . ' · Mindigo LMS')

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">

    {{-- Header --}}
    <header class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white/90 px-6 py-4 backdrop-blur">
        <div>
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">@lang('student-exam::app.area')</p>
            <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('student-exam::app.exams')</h1>
        </div>
        <span class="hidden sm:grid h-11 w-11 place-items-center rounded-2xl bg-blue-50 text-blue-600">
            <x-heroicon-o-document-text class="h-6 w-6" />
        </span>
    </header>

    <div class="flex-1 p-6 space-y-8">

        {{-- Upcoming --}}
        @if($upcoming->isNotEmpty())
        <section>
            <h2 class="mb-4 flex items-center gap-2 text-sm font-black text-slate-700">
                <x-heroicon-o-clock class="h-5 w-5 text-amber-500" />
                @lang('student-exam::app.upcoming')
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach($upcoming as $exam)
                    @include('student-exam::partials.exam-card', ['exam' => $exam, 'status' => 'upcoming'])
                @endforeach
            </div>
        </section>
        @endif

        {{-- Ongoing --}}
        @if($ongoing->isNotEmpty())
        <section>
            <h2 class="mb-4 flex items-center gap-2 text-sm font-black text-slate-700">
                <x-heroicon-o-play-circle class="h-5 w-5 text-green-600" />
                @lang('student-exam::app.ongoing')
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach($ongoing as $exam)
                    @include('student-exam::partials.exam-card', ['exam' => $exam, 'status' => 'ongoing'])
                @endforeach
            </div>
        </section>
        @endif

        {{-- Completed --}}
        @if($completed->isNotEmpty())
        <section>
            <h2 class="mb-4 flex items-center gap-2 text-sm font-black text-slate-700">
                <x-heroicon-o-check-circle class="h-5 w-5 text-slate-500" />
                @lang('student-exam::app.completed')
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach($completed as $exam)
                    @include('student-exam::partials.exam-card', ['exam' => $exam, 'status' => 'completed'])
                @endforeach
            </div>
        </section>
        @endif

        @if($upcoming->isEmpty() && $ongoing->isEmpty() && $completed->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <x-heroicon-o-document-text class="h-16 w-16 text-slate-300" />
                <p class="mt-4 text-lg font-black text-slate-700">@lang('student-exam::app.no_exams')</p>
            </div>
        @endif
    </div>
</div>
@endsection