@extends('core::layouts.home')

@php
    $terms = __('core::terms');
@endphp

@section('content')
<div class="min-h-screen bg-slate-50 text-slate-800">
    <header class="relative overflow-hidden bg-emerald-950">
        <div class="absolute inset-0 opacity-20">
            <img
                src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1800&q=85"
                alt=""
                class="h-full w-full object-cover"
            >
        </div>
        <div class="absolute inset-0 bg-gradient-to-r from-emerald-950 via-emerald-900/95 to-emerald-700/80"></div>
        <div class="absolute -right-24 bottom-[-38%] h-96 w-[52rem] rounded-[100%] bg-white/18 rotate-[-7deg]"></div>

        <div class="relative mx-auto flex max-w-7xl items-center justify-between px-6 py-5 lg:px-10">
            <a href="{{ route('home', [], false) }}" class="flex items-center gap-2">
                <svg width="38" height="38" viewBox="0 0 200 220" fill="none" aria-hidden="true">
                    <path d="M48 160 L22 148 L38 158 L16 152 L35 164" fill="#15803d" stroke="#14532d" stroke-width="1"/>
                    <circle cx="105" cy="145" r="90" fill="#22c55e" stroke="#14532d" stroke-width="3"/>
                    <ellipse cx="115" cy="185" rx="55" ry="38" fill="#86efac" stroke="#14532d" stroke-width="2"/>
                    <path d="M95 58 Q85 20 105 8 Q118 22 112 58" fill="#16a34a" stroke="#14532d" stroke-width="2.5" stroke-linejoin="round"/>
                    <path d="M108 55 Q100 18 118 10 Q128 26 120 56" fill="#22c55e" stroke="#14532d" stroke-width="2" stroke-linejoin="round"/>
                    <path d="M52 118 L95 108 L88 128 Z" fill="#14532d"/>
                    <path d="M148 118 L108 108 L114 128 Z" fill="#14532d"/>
                    <circle cx="82" cy="135" r="20" fill="white" stroke="#14532d" stroke-width="2"/>
                    <circle cx="86" cy="138" r="12" fill="#14532d"/>
                    <circle cx="128" cy="135" r="20" fill="white" stroke="#14532d" stroke-width="2"/>
                    <circle cx="132" cy="138" r="12" fill="#14532d"/>
                    <path d="M85 158 Q105 148 130 158 L118 175 Q105 180 92 175 Z" fill="#f59e0b" stroke="#14532d" stroke-width="2"/>
                </svg>
                <span class="text-xl font-black tracking-tight text-white">mindigo</span>
            </a>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-1 rounded-xl bg-white/10 p-1 ring-1 ring-white/20">
                    <a href="{{ route('lang.switch', ['locale' => 'vi'], false) }}"
                       class="rounded-lg px-3 py-1.5 text-xs font-black transition {{ app()->getLocale() === 'vi' ? 'bg-white text-emerald-700' : 'text-white/70 hover:text-white' }}">
                        VI
                    </a>
                    <a href="{{ route('lang.switch', ['locale' => 'en'], false) }}"
                       class="rounded-lg px-3 py-1.5 text-xs font-black transition {{ app()->getLocale() === 'en' ? 'bg-white text-emerald-700' : 'text-white/70 hover:text-white' }}">
                        EN
                    </a>
                </div>
                <a href="{{ route('login', [], false) }}" class="rounded-xl bg-white px-5 py-2.5 text-sm font-black text-emerald-700 shadow-[0_4px_0_rgba(20,83,45,0.35)] transition hover:-translate-y-0.5 hover:bg-emerald-50">
                    {{ __('core::app.navbar.login') }}
                </a>
            </div>
        </div>

        <div class="relative mx-auto max-w-7xl px-6 pb-20 pt-16 lg:px-10 lg:pb-24">
            <div class="max-w-4xl">
                <span class="inline-flex rounded-full border border-white/30 bg-white/10 px-4 py-2 text-xs font-black uppercase tracking-[0.18em] text-white">
                    {{ $terms['hero']['badge'] }}
                </span>
                <h1 class="mt-8 text-5xl font-black leading-tight text-white md:text-7xl">
                    {{ $terms['hero']['title'] }}
                </h1>
                <p class="mt-6 max-w-3xl text-lg font-semibold leading-8 text-emerald-50">
                    {{ $terms['hero']['description'] }}
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <span class="rounded-lg border border-white/25 bg-white/10 px-4 py-3 text-sm font-black text-white">{{ $terms['meta']['effective_date'] }}</span>
                    <span class="rounded-lg border border-white/25 bg-white/10 px-4 py-3 text-sm font-black text-white">{{ $terms['meta']['version'] }}</span>
                    <span class="rounded-lg border border-white/25 bg-white/10 px-4 py-3 text-sm font-black text-white">{{ $terms['meta']['audience'] }}</span>
                </div>
            </div>
        </div>
    </header>

    <main class="mx-auto grid max-w-7xl gap-8 px-6 py-12 lg:grid-cols-[320px_1fr] lg:px-10">
        <aside class="lg:sticky lg:top-6 lg:self-start">
            <nav class="max-h-[calc(100vh-3rem)] overflow-auto rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="mb-4 text-sm font-black uppercase tracking-[0.14em] text-emerald-950">{{ $terms['toc_title'] }}</p>
                <div class="space-y-1">
                    @foreach($terms['sections'] as $section)
                        <a href="#{{ $section['id'] }}" class="block rounded-md px-3 py-2.5 text-sm font-bold leading-5 text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-700">
                            {{ $section['title'] }}
                        </a>
                    @endforeach
                </div>
            </nav>
        </aside>

        <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm md:p-10">
            <div class="rounded-lg border border-emerald-200 bg-emerald-50/70 p-6 text-base leading-8 text-slate-700">
                @foreach($terms['intro'] as $paragraph)
                    <p @class(['mt-4' => ! $loop->first])>{{ $paragraph }}</p>
                @endforeach
            </div>

            @foreach($terms['sections'] as $section)
                <section id="{{ $section['id'] }}" class="legal-section">
                    <h2>{{ $section['title'] }}</h2>
                    @foreach($section['paragraphs'] ?? [] as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                    @if(! empty($section['items']))
                        <ul>
                            @foreach($section['items'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if(! empty($section['cards']))
                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            @foreach($section['cards'] as $card)
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                                    <p class="text-sm font-black text-slate-900">{{ $card['title'] }}</p>
                                    <p class="mt-2 text-sm text-slate-600">{{ $card['body'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endforeach
        </article>
    </main>

    <footer class="border-t border-slate-200 bg-white px-6 py-8 lg:px-10">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
            <p>{{ $terms['footer']['copyright'] }}</p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('home', [], false) }}" class="font-bold text-slate-600 hover:text-emerald-700">{{ $terms['footer']['home'] }}</a>
                <a href="{{ route('login', [], false) }}" class="font-bold text-slate-600 hover:text-emerald-700">{{ __('core::app.navbar.login') }}</a>
            </div>
        </div>
    </footer>
</div>

<style>
    html {
        scroll-behavior: smooth;
    }

    .legal-section {
        border-top: 1px solid rgb(226 232 240);
        margin-top: 2rem;
        padding-top: 2rem;
        scroll-margin-top: 2rem;
    }

    .legal-section h2 {
        color: rgb(15 23 42);
        font-size: 1.5rem;
        font-weight: 900;
        line-height: 1.3;
        margin-bottom: 1rem;
    }

    .legal-section p {
        color: rgb(51 65 85);
        font-size: 1rem;
        line-height: 1.9;
        margin-top: 1rem;
    }

    .legal-section ul {
        color: rgb(51 65 85);
        line-height: 1.8;
        list-style: disc;
        margin-top: 1rem;
        padding-left: 1.25rem;
    }

    .legal-section li + li {
        margin-top: 0.5rem;
    }
</style>
@endsection
