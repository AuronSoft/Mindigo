@extends('Mindigo-dashboard::layouts')

@section('title', 'Sửa khóa học — ' . $course->name)

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    <header class="sticky top-0 z-10 flex items-center gap-4 border-b border-slate-200 bg-white/90 px-6 py-4 backdrop-blur">
        <a href="{{ route('teacher.courses.show', $course) }}"
           class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline transition hover:bg-green-50 hover:text-green-700">
            <x-heroicon-o-arrow-left class="h-4 w-4" />
        </a>
        <div>
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">{{ $course->name }}</p>
            <h1 class="mt-0.5 text-lg font-black text-slate-950">Chỉnh sửa khóa học</h1>
        </div>
    </header>

    <div class="flex flex-1 items-start justify-center p-6">
        <div class="w-full max-w-2xl">
            <form method="POST" action="{{ route('teacher.courses.update', $course) }}"
                  enctype="multipart/form-data"
                  class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-5">
                @csrf @method('PUT')
                @include('teacher-course::partials.form', ['course' => $course])
                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-5">
                    <a href="{{ route('teacher.courses.show', $course) }}"
                       class="inline-flex h-10 items-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-600 no-underline transition hover:bg-slate-50">
                        Hủy
                    </a>
                    <button type="submit"
                            class="inline-flex h-10 items-center gap-2 rounded-2xl bg-green-600 px-6 text-sm font-black text-white shadow-sm shadow-green-200 transition hover:bg-green-500">
                        <x-heroicon-o-check class="h-4 w-4" /> Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
