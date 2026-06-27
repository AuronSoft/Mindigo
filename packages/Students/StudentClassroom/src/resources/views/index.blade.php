@extends('Mindigo-dashboard::layouts')
@section('title', __('student-classroom::app.title') . ' · Mindigo LMS')
@section('meta_description', __('student-classroom::app.subtitle'))

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
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">@lang('student-classroom::app.area')</p>
                <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('student-classroom::app.title')</h1>
                <p class="text-xs font-semibold text-slate-400">@lang('student-classroom::app.subtitle')</p>
            </div>
            <span class="hidden sm:grid h-11 w-11 place-items-center rounded-2xl bg-green-50 text-green-600">
                <x-heroicon-o-user-group class="h-6 w-6" />
            </span>
        </header>

        <div class="flex flex-1 flex-col gap-5 p-6">
            @if($classrooms->isEmpty())
                <div class="flex flex-1 flex-col items-center justify-center gap-4 rounded-3xl border border-dashed border-slate-200 bg-white py-20">
                    <span class="grid h-20 w-20 place-items-center rounded-full bg-slate-50 text-slate-300">
                        <x-heroicon-o-user-group class="h-10 w-10" />
                    </span>
                    <div class="text-center">
                        <p class="text-lg font-black text-slate-700">@lang('student-classroom::app.empty_title')</p>
                        <p class="mt-1 max-w-xs text-sm font-semibold leading-relaxed text-slate-400">@lang('student-classroom::app.empty_desc')</p>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($classrooms as $classroom)
                        <a href="{{ route('student.classrooms.show', $classroom) }}"
                           class="group flex flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white no-underline shadow-sm transition hover:-translate-y-0.5 hover:border-green-200 hover:shadow-md">
                            {{-- Banner --}}
                            <div class="relative h-24 bg-linear-to-br from-green-500 to-emerald-600 px-5 py-4">
                                <span class="inline-flex items-center rounded-full bg-white/20 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-white backdrop-blur">
                                    {{ $classroom->code }}
                                </span>
                                <h3 class="mt-1.5 line-clamp-2 text-base font-black leading-snug text-white">{{ $classroom->name }}</h3>
                            </div>

                            <div class="flex flex-1 flex-col gap-3 p-5">
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="grid h-8 w-8 place-items-center rounded-full bg-slate-100 text-slate-500">
                                        <x-heroicon-o-academic-cap class="h-4 w-4" />
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">@lang('student-classroom::app.teacher')</p>
                                        <p class="truncate font-black text-slate-700">{{ $classroom->teacher->name ?? __('student-classroom::app.no_teacher') }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($classroom->subjects as $subject)
                                        <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-bold text-green-700">{{ $subject->name }}</span>
                                    @empty
                                        <span class="text-xs font-semibold text-slate-400">@lang('student-classroom::app.no_subject')</span>
                                    @endforelse
                                </div>

                                <div class="mt-auto flex items-center justify-between border-t border-slate-100 pt-3">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500">
                                        <x-heroicon-o-users class="h-4 w-4 text-slate-400" />
                                        {{ $classroom->students_count }} @lang('student-classroom::app.students')
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-xs font-black text-green-600 transition group-hover:gap-2">
                                        @lang('student-classroom::app.view_class')
                                        <x-heroicon-o-arrow-right class="h-4 w-4" />
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
