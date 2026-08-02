@extends('core::layouts.home')

@section('title', __('teacher-course::catalog.title').' - Mindigo')
@section('meta_description', __('teacher-course::catalog.subtitle'))
@section('canonical', route('courses.index'))

@section('content')
<div class="min-h-screen bg-slate-50 text-slate-900">
    @include('core::partials.home.navbar')

    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:px-10">
            <nav class="mb-5 flex items-center gap-2 text-xs font-bold text-slate-400" aria-label="@lang('teacher-course::catalog.breadcrumb')">
                <a href="{{ route('home') }}" class="text-slate-500 no-underline hover:text-green-700">@lang('teacher-course::catalog.home')</a>
                <x-heroicon-o-chevron-right class="h-3.5 w-3.5" />
                <span class="text-green-700">@lang('teacher-course::catalog.title')</span>
            </nav>
            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-green-700">@lang('teacher-course::catalog.eyebrow')</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">@lang('teacher-course::catalog.title')</h1>
            <p class="mt-2 max-w-3xl text-sm font-semibold leading-6 text-slate-500">@lang('teacher-course::catalog.subtitle')</p>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-5 py-7 sm:px-8 lg:px-10">
        @if($featuredCourses->isNotEmpty())
            <section class="mb-7" aria-labelledby="featured-courses-title">
                <div class="mb-3"><h2 id="featured-courses-title" class="text-lg font-black text-slate-950">@lang('teacher-course::discovery.featured')</h2><p class="text-xs font-semibold text-slate-400">@lang('teacher-course::discovery.featured_description')</p></div>
                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">@foreach($featuredCourses->take(4) as $course) @include('teacher-course::catalog.partials.course-card', ['course' => $course]) @endforeach</div>
            </section>
        @endif

        @auth
            @if(auth()->user()->isStudent() && ($recentCourses->isNotEmpty() || $recommendedCourses->isNotEmpty()))
                <nav class="mb-5 flex flex-wrap gap-2" aria-label="@lang('teacher-course::discovery.continue')">
                    <a href="{{ route('student.courses.recent') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-700 no-underline hover:border-green-300 hover:text-green-700">@lang('teacher-course::discovery.continue')</a>
                    <a href="{{ route('student.courses.recommended') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-700 no-underline hover:border-green-300 hover:text-green-700">@lang('teacher-course::discovery.recommended')</a>
                    <a href="{{ route('student.wishlist.index') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-700 no-underline hover:border-green-300 hover:text-green-700">@lang('teacher-course::discovery.wishlist')</a>
                </nav>
            @endif
        @endauth

        <form method="GET" action="{{ route('courses.index') }}" class="rounded-xl border border-slate-200 bg-white" role="search">
            <div class="flex flex-col gap-3 border-b border-slate-200 p-4 lg:flex-row lg:items-center">
                <label class="relative min-w-0 flex-1">
                    <span class="sr-only">@lang('teacher-course::catalog.search_label')</span>
                    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                    <input type="search" name="search" list="course-search-suggestions" value="{{ $filters['search'] ?? '' }}" placeholder="@lang('teacher-course::catalog.search_placeholder')" class="h-11 w-full rounded-lg border border-slate-300 bg-white pl-11 pr-4 text-sm font-semibold outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100">
                    <datalist id="course-search-suggestions">@foreach($popularKeywords->merge($recentSearches)->unique() as $keyword)<option value="{{ $keyword }}"></option>@endforeach</datalist>
                </label>
                <label class="lg:w-52">
                    <span class="sr-only">@lang('teacher-course::catalog.sort_label')</span>
                    <select name="sort" class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-bold text-slate-700 outline-none focus:border-green-500">
                        @foreach(['newest', 'popular', 'rating', 'enrolled'] as $sort)
                            <option value="{{ $sort }}" @selected(($filters['sort'] ?? 'newest') === $sort)>@lang('teacher-course::catalog.sorts.'.$sort)</option>
                        @endforeach
                    </select>
                </label>
                <button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-green-600 px-5 text-sm font-black text-white transition hover:bg-green-500">
                    <x-heroicon-o-funnel class="h-4 w-4" />@lang('teacher-course::catalog.apply')
                </button>
            </div>

            <details class="group" @if(collect($filters)->except(['search', 'sort', 'page'])->filter()->isNotEmpty()) open @endif>
                <summary class="flex cursor-pointer list-none items-center justify-between px-4 py-3 text-sm font-black text-slate-700">
                    <span class="flex items-center gap-2"><x-heroicon-o-adjustments-horizontal class="h-4 w-4 text-green-600" />@lang('teacher-course::catalog.advanced_filters')</span>
                    <x-heroicon-o-chevron-down class="h-4 w-4 transition group-open:rotate-180" />
                </summary>
                <div class="grid gap-3 border-t border-slate-100 p-4 sm:grid-cols-2 lg:grid-cols-4">
                    @include('teacher-course::catalog.partials.filter-select', ['name' => 'subject_id', 'label' => __('teacher-course::catalog.subject'), 'items' => $subjects])
                    @include('teacher-course::catalog.partials.filter-select', ['name' => 'category_id', 'label' => __('teacher-course::catalog.category'), 'items' => $categories])
                    @include('teacher-course::catalog.partials.enum-filter', ['name' => 'education_level', 'label' => __('teacher-course::catalog.education_level'), 'values' => \Mindigo\TeacherCourse\Models\Course::EDUCATION_LEVELS, 'translation' => 'teacher-course::app.education_levels'])
                    @include('teacher-course::catalog.partials.enum-filter', ['name' => 'difficulty', 'label' => __('teacher-course::catalog.difficulty'), 'values' => \Mindigo\TeacherCourse\Models\Course::DIFFICULTIES, 'translation' => 'teacher-course::app.difficulties'])
                </div>
            </details>
        </form>

        @if($popularKeywords->isNotEmpty() || $recentSearches->isNotEmpty())
            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs font-bold text-slate-400">
                <span>@lang('teacher-course::discovery.popular_keywords'):</span>
                @foreach($popularKeywords->take(6) as $keyword)<a href="{{ route('courses.index', ['search' => $keyword]) }}" class="rounded-full bg-white px-3 py-1.5 text-slate-600 no-underline ring-1 ring-slate-200 hover:text-green-700">{{ $keyword }}</a>@endforeach
            </div>
        @endif

        <div class="mt-6 flex items-center justify-between gap-4">
            <p class="text-sm font-bold text-slate-500">{{ trans_choice('teacher-course::catalog.result_count', $courses->total(), ['count' => $courses->total()]) }}</p>
            @if(collect($filters)->filter()->isNotEmpty())
                <a href="{{ route('courses.index') }}" class="text-xs font-black text-green-700 no-underline hover:text-green-600">@lang('teacher-course::catalog.clear_filters')</a>
            @endif
        </div>

        @if($courses->isEmpty())
            <section class="mt-4 flex min-h-80 flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white px-6 text-center">
                <span class="grid h-12 w-12 place-items-center rounded-xl bg-slate-100 text-slate-400"><x-heroicon-o-book-open class="h-6 w-6" /></span>
                <h2 class="mt-4 text-base font-black">@lang('teacher-course::catalog.empty_title')</h2>
                <p class="mt-1 text-sm font-semibold text-slate-400">@lang('teacher-course::catalog.empty_description')</p>
            </section>
        @else
            <section class="mt-4 grid gap-5 sm:grid-cols-2 xl:grid-cols-3" aria-label="@lang('teacher-course::catalog.results')">
                @foreach($courses as $course)
                    @include('teacher-course::catalog.partials.course-card', ['course' => $course])
                @endforeach
            </section>
            @if($courses->hasPages())
                <div class="mt-7">{{ $courses->links() }}</div>
            @endif
        @endif
    </main>
</div>
@endsection
