@extends('Mindigo-dashboard::layouts')
@section('title', $deck->title . ' · Mindigo LMS')
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection
@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    @include('learning-tools::partials.header', ['eyebrow' => __('learning-tools::app.flashcards.title'), 'title' => $deck->title, 'subtitle' => $deck->description ?: __('learning-tools::app.flashcards.detail_subtitle')])
    <div class="grid gap-5 p-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <section>
            <div class="flex items-center justify-between"><h2 class="font-black text-slate-900">{{ trans_choice('learning-tools::app.flashcards.card_count', $deck->cards->count(), ['count' => $deck->cards->count()]) }}</h2>@if($deck->cards->isNotEmpty())<a href="{{ route('learning-tools.flashcards.study', $deck) }}" class="inline-flex h-10 items-center rounded-full bg-green-600 px-5 text-sm font-black text-white no-underline">@lang('learning-tools::app.flashcards.study')</a>@endif</div>
            <div class="mt-4 space-y-3">@forelse($deck->cards as $card)<article class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-2"><div><p class="text-[10px] font-black uppercase text-slate-400">@lang('learning-tools::app.flashcards.front')</p><p class="mt-2 whitespace-pre-wrap text-sm font-bold text-slate-800">{{ $card->front }}</p></div><div class="border-t border-slate-100 pt-4 sm:border-l sm:border-t-0 sm:pl-4 sm:pt-0"><p class="text-[10px] font-black uppercase text-slate-400">@lang('learning-tools::app.flashcards.back')</p><p class="mt-2 whitespace-pre-wrap text-sm font-bold text-slate-800">{{ $card->back }}</p></div>@if(auth()->id() === $deck->owner_id)<form method="POST" action="{{ route('learning-tools.flashcards.cards.destroy', [$deck, $card]) }}" class="sm:col-span-2 text-right">@csrf @method('DELETE')<button class="text-xs font-black text-red-600">@lang('learning-tools::app.actions.delete')</button></form>@endif</article>@empty<div class="rounded-3xl border border-dashed border-slate-300 bg-white py-12 text-center font-black text-slate-500">@lang('learning-tools::app.flashcards.empty_cards')</div>@endforelse</div>
        </section>
        <aside class="space-y-4">
            @if(auth()->id() === $deck->owner_id)
                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-black text-slate-900">@lang('learning-tools::app.flashcards.add_card')</h2><form method="POST" action="{{ route('learning-tools.flashcards.cards.store', $deck) }}" class="mt-4 space-y-4">@csrf<label class="block"><span class="text-xs font-black text-slate-500">@lang('learning-tools::app.flashcards.front')</span><textarea name="front" required rows="4" class="mt-2 w-full rounded-xl border border-slate-200 p-3 text-sm font-semibold"></textarea></label><label class="block"><span class="text-xs font-black text-slate-500">@lang('learning-tools::app.flashcards.back')</span><textarea name="back" required rows="5" class="mt-2 w-full rounded-xl border border-slate-200 p-3 text-sm font-semibold"></textarea></label><button class="h-10 w-full rounded-full bg-green-600 text-sm font-black text-white">@lang('learning-tools::app.flashcards.add_card')</button></form></section>
                <a href="{{ route('learning-tools.flashcards.edit', $deck) }}" class="inline-flex h-10 w-full items-center justify-center rounded-full border border-slate-200 bg-white text-sm font-black text-slate-700 no-underline">@lang('learning-tools::app.actions.edit')</a>
            @endif
        </aside>
    </div>
</div>
@endsection
