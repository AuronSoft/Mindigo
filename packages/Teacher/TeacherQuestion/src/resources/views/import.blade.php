@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-question::app.import_title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('scripts')
<script>
(function () {
    const drop  = document.getElementById('import-drop');
    const input = document.getElementById('import-file-input');
    const label = document.getElementById('import-file-label');
    const icon  = document.getElementById('import-icon');
    const btn   = document.getElementById('import-submit-btn');

    if (!drop || !input) return;

    const updateLabel = (name) => {
        label.textContent = name;
        icon.innerHTML = `<path d="M9 12l2 2 4-4M7 16a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M15 13l-3-3m0 0l-3 3m3-3v8" stroke-linecap="round" stroke-linejoin="round"/>`;
        drop.classList.add('border-green-400', 'bg-green-50');
        drop.classList.remove('border-slate-200');
        btn.disabled = false;
        btn.classList.remove('opacity-50', 'cursor-not-allowed');
    };

    input.addEventListener('change', () => {
        if (input.files[0]) updateLabel(input.files[0].name);
    });

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
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        updateLabel(file.name);
        drop.classList.remove('bg-green-50/50');
    });

    drop.addEventListener('click', () => input.click());
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
                       accept=".csv,.txt,.json" class="hidden" required>
            </div>
            @error('import_file')
                <p class="flex items-center gap-1 text-sm font-bold text-red-600">
                    <x-heroicon-o-exclamation-circle class="h-4 w-4" />{{ $message }}
                </p>
            @enderror

            {{-- Options --}}
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-question::app.import_folder')</label>
                    <select name="folder_id"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 outline-none focus:border-green-400 focus:ring-2 focus:ring-green-50">
                        <option value="">@lang('teacher-question::app.import_folder_ph')</option>
                        @foreach($folders as $folder)
                            <option value="{{ $folder->id }}">{{ $folder->name }} ({{ $folder->questions_count }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-question::app.import_status')</label>
                    <div class="space-y-2 pt-1">
                        @foreach(['draft' => __('teacher-question::app.import_draft'), 'reviewing' => __('teacher-question::app.import_reviewing')] as $val => $label)
                            <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 px-3 py-2.5 transition hover:bg-slate-50 has-[:checked]:border-green-400 has-[:checked]:bg-green-50">
                                <input type="radio" name="status" value="{{ $val }}" @checked($val === 'draft') class="accent-green-600">
                                <span class="text-sm font-bold text-slate-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
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
        </div>
    </div>
</div>
@endsection
