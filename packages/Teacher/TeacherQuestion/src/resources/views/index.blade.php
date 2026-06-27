@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-question::app.title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="flex min-h-screen bg-slate-50">

    {{-- Narrow filter sidebar --}}
    <aside class="hidden w-56 shrink-0 border-r border-slate-200 bg-white xl:block">
        <div class="sticky top-0 max-h-screen overflow-y-auto p-4 space-y-4">
            <div>
                <p class="mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">@lang('teacher-question::app.col_status')</p>
                @foreach(['', 'draft', 'reviewing', 'approved'] as $st)
                    <a href="{{ route('teacher.questions.index', array_merge($filters, ['status' => $st])) }}"
                       class="flex min-h-8 items-center gap-2 rounded-xl px-2.5 text-sm font-bold no-underline transition
                           {{ ($filters['status'] ?? '') === $st ? 'bg-green-50 font-black text-green-700' : 'text-slate-600 hover:bg-slate-50' }}">
                        @if($st)
                            <span class="h-2 w-2 shrink-0 rounded-full {{ match($st) { 'approved'=>'bg-green-500', 'reviewing'=>'bg-amber-400', default=>'bg-slate-300' } }}"></span>
                            @lang('teacher-question::app.' . $st)
                        @else
                            <span class="h-2 w-2 shrink-0 rounded-full bg-slate-200"></span>
                            @lang('teacher-question::app.all_statuses')
                        @endif
                    </a>
                @endforeach
            </div>

            <div>
                <p class="mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">@lang('teacher-question::app.col_difficulty')</p>
                @foreach(['', 'easy', 'medium', 'hard'] as $diff)
                    <a href="{{ route('teacher.questions.index', array_merge($filters, ['difficulty' => $diff])) }}"
                       class="flex min-h-8 items-center gap-2 rounded-xl px-2.5 text-sm font-bold no-underline transition
                           {{ ($filters['difficulty'] ?? '') === $diff ? 'bg-green-50 font-black text-green-700' : 'text-slate-600 hover:bg-slate-50' }}">
                        @if($diff)
                            @lang('teacher-question::app.' . $diff)
                        @else
                            @lang('teacher-question::app.all_difficulties')
                        @endif
                    </a>
                @endforeach
            </div>

            <div>
                <p class="mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">@lang('teacher-question::app.col_folder')</p>
                <a href="{{ route('teacher.questions.index', array_merge($filters, ['folder_id' => ''])) }}"
                   class="flex min-h-8 items-center rounded-xl px-2.5 text-sm font-bold no-underline transition
                       {{ !($filters['folder_id'] ?? '') ? 'bg-green-50 font-black text-green-700' : 'text-slate-600 hover:bg-slate-50' }}">
                    @lang('teacher-question::app.all_folders')
                </a>
                @foreach($folders as $folder)
                    <a href="{{ route('teacher.questions.index', array_merge($filters, ['folder_id' => $folder->id])) }}"
                       class="flex min-h-8 items-center gap-2 rounded-xl px-2.5 text-sm font-bold no-underline transition
                           {{ ($filters['folder_id'] ?? '') == $folder->id ? 'bg-green-50 font-black text-green-700' : 'text-slate-600 hover:bg-slate-50' }}">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background:{{ $folder->color ?: '#94a3b8' }}"></span>
                        <span class="min-w-0 truncate">{{ $folder->name }}</span>
                        <span class="ml-auto shrink-0 text-[10px] text-slate-400">{{ $folder->questions_count }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex flex-1 flex-col">

        {{-- Header --}}
        <header class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white/95 px-5 py-3 backdrop-blur">
            <div class="flex items-center gap-3">
                {{-- Mobile filter toggle could go here --}}
                <div>
                    <h1 class="text-base font-black text-slate-950">@lang('teacher-question::app.title')</h1>
                    <p class="text-xs font-bold text-slate-400">{{ number_format($stats['total']) }} @lang('teacher-question::app.stat_total') · {{ $stats['approved'] }} @lang('teacher-question::app.stat_approved')</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <form method="GET" class="flex items-center">
                    <div class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 focus-within:border-green-300 focus-within:bg-white">
                        <x-heroicon-o-magnifying-glass class="h-3.5 w-3.5 text-slate-400" />
                        <input type="text" name="keyword" value="{{ $filters['keyword'] ?? '' }}"
                               placeholder="@lang('teacher-question::app.search_ph')"
                               class="w-44 bg-transparent text-sm font-bold text-slate-700 outline-none placeholder:text-slate-400">
                    </div>
                </form>
                {{-- chọn tất cả --}}
                <button type="button" id="bulk-mode-btn"
                    class="inline-flex h-9 items-center gap-1.5 rounded-full border border-slate-200 bg-white px-4 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                <x-heroicon-o-check-circle class="h-4 w-4" />Chọn nhiều
            </button>

                <a href="{{ route('teacher.questions.import') }}"
                   class="inline-flex h-9 items-center gap-1.5 rounded-full border border-slate-200 bg-white px-4 text-sm font-black text-slate-600 no-underline transition hover:bg-slate-50">
                    <x-heroicon-o-arrow-up-tray class="h-4 w-4" />@lang('teacher-question::app.import')
                </a>
                <a href="{{ route('teacher.questions.create') }}"
                   class="inline-flex h-9 items-center gap-1.5 rounded-full bg-green-600 px-4 text-sm font-black text-white no-underline shadow-sm transition hover:bg-green-500">
                    <x-heroicon-o-plus class="h-4 w-4" />@lang('teacher-question::app.create')
                </a>
            </div>
        </header>

        {{-- Stats chips --}}
        <div class="flex items-center gap-2 border-b border-slate-100 bg-white px-5 py-2">
            @foreach([
                ['key'=>'stat_draft',     'val'=>$stats['draft'],     'dot'=>'bg-slate-300'],
                ['key'=>'stat_reviewing', 'val'=>$stats['reviewing'], 'dot'=>'bg-amber-400'],
                ['key'=>'stat_approved',  'val'=>$stats['approved'],  'dot'=>'bg-green-500'],
            ] as $chip)
                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-black text-slate-700">
                    <span class="h-1.5 w-1.5 rounded-full {{ $chip['dot'] }}"></span>
                    @lang('teacher-question::app.' . $chip['key'])
                    <span class="ml-0.5 rounded-full bg-slate-100 px-1.5 py-0.5 text-[10px] font-black text-slate-500">{{ $chip['val'] }}</span>
                </span>
            @endforeach
        </div>

        {{-- Chọn tất cả --}}
        <div id="bulk-bar" class="hidden items-center gap-3 border-b border-amber-100 bg-amber-50 px-5 py-2.5">
    <label class="flex cursor-pointer items-center gap-2 text-xs font-black text-amber-700">
        <input type="checkbox" id="bulk-select-all" class="h-4 w-4 accent-green-600 cursor-pointer">
        Chọn tất cả
    </label>
    <span id="bulk-count" class="text-xs font-black text-amber-700">0 câu đã chọn</span>
        <div class="ml-auto flex items-center gap-2">
            {{-- Đổi độ khó --}}
            <select id="bulk-difficulty-select"
                    class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-black text-slate-700 outline-none focus:border-green-400">
                <option value="">Đổi độ khó...</option>
                <option value="easy">Dễ</option>
                <option value="medium">Trung bình</option>
                <option value="hard">Khó</option>
            </select>
            <button type="button" id="bulk-difficulty-btn"
                    class="inline-flex h-8 items-center gap-1.5 rounded-xl bg-green-600 px-3 text-xs font-black text-white transition hover:bg-green-500">
                <x-heroicon-o-check class="h-3.5 w-3.5" />Áp dụng
            </button>
            <div class="h-4 w-px bg-slate-300"></div>
            <select id="bulk-status-select"
                    class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-black text-slate-700 outline-none focus:border-green-400">
                <option value="">Đổi trạng thái...</option>
                <option value="draft">Bản nháp</option>
                <option value="reviewing">Chờ duyệt</option>
                <option value="approved">Đã duyệt</option>
            </select>
            <button type="button" id="bulk-status-btn"
                    class="inline-flex h-8 items-center gap-1.5 rounded-xl bg-green-600 px-3 text-xs font-black text-white transition hover:bg-green-500">
                <x-heroicon-o-check class="h-3.5 w-3.5" />Áp dụng
            </button>
            <div class="h-4 w-px bg-slate-300"></div>
            <button type="button" id="bulk-delete-btn"
                    class="inline-flex h-8 items-center gap-1.5 rounded-xl bg-red-50 px-3 text-xs font-black text-red-600 transition hover:bg-red-100">
                <x-heroicon-o-trash class="h-3.5 w-3.5" />Xóa đã chọn
            </button>
            <button type="button" id="bulk-cancel-btn"
                    class="inline-flex h-8 items-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-black text-slate-600 transition hover:bg-slate-50">
                Hủy
            </button>
        </div>
    </div>

        {{-- Question list --}}
        <div class="flex-1 p-5">
            @if($questions->isEmpty())
                <div class="flex flex-col items-center justify-center gap-4 rounded-3xl border border-dashed border-slate-200 bg-white py-24">
                    <span class="grid h-20 w-20 place-items-center rounded-full bg-slate-50 text-slate-300">
                        <x-heroicon-o-circle-stack class="h-10 w-10" />
                    </span>
                    <div class="text-center">
                        <p class="text-lg font-black text-slate-700">@lang('teacher-question::app.empty_title')</p>
                        <p class="mt-1 text-sm font-semibold text-slate-400">@lang('teacher-question::app.empty_desc')</p>
                    </div>
                    <a href="{{ route('teacher.questions.create') }}"
                       class="mt-1 inline-flex h-10 items-center gap-2 rounded-full bg-green-600 px-6 text-sm font-black text-white no-underline shadow-sm transition hover:bg-green-500">
                        <x-heroicon-o-plus class="h-4 w-4" />@lang('teacher-question::app.create')
                    </a>
                </div>
            @else
                @php
                    $diffColors = ['easy'=>'text-emerald-600 bg-emerald-50','medium'=>'text-amber-600 bg-amber-50','hard'=>'text-red-600 bg-red-50'];
                    $statusColors = ['approved'=>'bg-green-100 text-green-700','reviewing'=>'bg-amber-100 text-amber-700','draft'=>'bg-slate-100 text-slate-500','rejected'=>'bg-red-100 text-red-600'];
                @endphp
                <div class="space-y-2">
                    @foreach($questions as $q)
                    {{-- chọn --}}
                        <div class="group flex items-start gap-4 rounded-2xl border border-slate-100 bg-white px-4 py-3.5 shadow-sm transition hover:border-green-100 hover:shadow-md"
                        data-question-id="{{ $q->id }}">

                        {{-- Checkbox bulk --}}
                        <label class="bulk-checkbox-wrap mt-1 hidden cursor-pointer shrink-0">
                            <input type="checkbox" class="bulk-checkbox h-4 w-4 accent-green-600 cursor-pointer"
                                value="{{ $q->id }}">
                        </label>
                            {{-- Type badge --}}
                            <span class="mt-0.5 shrink-0 rounded-xl border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-black text-slate-500 whitespace-nowrap">
                                @lang('teacher-question::app.' . $q->type)
                            </span>

                            {{-- Content --}}
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('teacher.questions.show', $q) }}"
                                   class="text-sm font-black leading-snug text-slate-900 no-underline hover:text-green-700 line-clamp-2">
                                    {{ strip_tags($q->content) }}
                                </a>
                                <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                    @if($q->subject)
                                        <span class="text-xs font-bold text-slate-400">{{ $q->subject }}</span>
                                    @endif
                                    @if($q->topic)
                                        <span class="text-slate-300">·</span>
                                        <span class="text-xs font-bold text-slate-400">{{ $q->topic }}</span>
                                    @endif
                                    @if($q->folder)
                                        <span class="text-slate-300">·</span>
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-slate-400">
                                            <span class="h-1.5 w-1.5 rounded-full" style="background:{{ $q->folder->color ?: '#94a3b8' }}"></span>
                                            {{ $q->folder->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Meta + actions --}}
                            <div class="flex shrink-0 items-center gap-2">
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-black {{ $diffColors[$q->difficulty] ?? 'bg-slate-100 text-slate-500' }}">
                                    @lang('teacher-question::app.' . $q->difficulty)
                                </span>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-black {{ $statusColors[$q->status] ?? 'bg-slate-100 text-slate-500' }}">
                                    @lang('teacher-question::app.' . $q->status)
                                </span>

                                {{-- Actions --}}
                                <div class="flex items-center gap-0.5 opacity-0 transition group-hover:opacity-100">
                                    @if(in_array($q->status, ['draft']))
                                        <a href="{{ route('teacher.questions.edit', $q) }}"
                                           class="grid h-8 w-8 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                                            <x-heroicon-o-pencil-square class="h-4 w-4" />
                                        </a>
                                    @endif
                                    <form method="POST" action="{{ route('teacher.questions.destroy', $q) }}"
                                          data-mindigo-confirm-title="@lang('teacher-question::app.delete_title')"
                                          data-mindigo-confirm-message="@lang('teacher-question::app.delete_confirm')"
                                          data-mindigo-confirm-text="@lang('teacher-question::app.delete')"
                                          data-mindigo-confirm-cancel="{{ __('teacher-question::app.cancel') }}"
                                          data-mindigo-confirm-type="danger">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="grid h-8 w-8 place-items-center rounded-xl text-slate-400 transition hover:bg-red-50 hover:text-red-600">
                                            <x-heroicon-o-trash class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($questions->hasPages())
                    <div class="mt-4 flex justify-center">{{ $questions->links() }}</div>
                @endif
            @endif
        </div>
    </div>
</div>
{{-- Bulk forms --}}
<form id="bulk-difficulty-form" method="POST" action="{{ route('teacher.questions.bulk.difficulty') }}">
    @csrf @method('PATCH')
    <input type="hidden" name="ids" id="bulk-ids-difficulty">
    <input type="hidden" name="difficulty" id="bulk-difficulty-value">
</form>

<form id="bulk-delete-form" method="POST" action="{{ route('teacher.questions.bulk.destroy') }}">
    @csrf @method('DELETE')
    <input type="hidden" name="ids" id="bulk-ids-delete">
</form>

<form id="bulk-status-form" method="POST" action="{{ route('teacher.questions.bulk.status') }}">
    @csrf @method('PATCH')
    <input type="hidden" name="ids" id="bulk-ids-status">
    <input type="hidden" name="status" id="bulk-status-value">
</form>

@section('scripts')
<script>
(function () {
    const modeBtn    = document.getElementById('bulk-mode-btn');
    const bar        = document.getElementById('bulk-bar');
    const countEl    = document.getElementById('bulk-count');
    const cancelBtn  = document.getElementById('bulk-cancel-btn');
    const diffSelect = document.getElementById('bulk-difficulty-select');
    const diffBtn    = document.getElementById('bulk-difficulty-btn');
    const deleteBtn  = document.getElementById('bulk-delete-btn');

    let bulkMode = false;

    function getChecked() {
        return [...document.querySelectorAll('.bulk-checkbox:checked')].map(el => el.value);
    }

    function updateBar() {
        countEl.textContent = `${getChecked().length} câu đã chọn`;
    }

    function enterBulkMode() {
        bulkMode = true;
        document.querySelectorAll('.bulk-checkbox-wrap').forEach(w => w.classList.remove('hidden'));
        bar.classList.remove('hidden');
        bar.classList.add('flex');
        modeBtn.classList.add('bg-green-50', 'text-green-700', 'border-green-300');
    }

    function exitBulkMode() {
        bulkMode = false;
        document.querySelectorAll('.bulk-checkbox-wrap').forEach(w => {
            w.classList.add('hidden');
            w.querySelector('input').checked = false;
        });
        const selectAll = document.getElementById('bulk-select-all');
        if (selectAll) selectAll.checked = false;
        bar.classList.add('hidden');
        bar.classList.remove('flex');
        modeBtn.classList.remove('bg-green-50', 'text-green-700', 'border-green-300');
        updateBar();
    }

    modeBtn?.addEventListener('click', () => bulkMode ? exitBulkMode() : enterBulkMode());
    cancelBtn?.addEventListener('click', exitBulkMode);

    // Click vào card để toggle checkbox
    document.querySelectorAll('[data-question-id]').forEach(card => {
        card.addEventListener('click', (e) => {
            if (!bulkMode) return;
            if (e.target.closest('a, button, form, label')) return;
            const cb = card.querySelector('.bulk-checkbox');
            if (cb) { cb.checked = !cb.checked; updateBar(); }
        });
    });

    document.querySelectorAll('.bulk-checkbox').forEach(cb => {
        cb.addEventListener('change', updateBar);
    });

    // Chọn tất cả / bỏ chọn tất cả
    document.getElementById('bulk-select-all')?.addEventListener('change', (e) => {
        document.querySelectorAll('.bulk-checkbox').forEach(cb => {
            cb.checked = e.target.checked;
        });
        updateBar();
    });

    // Áp dụng 
   // Áp dụng độ khó
diffBtn?.addEventListener('click', () => {
    const ids = getChecked();
    const diff = diffSelect.value;
    if (!ids.length) { alert('Chưa chọn câu hỏi nào.'); return; }
    if (!diff)       { alert('Chưa chọn độ khó.'); return; }
    document.getElementById('bulk-ids-difficulty').value = ids.join(',');
    document.getElementById('bulk-difficulty-value').value = diff;
    document.getElementById('bulk-difficulty-form').submit();
});

// Áp dụng trạng thái
const statusSelect = document.getElementById('bulk-status-select');
const statusBtn    = document.getElementById('bulk-status-btn');
statusBtn?.addEventListener('click', () => {
    const ids    = getChecked();
    const status = statusSelect.value;
    if (!ids.length) { alert('Chưa chọn câu hỏi nào.'); return; }
    if (!status)     { alert('Chưa chọn trạng thái.'); return; }
    if (!confirm(`Đổi ${ids.length} câu hỏi sang "${statusSelect.options[statusSelect.selectedIndex].text}"?`)) return;
    document.getElementById('bulk-ids-status').value   = ids.join(',');
    document.getElementById('bulk-status-value').value = status;
    document.getElementById('bulk-status-form').submit();
});

    // Xóa hàng loạt
    deleteBtn?.addEventListener('click', () => {
        const ids = getChecked();
        if (!ids.length) { alert('Chưa chọn câu hỏi nào.'); return; }
        if (!confirm(`Xóa ${ids.length} câu hỏi đã chọn?`)) return;
        document.getElementById('bulk-ids-delete').value = ids.join(',');
        document.getElementById('bulk-delete-form').submit();
    });
})();
</script>
@endsection
@endsection
