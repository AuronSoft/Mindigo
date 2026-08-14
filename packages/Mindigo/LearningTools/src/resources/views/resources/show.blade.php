@extends('Mindigo-dashboard::layouts')
@section('title', $resource->title . ' · Auronsoft LMS')
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    @include('learning-tools::partials.header', [
        'eyebrow' => __('learning-tools::app.resources.title'),
        'title' => $resource->title,
        'subtitle' => $resource->summary ?: __('learning-tools::app.resources.detail_subtitle'),
    ])
    <div class="grid gap-5 p-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="whitespace-pre-wrap text-sm font-semibold leading-7 text-slate-700">{{ $resource->content }}</div>
        </article>
        <aside class="space-y-4">
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <dl class="space-y-4 text-sm">
                    <div><dt class="text-xs font-black uppercase tracking-wider text-slate-400">@lang('learning-tools::app.fields.subject')</dt><dd class="mt-1 font-black text-slate-700">{{ $resource->subject?->name ?? __('learning-tools::app.resources.general') }}</dd></div>
                    <div><dt class="text-xs font-black uppercase tracking-wider text-slate-400">@lang('learning-tools::app.resources.author')</dt><dd class="mt-1 font-black text-slate-700">{{ $resource->author?->name ?? __('learning-tools::app.resources.system') }}</dd></div>
                </dl>
                <form method="POST" action="{{ route('learning-tools.resources.favorite', $resource) }}" class="mt-5">
                    @csrf
                    <button class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-full {{ $isFavorite ? 'bg-amber-100 text-amber-700' : 'bg-green-600 text-white' }} text-sm font-black">
                        <x-heroicon-o-bookmark class="h-4 w-4" />
                        {{ $isFavorite ? __('learning-tools::app.resources.unfavorite') : __('learning-tools::app.resources.favorite') }}
                    </button>
                </form>
            </section>
            @if(auth()->user()->role === 'admin' || auth()->id() === $resource->author_id)
                <a href="{{ route('learning-tools.resources.edit', $resource) }}" class="inline-flex h-10 w-full items-center justify-center rounded-full border border-slate-200 bg-white text-sm font-black text-slate-700 no-underline hover:border-green-200 hover:text-green-700">@lang('learning-tools::app.actions.edit')</a>
            @endif
        </aside>
    </div>
</div>
@endsection
