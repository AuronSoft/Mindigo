@extends('Mindigo-dashboard::layouts')
@section('title', __('teacher-assignment::app.assignment.title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
    <div class="flex min-h-screen flex-col bg-slate-50">

        {{-- Header --}}
        <header
            class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white/90 px-6 py-4 backdrop-blur">
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">
                    @lang('teacher-assignment::app.assignment.title')
                </p>
                <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('teacher-assignment::app.assignment.subtitle')
                </h1>
            </div>
            <a href="{{ route('teacher.assignments.create') }}"
                class="inline-flex h-10 items-center gap-2 rounded-full bg-green-600 px-5 text-sm font-black text-white no-underline shadow-sm shadow-green-200 transition hover:bg-green-500">
                <x-heroicon-o-plus class="h-4 w-4" />
                @lang('teacher-assignment::app.assignment.create')
            </a>
        </header>

        <div class="flex flex-1 flex-col gap-5 p-6">
            <form action="{{ route('teacher.assignments.index') }}" method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-3 md:grid-cols-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm items-end">
                
                {{-- Lọc theo Tiêu đề --}}
                <div class="space-y-1 sm:col-span-1 md:col-span-2">
                    <label class="text-xs font-bold text-slate-500 block">@lang('teacher-assignment::app.assignment.search_title_label')</label>
                    <div class="relative">
                        <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input type="text" name="search_title" value="{{ request('search_title') }}"
                               class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-9 pr-4 text-sm font-semibold text-slate-700 outline-none transition focus:border-green-300 focus:ring-2 focus:ring-green-50"
                               placeholder="{{ __('teacher-assignment::app.assignment.search_title_placeholder') }}">
                    </div>
                </div>

                {{-- Lọc theo Lớp học --}}
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 block">@lang('teacher-assignment::app.assignment.filter_classroom_label')</label>
                    <select name="classroom_id" class="block h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700 outline-none transition focus:border-green-300">
                        <option value="">-- @lang('teacher-assignment::app.assignment.all_classrooms') --</option>
                        @foreach($classrooms as $class)
                            <option value="{{ $class->id }}" {{ request('classroom_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} ({{ $class->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Nút hành động --}}
                <div class="flex gap-2 min-w-[140px]">
                    <button type="submit" id="btn-submit-filter" class="flex-1 inline-flex h-10 items-center justify-center gap-1.5 rounded-xl bg-green-600 px-4 text-xs font-black text-white shadow-sm transition hover:bg-green-500">
                        @lang('teacher-assignment::app.assignment.filter_submit')
                    </button>
                    
                    @if(request('search_title') || request('classroom_id'))
                        <button type="button" onclick="window.location.href='{{ route('teacher.assignments.index') }}'" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-500 hover:bg-slate-100" title="{{ __('teacher-assignment::app.assignment.clear_filter') }}">
                            <x-heroicon-o-x-mark class="h-4 w-4" />
                        </button>
                    @endif
                </div>
            </form>

            @if($assignments->isEmpty())
                <div
                    class="flex flex-1 flex-col items-center justify-center gap-4 rounded-3xl border border-dashed border-slate-200 bg-white py-20">
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
    </div>
@endsection