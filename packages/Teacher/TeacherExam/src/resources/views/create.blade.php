@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-exam::app.create'))

@section('styles')
    {{-- Tái dùng CSS/JS của ExamManagement (form builder phức tạp) --}}
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Mindigo/ExamManagement/src/resources/css/app.css',
        'packages/Mindigo/ExamManagement/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    <header class="sticky top-0 z-10 flex items-center gap-3 border-b border-slate-200 bg-white/90 px-6 py-4 backdrop-blur">
        <a href="{{ route('teacher.exams.index') }}"
           class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline transition hover:bg-green-50 hover:text-green-700">
            <x-heroicon-o-arrow-left class="h-4 w-4" />
        </a>
        <div>
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">@lang('teacher-exam::app.title')</p>
            <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('teacher-exam::app.create')</h1>
        </div>
    </header>

    <div class="p-6">
        <form method="POST" action="{{ route('teacher.exams.store') }}" class="mx-auto max-w-4xl space-y-4">
            @csrf
            {{-- Tái dùng hoàn toàn form builder của admin ExamManagement --}}
            @include('Mindigo-exam-management::partials.form')

            <div class="flex items-center justify-end gap-2 rounded-3xl border border-slate-200 bg-white p-4">
                <a href="{{ route('teacher.exams.index') }}"
                   class="inline-flex h-10 items-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-600 no-underline transition hover:bg-slate-50">
                    @lang('teacher-exam::app.back')
                </a>
                <button type="submit"
                        class="inline-flex h-10 items-center gap-2 rounded-2xl bg-green-600 px-6 text-sm font-black text-white shadow-sm shadow-green-200 transition hover:bg-green-500">
                    <x-heroicon-o-check class="h-4 w-4" />@lang('teacher-exam::app.save')
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
