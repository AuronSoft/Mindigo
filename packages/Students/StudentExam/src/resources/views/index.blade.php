{{-- student-exam::index --}}
@extends('Mindigo-dashboard::layouts')

@section('title', __('student-exam::app.my_exams'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Students/StudentExam/src/resources/css/app.css',
        'packages/Students/StudentExam/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8 space-y-10">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ __('student-exam::app.my_exams') }}
        </h1>
    </div>

    {{-- Flash messages --}}
    @if(session('warning'))
        <div class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
            {{ session('warning') }}
        </div>
    @endif

    {{-- ① Đang mở --}}
    <section>
        <h2 class="mb-4 flex items-center gap-2 text-base font-semibold text-emerald-700 dark:text-emerald-400">
            <span class="inline-block h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
            {{ __('student-exam::app.ongoing_exams') }}
        </h2>

        @if($ongoing->isEmpty())
            <p class="rounded-xl border border-dashed border-gray-200 py-10 text-center text-sm text-gray-400 dark:border-gray-700 dark:text-gray-500">
                {{ __('student-exam::app.no_ongoing') }}
            </p>
        @else
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach($ongoing as $exam)
                    @include('student-exam::partials.exam-card', ['exam' => $exam, 'group' => 'ongoing'])
                @endforeach
            </div>
        @endif
    </section>

    {{-- ② Sắp mở --}}
    <section>
        <h2 class="mb-4 flex items-center gap-2 text-base font-semibold text-blue-700 dark:text-blue-400">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"/></svg>
            {{ __('student-exam::app.upcoming_exams') }}
        </h2>

        @if($upcoming->isEmpty())
            <p class="rounded-xl border border-dashed border-gray-200 py-10 text-center text-sm text-gray-400 dark:border-gray-700 dark:text-gray-500">
                {{ __('student-exam::app.no_upcoming') }}
            </p>
        @else
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach($upcoming as $exam)
                    @include('student-exam::partials.exam-card', ['exam' => $exam, 'group' => 'upcoming'])
                @endforeach
            </div>
        @endif
    </section>

    {{-- ③ Đã làm --}}
    <section>
        <h2 class="mb-4 flex items-center gap-2 text-base font-semibold text-gray-500 dark:text-gray-400">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ __('student-exam::app.completed_exams') }}
        </h2>

        @if($completed->isEmpty())
            <p class="rounded-xl border border-dashed border-gray-200 py-10 text-center text-sm text-gray-400 dark:border-gray-700 dark:text-gray-500">
                {{ __('student-exam::app.no_completed') }}
            </p>
        @else
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach($completed as $exam)
                    @include('student-exam::partials.exam-card', ['exam' => $exam, 'group' => 'completed'])
                @endforeach
            </div>
        @endif
    </section>

</div>
@endsection
