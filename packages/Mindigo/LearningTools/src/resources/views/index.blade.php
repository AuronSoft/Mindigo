@extends('Mindigo-dashboard::layouts')
@section('title', __('learning-tools::app.meta_title'))
@section('meta_description', __('learning-tools::app.subtitle'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
<main class="min-h-screen bg-slate-50">
    <header class="border-b border-slate-200 bg-white px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-widest text-green-600">@lang('learning-tools::app.eyebrow')</p>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">@lang('learning-tools::app.title')</h1>
                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">@lang('learning-tools::app.subtitle')</p>
            </div>
            <div class="inline-flex w-fit items-center gap-2 rounded-full border border-green-100 bg-green-50 px-4 py-2 text-xs font-black text-green-700">
                <x-heroicon-o-shield-check class="h-4 w-4" />
                @lang('learning-tools::app.role_access.' . auth()->user()->role)
            </div>
        </div>
    </header>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('learning-tools.index') }}" class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <label class="relative min-w-0 flex-1">
                    <span class="sr-only">@lang('learning-tools::app.search_label')</span>
                    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                    <input type="search" name="q" value="{{ $search }}"
                        placeholder="{{ __('learning-tools::app.search_placeholder') }}"
                        class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 pl-12 pr-4 text-sm font-bold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-green-400 focus:bg-white focus:ring-4 focus:ring-green-50">
                </label>
                <div class="flex flex-wrap gap-2" aria-label="{{ __('learning-tools::app.category_label') }}">
                    @foreach($categories as $item)
                        <button type="submit" name="category" value="{{ $item }}"
                            class="h-10 rounded-full px-4 text-xs font-black transition {{ $category === $item ? 'bg-green-600 text-white shadow-sm shadow-green-200' : 'border border-slate-200 bg-white text-slate-600 hover:border-green-200 hover:bg-green-50 hover:text-green-700' }}">
                            {{ __('learning-tools::app.categories.' . $item) }}
                        </button>
                    @endforeach
                </div>
            </div>
        </form>

        <div class="mt-6 flex items-center justify-between gap-4">
            <h2 class="text-lg font-black text-slate-900">@lang('learning-tools::app.available_title')</h2>
            <span class="text-xs font-black uppercase tracking-wider text-slate-400">
                {{ trans_choice('learning-tools::app.result_count', $tools->count(), ['count' => $tools->count()]) }}
            </span>
        </div>

        @if($tools->isEmpty())
            <section class="mt-4 rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                <span class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                    <x-heroicon-o-magnifying-glass class="h-8 w-8" />
                </span>
                <h2 class="mt-4 text-lg font-black text-slate-800">@lang('learning-tools::app.empty_title')</h2>
                <p class="mt-1 text-sm font-semibold text-slate-500">@lang('learning-tools::app.empty_description')</p>
                <a href="{{ route('learning-tools.index') }}" class="mt-5 inline-flex h-10 items-center rounded-full bg-green-600 px-5 text-sm font-black text-white no-underline transition hover:bg-green-500">
                    @lang('learning-tools::app.clear_filters')
                </a>
            </section>
        @else
            <section class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($tools as $tool)
                    <article class="group flex min-h-64 flex-col rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-green-200 hover:shadow-md">
                        <div class="flex items-start justify-between gap-4">
                            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-green-50 text-green-700 transition group-hover:bg-green-600 group-hover:text-white">
                                <x-dynamic-component :component="$tool['icon']" class="h-6 w-6" />
                            </span>
                            <span class="rounded-full bg-amber-50 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-amber-700">
                                @lang('learning-tools::app.statuses.' . $tool['status'])
                            </span>
                        </div>
                        <h3 class="mt-5 text-lg font-black text-slate-900">{{ $tool['name'] }}</h3>
                        <p class="mt-2 flex-1 text-sm font-semibold leading-6 text-slate-500">{{ $tool['description'] }}</p>
                        <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                            <span class="text-xs font-black text-slate-400">{{ __('learning-tools::app.categories.' . $tool['category']) }}</span>
                            <span class="inline-flex items-center gap-1.5 text-xs font-black text-slate-400">
                                <x-heroicon-o-lock-closed class="h-4 w-4" />
                                @lang('learning-tools::app.coming_soon')
                            </span>
                        </div>
                    </article>
                @endforeach
            </section>
        @endif
    </div>
</main>
@endsection
