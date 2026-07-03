@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-classroom::app.edit') . ' — ' . $classroom->name)

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Teacher/TeacherClassroom/src/resources/css/app.css',
    ])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">

    <header class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white/90 px-6 py-4 backdrop-blur">
        <div class="flex items-center gap-3">
            <a href="{{ route('teacher.classrooms.show', $classroom) }}"
               class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline transition hover:bg-green-50 hover:text-green-700">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
            </a>
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">{{ $classroom->name }}</p>
                <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('teacher-classroom::app.edit')</h1>
                <h1>test teacher classroom</h1>
            </div>
        </div>
        {{-- Quick delete from edit page --}}
        <form method="POST" action="{{ route('teacher.classrooms.destroy', $classroom) }}"
              data-mindigo-confirm-title="@lang('teacher-classroom::app.delete_title')"
              data-mindigo-confirm-message="@lang('teacher-classroom::app.delete_confirm')"
              data-mindigo-confirm-text="@lang('teacher-classroom::app.delete')"
              data-mindigo-confirm-cancel="{{ __('teacher-classroom::app.cancel') }}"
              data-mindigo-confirm-type="danger">
            @csrf @method('DELETE')
            <button type="submit"
                    class="inline-flex h-9 items-center gap-2 rounded-full border border-red-200 bg-red-50 px-4 text-xs font-black text-red-600 transition hover:bg-red-100">
                <x-heroicon-o-trash class="h-4 w-4" />@lang('teacher-classroom::app.delete')
            </button>
        </form>
    </header>

    <div class="flex flex-1 items-start justify-center p-6">
        <div class="w-full max-w-2xl">
            <form method="POST" action="{{ route('teacher.classrooms.update', $classroom) }}"
                  class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf @method('PUT')

                @include('teacher-classroom::partials.form')

                <div class="mt-6 flex items-center justify-end gap-2 border-t border-slate-100 pt-5">
                    <a href="{{ route('teacher.classrooms.show', $classroom) }}"
                       class="inline-flex h-10 items-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-600 no-underline transition hover:bg-slate-50">
                        @lang('teacher-classroom::app.back')
                    </a>
                    <button type="submit"
                            class="inline-flex h-10 items-center gap-2 rounded-2xl bg-green-600 px-6 text-sm font-black text-white shadow-sm shadow-green-200 transition hover:bg-green-500">
                        <x-heroicon-o-check class="h-4 w-4" />@lang('teacher-classroom::app.save')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
