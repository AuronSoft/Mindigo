@extends('core::layouts.home')

@php
    $categories = trans('core::exam_tips.categories');
    $featuredPosts = collect($posts)->where('featured', true);
    $regularPosts = collect($posts)->where('featured', false);
    $categoryClasses = collect($categories)->keyBy('id');
    $examTipUser = auth()->user();
    $examTipUserLabel = $examTipUser ? ($examTipUser->name ?: $examTipUser->email) : null;
    $examTipUserInitial = $examTipUserLabel ? \Illuminate\Support\Str::of($examTipUserLabel)->trim()->substr(0, 1)->upper() : 'U';
    $examTipLoginReturnUrl = route('login', ['redirect' => route('exam-tips', [], false)], false);
@endphp

@section('content')
<div class="min-h-screen bg-white text-gray-900" x-data="{ open: false, shareOpen: @json($errors->any() || session()->has('exam_tip_shared')), shared: @json(session()->has('exam_tip_shared')) }" @keydown.escape.window="shareOpen = false" style="font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;">
    <style>
        .exam-tip-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .exam-tip-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .exam-tip-scrollbar::-webkit-scrollbar {
            display: none;
        }
    </style>

    <header class="sticky top-0 z-50 border-b border-gray-100 bg-white/95 shadow-sm backdrop-blur">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <div class="flex h-16 items-center justify-between gap-4">
                <a href="{{ route('home', [], false) }}" class="flex items-center gap-3 no-underline">
                    <span class="flex h-9 w-9 items-center justify-center">
                        <svg class="h-9 w-9" viewBox="0 0 200 220" fill="none" aria-hidden="true">
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
                    </span>
                    <span class="text-lg font-black tracking-tight text-green-600">Mindigo</span>
                </a>

                <label class="relative hidden flex-1 md:block md:max-w-md">
                    <span class="sr-only">@lang('core::exam_tips.nav.search_placeholder')</span>
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3m1.8-5.2a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                    </svg>
                    <input type="search" data-exam-tip-search placeholder="@lang('core::exam_tips.nav.search_placeholder')" class="w-full rounded-xl border border-gray-200 bg-gray-100 py-2.5 pl-10 pr-4 text-sm font-semibold text-gray-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-100">
                </label>

                <div class="hidden items-center gap-3 md:flex">
                    <div class="flex items-center gap-1 rounded-xl bg-gray-100 p-1">
                        <a href="{{ route('lang.switch', ['locale' => 'vi'], false) }}" class="rounded-lg px-3 py-1.5 text-xs font-black transition {{ app()->getLocale() === 'vi' ? 'bg-white text-green-600 shadow-sm' : 'text-gray-400 hover:text-gray-700' }}">VI</a>
                        <a href="{{ route('lang.switch', ['locale' => 'en'], false) }}" class="rounded-lg px-3 py-1.5 text-xs font-black transition {{ app()->getLocale() === 'en' ? 'bg-white text-green-600 shadow-sm' : 'text-gray-400 hover:text-gray-700' }}">EN</a>
                    </div>
                    @auth
                        <button type="button" data-exam-tip-share-action @click="shareOpen = true; shared = false" class="inline-flex items-center gap-2 rounded-xl bg-green-500 px-5 py-2.5 text-sm font-black text-white shadow-[0_4px_0_#15803d] transition-all hover:translate-y-0.5 hover:bg-green-400 hover:shadow-[0_2px_0_#15803d] active:translate-y-1 active:shadow-none">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z" />
                            </svg>
                            @lang('core::exam_tips.nav.share')
                        </button>
                    @else
                        <a href="{{ $examTipLoginReturnUrl }}" data-exam-tip-share-login title="@lang('core::exam_tips.actions.login_to_share')" class="inline-flex items-center gap-2 rounded-xl bg-green-500 px-5 py-2.5 text-sm font-black text-white shadow-[0_4px_0_#15803d] transition-all hover:translate-y-0.5 hover:bg-green-400 hover:shadow-[0_2px_0_#15803d] active:translate-y-1 active:shadow-none">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z" />
                            </svg>
                            @lang('core::exam_tips.nav.share')
                        </a>
                    @endauth
                    @auth
                        <a href="{{ $accountUrl }}" data-exam-tip-user-menu title="{{ $examTipUserLabel }}" class="inline-flex h-11 w-11 items-center justify-center overflow-hidden rounded-full border-2 border-green-100 bg-green-50 text-sm font-black text-green-700 no-underline shadow-sm transition hover:border-green-200 hover:bg-green-100">
                            @if($examTipUser->avatar)
                                <img src="{{ $examTipUser->avatar_url }}" alt="{{ $examTipUserLabel }}" class="h-full w-full object-cover">
                            @else
                                <span aria-hidden="true">{{ $examTipUserInitial }}</span>
                            @endif
                            <span class="sr-only">{{ $examTipUserLabel }}</span>
                        </a>
                    @else
                        <a href="{{ route('login', [], false) }}" data-exam-tip-login-link class="inline-flex h-11 items-center justify-center rounded-xl border border-green-200 bg-white px-4 text-sm font-black text-green-700 no-underline shadow-sm transition hover:border-green-300 hover:bg-green-50">
                            @lang('core::exam_tips.nav.login')
                        </a>
                    @endauth
                </div>

                <button type="button" class="rounded-lg p-2 text-gray-500 md:hidden" @click="open = ! open" aria-label="Menu">
                    <svg x-show="!open" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                    <svg x-show="open" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>

            <div class="space-y-3 border-t border-gray-100 py-3 md:hidden" x-show="open" x-cloak>
                <label class="relative block">
                    <span class="sr-only">@lang('core::exam_tips.nav.search_placeholder')</span>
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3m1.8-5.2a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                    </svg>
                    <input type="search" data-exam-tip-search placeholder="@lang('core::exam_tips.nav.search_placeholder')" class="w-full rounded-xl border border-gray-200 bg-gray-100 py-2.5 pl-10 pr-4 text-sm font-semibold outline-none focus:border-green-500 focus:ring-4 focus:ring-green-100">
                </label>
                @auth
                    <button type="button" data-exam-tip-share-action @click="shareOpen = true; shared = false; open = false" class="flex w-full items-center justify-center gap-2 rounded-xl bg-green-500 px-4 py-2.5 text-sm font-black text-white no-underline shadow-[0_4px_0_#15803d] transition-all hover:translate-y-0.5 hover:bg-green-400 hover:shadow-[0_2px_0_#15803d] active:translate-y-1 active:shadow-none">
                        @lang('core::exam_tips.nav.share')
                    </button>
                @else
                    <a href="{{ $examTipLoginReturnUrl }}" data-exam-tip-share-login title="@lang('core::exam_tips.actions.login_to_share')" class="flex items-center justify-center gap-2 rounded-xl bg-green-500 px-4 py-2.5 text-sm font-black text-white no-underline shadow-[0_4px_0_#15803d] transition-all hover:translate-y-0.5 hover:bg-green-400 hover:shadow-[0_2px_0_#15803d] active:translate-y-1 active:shadow-none">
                        @lang('core::exam_tips.nav.share')
                    </a>
                @endauth
                @auth
                    <a href="{{ $accountUrl }}" data-exam-tip-user-menu class="flex items-center gap-3 rounded-xl border border-green-100 bg-green-50 px-4 py-2.5 text-sm font-black text-green-700 no-underline transition hover:bg-green-100">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-green-600 text-xs text-white">
                            @if($examTipUser->avatar)
                                <img src="{{ $examTipUser->avatar_url }}" alt="{{ $examTipUserLabel }}" class="h-full w-full object-cover">
                            @else
                                <span aria-hidden="true">{{ $examTipUserInitial }}</span>
                            @endif
                        </span>
                        <span class="min-w-0 truncate">{{ $examTipUserLabel }}</span>
                    </a>
                @else
                    <a href="{{ route('login', [], false) }}" data-exam-tip-login-link class="flex items-center justify-center rounded-xl border border-green-200 bg-white px-4 py-2.5 text-sm font-black text-green-700 no-underline shadow-sm transition hover:border-green-300 hover:bg-green-50">
                        @lang('core::exam_tips.nav.login')
                    </a>
                @endauth
            </div>
        </div>
    </header>

    @auth
        <div x-show="shareOpen" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center overflow-y-auto bg-slate-950/55 px-4 py-6 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="exam-tip-share-title">
            <div class="my-auto w-full max-w-xl overflow-hidden rounded-2xl bg-white shadow-2xl" @click.outside="shareOpen = false">
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-green-600">@lang('core::exam_tips.share_modal.eyebrow')</p>
                        <h2 id="exam-tip-share-title" class="mt-1 text-xl font-black text-slate-950">@lang('core::exam_tips.share_modal.title')</h2>
                    </div>
                    <button type="button" @click="shareOpen = false" class="grid h-10 w-10 shrink-0 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" aria-label="@lang('core::exam_tips.share_modal.close')">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" /></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('exam-tips.store', [], false) }}" class="max-h-[calc(100vh-9rem)] space-y-4 overflow-y-auto px-5 py-5">
                    @csrf
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500">@lang('core::exam_tips.share_modal.post_title')</span>
                        <input type="text" name="title" value="{{ old('title') }}" required maxlength="120" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none transition focus:border-green-500 focus:bg-white focus:ring-4 focus:ring-green-100" placeholder="@lang('core::exam_tips.share_modal.post_title_placeholder')">
                        @error('title')
                            <span class="mt-1.5 block text-xs font-bold text-rose-600">{{ $message }}</span>
                        @enderror
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500">@lang('core::exam_tips.share_modal.category')</span>
                        <select name="category" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none transition focus:border-green-500 focus:bg-white focus:ring-4 focus:ring-green-100">
                            @foreach($categories as $category)
                                @if($category['id'] !== 'all')
                                    <option value="{{ $category['id'] }}" @selected(old('category') === $category['id'])>{{ $category['label'] }}</option>
                                @endif
                            @endforeach
                        </select>
                        @error('category')
                            <span class="mt-1.5 block text-xs font-bold text-rose-600">{{ $message }}</span>
                        @enderror
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500">@lang('core::exam_tips.share_modal.content')</span>
                        <textarea name="content" required rows="5" maxlength="5000" class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold leading-6 text-slate-900 outline-none transition focus:border-green-500 focus:bg-white focus:ring-4 focus:ring-green-100" placeholder="@lang('core::exam_tips.share_modal.content_placeholder')">{{ old('content') }}</textarea>
                        @error('content')
                            <span class="mt-1.5 block text-xs font-bold text-rose-600">{{ $message }}</span>
                        @enderror
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500">@lang('core::exam_tips.share_modal.tags')</span>
                        <input type="text" name="tags" value="{{ old('tags') }}" maxlength="255" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none transition focus:border-green-500 focus:bg-white focus:ring-4 focus:ring-green-100" placeholder="@lang('core::exam_tips.share_modal.tags_placeholder')">
                        @error('tags')
                            <span class="mt-1.5 block text-xs font-bold text-rose-600">{{ $message }}</span>
                        @enderror
                    </label>
                    <div x-show="shared" x-cloak class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-bold text-green-700">
                        @lang('core::exam_tips.share_modal.success')
                    </div>
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" @click="shareOpen = false" class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                            @lang('core::exam_tips.share_modal.cancel')
                        </button>
                        <button type="submit" class="rounded-xl bg-green-500 px-5 py-3 text-sm font-black text-white shadow-[0_4px_0_#15803d] transition-all hover:translate-y-0.5 hover:bg-green-400 hover:shadow-[0_2px_0_#15803d] active:translate-y-1 active:shadow-none">
                            @lang('core::exam_tips.share_modal.submit')
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endauth

    <section class="relative overflow-hidden bg-green-500 text-white">
        <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=1800&q=80" alt="" class="absolute inset-0 h-full w-full scale-105 object-cover opacity-45">
        <div class="absolute inset-0 bg-linear-to-r from-green-700/95 via-green-500/80 to-green-500/55"></div>
        <div class="absolute -bottom-16 right-0 h-48 w-2/3 rounded-tl-[80px] bg-white/10 blur-2xl"></div>
        <div class="relative mx-auto flex max-w-7xl flex-col gap-8 px-4 py-10 sm:px-6 md:flex-row md:items-center md:justify-between md:py-14">
            <div class="max-w-2xl drop-shadow-sm">
                <div class="mb-3 flex items-center gap-2">
                    <svg class="h-4 w-4 text-green-100" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M13.5 2.5c.38 2.22-.22 3.98-1.8 5.3-1.25 1.05-1.91 2.12-1.98 3.2-.75-.52-1.13-1.46-1.13-2.8C5.74 10.08 4.5 12.43 4.5 15.25 4.5 19 7.48 22 12 22s7.5-3 7.5-6.75c0-4.36-2.35-7.94-6-12.75Z" />
                    </svg>
                    <span class="text-xs font-black uppercase tracking-[0.22em] text-green-100">@lang('core::exam_tips.hero.eyebrow')</span>
                </div>
                <h1 class="mb-3 text-3xl font-black leading-tight tracking-tight md:text-5xl">
                    @lang('core::exam_tips.hero.title')
                    <span class="block text-green-100">@lang('core::exam_tips.hero.highlight')</span>
                </h1>
                <p class="max-w-xl text-base font-semibold leading-7 text-white/75">@lang('core::exam_tips.hero.description')</p>
            </div>

            <div class="grid grid-cols-3 gap-4 sm:gap-6">
                @foreach($stats as $stat)
                    <div class="text-center">
                        <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-xl bg-white/10">
                            <svg class="h-5 w-5 text-green-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75c-2.2-1.4-4.64-2.05-7.25-1.95A1.75 1.75 0 0 0 3 6.55v10.7A1.75 1.75 0 0 0 4.86 19c2.57-.13 4.96.52 7.14 1.95 2.18-1.43 4.57-2.08 7.14-1.95A1.75 1.75 0 0 0 21 17.25V6.55a1.75 1.75 0 0 0-1.75-1.75c-2.61-.1-5.05.55-7.25 1.95Zm0 0v14.2" />
                            </svg>
                        </div>
                        <div class="text-2xl font-black">{{ $stat['value'] }}</div>
                        <div class="mt-0.5 text-xs font-semibold text-white/60">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="sticky top-16 z-40 border-b border-gray-100 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <div class="exam-tip-scrollbar flex gap-2 overflow-x-auto py-3">
                @foreach($categories as $category)
                    <button type="button" data-exam-tip-category="{{ $category['id'] }}" class="shrink-0 rounded-xl border px-4 py-2 text-sm font-black transition-all {{ $loop->first ? 'border-green-500 bg-green-500 text-white shadow-[0_3px_0_#15803d]' : 'border-gray-200 bg-gray-100 text-gray-500 hover:bg-green-50 hover:text-green-600' }}">
                        {{ $category['label'] }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <div class="space-y-8 lg:col-span-2">
                <section data-exam-tip-section>
                    <div class="mb-4 flex items-center gap-2">
                        <svg class="h-4 w-4 text-green-600" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m12 2 2.94 6.03 6.66.95-4.82 4.69 1.14 6.63L12 17.18 6.08 20.3l1.14-6.63L2.4 8.98l6.66-.95L12 2Z" />
                        </svg>
                        <h2 class="text-base font-black">@lang('core::exam_tips.sections.featured')</h2>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @foreach($featuredPosts as $post)
                            @php $badgeClass = $categoryClasses[$post['category']]['class'] ?? 'bg-gray-100 text-gray-700'; @endphp
                            <article id="exam-tip-post-{{ $post['id'] }}" data-exam-tip-card data-featured="1" data-category="{{ $post['category'] }}" data-search="{{ e($post['title'] . ' ' . $post['excerpt'] . ' ' . implode(' ', $post['tags']) . ' ' . $post['category_label']) }}" class="group flex h-full min-h-69 flex-col rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-green-200 hover:shadow-md">
                                <div class="mb-3 flex items-start justify-between gap-3">
                                    <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-black {{ $badgeClass }}">{{ $post['category_label'] }}</span>
                                    <span class="shrink-0 text-xs font-bold text-gray-500">{{ $post['read_time'] }}</span>
                                </div>
                                <h3 class="exam-tip-clamp-2 mb-3 min-h-12 text-base font-black leading-snug text-gray-900 transition group-hover:text-green-600">{{ $post['title'] }}</h3>
                                <p class="exam-tip-clamp-3 mb-4 min-h-18 text-sm font-semibold leading-6 text-gray-500">{{ $post['excerpt'] }}</p>
                                <div class="mb-4 flex min-h-7 flex-wrap gap-1.5">
                                    @foreach($post['tags'] as $tag)
                                        <button type="button" data-exam-tip-tag="{{ $tag }}" class="rounded-lg bg-green-50 px-2 py-1 text-xs font-black text-green-700 transition hover:bg-green-100">#{{ $tag }}</button>
                                    @endforeach
                                </div>
                                <div class="mt-auto flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-black text-white {{ $post['avatar_class'] }}">{{ $post['avatar'] }}</span>
                                        <span>
                                            <span class="block text-xs font-black">{{ $post['author'] }}</span>
                                            <span class="block text-xs font-semibold text-gray-500">{{ $post['date'] }}</span>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs font-bold text-gray-500">
                                        <span class="flex items-center gap-1">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12 18.25 18.75 12 18.75 2.25 12 2.25 12Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></svg>
                                            {{ $post['views'] }}
                                        </span>
                                        <button type="button" data-exam-tip-like class="flex items-center gap-1 transition hover:text-green-600" data-count="{{ $post['likes'] }}">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z"/></svg>
                                            <span data-exam-tip-like-count>{{ $post['likes'] }}</span>
                                        </button>
                                        <a href="{{ $examTipLoginReturnUrl }}" title="@lang('core::exam_tips.actions.login_to_comment')" class="flex items-center gap-1 text-gray-500 no-underline transition hover:text-green-600">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5A8.48 8.48 0 0 1 21 11v.5Z"/></svg>
                                            {{ $post['comments'] }}
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section data-exam-tip-section>
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m22 7-8.5 8.5-5-5L2 17" /><path stroke-linecap="round" stroke-linejoin="round" d="M16 7h6v6" />
                            </svg>
                            <h2 class="text-base font-black">@lang('core::exam_tips.sections.latest')</h2>
                        </div>
                        <span class="text-sm font-bold text-gray-500" data-exam-tip-count>@lang('core::exam_tips.sections.article_count', ['count' => $regularPosts->count()])</span>
                    </div>
                    <div class="space-y-3">
                        @foreach($regularPosts as $post)
                            @php $badgeClass = $categoryClasses[$post['category']]['class'] ?? 'bg-gray-100 text-gray-700'; @endphp
                            <article id="exam-tip-post-{{ $post['id'] }}" data-exam-tip-card data-category="{{ $post['category'] }}" data-search="{{ e($post['title'] . ' ' . $post['excerpt'] . ' ' . implode(' ', $post['tags']) . ' ' . $post['category_label']) }}" class="group flex gap-4 rounded-xl border border-gray-100 bg-white px-5 py-4 shadow-sm transition hover:border-green-200 hover:shadow-md">
                                <div class="min-w-0 flex-1">
                                    <div class="mb-1.5 flex flex-wrap items-center gap-2">
                                        <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-black {{ $badgeClass }}">{{ $post['category_label'] }}</span>
                                        <span class="text-xs font-semibold text-gray-500">{{ $post['date'] }}</span>
                                    </div>
                                    <h3 class="mb-1.5 text-sm font-black leading-snug transition group-hover:text-green-600">{{ $post['title'] }}</h3>
                                    <p class="exam-tip-clamp-2 mb-2 text-xs font-semibold leading-5 text-gray-500">{{ $post['excerpt'] }}</p>
                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs font-bold text-gray-500">
                                        <span class="flex items-center gap-1.5">
                                            <span class="flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-black text-white {{ $post['avatar_class'] }}">{{ $post['avatar'] }}</span>
                                            {{ $post['author'] }}
                                        </span>
                                        <span class="flex items-center gap-1">{{ $post['views'] }}</span>
                                        <button type="button" data-exam-tip-like class="flex items-center gap-1 transition hover:text-green-600" data-count="{{ $post['likes'] }}">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z"/></svg>
                                            <span data-exam-tip-like-count>{{ $post['likes'] }}</span>
                                        </button>
                                        <a href="{{ $examTipLoginReturnUrl }}" title="@lang('core::exam_tips.actions.login_to_comment')" class="flex items-center gap-1 text-gray-500 no-underline transition hover:text-green-600">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5A8.48 8.48 0 0 1 21 11v.5Z"/></svg>
                                            {{ $post['comments'] }}
                                        </a>
                                    </div>
                                    <div class="mt-3 flex flex-wrap items-center gap-2">
                                        <a href="#exam-tip-post-{{ $post['id'] }}" class="inline-flex items-center gap-1.5 rounded-xl border-2 border-green-200 bg-white px-4 py-2 text-xs font-black text-green-600 no-underline transition-all hover:border-green-400 hover:bg-green-50">
                                            @lang('core::exam_tips.actions.read')
                                        </a>
                                    </div>
                                </div>
                                <div class="flex shrink-0 items-center text-gray-500 transition group-hover:text-green-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" /></svg>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <div data-exam-tip-empty class="hidden py-20 text-center text-gray-500">
                    <svg class="mx-auto mb-3 h-12 w-12 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75c-2.2-1.4-4.64-2.05-7.25-1.95A1.75 1.75 0 0 0 3 6.55v10.7A1.75 1.75 0 0 0 4.86 19c2.57-.13 4.96.52 7.14 1.95 2.18-1.43 4.57-2.08 7.14-1.95A1.75 1.75 0 0 0 21 17.25V6.55a1.75 1.75 0 0 0-1.75-1.75c-2.61-.1-5.05.55-7.25 1.95Zm0 0v14.2" />
                    </svg>
                    <p class="text-base font-black">@lang('core::exam_tips.sections.empty_title')</p>
                    <p class="mt-1 text-sm font-semibold">@lang('core::exam_tips.sections.empty_text')</p>
                </div>
            </div>

            <aside class="space-y-6 pb-8">
                <div class="overflow-hidden rounded-2xl border border-green-100 bg-white p-5 shadow-sm">
                    <div class="mb-4 rounded-2xl bg-green-50 px-4 py-3">
                        <h3 class="mb-1 text-lg font-black text-green-800">@lang('core::exam_tips.sidebar.cta_title')</h3>
                        <p class="text-sm font-semibold leading-6 text-green-700/80">@lang('core::exam_tips.sidebar.cta_text')</p>
                    </div>
                    @auth
                        <button type="button" data-exam-tip-share-action @click="shareOpen = true; shared = false" class="flex w-full items-center justify-center gap-2 rounded-xl bg-green-500 px-4 py-3 text-sm font-black text-white no-underline shadow-sm transition hover:bg-green-600">
                            @lang('core::exam_tips.sidebar.cta_button')
                        </button>
                    @else
                        <a href="{{ $examTipLoginReturnUrl }}" data-exam-tip-share-login title="@lang('core::exam_tips.actions.login_to_share')" class="flex w-full items-center justify-center gap-2 rounded-xl bg-green-500 px-4 py-3 text-sm font-black text-white no-underline shadow-sm transition hover:bg-green-600">
                            @lang('core::exam_tips.sidebar.cta_button')
                        </a>
                    @endauth
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center gap-2">
                        <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.59 13.41 13.42 20.6a2 2 0 0 1-2.83 0L3 13V3h10l7.59 7.59a2 2 0 0 1 0 2.82Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01"/></svg>
                        <h3 class="font-black">@lang('core::exam_tips.sidebar.trending_title')</h3>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @forelse($trendingTags as $tag)
                            <button type="button" data-exam-tip-tag="{{ $tag }}" class="rounded-xl bg-gray-100 px-3 py-2 text-xs font-black text-gray-500 transition-all hover:bg-green-50 hover:text-green-600">#{{ $tag }}</button>
                        @empty
                            <p class="text-sm font-semibold text-gray-400">@lang('core::exam_tips.sidebar.empty_topics')</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center gap-2">
                        <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8M12 17v4M7 4h10v4a5 5 0 0 1-10 0V4Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 6H3a3 3 0 0 0 3 3h1M19 6h2a3 3 0 0 1-3 3h-1"/></svg>
                        <h3 class="font-black">@lang('core::exam_tips.sidebar.contributors_title')</h3>
                    </div>
                    <div class="space-y-3">
                        @forelse($contributors as $index => $user)
                            <div class="flex items-center gap-3 rounded-xl p-2 transition hover:bg-gray-100">
                                <span class="w-4 text-center text-xs font-black text-gray-500">{{ $index + 1 }}</span>
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-black text-white {{ $user['class'] }}">{{ $user['avatar'] }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-black">{{ $user['name'] }}</span>
                                    <span class="block text-xs font-semibold text-gray-500">{{ $user['posts'] }} @lang('core::exam_tips.sidebar.posts_suffix') - {{ $user['likes'] }} @lang('core::exam_tips.sidebar.likes_suffix')</span>
                                </span>
                                @if($loop->first)
                                    <svg class="h-4 w-4 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 2.94 6.03 6.66.95-4.82 4.69 1.14 6.63L12 17.18 6.08 20.3l1.14-6.63L2.4 8.98l6.66-.95L12 2Z" /></svg>
                                @endif
                            </div>
                        @empty
                            <p class="rounded-xl bg-gray-50 p-3 text-sm font-semibold text-gray-400">@lang('core::exam_tips.sidebar.empty_contributors')</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center gap-2">
                        <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/></svg>
                        <h3 class="font-black">@lang('core::exam_tips.sidebar.exams_title')</h3>
                    </div>
                    <div class="space-y-3">
                        @forelse($upcomingExams as $exam)
                            <div class="flex items-center gap-3">
                                <span class="h-2 w-2 shrink-0 rounded-full {{ $exam['class'] }}"></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-black">{{ $exam['name'] }}</span>
                                    <span class="block text-xs font-semibold text-gray-500">{{ $exam['date'] }}</span>
                                </span>
                                <span class="text-xs font-black text-green-600">{{ $exam['days'] }} @lang('core::exam_tips.sidebar.days_suffix')</span>
                            </div>
                        @empty
                            <p class="rounded-xl bg-gray-50 p-3 text-sm font-semibold text-gray-400">@lang('core::exam_tips.sidebar.empty_exams')</p>
                        @endforelse
                    </div>
                </div>
            </aside>
        </div>
    </main>
</div>

<script>
    (() => {
        const normalize = (value) => (value || '')
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/\u0111/g, 'd');

        const state = { category: 'all', query: '' };
        const cards = Array.from(document.querySelectorAll('[data-exam-tip-card]'));
        const categoryButtons = Array.from(document.querySelectorAll('[data-exam-tip-category]'));
        const searchInputs = Array.from(document.querySelectorAll('[data-exam-tip-search]'));
        const sections = Array.from(document.querySelectorAll('[data-exam-tip-section]'));
        const empty = document.querySelector('[data-exam-tip-empty]');
        const countLabel = document.querySelector('[data-exam-tip-count]');
        const countTemplate = @json(__('core::exam_tips.sections.article_count', ['count' => '__COUNT__']));

        const setButtonState = () => {
            categoryButtons.forEach((button) => {
                const active = button.dataset.examTipCategory === state.category;
                button.className = active
                    ? 'shrink-0 rounded-xl border px-4 py-2 text-sm font-black transition-all border-green-500 bg-green-500 text-white shadow-[0_3px_0_#15803d]'
                    : 'shrink-0 rounded-xl border px-4 py-2 text-sm font-black transition-all border-gray-200 bg-gray-100 text-gray-500 hover:bg-green-50 hover:text-green-600';
            });
        };

        const applyFilter = () => {
            const query = normalize(state.query);
            let visibleRegular = 0;
            let visibleTotal = 0;

            cards.forEach((card) => {
                const matchesCategory = state.category === 'all' || card.dataset.category === state.category;
                const matchesQuery = query === '' || normalize(card.dataset.search).includes(query);
                const visible = matchesCategory && matchesQuery;
                card.classList.toggle('hidden', !visible);

                if (visible) {
                    visibleTotal += 1;
                    if (card.dataset.featured !== '1') {
                        visibleRegular += 1;
                    }
                }
            });

            sections.forEach((section) => {
                const hasVisibleCard = section.querySelector('[data-exam-tip-card]:not(.hidden)');
                section.classList.toggle('hidden', !hasVisibleCard);
            });

            if (empty) {
                empty.classList.toggle('hidden', visibleTotal > 0);
            }

            if (countLabel) {
                countLabel.textContent = countTemplate.replace('__COUNT__', visibleRegular);
            }
        };

        categoryButtons.forEach((button) => {
            button.addEventListener('click', () => {
                state.category = button.dataset.examTipCategory;
                setButtonState();
                applyFilter();
            });
        });

        searchInputs.forEach((input) => {
            input.addEventListener('input', () => {
                state.query = input.value;
                searchInputs.forEach((otherInput) => {
                    if (otherInput !== input) {
                        otherInput.value = input.value;
                    }
                });
                applyFilter();
            });
        });

        document.querySelectorAll('[data-exam-tip-tag]').forEach((button) => {
            button.addEventListener('click', () => {
                state.query = button.dataset.examTipTag;
                searchInputs.forEach((input) => {
                    input.value = state.query;
                });
                applyFilter();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });

        document.querySelectorAll('[data-exam-tip-like]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                const liked = button.dataset.liked === '1';
                const baseCount = Number(button.dataset.count || 0);
                button.dataset.liked = liked ? '0' : '1';
                button.classList.toggle('text-green-600', !liked);
                const label = button.querySelector('[data-exam-tip-like-count]');
                if (label) {
                    label.textContent = String(baseCount + (liked ? 0 : 1));
                }
            });
        });

        setButtonState();
        applyFilter();
    })();
</script>
@endsection
