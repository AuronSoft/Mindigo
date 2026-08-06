@extends('core::layouts.home')

@section('title', __('teacher-course::reviews.directory_title').' - Mindigo')

@section('content')
<main class="min-h-screen bg-slate-50">
    @include('core::partials.home.navbar')

    <header class="border-b border-slate-200 bg-white px-5 py-4 sm:px-6">
        <div class="mx-auto max-w-7xl">
            <p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('teacher-course::reviews.directory_kicker')</p>
            <h1 class="mt-0.5 text-2xl font-black text-slate-950">@lang('teacher-course::reviews.directory_title')</h1>
            <p class="mt-1 text-sm font-semibold text-slate-400">@lang('teacher-course::reviews.directory_subtitle')</p>
        </div>
    </header>

    <section class="mx-auto max-w-7xl space-y-5 p-4 sm:p-6">
        <form method="GET" action="{{ route('teachers.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4">
            <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_260px_auto]">
                <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="@lang('teacher-course::reviews.search_teacher')" class="h-11 rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700">
                <select name="specialization" class="h-11 rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700">
                    <option value="">@lang('teacher-course::reviews.all_specializations')</option>
                    @foreach($specializations as $specialization)
                        <option value="{{ $specialization }}" @selected(($filters['specialization'] ?? '') === $specialization)>{{ $specialization }}</option>
                    @endforeach
                </select>
                <button class="h-11 rounded-xl bg-green-600 px-6 text-sm font-black text-white">@lang('teacher-course::reviews.filter')</button>
            </div>
        </form>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($teachers as $teacher)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-green-200 hover:shadow-sm">
                    <div class="flex items-start gap-4">
                        <span class="grid h-14 w-14 shrink-0 place-items-center overflow-hidden rounded-full bg-green-50 text-lg font-black text-green-700">
                            @if($teacher->avatar)<img src="{{ asset('storage/'.$teacher->avatar) }}" alt="{{ $teacher->name }}" class="h-full w-full object-cover">@else{{ str($teacher->name)->substr(0, 1)->upper() }}@endif
                        </span>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="truncate text-lg font-black text-slate-950">{{ $teacher->name }}</h2>
                                <span class="rounded-full bg-green-50 px-2 py-0.5 text-[9px] font-black uppercase text-green-700">@lang('teacher-course::reviews.verified')</span>
                            </div>
                            <p class="mt-1 line-clamp-2 text-sm font-semibold text-slate-500">{{ $teacher->teacherProfile->headline ?: $teacher->teacherProfile->specialization }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2 text-[10px] font-black">
                        @if($teacher->teacherProfile->specialization)<span class="rounded-full bg-slate-50 px-2.5 py-1 text-slate-600">{{ $teacher->teacherProfile->specialization }}</span>@endif
                        <span class="rounded-full bg-slate-50 px-2.5 py-1 text-slate-600">{{ __('teacher-course::reviews.experience', ['count' => $teacher->teacherProfile->experience_years]) }}</span>
                    </div>
                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <a href="{{ route('teachers.show', $teacher) }}" class="inline-flex h-10 w-full items-center justify-center rounded-xl bg-green-600 px-4 text-xs font-black text-white no-underline">@lang('teacher-course::reviews.view_profile')</a>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white py-16 text-center text-sm font-semibold text-slate-400">@lang('teacher-course::reviews.directory_empty')</div>
            @endforelse
        </div>

        {{ $teachers->links() }}
    </section>
</main>
@endsection
