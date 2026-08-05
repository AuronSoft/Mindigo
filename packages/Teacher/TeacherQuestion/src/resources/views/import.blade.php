@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-question::app.import_title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.6.0/mammoth.browser.min.js"></script>
<script>
(function () {
    const drop  = document.getElementById('import-drop');
    const input = document.getElementById('import-file-input');
    const label = document.getElementById('import-file-label');
    const icon  = document.getElementById('import-icon');
    const btn   = document.getElementById('import-submit-btn');
    // Thêm hidden input chứa JSON câu hỏi đã parse
    const parsedInput = document.getElementById('import-parsed-json');
    const preview     = document.getElementById('import-preview');

    if (!drop || !input) return;

    const updateLabel = (name) => {
        label.textContent = name;
        icon.innerHTML = `<path d="M9 12l2 2 4-4M7 16a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M15 13l-3-3m0 0l-3 3m3-3v8" stroke-linecap="round" stroke-linejoin="round"/>`;
        drop.classList.add('border-green-400', 'bg-green-50');
        drop.classList.remove('border-slate-200');
    };

    const enableBtn = () => {
        btn.disabled = false;
        btn.classList.remove('opacity-50', 'cursor-not-allowed');
    };

    // ----------------------------------------------------------------
    // Parse text từ mammoth thành mảng câu hỏi
    // ----------------------------------------------------------------
    function parseQuestions(text) {
    const lines     = text.split('\n');
    const questions = [];
    let current     = null;

    for (let rawLine of lines) {
        const trimmed = rawLine.trim();

        // Bỏ qua dòng trắng hoàn toàn
        if (trimmed === '') continue;

        // Câu hỏi mới
        const questionMatch = trimmed.match(/^(Câu|Question)\s*(\d+)\s*[:.)\s]\s*(.+)/i);
        if (questionMatch) {
            // Finalize câu trước (nếu có)
            if (current) {
                const q = finalizeQuestion(current);
                if (q) questions.push(q);
            }
            current = {
                number:      parseInt(questionMatch[2]),
                content:     questionMatch[3].trim(),
                options:     [],
                explanation: null,
            };
            continue;
        }

        if (!current) continue;

        // Option: "*A. text" hoặc "A. text"
        const optionMatch = trimmed.match(/^(\*?)([A-Za-z])[.)]\s*(.+)/);
        if (optionMatch) {
            current.options.push({
                key:     optionMatch[2].toUpperCase(),
                text:    optionMatch[3].trim(),
                correct: optionMatch[1] === '*',
            });
            continue;
        }

        // Giải thích
        const explainMatch = trimmed.match(/^(Giải thích|Explanation)\s*[:\-]\s*(.+)/i);
        if (explainMatch) {
            current.explanation = explainMatch[2].trim();
            continue;
        }

        // Nối nội dung câu hỏi nhiều dòng (chưa có option)
        if (current.options.length === 0) {
            current.content += ' ' + trimmed;
        }
    }

    // Câu cuối file
    if (current) {
        const q = finalizeQuestion(current);
        if (q) questions.push(q);
    }

    return questions;
}

    function finalizeQuestion(raw) {
        const content = raw.content.trim();
        if (!content) return null;

        const correctAnswers = raw.options
            .filter(o => o.correct)
            .map(o => o.text);

        const cleanOptions = raw.options.map(o => ({ key: o.key, text: o.text }));

        let type = 'single_choice';
        if (correctAnswers.length > 1) {
            type = 'multiple_choice';
        } else if (cleanOptions.length === 0) {
            type = 'short_answer';
        } else if (cleanOptions.length === 2) {
            const texts = cleanOptions.map(o => o.text.toLowerCase());
            const tfPairs = [['đúng','sai'],['true','false'],['có','không'],['yes','no']];
            if (tfPairs.some(([a,b]) => texts.includes(a) && texts.includes(b))) {
                type = 'true_false';
            }
        }

        return {
            type,
            difficulty:      'medium',
            content,
            options:         cleanOptions,
            correct_answers: correctAnswers,
            explanation:     raw.explanation,
            tags:            [],
        };
    }

    // ----------------------------------------------------------------
    // Hiển thị preview
    // ----------------------------------------------------------------
    function renderPreview(questions) {
        if (!preview) return;
        preview.innerHTML = '';
        preview.classList.remove('hidden');

        if (!questions.length) {
            preview.innerHTML = `<p class="text-sm font-bold text-red-500">Không tìm thấy câu hỏi nào. Kiểm tra lại format file.</p>`;
            return;
        }

        const badge = document.createElement('p');
        badge.className = 'mb-3 text-sm font-black text-green-700';
        badge.textContent = `Tìm thấy ${questions.length} câu hỏi`;
        preview.appendChild(badge);

        questions.slice(0, 3).forEach((q, i) => {
            const div = document.createElement('div');
            div.className = 'mb-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-bold text-slate-700';
            const correct = q.correct_answers.join(', ');
            div.innerHTML = `
                <p class="font-black text-slate-900">Câu ${i + 1}: ${q.content.substring(0, 80)}${q.content.length > 80 ? '...' : ''}</p>
                <p class="mt-1 text-slate-500">${q.options.length} đáp án · Đúng: <span class="text-green-700">${correct.substring(0, 50)}</span></p>
            `;
            preview.appendChild(div);
        });

        if (questions.length > 3) {
            const more = document.createElement('p');
            more.className = 'text-xs font-bold text-slate-400';
            more.textContent = `... và ${questions.length - 3} câu hỏi khác`;
            preview.appendChild(more);
        }
    }

    // ----------------------------------------------------------------
    // Xử lý file khi chọn
    // ----------------------------------------------------------------
    async function handleFile(file) {
        if (!file) return;

        const ext = file.name.split('.').pop().toLowerCase();

        updateLabel(file.name);

        if (ext === 'docx') {
            document.getElementById('docx-subject-field')?.classList.remove('hidden');
            try {
                const arrayBuffer = await file.arrayBuffer();
                const result      = await mammoth.extractRawText({ arrayBuffer });
                result.value.split('\n').forEach(line => {
            if (line.includes('*')) {
                console.log('LINE WITH STAR:', JSON.stringify(line));
                console.log('CHARCODE[0]:', line.charCodeAt(0));
            }
        });
                console.log('RAW TEXT:', result.value);
                const questions   = parseQuestions(result.value);

                parsedInput.value = JSON.stringify(questions);
                renderPreview(questions);

                if (questions.length > 0) enableBtn();
            } catch (err) {
                preview.innerHTML = `<p class="text-sm font-bold text-red-500">Lỗi đọc file: ${err.message}</p>`;
            }
        } else {
            // CSV / TXT / JSON — xử lý server-side như cũ
            document.getElementById('docx-subject-field')?.classList.add('hidden');
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            parsedInput.value = '';
            if (preview) preview.innerHTML = '';
            enableBtn();
        }
    }

    input.addEventListener('change', () => handleFile(input.files[0]));

    drop.addEventListener('dragover', (e) => {
        e.preventDefault();
        drop.classList.add('border-green-400', 'bg-green-50/50');
    });
    drop.addEventListener('dragleave', () => {
        drop.classList.remove('bg-green-50/50');
    });
    drop.addEventListener('drop', (e) => {
        e.preventDefault();
        const file = e.dataTransfer.files[0];
        if (!file) return;
        drop.classList.remove('bg-green-50/50');
        handleFile(file);
    });

    drop.addEventListener('click', () => input.click());
})();

    // ── Subject dropdown 
    (function () {
        const btn       = document.getElementById('subject-dropdown-btn');
        const panel     = document.getElementById('subject-dropdown-panel');
        const search    = document.getElementById('subject-search-input');
        const list      = document.getElementById('subject-list');
        const empty     = document.getElementById('subject-empty');
        const newInput  = document.getElementById('subject-new-input');
        const addBtn    = document.getElementById('subject-add-btn');
        const hidden    = document.getElementById('docx-subject-value');
        const labelEl   = document.getElementById('subject-dropdown-label');

        if (!btn) return;

        // Toggle panel
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isHidden = panel.classList.contains('hidden');
            panel.classList.toggle('hidden', !isHidden);
            if (isHidden) search.focus();
        });

        // Close khi click ngoài
        document.addEventListener('click', () => panel.classList.add('hidden'));
        panel.addEventListener('click', (e) => e.stopPropagation());

        // Search filter
        search.addEventListener('input', () => {
            const q = search.value.toLowerCase();
            const items = list.querySelectorAll('.subject-option');
            let visible = 0;
            items.forEach(item => {
                const match = item.dataset.value.toLowerCase().includes(q);
                item.classList.toggle('hidden', !match);
                if (match) visible++;
            });
            empty.classList.toggle('hidden', visible > 0);
        });

        // Chọn option
        list.addEventListener('click', (e) => {
            const item = e.target.closest('.subject-option');
            if (!item) return;
            selectSubject(item.dataset.value, item.dataset.value);
        });

        // Thêm môn học mới
        addBtn.addEventListener('click', () => {
            const val = newInput.value.trim();
            if (!val) return;

            // Thêm vào list
            const li = document.createElement('li');
            li.className = 'subject-option cursor-pointer rounded-xl px-3 py-2 text-sm font-bold text-slate-700 transition hover:bg-green-50 hover:text-green-700';
            li.dataset.value = val;
            li.textContent = val;
            list.insertBefore(li, empty);

            newInput.value = '';
            selectSubject(val, val);
        });

        // Enter trong input mới
        newInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); addBtn.click(); }
        });

        function selectSubject(value, label) {
            hidden.value = value;
            labelEl.textContent = label;
            labelEl.classList.remove('text-slate-400');
            labelEl.classList.add('text-slate-700');

            // Highlight item đang chọn
            list.querySelectorAll('.subject-option').forEach(el => {
                el.classList.toggle('bg-green-50', el.dataset.value === value);
                el.classList.toggle('text-green-700', el.dataset.value === value);
            });

            panel.classList.add('hidden');
            search.value = '';
            list.querySelectorAll('.subject-option').forEach(el => el.classList.remove('hidden'));
            empty.classList.add('hidden');
        }
    })();
</script>
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    <header class="sticky top-0 z-10 flex items-center gap-3 border-b border-slate-200 bg-white/95 px-6 py-3 backdrop-blur">
        <a href="{{ route('teacher.questions.index') }}"
           class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline transition hover:bg-green-50 hover:text-green-700">
            <x-heroicon-o-arrow-left class="h-4 w-4" />
        </a>
        <div>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">@lang('teacher-question::app.title')</p>
            <h1 class="text-base font-black text-slate-950">@lang('teacher-question::app.import_title')</h1>
        </div>
    </header>

    <div class="mx-auto w-full max-w-2xl flex-1 p-6">
        <p class="mb-5 text-sm font-semibold text-slate-500">@lang('teacher-question::app.import_subtitle')</p>

        <form method="POST" action="{{ route('teacher.questions.import.store') }}"
              enctype="multipart/form-data"
              class="space-y-4">
            @csrf

            {{-- Drop zone --}}
            <div id="import-drop"
                 class="flex cursor-pointer flex-col items-center justify-center gap-3 rounded-3xl border-2 border-dashed border-slate-200 bg-white px-6 py-12 text-center shadow-sm transition hover:border-green-300 hover:bg-green-50/30">
                <svg id="import-icon" viewBox="0 0 24 24" class="h-12 w-12 fill-none stroke-current stroke-[1.5] text-slate-300" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                <div>
                    <p class="text-sm font-black text-slate-700">@lang('teacher-question::app.import_file_desc')</p>
                    <p id="import-file-label" class="mt-1 text-xs font-bold text-slate-400">@lang('teacher-question::app.fmt_file_types')</p>
                </div>
                <input id="import-file-input" type="file" name="import_file"
                 accept=".csv,.txt,.json,.docx" class="hidden" required>
                 <input type="hidden" name="docx_parsed" id="import-parsed-json">
            </div>
            @error('import_file')
                <p class="flex items-center gap-1 text-sm font-bold text-red-600">
                    <x-heroicon-o-exclamation-circle class="h-4 w-4" />{{ $message }}
                </p>
            @enderror
            <div id="import-preview" class="rounded-2xl border border-slate-200 bg-white p-4 hidden empty:hidden"></div>
            {{-- Options --}}
            <div class="grid gap-4 sm:grid-cols-2">
                <div id="docx-subject-field" class="hidden sm:col-span-2">
                <label class="mb-1.5 block text-xs font-black text-slate-600">
                    Môn học <span class="text-red-500">*</span>
                </label>
                <input type="hidden" name="docx_subject" id="docx-subject-value">

    {{-- Trigger button --}}
    <div class="relative" id="subject-dropdown-wrapper">
        <button type="button" id="subject-dropdown-btn"
                class="flex w-full items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 outline-none transition focus:border-green-400 focus:ring-2 focus:ring-green-100">
            <span id="subject-dropdown-label" class="text-slate-400">-- Chọn môn học --</span>
            <x-heroicon-o-chevron-down class="h-4 w-4 shrink-0 text-slate-400" />
        </button>

        {{-- Dropdown panel --}}
        <div id="subject-dropdown-panel"
             class="absolute z-50 mt-1 hidden w-full rounded-2xl border border-slate-200 bg-white shadow-lg">

            {{-- Search --}}
            <div class="p-2 border-b border-slate-100">
                <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus-within:border-green-400 focus-within:bg-white">
                    <x-heroicon-o-magnifying-glass class="h-3.5 w-3.5 shrink-0 text-slate-400" />
                    <input type="text" id="subject-search-input"
                           placeholder="Tìm môn học..."
                           class="w-full bg-transparent text-sm font-bold text-slate-700 outline-none placeholder:text-slate-400">
                </div>
            </div>

            {{-- List --}}
            <ul id="subject-list" class="max-h-48 overflow-y-auto p-1">
                @foreach($subjects as $subject)
                    <li class="subject-option cursor-pointer rounded-xl px-3 py-2 text-sm font-bold text-slate-700 transition hover:bg-green-50 hover:text-green-700"
                        data-value="{{ $subject }}">
                        {{ $subject }}
                    </li>
                @endforeach
                <li id="subject-empty" class="hidden px-3 py-2 text-sm font-semibold text-slate-400">
                    Không tìm thấy môn học
                </li>
            </ul>

            {{-- Add new --}}
            <div class="border-t border-slate-100 p-2">
                <div class="flex gap-2">
                    <input type="text" id="subject-new-input"
                           placeholder="Nhập tên môn học mới..."
                           class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-bold text-slate-700 outline-none focus:border-green-400 focus:bg-white">
                    <button type="button" id="subject-add-btn"
                            class="inline-flex shrink-0 items-center gap-1 rounded-xl bg-green-600 px-3 py-2 text-xs font-black text-white transition hover:bg-green-500">
                        <x-heroicon-o-plus class="h-3.5 w-3.5" />Thêm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
            </div>

                    {{--chỉnh độ khó --}}
                    <div>
        <label class="mb-1.5 block text-xs font-black text-slate-600">Độ khó mặc định</label>
        <div class="space-y-2 pt-1">
            @foreach(['easy' => __('teacher-question::app.easy'), 'medium' => __('teacher-question::app.medium'), 'hard' => __('teacher-question::app.hard')] as $val => $label)
                <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 px-3 py-2.5 transition hover:bg-slate-50 has-checked:border-green-400 has-checked:bg-green-50">
                    <input type="radio" name="default_difficulty" value="{{ $val }}"
                           @checked($val === 'medium') class="accent-green-600">
                    <span class="text-sm font-bold text-slate-700">{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>


            <div>
    <label class="mb-1.5 block text-xs font-black text-slate-600">
        Trạng thái
    </label>

    <div class="space-y-2 pt-1">
        @foreach([
            'draft' => __('teacher-question::app.import_draft'),
            'reviewing' => __('teacher-question::app.import_reviewing')
        ] as $val => $label)

            <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 px-3 py-2.5 hover:bg-slate-50">
                <input
                    type="radio"
                    name="status"
                    value="{{ $val }}"
                    @checked($val === 'draft')
                    class="accent-green-600">

                <span class="text-sm font-bold">
                    {{ $label }}
                </span>
            </label>

        @endforeach
    </div>
</div>

            {{-- Submit --}}
            <div class="flex items-center justify-end gap-2 pt-2">
                <a href="{{ route('teacher.questions.index') }}"
                   class="inline-flex h-10 items-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-600 no-underline transition hover:bg-slate-50">
                    @lang('teacher-question::app.back')
                </a>
                <button id="import-submit-btn" type="submit" disabled
                        class="inline-flex h-10 cursor-not-allowed items-center gap-2 rounded-2xl bg-green-600 px-6 text-sm font-black text-white opacity-50 shadow-sm transition hover:bg-green-500">
                    <x-heroicon-o-arrow-up-tray class="h-4 w-4" />@lang('teacher-question::app.import_submit')
                </button>
            </div>
        </form>

        {{-- Format guide --}}
        <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="mb-3 text-xs font-black uppercase tracking-wider text-slate-400">@lang('teacher-question::app.import_format')</p>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="text-[11px] font-black uppercase text-slate-400">
                        <tr class="border-b border-slate-100">
                            <th class="pb-2 pr-4">@lang('teacher-question::app.fmt_col')</th>
                            <th class="pb-2 pr-4">@lang('teacher-question::app.fmt_required_hd')</th>
                            <th class="pb-2">@lang('teacher-question::app.fmt_example_hd')</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 font-bold text-slate-600">
                        @foreach([
                            ['type',             true,  'single_choice | multiple_choice | true_false | short_answer | essay'],
                            ['difficulty',       true,  'easy | medium | hard'],
                            ['content',          true,  __('teacher-question::app.ex_content')],
                            ['subject',          false, __('teacher-question::app.ex_subject')],
                            ['topic',            false, __('teacher-question::app.ex_topic')],
                            ['options',          false, __('teacher-question::app.ex_options')],
                            ['correct_answers',  false, __('teacher-question::app.ex_answers')],
                            ['explanation',      false, __('teacher-question::app.ex_explanation')],
                            ['folder_name',      false, __('teacher-question::app.ex_folder')],
                            ['tags',             false, __('teacher-question::app.ex_tags')],
                        ] as $col)
                            <tr>
                                <td class="py-1.5 pr-4 font-black text-slate-800 font-mono">{{ $col[0] }}</td>
                                <td class="py-1.5 pr-4 whitespace-nowrap">
                                    @if($col[1])
                                        <span class="whitespace-nowrap rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-black text-red-700">@lang('teacher-question::app.fmt_badge_req')</span>
                                    @else
                                        <span class="whitespace-nowrap rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black text-slate-500">@lang('teacher-question::app.fmt_badge_opt')</span>
                                    @endif
                                </td>
                                <td class="py-1.5 text-slate-500">{{ $col[2] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-xs font-semibold text-slate-400">@lang('teacher-question::app.fmt_separator')</p>
            <p class="mt-2 text-xs font-semibold text-slate-400">@lang('teacher-question::app.fmt_docx_note')</p>
        </div>
    </div>
</div>
@endsection
