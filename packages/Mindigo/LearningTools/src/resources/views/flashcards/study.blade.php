@extends('Mindigo-dashboard::layouts')
@section('title', __('learning-tools::app.flashcards.study') . ' · ' . $deck->title)
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection
@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    @include('learning-tools::partials.header', ['eyebrow' => __('learning-tools::app.flashcards.study'), 'title' => $deck->title, 'subtitle' => __('learning-tools::app.flashcards.study_subtitle')])
    <div class="mx-auto w-full max-w-4xl p-6">
        <div class="space-y-5">
            @forelse($deck->cards as $card)
                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm" data-study-card>
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">@lang('learning-tools::app.flashcards.question')</p>
                    <p class="mt-4 whitespace-pre-wrap text-xl font-black text-slate-900">{{ $card->front }}</p>
                    <details class="mt-6 rounded-2xl bg-green-50 p-5"><summary class="cursor-pointer text-sm font-black text-green-700">@lang('learning-tools::app.flashcards.reveal')</summary><p class="mt-4 whitespace-pre-wrap text-sm font-bold leading-7 text-slate-700">{{ $card->back }}</p></details>
                    <form method="POST" action="{{ route('learning-tools.flashcards.review', [$deck, $card]) }}" class="mt-5 grid grid-cols-2 gap-2 sm:grid-cols-4">@csrf @foreach(['again', 'hard', 'good', 'easy'] as $rating)<button name="rating" value="{{ $rating }}" class="h-10 rounded-full border border-slate-200 bg-white text-xs font-black text-slate-600 hover:border-green-300 hover:text-green-700">@lang('learning-tools::app.flashcards.ratings.' . $rating)</button>@endforeach</form>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white py-16 text-center font-black text-slate-500">@lang('learning-tools::app.flashcards.empty_cards')</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
