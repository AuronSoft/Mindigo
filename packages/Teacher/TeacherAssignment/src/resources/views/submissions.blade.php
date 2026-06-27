@extends('Mindigo-dashboard::layouts')
@section('title', __('teacher-assignment::app.submission.title') . ' - ' . $assignment->title)

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="min-h-screen bg-slate-50">
    <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/95 backdrop-blur">
        <div class="mx-auto flex w-full max-w-7xl flex-col gap-4 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-start gap-4">
                <a href="{{ route('teacher.assignments.index') }}"
                   class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50">
                    <x-heroicon-o-arrow-left class="h-5 w-5" />
                </a>
                <div class="min-w-0">
                    @if($assignment->classroom)
                        <a href="{{ route('teacher.classrooms.show', $assignment->classroom) }}"
                           class="inline-flex items-center gap-1 text-[11px] font-black uppercase tracking-widest text-green-700 no-underline hover:text-green-800">
                            <x-heroicon-o-academic-cap class="h-3.5 w-3.5" />
                            {{ __('teacher-assignment::app.assignment.classroom_roster_hint', ['name' => $assignment->classroom->name, 'count' => $stats['total_students']]) }}
                        </a>
                    @else
                        <p class="text-[11px] font-black uppercase tracking-widest text-slate-400 truncate">
                            {{ __('teacher-assignment::app.assignment.field_classroom') }}
                        </p>
                    @endif
                    <h1 class="mt-1 truncate text-xl font-black text-slate-950">{{ $assignment->title }}</h1>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                    @lang('teacher-assignment::app.assignment.field_due_date'): {{ $assignment->due_date ? $assignment->due_date->format('d/m/Y H:i') : __('teacher-assignment::app.assignment.no_limit') }}
                </span>
                @if($assignment->isOverdue())
                    <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-black text-red-700">@lang('teacher-assignment::app.assignment.overdue')</span>
                @endif
                @if($assignment->submission_type === 'file')
                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">
                        <x-heroicon-o-paper-clip class="h-3.5 w-3.5" /> @lang('teacher-assignment::app.assignment.sub_type_file')
                    </span>
                @elseif($assignment->submission_type === 'text')
                    <span class="inline-flex items-center gap-1 rounded-full bg-sky-50 px-3 py-1 text-xs font-black text-sky-700">
                        <x-heroicon-o-pencil class="h-3.5 w-3.5" /> @lang('teacher-assignment::app.assignment.sub_type_text')
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 rounded-full bg-purple-50 px-3 py-1 text-xs font-black text-purple-700">
                        <x-heroicon-o-document-duplicate class="h-3.5 w-3.5" /> @lang('teacher-assignment::app.assignment.sub_type_both')
                    </span>
                @endif
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-7xl space-y-5 px-6 py-5">
        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
            @foreach([
                ['key' => 'all', 'label' => __('teacher-assignment::app.submission.total_students'), 'val' => $stats['total_students'], 'icon' => 'heroicon-o-users', 'bg' => 'bg-slate-900', 'text' => 'text-white'],
                ['key' => 'submitted', 'label' => __('teacher-assignment::app.submission.submitted'), 'val' => $stats['submitted'], 'icon' => 'heroicon-o-check-circle', 'bg' => 'bg-emerald-600', 'text' => 'text-white'],
                ['key' => 'not_submitted', 'label' => __('teacher-assignment::app.submission.not_submitted'), 'val' => $stats['not_submitted'], 'icon' => 'heroicon-o-x-circle', 'bg' => 'bg-red-500', 'text' => 'text-white'],
                ['key' => 'graded', 'label' => __('teacher-assignment::app.submission.graded'), 'val' => $stats['graded'], 'icon' => 'heroicon-o-academic-cap', 'bg' => 'bg-sky-600', 'text' => 'text-white'],
                ['key' => 'late', 'label' => __('teacher-assignment::app.submission.late'), 'val' => $stats['late'], 'icon' => 'heroicon-o-exclamation-circle', 'bg' => 'bg-amber-500', 'text' => 'text-white'],
                ['key' => 'graded', 'label' => __('teacher-assignment::app.submission.avg_score'), 'val' => $stats['avg_score'] !== null ? $stats['avg_score'] : '-', 'icon' => 'heroicon-o-chart-bar', 'bg' => 'bg-fuchsia-600', 'text' => 'text-white'],
            ] as $s)
                <button type="button"
                        data-submission-filter="{{ $s['key'] }}"
                        class="submission-filter-btn group flex min-h-[88px] items-center gap-3 rounded-2xl border bg-white p-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-green-200 hover:bg-green-50 hover:shadow-md {{ $s['key'] === 'all' ? 'border-green-200 ring-2 ring-green-50' : 'border-slate-200' }}">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl {{ $s['bg'] }} {{ $s['text'] }}">
                        <x-dynamic-component :component="$s['icon']" class="h-5 w-5" />
                    </span>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $s['label'] }}</p>
                        <strong class="mt-0.5 block text-xl font-black text-slate-950">{{ $s['val'] }}</strong>
                    </div>
                </button>
            @endforeach
        </section>

        <section class="flex justify-end">
            <label class="relative block w-full sm:w-96">
                <span class="sr-only">Tìm học sinh</span>
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input id="submissionSearch" type="search"
                       class="h-11 w-full rounded-full border border-slate-200 bg-white pl-10 pr-4 text-sm font-semibold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-green-300 focus:ring-2 focus:ring-green-50"
                       placeholder="Tìm học sinh hoặc email">
            </label>
        </section>

        <section class="space-y-3">
            @foreach($studentList as $row)
                @php
                    $student = $row->student;
                    $submission = $row->submission;
                    $hasSubmit = !is_null($submission);
                    $stateTokens = ['not_submitted'];
                    $primaryLabel = __('teacher-assignment::app.submission.no_submission_badge');
                    $primaryClass = 'bg-red-100 text-red-700';
                    $primaryIcon = 'heroicon-o-x-circle';
                    $secondaryLabel = null;
                    $secondaryClass = null;

                    if ($hasSubmit) {
                        $stateTokens = ['submitted'];
                        $primaryLabel = __('teacher-assignment::app.submission.submitted_badge');
                        $primaryClass = 'bg-emerald-100 text-emerald-700';
                        $primaryIcon = 'heroicon-o-check-circle';

                        if ($submission->is_late) {
                            $stateTokens[] = 'late';
                            $primaryLabel = __('teacher-assignment::app.submission.late_badge');
                            $primaryClass = 'bg-amber-100 text-amber-700';
                            $primaryIcon = 'heroicon-o-exclamation-circle';
                        }

                        if ($submission->isGraded()) {
                            $stateTokens[] = 'graded';
                            $secondaryLabel = $submission->status === 'returned'
                                ? __('teacher-assignment::app.submission.returned')
                                : __('teacher-assignment::app.submission.graded');
                            $secondaryClass = $submission->status === 'returned'
                                ? 'bg-emerald-100 text-emerald-700'
                                : 'bg-sky-100 text-sky-700';

                            if (! $submission->is_late) {
                                $primaryLabel = $secondaryLabel;
                                $primaryClass = $secondaryClass;
                                $primaryIcon = 'heroicon-o-academic-cap';
                                $secondaryLabel = null;
                                $secondaryClass = null;
                            }
                        }
                    }

                    $searchText = strtolower(trim(implode(' ', array_filter([
                        $student->name ?? '',
                        $student->email ?? '',
                        $primaryLabel,
                        $secondaryLabel ?? '',
                        $submission && $submission->submitted_at ? $submission->submitted_at->format('d/m/Y H:i') : '',
                        $submission && $submission->file_original_name ? $submission->file_original_name : '',
                    ]))));
                @endphp

                <article class="submission-row overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                         data-submission-row
                         data-row-key="{{ $hasSubmit ? 'submission-' . $submission->id : 'student-' . $student->id }}"
                         data-state="{{ implode(' ', $stateTokens) }}"
                         data-search="{{ $searchText }}">
                    <div class="border-l-4 {{ $hasSubmit ? ($submission->isGraded() ? 'border-l-sky-500' : ($submission->is_late ? 'border-l-amber-500' : 'border-l-emerald-500')) : 'border-l-red-500' }}">
                        <div class="{{ $hasSubmit ? 'grid gap-4 p-4 lg:grid-cols-[minmax(0,1fr)_360px]' : 'p-4' }}">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <strong class="text-sm font-black text-slate-950">{{ $student->name }}</strong>
                                    <span class="text-[11px] font-semibold text-slate-400">{{ $student->email }}</span>
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-black {{ $primaryClass }}">
                                        <x-dynamic-component :component="$primaryIcon" class="h-4 w-4" />
                                        {{ $primaryLabel }}
                                    </span>
                                    @if($secondaryLabel)
                                        <span class="inline-flex items-center rounded-full {{ $secondaryClass }} px-2.5 py-0.5 text-xs font-black">
                                            {{ $secondaryLabel }}
                                        </span>
                                    @endif
                                </div>

                                @if($hasSubmit)
                                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1">
                                            @lang('teacher-assignment::app.submission.submitted_at'): {{ $submission->submitted_at ? $submission->submitted_at->format('d/m/Y H:i') : '-' }}
                                        </span>
                                        @if($submission->score !== null)
                                            <span class="rounded-full bg-sky-50 px-2.5 py-1 font-black text-sky-700">
                                                @lang('teacher-assignment::app.submission.score'): {{ $submission->score }}
                                            </span>
                                        @endif
                                        @if($submission->feedback)
                                            <span class="rounded-full bg-amber-50 px-2.5 py-1 font-black text-amber-700">
                                                @lang('teacher-assignment::app.submission.current_feedback')
                                            </span>
                                        @endif
                                    </div>

                                    <div class="mt-3 flex flex-wrap items-center gap-2">
                                        @if($submission->hasFile())
                                            <a href="{{ route('teacher.assignments.submissions.file', [$assignment, $submission]) }}"
                                               target="_blank"
                                               class="inline-flex h-9 items-center gap-1.5 rounded-full border border-slate-200 bg-white px-4 text-xs font-black text-slate-600 no-underline shadow-xs transition hover:bg-slate-50">
                                                <x-heroicon-o-arrow-down-tray class="h-4 w-4 text-slate-400" />
                                                {{ __('teacher-assignment::app.submission.download_file') }}
                                            </a>
                                        @endif

                                        @if($submission->hasText())
                                            <button type="button"
                                                    class="toggle-text-btn inline-flex h-9 items-center gap-1.5 rounded-full border border-slate-200 bg-white px-4 text-xs font-black text-slate-600 no-underline shadow-xs transition hover:bg-slate-50"
                                                    data-target="text-{{ $submission->id }}">
                                                <x-heroicon-o-bars-3-bottom-left class="h-4 w-4 text-slate-400" />
                                                {{ __('teacher-assignment::app.submission.view_text') }}
                                            </button>
                                        @endif
                                    </div>

                                    @if($submission->hasText())
                                        <div id="text-{{ $submission->id }}"
                                             class="hidden mt-3 max-h-64 overflow-auto rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm font-semibold leading-relaxed text-slate-700 whitespace-pre-wrap">
                                            {{ $submission->text_content }}
                                        </div>
                                    @endif

                                    @if($submission->feedback)
                                        <div class="mt-3 rounded-2xl border border-amber-100 bg-amber-50/60 p-3 text-xs font-bold text-amber-800">
                                            <span class="mb-1 block text-[10px] font-black uppercase tracking-wider text-amber-500">@lang('teacher-assignment::app.submission.current_feedback')</span>
                                            {{ $submission->feedback }}
                                        </div>
                                    @endif
                                @else
                                    <p class="mt-1 text-xs font-bold italic text-slate-400">@lang('teacher-assignment::app.submission.no_submission')</p>
                                @endif
                            </div>

                            @if($hasSubmit)
                                <form action="{{ route('teacher.assignments.submissions.grade', [$assignment, $submission]) }}"
                                      method="POST"
                                      data-grade-form
                                      data-row-key="submission-{{ $submission->id }}"
                                      class="flex h-full flex-col gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                                    @csrf
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Chấm bài</p>
                                        @if($submission->score !== null)
                                            <span class="rounded-full bg-white px-2.5 py-1 text-xs font-black text-slate-700 shadow-xs">
                                                {{ $submission->score }}/{{ $assignment->max_score }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div class="space-y-1">
                                            <label class="text-xs font-bold text-slate-600 block">{{ __('teacher-assignment::app.submission.grade_label', ['max' => $assignment->max_score]) }}</label>
                                            <input type="number" name="score"
                                                   class="block h-10 w-full rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-sm font-bold text-slate-700 outline-none focus:border-green-300"
                                                   step="0.5" min="0" max="{{ $assignment->max_score }}"
                                                   value="{{ $submission->score }}" placeholder="-">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-xs font-bold text-slate-600 block">@lang('teacher-assignment::app.submission.status')</label>
                                            <select name="status" class="block h-10 w-full rounded-xl border border-slate-200 bg-white px-2 text-sm font-bold text-slate-700 outline-none focus:border-green-300">
                                                <option value="graded" {{ $submission->status === 'graded' ? 'selected' : '' }}>@lang('teacher-assignment::app.submission.graded')</option>
                                                <option value="returned" {{ $submission->status === 'returned' ? 'selected' : '' }}>@lang('teacher-assignment::app.submission.returned')</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-slate-600 block">@lang('teacher-assignment::app.submission.feedback')</label>
                                        <textarea name="feedback" rows="3"
                                                  class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 outline-none focus:border-green-300"
                                                  placeholder="{{ __('teacher-assignment::app.submission.feedback_ph') }}">{{ $submission->feedback }}</textarea>
                                    </div>

                                    <div class="flex justify-end pt-1">
                                        <button type="submit"
                                                class="inline-flex h-9 items-center gap-1.5 rounded-full bg-green-600 px-4 text-xs font-black text-white shadow-sm transition hover:bg-green-500">
                                            <x-heroicon-o-check class="h-4 w-4" />
                                            {{ __('teacher-assignment::app.submission.save_grade') }}
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach

            <div id="submissionEmptyState" class="hidden rounded-3xl border border-dashed border-slate-200 bg-white px-6 py-14 text-center shadow-sm">
                <x-heroicon-o-magnifying-glass class="mx-auto h-10 w-10 text-slate-300" />
                <p class="mt-3 text-base font-black text-slate-700">Không tìm thấy học sinh phù hợp.</p>
                <p class="mt-1 text-sm font-semibold text-slate-400">Hãy đổi bộ lọc hoặc từ khóa tìm kiếm.</p>
            </div>
        </section>
    </main>
</div>

<script>
(() => {
    const buttons = Array.from(document.querySelectorAll('[data-submission-filter]'));
    const rows = Array.from(document.querySelectorAll('[data-submission-row]'));
    const searchInput = document.getElementById('submissionSearch');
    const emptyState = document.getElementById('submissionEmptyState');
    let activeFilter = 'all';

    const applyFilters = () => {
        const query = (searchInput?.value || '').trim().toLowerCase();
        let visibleCount = 0;

        rows.forEach(row => {
            const states = (row.dataset.state || '').split(/\s+/).filter(Boolean);
            const searchText = row.dataset.search || '';
            const matchState = activeFilter === 'all' || states.includes(activeFilter);
            const matchSearch = !query || searchText.includes(query);
            const visible = matchState && matchSearch;
            row.classList.toggle('hidden', !visible);
            if (visible) visibleCount += 1;
        });

        if (emptyState) {
            emptyState.classList.toggle('hidden', visibleCount !== 0);
        }
    };

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            activeFilter = button.dataset.submissionFilter || 'all';
            buttons.forEach(btn => {
                const isActive = btn === button;
                btn.classList.toggle('border-green-200', isActive);
                btn.classList.toggle('bg-green-50', isActive);
                btn.classList.toggle('text-green-700', isActive);
                btn.classList.toggle('ring-2', isActive);
                btn.classList.toggle('ring-green-50', isActive);
                btn.classList.toggle('border-slate-200', !isActive);
                btn.classList.toggle('bg-white', !isActive);
                btn.classList.toggle('text-slate-600', !isActive);
            });
            applyFilters();
        });
    });

    const scrollMemoryKey = 'teacher-assignment:last-grade-position';
    const savedPosition = sessionStorage.getItem(scrollMemoryKey);
    if (savedPosition) {
        sessionStorage.removeItem(scrollMemoryKey);
        try {
            const payload = JSON.parse(savedPosition);
            requestAnimationFrame(() => {
                if (Number.isFinite(payload.scrollY)) {
                    window.scrollTo(0, payload.scrollY);
                }

                if (payload.rowKey) {
                    const row = document.querySelector(`[data-row-key="${payload.rowKey}"]`);
                    row?.classList.add('ring-2', 'ring-green-200');
                    setTimeout(() => row?.classList.remove('ring-2', 'ring-green-200'), 1800);
                }
            });
        } catch (error) {
            sessionStorage.removeItem(scrollMemoryKey);
        }
    }

    document.querySelectorAll('[data-grade-form]').forEach(form => {
        form.addEventListener('submit', () => {
            sessionStorage.setItem(scrollMemoryKey, JSON.stringify({
                scrollY: window.scrollY,
                rowKey: form.dataset.rowKey || null,
            }));
        });
    });

    searchInput?.addEventListener('input', applyFilters);

    document.querySelectorAll('.toggle-text-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetEl = document.getElementById(targetId);
            if (targetEl) {
                targetEl.classList.toggle('hidden');
            }
        });
    });

    applyFilters();
})();
</script>
@endsection
