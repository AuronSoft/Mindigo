@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-question::app.title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    <header class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white/95 px-6 py-3 backdrop-blur">
        <div class="flex items-center gap-3">
            <a href="{{ route('teacher.questions.index') }}"
               class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline transition hover:bg-green-50 hover:text-green-700">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
            </a>
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">@lang('teacher-question::app.title')</p>
                <h1 class="text-base font-black text-slate-950">@lang('teacher-question::app.' . $question->type)</h1>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($question->status === 'draft')
                <a href="{{ route('teacher.questions.edit', $question) }}"
                   class="inline-flex h-9 items-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 no-underline transition hover:bg-slate-50">
                    <x-heroicon-o-pencil-square class="h-4 w-4" />@lang('teacher-question::app.edit')
                </a>
                <form method="POST" action="{{ route('teacher.questions.submit', $question) }}"
                      data-mindigo-confirm-title="@lang('teacher-question::app.submit_title')"
                      data-mindigo-confirm-message="@lang('teacher-question::app.submit_confirm')"
                      data-mindigo-confirm-text="@lang('teacher-question::app.submit_review')"
                      data-mindigo-confirm-cancel="{{ __('teacher-question::app.cancel') }}"
                      data-mindigo-confirm-type="info">
                    @csrf
                    <button type="submit"
                            class="inline-flex h-9 items-center gap-2 rounded-full bg-green-600 px-4 text-xs font-black text-white shadow-sm transition hover:bg-green-500">
                        <x-heroicon-o-arrow-up-tray class="h-4 w-4" />@lang('teacher-question::app.submit_review')
                    </button>
                </form>
            @endif
            <form method="POST" action="{{ route('teacher.questions.destroy', $question) }}"
                  data-mindigo-confirm-title="@lang('teacher-question::app.delete_title')"
                  data-mindigo-confirm-message="@lang('teacher-question::app.delete_confirm')"
                  data-mindigo-confirm-text="@lang('teacher-question::app.delete')"
                  data-mindigo-confirm-cancel="{{ __('teacher-question::app.cancel') }}"
                  data-mindigo-confirm-type="danger">
                @csrf @method('DELETE')
                <button type="submit"
                        class="inline-flex h-9 items-center gap-2 rounded-full border border-red-200 bg-red-50 px-4 text-xs font-black text-red-600 transition hover:bg-red-100">
                    <x-heroicon-o-trash class="h-4 w-4" />@lang('teacher-question::app.delete')
                </button>
            </form>
        </div>
    </header>

    <div class="mx-auto w-full max-w-3xl flex-1 space-y-4 p-6">

        {{-- Status + meta row --}}
        <div class="flex flex-wrap items-center gap-2">
            @php
                $statusColors = ['approved'=>'bg-green-100 text-green-700','reviewing'=>'bg-amber-100 text-amber-700','draft'=>'bg-slate-100 text-slate-600','rejected'=>'bg-red-100 text-red-700'];
                $diffColors = ['easy'=>'bg-emerald-100 text-emerald-700','medium'=>'bg-amber-100 text-amber-600','hard'=>'bg-red-100 text-red-700'];
            @endphp
            <span class="rounded-full px-3 py-1 text-xs font-black {{ $statusColors[$question->status] ?? 'bg-slate-100 text-slate-500' }}">
                @lang('teacher-question::app.' . $question->status)
            </span>
            <span class="rounded-full px-3 py-1 text-xs font-black {{ $diffColors[$question->difficulty] ?? 'bg-slate-100 text-slate-500' }}">
                @lang('teacher-question::app.' . $question->difficulty)
            </span>
            @if($question->subject)
                <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-black text-slate-600">{{ $question->subject }}</span>
            @endif
            @if($question->topic)
                <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-bold text-slate-500">{{ $question->topic }}</span>
            @endif
        </div>

        {{-- Content --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="mb-3 text-xs font-black uppercase tracking-wider text-slate-400">@lang('teacher-question::app.question_content')</p>
            <div class="prose prose-sm max-w-none text-slate-800">{!! nl2br(e($question->content)) !!}</div>

            @if($question->options && count($question->options) > 0)
                <div class="mt-4 space-y-2">
                    @foreach($question->options as $opt)
                        @php
                            $key = $opt['key'] ?? '';
                            $text = $opt['text'] ?? '';
                            $isCorrect = in_array($key, $question->correct_answers ?? []);
                        @endphp
                        <div class="flex items-start gap-3 rounded-2xl px-3 py-2.5 {{ $isCorrect ? 'bg-green-50 ring-1 ring-green-200' : 'bg-slate-50' }}">
                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full text-xs font-black {{ $isCorrect ? 'bg-green-600 text-white' : 'bg-white text-slate-500 ring-1 ring-slate-200' }}">{{ $key }}</span>
                            <span class="text-sm font-bold {{ $isCorrect ? 'text-green-800' : 'text-slate-700' }}">{{ $text }}</span>
                            @if($isCorrect)
                                <x-heroicon-o-check class="ml-auto h-4 w-4 shrink-0 text-green-600" />
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Explanation --}}
        @if($question->explanation)
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="mb-2 text-xs font-black uppercase tracking-wider text-slate-400">@lang('teacher-question::app.explanation')</p>
                <p class="text-sm font-semibold leading-relaxed text-slate-600">{{ $question->explanation }}</p>
            </div>
        @endif

        {{-- Tags --}}
        @if($question->tags && count($question->tags) > 0)
            <div class="flex flex-wrap gap-2">
                @foreach($question->tags as $tag)
                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-black text-slate-600">#{{ $tag }}</span>
                @endforeach
            </div>
        @endif

    </div>
</div>
@endsection
