@extends('Mindigo-dashboard::layouts')
@section('title', __('teacher-assignment::app.assignment.title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
    @php
        $selectedClassroom = request('classroom_id')
            ? $classrooms->firstWhere('id', (int) request('classroom_id'))
            : null;
        $hasAssignmentFilters = filled(request('search_title')) || filled(request('classroom_id'));
        $assignmentFilterCount = (int) filled(request('search_title')) + (int) filled(request('classroom_id'));
    @endphp

    <div class="flex min-h-screen flex-col bg-slate-50">

        {{-- Header --}}
        <header
            class="sticky top-0 z-10 border-b border-slate-200 bg-white/95 px-6 py-4 backdrop-blur">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-widest text-green-700">
                        @lang('teacher-assignment::app.assignment.teaching_assignment')
                    </p>
                    <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('teacher-assignment::app.assignment.title')
                    </h1>
                    <p class="mt-1 text-xs font-semibold text-slate-400">@lang('teacher-assignment::app.assignment.subtitle')</p>
                </div>
                <div class="flex flex-wrap gap-2">
                <button type="button" data-mindigo-drawer-open="assignment-filter"
                    class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 no-underline hover:bg-slate-50 transition">
                    <x-heroicon-o-adjustments-horizontal class="h-4 w-4" />
                    @lang('teacher-assignment::app.assignment.filter_button')
                    @if($hasAssignmentFilters)
                        <span class="grid h-5 min-w-5 place-items-center rounded-full bg-green-600 px-1.5 text-[11px] text-white">
                            {{ $assignmentFilterCount }}
                        </span>
                    @endif
                </button>
                <a href="{{ route('teacher.assignments.create') }}"
                    class="inline-flex h-10 items-center gap-2 rounded-xl bg-green-600 px-4 text-sm font-black text-white no-underline shadow-sm transition hover:bg-green-500">
                    <x-heroicon-o-plus class="h-4 w-4" />
                    @lang('teacher-assignment::app.assignment.create')
                </a>
                </div>
            </div>
        </header>

        <div class="flex flex-1 flex-col gap-5 p-6">
            @if($hasAssignmentFilters)
                <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-green-100 bg-green-50 px-4 py-3">
                    <span class="text-xs font-black uppercase tracking-wider text-green-700">
                        @lang('teacher-assignment::app.assignment.filter_active')
                    </span>
                    @if(request('search_title'))
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-bold text-slate-600 shadow-sm">
                            <x-heroicon-o-magnifying-glass class="h-3.5 w-3.5 text-green-600" />
                            {{ request('search_title') }}
                        </span>
                    @endif
                    @if($selectedClassroom)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-bold text-slate-600 shadow-sm">
                            <x-heroicon-o-user-group class="h-3.5 w-3.5 text-green-600" />
                            {{ $selectedClassroom->name }}
                        </span>
                    @endif
                    <a href="{{ route('teacher.assignments.index') }}"
                        class="ml-auto inline-flex h-8 items-center gap-1.5 rounded-full border border-green-200 bg-white px-3 text-xs font-black text-green-700 no-underline transition hover:bg-green-100">
                        <x-heroicon-o-x-mark class="h-3.5 w-3.5" />
                        @lang('teacher-assignment::app.assignment.clear_filter')
                    </a>
                </div>
            @endif

            @if($assignments->isEmpty())
                <div
                    class="flex min-h-107.5 flex-col items-center justify-center gap-4 rounded-3xl border border-dashed border-slate-200 bg-white px-6 py-16">
                    <span class="grid h-20 w-20 place-items-center rounded-full bg-slate-50 text-slate-300">
                        <x-heroicon-o-clipboard-document-list class="h-10 w-10" />
                    </span>
                    <div class="text-center">
                        <p class="text-lg font-black text-slate-700">@lang('teacher-assignment::app.assignment.empty_title')</p>
                        <p class="mt-1 max-w-xs text-sm font-semibold leading-relaxed text-slate-400">
                            @lang('teacher-assignment::app.assignment.empty_desc')
                        </p>
                    </div>
                    <a href="{{ route('teacher.assignments.create') }}"
                        class="mt-2 inline-flex h-10 items-center gap-2 rounded-full bg-green-600 px-6 text-sm font-black text-white no-underline shadow-sm transition hover:bg-green-500">
                        <x-heroicon-o-plus class="h-4 w-4" /> @lang('teacher-assignment::app.assignment.create')
                    </a>
                </div>
            @else
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="border-b border-slate-200 bg-slate-50/70 text-xs font-black uppercase tracking-wider text-slate-400">
                                    <th class="px-6 py-4">@lang('teacher-assignment::app.assignment.col_number')</th>
                                    <th class="px-6 py-4">@lang('teacher-assignment::app.assignment.col_title')</th>
                                    <th class="px-6 py-4">@lang('teacher-assignment::app.assignment.col_classroom')</th>
                                    <th class="px-6 py-4">@lang('teacher-assignment::app.assignment.col_sub_type')</th>
                                    <th class="px-6 py-4">@lang('teacher-assignment::app.assignment.col_due_date')</th>
                                    <th class="px-6 py-4">@lang('teacher-assignment::app.assignment.col_submitted')</th>
                                    <th class="px-6 py-4">@lang('teacher-assignment::app.assignment.col_status')</th>
                                    <th class="px-6 py-4 text-center">@lang('teacher-assignment::app.assignment.col_actions')
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm font-semibold text-slate-600">
                                @foreach($assignments as $i => $a)
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="px-6 py-4 text-slate-400">{{ $assignments->firstItem() + $i }}</td>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('teacher.assignments.submissions.index', $a) }}"
                                                class="font-black text-slate-900 no-underline hover:text-green-700">
                                                {{ $a->title }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-bold text-slate-600">
                                                {{ $a->classroom->name ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($a->submission_type === 'file')
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-700">
                                                    <x-heroicon-o-paper-clip class="h-3 w-3" />
                                                    @lang('teacher-assignment::app.assignment.sub_type_file')
                                                </span>
                                            @elseif($a->submission_type === 'text')
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full bg-sky-50 px-2.5 py-0.5 text-xs font-bold text-sky-700">
                                                    <x-heroicon-o-pencil class="h-3 w-3" />
                                                    @lang('teacher-assignment::app.assignment.sub_type_text')
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full bg-purple-50 px-2.5 py-0.5 text-xs font-bold text-purple-700">
                                                    <x-heroicon-o-document-duplicate class="h-3 w-3" />
                                                    @lang('teacher-assignment::app.assignment.sub_type_both')
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="{{ $a->isOverdue() ? 'text-red-600 font-bold' : '' }}">
                                                {{ $a->due_date ? $a->due_date->format('d/m/Y H:i') : __('teacher-assignment::app.assignment.no_limit') }}
                                            </span>
                                            @if($a->isOverdue())
                                                <span
                                                    class="ml-1.5 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-black text-red-700 uppercase">@lang('teacher-assignment::app.assignment.overdue')</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-700">
                                                {{ __('teacher-assignment::app.assignment.submitted_count', ['count' => $a->submittedCount()]) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($a->status === 'published')
                                                <span
                                                    class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-700">@lang('teacher-assignment::app.assignment.status_published_badge')</span>
                                            @else
                                                <span
                                                    class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-500">@lang('teacher-assignment::app.assignment.status_draft_badge')</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-center gap-1">
                                                <a href="{{ route('teacher.assignments.submissions.index', $a) }}"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                                                    title="{{ __('teacher-assignment::app.submission.title') }}">
                                                    <x-heroicon-o-eye class="h-4 w-4" />
                                                </a>
                                                <a href="{{ route('teacher.assignments.edit', $a) }}"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                                                    title="{{ __('teacher-assignment::app.edit') }}">
                                                    <x-heroicon-o-pencil-square class="h-4 w-4" />
                                                </a>
                                                <form action="{{ route('teacher.assignments.destroy', $a) }}" method="POST"
                                                    class="inline"
                                                    data-mindigo-confirm-title="{{ __('teacher-assignment::app.assignment.delete_confirm_title') }}"
                                                    data-mindigo-confirm-message="{{ __('teacher-assignment::app.assignment.delete_confirm_message') }}"
                                                    data-mindigo-confirm-text="{{ __('teacher-assignment::app.delete') }}"
                                                    data-mindigo-confirm-cancel="{{ __('teacher-assignment::app.cancel') }}"
                                                    data-mindigo-confirm-type="danger">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 hover:bg-red-50 hover:text-red-600"
                                                        title="{{ __('teacher-assignment::app.delete') }}">
                                                        <x-heroicon-o-trash class="h-4 w-4" />
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($assignments->hasPages())
                    <div class="flex justify-center mt-4">{{ $assignments->links() }}</div>
                @endif
            @endif
        </div>

        <div data-mindigo-drawer="assignment-filter"
            class="fixed inset-0 z-40 hidden bg-slate-950/45 opacity-0 backdrop-blur-sm transition-opacity duration-200">
        </div>
        <aside data-mindigo-drawer-panel="assignment-filter"
            class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col border-l border-slate-200 bg-white shadow-2xl shadow-slate-950/20 transition-transform duration-200"
            style="transform: translateX(100%);">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-wider text-green-700">
                        @lang('teacher-assignment::app.assignment.filter_button')
                    </p>
                    <h2 class="mt-1 text-xl font-black text-slate-950">
                        @lang('teacher-assignment::app.assignment.filter_title')
                    </h2>
                    <p class="mt-1 text-sm font-semibold leading-relaxed text-slate-500">
                        @lang('teacher-assignment::app.assignment.filter_desc')
                    </p>
                </div>
                <button type="button" data-mindigo-drawer-close="assignment-filter"
                    class="grid h-10 w-10 shrink-0 place-items-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-800">
                    <x-heroicon-o-x-mark class="h-5 w-5" />
                </button>
            </div>

            <form action="{{ route('teacher.assignments.index') }}" method="GET" class="flex flex-1 flex-col">
                <div class="flex-1 space-y-5 overflow-y-auto px-5 py-5">
                    <div class="space-y-2">
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-500">
                            @lang('teacher-assignment::app.assignment.search_title_label')
                        </label>
                        <div class="relative">
                            <x-heroicon-o-magnifying-glass
                                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                            <input type="text" name="search_title" value="{{ request('search_title') }}"
                                class="h-11 w-full rounded-2xl border border-slate-200 bg-white pl-10 pr-4 text-sm font-semibold text-slate-700 outline-none transition focus:border-green-300 focus:ring-4 focus:ring-green-50"
                                placeholder="{{ __('teacher-assignment::app.assignment.search_title_placeholder') }}">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-500">
                            @lang('teacher-assignment::app.assignment.filter_classroom_label')
                        </label>
                        <select name="classroom_id"
                            class="block h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none transition focus:border-green-300 focus:ring-4 focus:ring-green-50">
                            <option value="">-- @lang('teacher-assignment::app.assignment.all_classrooms') --</option>
                            @foreach($classrooms as $class)
                                <option value="{{ $class->id }}" {{ request('classroom_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }} ({{ $class->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 border-t border-slate-100 p-5">
                    <a href="{{ route('teacher.assignments.index') }}"
                        class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-600 no-underline transition hover:bg-slate-50">
                        @lang('teacher-assignment::app.assignment.clear_filter')
                    </a>
                    <button type="submit"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-green-600 px-4 text-sm font-black text-white shadow-sm shadow-green-200 transition hover:bg-green-500">
                        <x-heroicon-o-funnel class="h-4 w-4" />
                        @lang('teacher-assignment::app.assignment.filter_submit')
                    </button>
                </div>
            </form>
        </aside>
    </div>
@endsection
