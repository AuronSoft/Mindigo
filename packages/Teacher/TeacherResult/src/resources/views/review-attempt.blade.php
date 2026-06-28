@extends('Mindigo-dashboard::layouts')

@section('title', 'Chấm bài — ' . $attempt->user?->name)

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    <header class="sticky top-0 z-10 flex items-center gap-3 border-b border-slate-200 bg-white/95 px-6 py-3 backdrop-blur">
        <a href="{{ route('teacher.results.by_exam', $attempt->exam_id) }}"
           class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline transition hover:bg-green-50 hover:text-green-700">
            <x-heroicon-o-arrow-left class="h-4 w-4" />
        </a>
        <div>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Chấm bài thủ công</p>
            <h1 class="text-base font-black text-slate-950">{{ $attempt->user?->name }}</h1>
        </div>
        <div class="ml-auto flex items-center gap-3">
            <div class="text-right">
                <p class="text-xs font-bold text-slate-400">{{ $attempt->exam?->title }}</p>
                <p class="text-xs font-black text-amber-600">{{ $pendingAnswers->count() }} câu chờ chấm</p>
            </div>
        </div>
    </header>

    <div class="mx-auto w-full max-w-3xl flex-1 space-y-4 p-6">

        @if(session('success'))
            <div class="rounded-2xl bg-green-50 px-4 py-3 text-sm font-bold text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if($pendingAnswers->isEmpty())
            <div class="flex flex-col items-center justify-center gap-3 rounded-3xl border border-dashed border-slate-200 bg-white py-20">
                <x-heroicon-o-check-circle class="h-12 w-12 text-green-400" />
                <p class="text-sm font-black text-slate-700">Tất cả câu hỏi đã được chấm.</p>
                <a href="{{ route('teacher.results.by_exam', $attempt->exam_id) }}"
                   class="inline-flex h-9 items-center gap-2 rounded-full bg-green-600 px-5 text-sm font-black text-white no-underline transition hover:bg-green-500">
                    Quay lại kết quả
                </a>
            </div>
        @else
            {{-- Thông tin bài làm --}}
            <div class="flex flex-wrap gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-3 shadow-sm">
                <div>
                    <p class="text-[10px] font-black uppercase text-slate-400">Điểm TN hiện tại</p>
                    <p class="text-lg font-black text-slate-900">{{ $attempt->score }}/{{ $attempt->max_score }}</p>
                </div>
                <div class="h-8 w-px bg-slate-200 self-center"></div>
                <div>
                    <p class="text-[10px] font-black uppercase text-slate-400">Nộp lúc</p>
                    <p class="text-sm font-black text-slate-700">{{ $attempt->submitted_at?->format('H:i d/m/Y') }}</p>
                </div>
                <div class="h-8 w-px bg-slate-200 self-center"></div>
                <div>
                    <p class="text-[10px] font-black uppercase text-slate-400">Trạng thái</p>
                    <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-black text-amber-700">Chờ chấm</span>
                </div>
            </div>

            {{-- Form chấm --}}
            <form method="POST" action="{{ route('teacher.results.grade_attempt', $attempt) }}">
                @csrf
                <div class="space-y-4">
                    @foreach($pendingAnswers as $index => $answer)
                        @php
                            $maxPts = (float) ($answer->question?->points ?? 0);
                            $typeMap = ['essay' => 'Tự luận', 'short_answer' => 'Trả lời ngắn'];
                            $typeLabel = $typeMap[$answer->type] ?? $answer->type;
                        @endphp
                        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                            {{-- Header câu --}}
                            <div class="mb-4 flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3">
                                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-amber-100 text-xs font-black text-amber-700">
                                        {{ $index + 1 }}
                                    </span>
                                    <div>
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black text-slate-500">
                                            {{ $typeLabel }}
                                        </span>
                                        <p class="mt-1.5 text-sm font-black text-slate-900">
                                            {{ $answer->question?->content }}
                                        </p>
                                    </div>
                                </div>
                                <span class="shrink-0 text-xs font-black text-slate-400">
                                    Tối đa {{ $maxPts }} điểm
                                </span>
                            </div>

                            {{-- Câu trả lời của học sinh --}}
                            <div class="mb-4 rounded-2xl bg-slate-50 px-4 py-3">
                                <p class="mb-1 text-[10px] font-black uppercase text-slate-400">Câu trả lời</p>
                                <p class="text-sm font-semibold leading-relaxed text-slate-700">
                                    {{ implode(', ', (array) ($answer->answer ?? [])) ?: '(Bỏ trống)' }}
                                </p>
                            </div>

                            {{-- Đáp án mẫu nếu có --}}
                            @if($answer->question?->correct_answers && count($answer->question->correct_answers) > 0)
                                <div class="mb-4 rounded-2xl bg-green-50 px-4 py-3">
                                    <p class="mb-1 text-[10px] font-black uppercase text-green-600">Đáp án mẫu</p>
                                    <p class="text-sm font-semibold text-green-800">
                                        {{ implode(', ', $answer->question->correct_answers) }}
                                    </p>
                                </div>
                            @endif

                            {{-- Nhập điểm --}}
                            <div class="flex items-center gap-3">
                                <label class="text-xs font-black text-slate-600">Điểm:</label>
                                <input type="number"
                                       name="grades[{{ $answer->id }}]"
                                       min="0"
                                       max="{{ $maxPts }}"
                                       step="0.25"
                                       value="0"
                                       class="w-24 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-black text-slate-800 outline-none focus:border-green-400 focus:ring-2 focus:ring-green-100">
                                <span class="text-xs font-bold text-slate-400">/ {{ $maxPts }}</span>

                                {{-- Quick buttons --}}
                                <div class="ml-auto flex gap-1.5">
                                    <button type="button"
                                            onclick="this.closest('.rounded-3xl').querySelector('input').value = 0"
                                            class="rounded-xl bg-red-50 px-3 py-1.5 text-xs font-black text-red-600 transition hover:bg-red-100">
                                        0 điểm
                                    </button>
                                    <button type="button"
                                            onclick="this.closest('.rounded-3xl').querySelector('input').value = {{ $maxPts / 2 }}"
                                            class="rounded-xl bg-amber-50 px-3 py-1.5 text-xs font-black text-amber-600 transition hover:bg-amber-100">
                                        ½ điểm
                                    </button>
                                    <button type="button"
                                            onclick="this.closest('.rounded-3xl').querySelector('input').value = {{ $maxPts }}"
                                            class="rounded-xl bg-green-50 px-3 py-1.5 text-xs font-black text-green-700 transition hover:bg-green-100">
                                        Đủ điểm
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Submit --}}
                <div class="mt-4 flex justify-end gap-2">
                    <a href="{{ route('teacher.results.by_exam', $attempt->exam_id) }}"
                       class="inline-flex h-10 items-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-600 no-underline transition hover:bg-slate-50">
                        Hủy
                    </a>
                    <button type="submit"
                            class="inline-flex h-10 items-center gap-2 rounded-2xl bg-green-600 px-6 text-sm font-black text-white shadow-sm transition hover:bg-green-500">
                        <x-heroicon-o-check class="h-4 w-4" />
                        Lưu điểm
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection