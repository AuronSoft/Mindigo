@extends('Mindigo-dashboard::layouts')

@section('title', __('student-assignment::app.title') . ' - Auronsoft LMS')

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
    @php
        $selectedStatus = $filters['status'] ?? '';
        $selectedClassroom = ($filters['classroom_id'] ?? '')
            ? $classrooms->firstWhere('id', (int) $filters['classroom_id'])
            : null;
        $hasAssignmentFilters = filled($selectedStatus) || filled($filters['classroom_id'] ?? '');
        $assignmentFilterCount = (int) filled($selectedStatus) + (int) filled($filters['classroom_id'] ?? '');
    @endphp

<div class="flex min-h-screen flex-col bg-slate-50">
    <header class="sticky top-0 z-20 flex items-center justify-between gap-4 border-b border-slate-200/80 bg-white/95 px-6 py-4 backdrop-blur">
        <div class="min-w-0">
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest text-green-700">{{ __('student-assignment::app.eyebrow') }}</p>
                <h1 class="mt-0.5 text-lg font-black text-slate-950">{{ __('student-assignment::app.title') }}</h1>
                <p class="text-xs font-semibold text-slate-400">{{ __('student-assignment::app.subtitle') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" data-mindigo-drawer-open="student-assignment-filter"
                class="inline-flex h-10 items-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 shadow-sm transition hover:border-green-200 hover:bg-green-50 hover:text-green-700">
                <x-heroicon-o-adjustments-horizontal class="h-4 w-4" />
                {{ __('student-assignment::app.filters.apply') }}
                @if($hasAssignmentFilters)
                    <span class="grid h-5 min-w-5 place-items-center rounded-full bg-green-600 px-1.5 text-[11px] text-white">
                        {{ $assignmentFilterCount }}
                    </span>
                @endif
            </button>
            <span class="hidden h-11 w-11 place-items-center rounded-2xl bg-green-50 text-green-600 sm:grid">
                <x-heroicon-o-clipboard-document-list class="h-6 w-6" />
            </span>
        </div>
    </header>

    <main class="flex flex-1 flex-col gap-5 p-6">
        @if($hasAssignmentFilters)
            <section class="flex flex-wrap items-center gap-2 rounded-2xl border border-green-100 bg-green-50 px-4 py-3">
                <span class="text-xs font-black uppercase tracking-wider text-green-700">
                    {{ __('student-assignment::app.filters.apply') }}
                </span>
                @if($selectedStatus)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-bold text-slate-600 shadow-sm">
                        <x-heroicon-o-flag class="h-3.5 w-3.5 text-green-600" />
                        {{ __('student-assignment::app.status.' . $selectedStatus) }}
                    </span>
                @endif
                @if($selectedClassroom)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-bold text-slate-600 shadow-sm">
                        <x-heroicon-o-user-group class="h-3.5 w-3.5 text-green-600" />
                        {{ $selectedClassroom->name }}
                    </span>
                @endif
                <a href="{{ route('student.assignments.index') }}"
                    class="ml-auto inline-flex h-8 items-center gap-1.5 rounded-full border border-green-200 bg-white px-3 text-xs font-black text-green-700 no-underline transition hover:bg-green-100">
                    <x-heroicon-o-x-mark class="h-3.5 w-3.5" />
                    {{ __('student-assignment::app.filters.clear') }}
                </a>
            </section>
        @endif

        @if($assignments->isEmpty())
            <section class="grid min-h-80 place-items-center rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center shadow-sm">
                <div>
                    <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-slate-100 text-slate-400">
                        <x-heroicon-o-clipboard-document-list class="h-7 w-7" />
                    </span>
                    <h2 class="mt-4 text-lg font-black">{{ __('student-assignment::app.empty.title') }}</h2>
                    <p class="mt-1 text-sm font-semibold text-slate-500">{{ __('student-assignment::app.empty.description') }}</p>
                </div>
            </section>
        @else
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/70 text-xs font-black uppercase tracking-wider text-slate-400">
                                <th class="px-6 py-4">{{ __('student-assignment::app.columns.number') }}</th>
                                <th class="px-6 py-4">{{ __('student-assignment::app.columns.title') }}</th>
                                <th class="px-6 py-4">{{ __('student-assignment::app.columns.classroom') }}</th>
                                <th class="px-6 py-4">{{ __('student-assignment::app.columns.due_date') }}</th>
                                <th class="px-6 py-4">{{ __('student-assignment::app.columns.status') }}</th>
                                <th class="px-6 py-4">{{ __('student-assignment::app.columns.score') }}</th>
                                <th class="px-6 py-4 text-center">{{ __('student-assignment::app.columns.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm font-semibold text-slate-600">
                            @foreach($assignments as $i => $assignment)
                                @php
                                    $submission = $assignment->submissions->first();
                                    $status = $submission?->isGraded() ? 'graded' : ($submission ? 'submitted' : 'pending');
                                    $statusBadge = match($status) {
                                        'graded' => 'bg-blue-100 text-blue-700',
                                        'submitted' => 'bg-green-100 text-green-700',
                                        default => 'bg-amber-100 text-amber-700',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-6 py-4 text-slate-400">{{ $assignments->firstItem() + $i }}</td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('student.assignments.show', $assignment) }}" class="font-black text-slate-900 no-underline hover:text-green-700">
                                            {{ $assignment->title }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-bold text-slate-600">
                                            {{ $assignment->classroom->name ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="{{ $assignment->isOverdue() && $status === 'pending' ? 'font-bold text-red-600' : '' }}">
                                            {{ $assignment->due_date->format('d/m/Y H:i') }}
                                        </span>
                                        @if($assignment->isOverdue() && $status === 'pending')
                                            <span class="ml-1.5 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-black uppercase text-red-700">{{ __('student-assignment::app.overdue') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold {{ $statusBadge }}">
                                            {{ __('student-assignment::app.status.' . $status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($submission?->isGraded())
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-700">
                                                {{ rtrim(rtrim(number_format($submission->score, 2), '0'), '.') }}/{{ $assignment->max_score }}
                                            </span>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-1">
                                            <a href="{{ route('student.assignments.show', $assignment) }}"
                                               class="inline-flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                                               title="{{ __('student-assignment::app.view') }}">
                                                <x-heroicon-o-eye class="h-4 w-4" />
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if($assignments->hasPages())
                <div class="mt-4 flex justify-center">{{ $assignments->links() }}</div>
            @endif
        @endif
    </main>

    <div data-mindigo-drawer="student-assignment-filter"
        class="fixed inset-0 z-40 hidden bg-slate-950/45 opacity-0 backdrop-blur-sm transition-opacity duration-200">
    </div>
    <aside data-mindigo-drawer-panel="student-assignment-filter"
        class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col border-l border-slate-200 bg-white shadow-2xl shadow-slate-950/20 transition-transform duration-200"
        style="transform: translateX(100%);">
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4">
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-green-700">
                    {{ __('student-assignment::app.eyebrow') }}
                </p>
                <h2 class="mt-1 text-xl font-black text-slate-950">
                    {{ __('student-assignment::app.filters.title') }}
                </h2>
                <p class="mt-1 text-sm font-semibold leading-relaxed text-slate-500">
                    {{ __('student-assignment::app.subtitle') }}
                </p>
            </div>
            <button type="button" data-mindigo-drawer-close="student-assignment-filter"
                class="grid h-10 w-10 shrink-0 place-items-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-800">
                <x-heroicon-o-x-mark class="h-5 w-5" />
            </button>
        </div>

        <form action="{{ route('student.assignments.index') }}" method="GET" class="flex flex-1 flex-col">
            <div class="flex-1 space-y-5 overflow-y-auto px-5 py-5">
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-500">
                        {{ __('student-assignment::app.filters.status_label') }}
                    </label>
                    <select name="status"
                        class="block h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none transition focus:border-green-300 focus:ring-4 focus:ring-green-50">
                        <option value="">{{ __('student-assignment::app.filters.all_statuses') }}</option>
                        <option value="pending" @selected($selectedStatus === 'pending')>{{ __('student-assignment::app.status.pending') }}</option>
                        <option value="submitted" @selected($selectedStatus === 'submitted')>{{ __('student-assignment::app.status.submitted') }}</option>
                        <option value="graded" @selected($selectedStatus === 'graded')>{{ __('student-assignment::app.status.graded') }}</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-500">
                        {{ __('student-assignment::app.filters.classroom_label') }}
                    </label>
                    <select name="classroom_id"
                        class="block h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none transition focus:border-green-300 focus:ring-4 focus:ring-green-50">
                        <option value="">-- {{ __('student-assignment::app.filters.all_classes') }} --</option>
                        @foreach($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" @selected((string)($filters['classroom_id'] ?? '') === (string)$classroom->id)>
                                {{ $classroom->name }} ({{ $classroom->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 border-t border-slate-100 p-5">
                <a href="{{ route('student.assignments.index') }}"
                    class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-600 no-underline transition hover:bg-slate-50">
                    {{ __('student-assignment::app.filters.clear') }}
                </a>
                <button type="submit"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-green-600 px-4 text-sm font-black text-white shadow-sm shadow-green-200 transition hover:bg-green-500">
                    <x-heroicon-o-funnel class="h-4 w-4" />
                    {{ __('student-assignment::app.filters.apply') }}
                </button>
            </div>
        </form>
    </aside>
</div>
@endsection
