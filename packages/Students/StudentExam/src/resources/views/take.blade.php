
@extends('Mindigo-dashboard::layouts')

@section('title', $attempt->exam->title)

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Students/StudentExam/src/resources/css/app.css',
        'packages/Students/StudentExam/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-950"
     x-data="examTake({
        attemptId:      {{ $attempt->id }},
        expiresAt:      '{{ $attempt->expires_at?->toIso8601String() ?? '' }}',
        totalQuestions: {{ $questions->count() }},
        savedAnswers:   @json($savedAnswers->map(fn($a) => count($a->answer ?? []) === 1 ? $a->answer[0] : ($a->answer ?? []))->toArray()),
        submitUrl:      '{{ route('student.exams.submit', $attempt) }}',
        csrfToken:      '{{ csrf_token() }}'
     })"
     @visibilitychange.window="handleVisibilityChange()">

    <header class="sticky top-0 z-30 flex items-center justify-between gap-4 border-b border-gray-200 bg-white px-4 py-3 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $attempt->exam->title }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500" x-text="progressLabel"></p>
        </div>

        @if($attempt->expires_at)
        <div class="flex shrink-0 items-center gap-1.5 rounded-xl px-3 py-1.5 font-mono text-sm font-semibold tabular-nums transition"
             :class="urgency === 'critical' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 animate-pulse'
                   : urgency === 'warning'  ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'
                   : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"/>
            </svg>
            <span x-text="timeDisplay">--:--</span>
        </div>
        @endif

        <div class="hidden shrink-0 items-center gap-1 text-xs text-amber-600 dark:text-amber-400 sm:flex"
             x-show="tabLeaveCount > 0" x-cloak>
            <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
            </svg>
            <span>@lang('student-exam::app.tab_leave_label')</span>
            <span x-text="tabLeaveCount" class="font-bold"></span>
        </div>

        <button @click="confirmSubmit()"
                :disabled="submitting"
                class="shrink-0 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 active:scale-95">
            <span x-show="!submitting">@lang('student-exam::app.submit_exam')</span>
            <span x-show="submitting" x-cloak class="flex items-center gap-1.5">
                <svg class="h-4 w-4 animate-spin shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                @lang('student-exam::app.saving')
            </span>
        </button>
    </header>

    <div class="mx-auto grid max-w-6xl gap-6 px-4 py-6 lg:grid-cols-12">

        <div class="space-y-6 lg:col-span-9">
            @foreach($questions as $index => $question)
            @php
                $qIndex   = $index + 1;
                $qId      = $question->id;
                $qType    = $question->type ?? 'single';
                $options  = $question->options ?? collect();
                $saved    = $savedAnswers[$qId] ?? null;
                $savedRaw = $saved?->answer ?? [];
                $savedArr = array_map('strval', $savedRaw);
                $savedVal = $savedArr[0] ?? null;
            @endphp

            <div id="q-{{ $qId }}"
                 class="scroll-mt-24 rounded-2xl border bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">

                <div class="mb-4 flex items-start justify-between gap-3">
                    <span class="mt-0.5 shrink-0 rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300">
                        @lang('student-exam::app.question_of', ['current' => $qIndex, 'total' => $questions->count()])
                    </span>
                    <div class="flex items-center gap-2">
                        @if($question->points)
                            <span class="text-xs text-gray-400">{{ $question->points }}đ</span>
                        @endif
                    </div>
                </div>

                <div class="mb-5 text-base font-medium leading-relaxed text-gray-900 dark:text-white">
                    {!! $question->content !!}
                </div>

                @if(in_array($qType, ['single', 'single_choice', 'multiple_choice_single']))
                <div class="space-y-2.5">
                    @foreach($options as $option)
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 transition hover:border-green-300 hover:bg-green-50/40 dark:border-gray-600 dark:hover:border-green-500">
                        <input type="radio"
                            name="q_{{ $qId }}"
                            value="{{ data_get($option, 'id') }}"
                            class="h-4 w-4 shrink-0 accent-green-600"
                            {{ (string)$savedVal === (string)data_get($option, 'id') ? 'checked' : '' }}
                            @change="saveAnswer({{ $qId }}, $event.target.value)">
                        <span class="text-sm text-gray-800 dark:text-gray-200">{{ data_get($option, 'content') }}</span>
                    </label>
                    @endforeach
                    <p class="text-xs text-gray-400">@lang('student-exam::app.single_choice_hint')</p>
                </div>

                @elseif(in_array($qType, ['multiple_choice', 'multiple', 'checkbox']))
                <div class="space-y-2.5">
                    @foreach($options as $option)
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 transition hover:border-green-300 hover:bg-green-50/40 dark:border-gray-600 dark:hover:border-green-500">
                        <input type="checkbox"
                            name="q_{{ $qId }}[]"
                            value="{{ data_get($option, 'id') }}"
                            class="h-4 w-4 shrink-0 rounded accent-green-600"
                            {{ in_array((string)data_get($option, 'id'), $savedArr) ? 'checked' : '' }}
                            @change="saveMultiAnswer({{ $qId }}, '{{ data_get($option, 'id') }}', $event.target.checked)">
                        <span class="text-sm text-gray-800 dark:text-gray-200">{{ data_get($option, 'content') }}</span>
                    </label>
                    @endforeach
                    <p class="text-xs text-gray-400">@lang('student-exam::app.multiple_choice_hint')</p>
                </div>
                @else
                <textarea
                    name="q_{{ $qId }}"
                    rows="6"
                    placeholder="@lang('student-exam::app.essay_placeholder')"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-indigo-500 dark:focus:ring-indigo-800/50"
                    @input.debounce.600ms="saveAnswer({{ $qId }}, $event.target.value)">{{ $savedVal ?? '' }}</textarea>
                @endif

                <p class="mt-2 text-right text-xs text-indigo-400 dark:text-indigo-400"
                   x-show="savingId === {{ $qId }}"
                   x-transition x-cloak>
                    @lang('student-exam::app.saving')
                </p>
            </div>
            @endforeach
        </div>

        <aside class="hidden lg:col-span-3 lg:block">
            <div class="sticky top-24 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-600 dark:bg-gray-800">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    @lang('student-exam::app.navigator_label')
                </p>
                <div class="grid grid-cols-5 gap-1.5">
                    @foreach($questions as $index => $question)
                    <a href="#q-{{ $question->id }}"
                       class="flex h-9 w-9 items-center justify-center rounded-lg text-xs font-semibold transition"
                       :class="answers['{{ $question->id }}'] !== undefined
                                && answers['{{ $question->id }}'] !== ''
                                && answers['{{ $question->id }}'] !== null
                              ? 'bg-indigo-500 text-white shadow-sm'
                              : 'bg-gray-100 text-gray-500 hover:bg-indigo-100 hover:text-indigo-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-indigo-900/40 dark:hover:text-indigo-300'">
                        {{ $index + 1 }}
                    </a>
                    @endforeach
                </div>

                <div class="mt-4 rounded-xl bg-amber-50 px-3 py-2.5 text-xs text-amber-700 dark:bg-amber-900/30 dark:text-amber-300"
                     x-show="unansweredCount > 0" x-cloak>
                    <span x-text="`@lang('student-exam::app.unanswered_warning')`.replace(':count', unansweredCount)"></span>
                </div>
            </div>
        </aside>
    </div>

    <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-show="showConfirm"
         x-transition.opacity
         x-cloak>
        <div class="absolute inset-0 bg-black/60" @click="showConfirm = false"></div>

        <div class="relative z-10 w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-800">
            <h2 class="mb-2 text-base font-semibold text-gray-900 dark:text-white">
                @lang('student-exam::app.submit_exam')
            </h2>
            <p class="mb-1 text-sm text-gray-600 dark:text-gray-400">
                @lang('student-exam::app.confirm_submit')
            </p>
            <p class="mb-5 text-sm font-semibold text-amber-600 dark:text-amber-400"
               x-show="unansweredCount > 0" x-cloak
               x-text="`@lang('student-exam::app.unanswered_warning')`.replace(':count', unansweredCount)">
            </p>
            <div class="flex gap-3">
                <button @click="showConfirm = false"
                        class="flex-1 rounded-xl border border-gray-200 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    @lang('student-exam::app.cancel')
                </button>
                <button @click="doSubmit()"
                        :disabled="submitting"
                        class="flex-1 rounded-xl bg-indigo-600 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                    @lang('student-exam::app.submit_exam')
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
@push('scripts')
<script>
function examTake({ attemptId, expiresAt, totalQuestions, savedAnswers, submitUrl, csrfToken }) {
    return {
        answers:       savedAnswers || {},
        multiAnswers:  {},
        savingId:      null,
        submitting:    false,
        showConfirm:   false,
        tabLeaveCount: 0,
        urgency:       'normal',
        timer:         null,
        timeDisplay:   '--:--',

        init() {
            if (expiresAt && expiresAt !== '' && !isNaN(new Date(expiresAt).getTime())) {
                this.startCountdown();
            }
            Object.keys(this.answers).forEach(qId => {
                const val = this.answers[qId];
                if (Array.isArray(val)) this.multiAnswers[qId] = new Set(val.map(String));
            });
        },

        startCountdown() {
            const end = new Date(expiresAt).getTime();
            const tick = () => {
                const diff = Math.floor((end - Date.now()) / 1000);
                if (diff <= 0) {
                    this.timeDisplay = '00:00';
                    this.urgency = 'critical';
                    clearInterval(this.timer);
                    this.doSubmit();
                    return;
                }
                const m = String(Math.floor(diff / 60)).padStart(2, '0');
                const s = String(diff % 60).padStart(2, '0');
                this.timeDisplay = `${m}:${s}`;
                this.urgency = diff < 60 ? 'critical' : diff < 300 ? 'warning' : 'normal';
            };
            tick();
            this.timer = setInterval(tick, 1000);
        },

        get progressLabel() {
            const answered = Object.values(this.answers)
                .filter(v => v !== null && v !== '' && v !== undefined).length;
            return `${answered} / ${totalQuestions} câu đã trả lời`;
        },

        get unansweredCount() {
            const answered = Object.values(this.answers)
                .filter(v => v !== null && v !== '' && v !== undefined).length;
            return totalQuestions - answered;
        },

        async saveAnswer(qId, value) {
            this.answers[String(qId)] = value;
            this.savingId = qId;
            await this.persistAnswer(qId, value);
            this.savingId = null;
        },

        async saveMultiAnswer(qId, optionId, checked) {
            const key = String(qId);
            if (!this.multiAnswers[key]) this.multiAnswers[key] = new Set();
            checked
                ? this.multiAnswers[key].add(String(optionId))
                : this.multiAnswers[key].delete(String(optionId));
            const val = [...this.multiAnswers[key]];
            this.answers[key] = val;
            this.savingId = qId;
            await this.persistAnswer(qId, val);
            this.savingId = null;
        },

        async persistAnswer(qId, value) {
            try {
                await fetch(`/student/exams/attempts/${attemptId}/autosave`, {
                    method:    'POST',
                    headers:   { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body:      JSON.stringify({ question_id: qId, answer: value }),
                    keepalive: true,
                });
            } catch (_) {}
        },

        handleVisibilityChange() {
            if (document.hidden) this.tabLeaveCount++;
        },

        confirmSubmit() {
            this.showConfirm = true;
        },

        async doSubmit() {
            if (this.submitting) return;
            this.submitting  = true;
            this.showConfirm = false;
            if (this.timer) clearInterval(this.timer);

            const form  = document.createElement('form');
            form.method = 'POST';
            form.action = submitUrl;

            const addHidden = (name, value) => {
                const i = document.createElement('input');
                i.type = 'hidden'; i.name = name; i.value = value ?? '';
                form.appendChild(i);
            };

            addHidden('_token', csrfToken);
            addHidden('tab_leave_count', this.tabLeaveCount);
            Object.entries(this.answers).forEach(([qId, val]) => {
                if (Array.isArray(val)) {
                    val.forEach(answer => addHidden(`answers[${qId}][]`, answer));
                    return;
                }
                addHidden(`answers[${qId}]`, val);
            });

            document.body.appendChild(form);
            form.submit();
        },
    };
}
</script>
@endpush
