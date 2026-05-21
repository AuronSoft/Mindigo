@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-question-bank::app.question_detail'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Mindigo/QuestionBank/src/resources/css/app.css',
        'packages/Mindigo/QuestionBank/src/resources/js/app.js',
    ])
@endsection

@section('content')
    <div class="question-page mx-auto flex max-w-6xl flex-col gap-6">
        <header class="question-hero">
            <div class="min-w-0">
                <div class="question-breadcrumb">
                    <a href="{{ route('dashboard') }}">@lang('Mindigo-dashboard::app.dashboard')</a>
                    <span>/</span>
                    <a href="{{ route('question-bank.index') }}">@lang('Mindigo-question-bank::app.breadcrumb')</a>
                    <span>/</span>
                    <strong>#{{ $question->id }}</strong>
                </div>
                <h1>@lang('Mindigo-question-bank::app.question_detail')</h1>
                <p>{{ $question->folder?->name ?: __('Mindigo-question-bank::app.no_folder') }} / {{ $question->subject }}{{ $question->topic ? ' / ' . $question->topic : '' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('question-bank.edit', $question) }}" class="question-primary-button">@lang('Mindigo-question-bank::app.edit')</a>
                <a href="{{ route('question-bank.index') }}" class="question-secondary-button">@lang('Mindigo-question-bank::app.back')</a>
            </div>
        </header>

        <section class="grid gap-5 lg:grid-cols-[1.35fr_0.75fr]">
            <article class="question-card p-5">
                <div class="flex flex-wrap gap-2">
                    <span class="question-badge question-status-{{ $question->status }}">@lang('Mindigo-question-bank::app.statuses.' . $question->status)</span>
                    <span class="question-badge question-type">@lang('Mindigo-question-bank::app.types.' . $question->type)</span>
                    <span class="question-badge question-difficulty-{{ $question->difficulty }}">@lang('Mindigo-question-bank::app.difficulties.' . $question->difficulty)</span>
                </div>
                <div class="mt-5 rounded-2xl bg-slate-50 p-5 text-base font-bold leading-8 text-slate-900">{{ $question->content }}</div>

                @if($question->options)
                    <div class="mt-5 grid gap-3">
                        @foreach($question->options as $option)
                            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700">{{ $option }}</div>
                        @endforeach
                    </div>
                @endif

                @if($question->correct_answers)
                    <div class="mt-5 rounded-2xl border border-green-100 bg-green-50 p-4">
                        <p class="text-xs font-black uppercase tracking-wide text-green-700">@lang('Mindigo-question-bank::app.correct_answers')</p>
                        <p class="mt-2 text-sm font-black text-green-900">{{ implode(', ', $question->correct_answers) }}</p>
                    </div>
                @endif

                @if($question->explanation)
                    <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 text-sm font-semibold leading-6 text-slate-600">{{ $question->explanation }}</div>
                @endif
            </article>

            <aside class="flex flex-col gap-5">
                <article class="question-card p-5">
                    <h2 class="text-lg font-black text-slate-950">@lang('Mindigo-question-bank::app.metadata')</h2>
                    <dl class="mt-4 grid gap-3">
                        <div class="question-detail-row"><dt>@lang('Mindigo-question-bank::app.creator')</dt><dd>{{ $question->creator?->name ?: '-' }}</dd></div>
                        <div class="question-detail-row"><dt>@lang('Mindigo-question-bank::app.reviewer')</dt><dd>{{ $question->reviewer?->name ?: '-' }}</dd></div>
                        <div class="question-detail-row"><dt>@lang('Mindigo-question-bank::app.updated_at')</dt><dd>{{ $question->updated_at?->format('d/m/Y H:i') }}</dd></div>
                    </dl>
                </article>

                @if(auth()->user()?->hasPermissionTo('questions.review'))
                    <form method="POST" action="{{ route('question-bank.review', $question) }}" class="question-card p-5" data-mindigo-confirm-title="@lang('Mindigo-question-bank::app.confirm_review_title')" data-mindigo-confirm-message="@lang('Mindigo-question-bank::app.confirm_review_message')" data-mindigo-confirm-text="@lang('Mindigo-question-bank::app.save_review')" data-mindigo-confirm-cancel="@lang('Mindigo-question-bank::app.cancel')">
                        @csrf
                        <label class="question-field">
                            <span>@lang('Mindigo-question-bank::app.review_status')</span>
                            <select name="status" class="question-select">
                                @foreach(['approved', 'rejected', 'reviewing'] as $status)
                                    <option value="{{ $status }}" @selected($question->status === $status)>@lang('Mindigo-question-bank::app.statuses.' . $status)</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="question-field mt-4">
                            <span>@lang('Mindigo-question-bank::app.review_note')</span>
                            <textarea name="review_note" class="question-textarea">{{ old('review_note', $question->review_note) }}</textarea>
                        </label>
                        <button type="submit" class="question-primary-button mt-4 w-full">@lang('Mindigo-question-bank::app.save_review')</button>
                    </form>
                @endif
            </aside>
        </section>
    </div>
@endsection
