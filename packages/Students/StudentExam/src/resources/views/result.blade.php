@extends('Mindigo-dashboard::layouts')
@section('title', __('student-exam::app.result') . ' · ' . $attempt->exam->title)

@section('content')
<div class="max-w-3xl mx-auto py-10 px-6">
    <div class="text-center mb-10">
        <div class="inline-flex h-24 w-24 rounded-full bg-gradient-to-br from-green-400 to-emerald-600 items-center justify-center text-white mb-6 shadow-lg">
            <x-heroicon-o-trophy class="h-12 w-12" />
        </div>
        <h1 class="text-4xl font-black text-slate-900">{{ $percentage }}%</h1>
        <p class="text-xl font-bold mt-1 {{ $passed ? 'text-green-600' : 'text-red-600' }}">
            {{ $passed ? __('student-exam::app.passed') : __('student-exam::app.failed') }}
        </p>
    </div>

    <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm mb-8">
        <div class="grid grid-cols-3 gap-6 text-center">
            <div>
                <p class="text-sm text-slate-400">@lang('student-exam::app.score')</p>
                <p class="text-3xl font-black">{{ $score }} / {{ $max_score }}</p>
            </div>
            <div>
                <p class="text-sm text-slate-400">@lang('student-exam::app.time_taken')</p>
                <p class="text-3xl font-black">{{ $attempt->started_at?->diffForHumans($attempt->submitted_at, ['short' => true]) }}</p>
            </div>
            <div>
                <p class="text-sm text-slate-400">@lang('student-exam::app.attempts')</p>
                <p class="text-3xl font-black">{{ $attempt->exam->max_attempts }}</p>
            </div>
        </div>
    </div>

    @if($show_review)
        <h2 class="text-lg font-black mb-5">@lang('student-exam::app.review')</h2>
        @foreach($questions as $q)
            @include('student-exam::partials.question-item', [
                'question' => $q, 
                'index' => $loop->index,
                'saved' => $answers[$q->id] ?? null
            ])
        @endforeach
    @else
        <div class="text-center py-12 text-slate-400">
            @lang('student-exam::app.review_not_available')
        </div>
    @endif
</div>
@endsection