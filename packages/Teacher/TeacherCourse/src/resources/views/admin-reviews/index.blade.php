@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-course::admin-review.title'))
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="min-h-screen bg-slate-50">
    <header class="sticky top-0 z-10 border-b border-slate-200 bg-white/95 px-6 py-4 backdrop-blur">
        <p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('teacher-course::admin-review.area')</p>
        <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('teacher-course::admin-review.title')</h1>
        <p class="mt-1 text-xs font-semibold text-slate-400">@lang('teacher-course::admin-review.subtitle')</p>
    </header>

    <main class="space-y-5 p-4 sm:p-6">
        @php($activeFilterCount = collect($filters)->only(['teacher_id', 'sort'])->filter(fn ($value, $key) => filled($value) && ! ($key === 'sort' && $value === 'newest'))->count())
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3">
                <form method="GET" action="{{ route('admin.course-publication-reviews.index') }}" class="min-w-0 flex-1" role="search">
                    @if(filled($filters['teacher_id'] ?? null))<input type="hidden" name="teacher_id" value="{{ $filters['teacher_id'] }}">@endif
                    @if(filled($filters['sort'] ?? null))<input type="hidden" name="sort" value="{{ $filters['sort'] }}">@endif
                    <label class="relative block"><span class="sr-only">@lang('teacher-course::admin-review.search')</span><x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" /><input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="@lang('teacher-course::admin-review.search')" class="h-10 w-full rounded-lg border border-slate-300 bg-white pl-9 pr-3 text-sm font-semibold outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"></label>
                </form>
                <button type="button" data-mindigo-drawer-open="admin-course-review-filter" class="inline-flex h-10 shrink-0 items-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 shadow-sm hover:bg-green-50 hover:text-green-700"><x-heroicon-o-adjustments-horizontal class="h-4 w-4" />@lang('teacher-course::admin-review.filter')@if($activeFilterCount)<span class="grid h-5 min-w-5 place-items-center rounded-full bg-green-600 px-1.5 text-[11px] text-white">{{ $activeFilterCount }}</span>@endif</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-225 text-left text-sm">
                    <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3">@lang('teacher-course::admin-review.course')</th><th class="px-5 py-3">@lang('teacher-course::admin-review.teacher')</th><th class="px-5 py-3">@lang('teacher-course::admin-review.curriculum')</th><th class="px-5 py-3">@lang('teacher-course::admin-review.submitted_at')</th><th class="px-5 py-3"></th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($courses as $course)
                            <tr class="hover:bg-slate-50/70"><td class="px-5 py-4"><p class="font-black text-slate-900">{{ $course->name }}</p><p class="mt-1 text-xs font-semibold text-slate-400">{{ $course->subject?->name }} · {{ $course->category?->name }}</p></td><td class="px-5 py-4"><p class="font-bold text-slate-700">{{ $course->teacher->name }}</p><p class="text-xs text-slate-400">{{ $course->teacher->email }}</p></td><td class="px-5 py-4 font-bold text-slate-600">{{ $course->chapters_count }} / {{ $course->lessons_count }}</td><td class="px-5 py-4 text-xs font-semibold text-slate-500">{{ $course->submitted_for_review_at?->format('d/m/Y H:i') }}</td><td class="px-5 py-4 text-right"><a href="{{ route('admin.course-publication-reviews.show', $course) }}" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 px-3 text-xs font-black text-slate-700 no-underline hover:border-green-200 hover:text-green-700"><x-heroicon-o-eye class="h-4 w-4" />@lang('teacher-course::admin-review.review')</a></td></tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-16 text-center"><x-heroicon-o-clipboard-document-check class="mx-auto h-10 w-10 text-slate-300" /><p class="mt-3 text-sm font-black text-slate-700">@lang('teacher-course::admin-review.empty')</p><p class="mt-1 text-xs font-semibold text-slate-400">@lang('teacher-course::admin-review.empty_description')</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($courses->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $courses->links() }}</div>@endif
        </section>
    </main>

    <div data-mindigo-drawer="admin-course-review-filter" class="fixed inset-0 z-40 hidden bg-slate-950/45 opacity-0 backdrop-blur-sm transition-opacity duration-200"></div>
    <aside data-mindigo-drawer-panel="admin-course-review-filter" aria-label="@lang('teacher-course::admin-review.filter_title')" class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col border-l border-slate-200 bg-white shadow-2xl transition-transform duration-200" style="transform: translateX(100%);">
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4"><div><p class="text-xs font-black uppercase tracking-wider text-green-700">@lang('teacher-course::admin-review.area')</p><h2 class="mt-1 text-xl font-black">@lang('teacher-course::admin-review.filter_title')</h2><p class="mt-1 text-sm font-semibold text-slate-500">@lang('teacher-course::admin-review.filter_description')</p></div><button type="button" data-mindigo-drawer-close="admin-course-review-filter" aria-label="@lang('teacher-course::app.close')" class="grid h-10 w-10 shrink-0 place-items-center rounded-full border border-slate-200 text-slate-500"><x-heroicon-o-x-mark class="h-5 w-5" /></button></div>
        <form method="GET" action="{{ route('admin.course-publication-reviews.index') }}" class="flex min-h-0 flex-1 flex-col">@if(filled($filters['search'] ?? null))<input type="hidden" name="search" value="{{ $filters['search'] }}">@endif<div class="flex-1 space-y-5 overflow-y-auto p-5"><label class="block space-y-2"><span class="text-xs font-black uppercase text-slate-500">@lang('teacher-course::admin-review.teacher')</span><select name="teacher_id" class="h-11 w-full rounded-2xl border border-slate-200 px-4 text-sm font-bold"><option value="">@lang('teacher-course::admin-review.all_teachers')</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected(($filters['teacher_id'] ?? null) == $teacher->id)>{{ $teacher->name }}</option>@endforeach</select></label><label class="block space-y-2"><span class="text-xs font-black uppercase text-slate-500">@lang('teacher-course::admin-review.sort')</span><select name="sort" class="h-11 w-full rounded-2xl border border-slate-200 px-4 text-sm font-bold"><option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>@lang('teacher-course::admin-review.newest')</option><option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>@lang('teacher-course::admin-review.oldest')</option><option value="name" @selected(($filters['sort'] ?? '') === 'name')>@lang('teacher-course::admin-review.name_sort')</option></select></label></div><div class="grid grid-cols-2 gap-3 border-t border-slate-100 p-5"><a href="{{ route('admin.course-publication-reviews.index') }}" class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 text-sm font-black text-slate-600 no-underline">@lang('teacher-course::admin-review.clear')</a><button class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-green-600 text-sm font-black text-white"><x-heroicon-o-funnel class="h-4 w-4" />@lang('teacher-course::admin-review.apply')</button></div></form>
    </aside>
</div>
@endsection
