@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-classroom::app.title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Teacher/TeacherClassroom/src/resources/css/app.css',
        'packages/Teacher/TeacherClassroom/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">

    {{-- Header --}}
    <header class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white/90 px-6 py-4 backdrop-blur">
        <div>
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">@lang('teacher-classroom::app.title')</p>
            <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('teacher-classroom::app.subtitle')</h1>
        </div>
        <a href="{{ route('teacher.classrooms.create') }}"
           class="inline-flex h-10 items-center gap-2 rounded-full bg-green-600 px-5 text-sm font-black text-white no-underline shadow-sm shadow-green-200 transition hover:bg-green-500">
            <x-heroicon-o-plus class="h-4 w-4" />
            @lang('teacher-classroom::app.create')
        </a>
    </header>

    <div class="flex flex-1 flex-col gap-5 p-6">

        {{-- Stats --}}
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['label' => __('teacher-classroom::app.stat_total'),    'value' => $stats['total'],    'icon' => 'heroicon-o-squares-2x2',   'bg' => 'bg-slate-900',   'ring' => ''],
                ['label' => __('teacher-classroom::app.stat_active'),   'value' => $stats['active'],   'icon' => 'heroicon-o-check-circle',   'bg' => 'bg-green-600',   'ring' => 'shadow-green-100'],
                ['label' => __('teacher-classroom::app.stat_students'), 'value' => $stats['students'], 'icon' => 'heroicon-o-academic-cap',   'bg' => 'bg-sky-600',     'ring' => 'shadow-sky-100'],
                ['label' => __('teacher-classroom::app.stat_inactive'), 'value' => $stats['inactive'], 'icon' => 'heroicon-o-pause-circle',   'bg' => 'bg-slate-400',   'ring' => ''],
            ] as $card)
                <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm {{ $card['ring'] }}">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl {{ $card['bg'] }} text-white shadow-sm">
                        <x-dynamic-component :component="$card['icon']" class="h-6 w-6" />
                    </span>
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">{{ $card['label'] }}</p>
                        <strong class="mt-0.5 block text-3xl font-black tracking-tight text-slate-950">{{ number_format($card['value']) }}</strong>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Filter bar --}}
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <div class="flex min-w-56 flex-1 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 shadow-sm focus-within:border-green-300 focus-within:ring-2 focus-within:ring-green-50">
                <x-heroicon-o-magnifying-glass class="h-4 w-4 shrink-0 text-slate-400" />
                <input type="text" name="keyword" value="{{ $filters['keyword'] ?? '' }}"
                       placeholder="@lang('teacher-classroom::app.search')"
                       class="min-w-0 flex-1 bg-transparent text-sm font-bold text-slate-700 outline-none placeholder:text-slate-400">
                @if($filters['keyword'] ?? '')
                    <a href="{{ route('teacher.classrooms.index', array_filter(array_merge($filters, ['keyword' => null]))) }}" class="text-slate-400 hover:text-slate-600">
                        <x-heroicon-m-x-mark class="h-4 w-4" />
                    </a>
                @endif
            </div>
            <select name="status" data-mindigo-auto-submit
                    class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-600 shadow-sm outline-none focus:border-green-300">
                <option value="">@lang('teacher-classroom::app.all_status')</option>
                <option value="active"   @selected(($filters['status'] ?? '') === 'active')>@lang('teacher-classroom::app.active')</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>@lang('teacher-classroom::app.inactive')</option>
            </select>
        </form>

        {{-- Grid --}}
        @if($classrooms->isEmpty())
            <div class="flex flex-1 flex-col items-center justify-center gap-4 rounded-3xl border border-dashed border-slate-200 bg-white py-20">
                <span class="grid h-20 w-20 place-items-center rounded-full bg-slate-50 text-slate-300">
                    <x-heroicon-o-user-group class="h-10 w-10" />
                </span>
                <div class="text-center">
                    <p class="text-lg font-black text-slate-700">@lang('teacher-classroom::app.empty_title')</p>
                    <p class="mt-1 max-w-xs text-sm font-semibold leading-relaxed text-slate-400">@lang('teacher-classroom::app.empty_desc')</p>
                </div>
                <a href="{{ route('teacher.classrooms.create') }}"
                   class="mt-2 inline-flex h-10 items-center gap-2 rounded-full bg-green-600 px-6 text-sm font-black text-white no-underline shadow-sm transition hover:bg-green-500">
                    <x-heroicon-o-plus class="h-4 w-4" />@lang('teacher-classroom::app.create')
                </a>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($classrooms as $classroom)
                <div class="group flex flex-col rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:border-green-200 hover:shadow-md">
                    {{-- Card top --}}
                    <a href="{{ route('teacher.classrooms.show', $classroom) }}" class="flex flex-1 flex-col gap-3 p-5 no-underline">
                        <div class="flex items-start justify-between gap-2">
                            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-linear-to-br from-sky-400 to-sky-600 text-xl font-black text-white shadow-sm">
                                {{ mb_substr($classroom->name, 0, 1) }}
                            </span>
                            <span class="mt-1 rounded-full px-3 py-1 text-[11px] font-black
                                {{ $classroom->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                @lang('teacher-classroom::app.' . $classroom->status)
                            </span>
                        </div>
                        <div>
                            <h3 class="text-base font-black leading-snug text-slate-950">{{ $classroom->name }}</h3>
                            <p class="mt-0.5 text-xs font-bold text-slate-400">{{ $classroom->code }}
                                @if($classroom->school_year) · {{ $classroom->school_year }} @endif
                            </p>
                        </div>
                        @if($classroom->description)
                            <p class="line-clamp-2 text-xs font-semibold leading-5 text-slate-500">{{ $classroom->description }}</p>
                        @endif
                        <div class="mt-auto flex items-center gap-4 border-t border-slate-100 pt-3">
                            <span class="inline-flex items-center gap-1.5 text-xs font-black text-slate-500">
                                <x-heroicon-o-academic-cap class="h-4 w-4 text-slate-400" />
                                @lang('teacher-classroom::app.students_count_badge', ['count' => $classroom->students_count])
                            </span>
                            <span class="inline-flex items-center gap-1.5 text-xs font-black text-slate-500">
                                <x-heroicon-o-book-open class="h-4 w-4 text-slate-400" />
                                @lang('teacher-classroom::app.subjects_count_badge', ['count' => $classroom->subjects->count()])
                            </span>
                        </div>
                    </a>

                    {{-- Card actions --}}
                    <div class="flex items-center gap-1 border-t border-slate-100 px-4 py-2.5">
                        <a href="{{ route('teacher.classrooms.edit', $classroom) }}"
                           class="inline-flex h-8 items-center gap-1.5 rounded-xl px-3 text-xs font-black text-slate-500 no-underline transition hover:bg-slate-50 hover:text-slate-800">
                            <x-heroicon-o-pencil-square class="h-3.5 w-3.5" />@lang('teacher-classroom::app.edit_short')
                        </a>
                        <a href="{{ route('teacher.classrooms.show', $classroom) }}"
                           class="inline-flex h-8 items-center gap-1.5 rounded-xl px-3 text-xs font-black text-slate-500 no-underline transition hover:bg-slate-50 hover:text-slate-800">
                            <x-heroicon-o-users class="h-3.5 w-3.5" />@lang('teacher-classroom::app.students')
                        </a>
                        <form method="POST" action="{{ route('teacher.classrooms.destroy', $classroom) }}" class="ml-auto"
                              data-mindigo-confirm-title="@lang('teacher-classroom::app.delete_title')"
                              data-mindigo-confirm-message="@lang('teacher-classroom::app.delete_confirm')"
                              data-mindigo-confirm-text="@lang('teacher-classroom::app.delete')"
                              data-mindigo-confirm-cancel="{{ __('teacher-classroom::app.cancel') }}"
                              data-mindigo-confirm-type="danger">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 transition hover:bg-red-50 hover:text-red-600">
                                <x-heroicon-o-trash class="h-4 w-4" />
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>

            @if($classrooms->hasPages())
                <div class="flex justify-center">{{ $classrooms->links() }}</div>
            @endif
        @endif
    </div>
</div>
@endsection
