@extends('Mindigo-dashboard::layouts')
@section('title', $attempt->exam->title . ' · Mindigo LMS')

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Students/StudentExam/src/resources/css/app.css',
        'packages/Students/StudentExam/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">

    {{-- Header --}}
    <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('student.exams.index') }}" 
                   class="text-slate-500 hover:text-slate-700">
                    <x-heroicon-o-arrow-left class="h-6 w-6" />
                </a>
                <div>
                    <h1 class="font-black text-lg text-slate-900 line-clamp-1">{{ $attempt->exam->title }}</h1>
                    <p class="text-xs text-slate-500">{{ $attempt->exam->classroom?->name }}</p>
                </div>
            </div>

            {{-- Timer --}}
            @include('student-exam::partials.timer', ['expiresAt' => $attempt->expires_at])
        </div>
    </header>

    <div class="flex flex-1">
        {{-- Sidebar câu hỏi --}}
        <div class="w-72 border-r border-slate-200 bg-white hidden lg:block overflow-y-auto">
            <div class="p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-3">@lang('student-exam::app.questions')</p>
                <div class="grid grid-cols-5 gap-2" id="question-nav">
                    @foreach($questions as $index => $question)
                        <button type="button" data-question-nav-button="{{ $index }}"
                                class="question-btn h-10 w-10 rounded-2xl border border-slate-200 font-bold text-slate-600 hover:border-blue-300 transition {{ $index === 0 ? 'bg-blue-600 text-white border-blue-600' : '' }}">
                            {{ $index + 1 }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Main content --}}
        <div class="flex-1 p-6 max-w-3xl mx-auto w-full">
            <form id="exam-form" action="{{ route('student.exams.submit', $attempt) }}" method="POST">
                @csrf
                <input type="hidden" name="tab_leave_count" id="tab_leave_count" value="{{ $attempt->tab_leave_count }}">

                @foreach($questions as $index => $question)
                    @include('student-exam::partials.question-item', [
                        'question' => $question, 
                        'index' => $index,
                        'saved' => $savedAnswers[$question->id] ?? null
                    ])
                @endforeach

                <div class="mt-10 flex justify-end">
                    <button type="button"
                            data-student-exam-submit="exam-form"
                            data-confirm-title="@lang('student-exam::app.submit_exam')"
                            data-confirm-message="@lang('student-exam::app.confirm_submit')"
                            data-confirm-text="@lang('student-exam::app.submit_exam')"
                            class="px-8 py-4 bg-green-600 hover:bg-green-700 text-white font-black rounded-3xl transition">
                        @lang('student-exam::app.submit_exam')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
