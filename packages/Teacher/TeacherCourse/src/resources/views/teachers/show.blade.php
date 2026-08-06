@extends('core::layouts.home')

@section('title', __('teacher-course::reviews.profile_title').' — '.$teacher->name)

@section('content')
<main class="min-h-screen bg-slate-50">
    @include('core::partials.home.navbar')

    <header class="border-b border-slate-200 bg-white px-5 py-4 sm:px-6">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4">
            <div class="min-w-0">
                <p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('teacher-course::reviews.profile_title')</p>
                <h1 class="mt-0.5 text-2xl font-black text-slate-950">{{ $teacher->name }}</h1>
                <p class="mt-1 text-sm font-semibold text-slate-400">@lang('teacher-course::reviews.profile_subtitle')</p>
            </div>
            <a href="{{ route('teachers.index') }}" class="grid h-10 w-10 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline hover:text-green-700" aria-label="@lang('teacher-course::reviews.back_to_directory')">
                <x-heroicon-o-arrow-left class="h-5 w-5" />
            </a>
        </div>
    </header>

    <div class="mx-auto grid max-w-7xl gap-5 p-4 sm:p-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        <aside class="space-y-5">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 text-center">
                <span class="mx-auto grid h-28 w-28 place-items-center overflow-hidden rounded-full bg-green-50 text-3xl font-black text-green-700">
                    @if($teacher->avatar)<img src="{{ asset('storage/'.$teacher->avatar) }}" alt="{{ $teacher->name }}" class="h-full w-full object-cover">@else{{ str($teacher->name)->substr(0, 1)->upper() }}@endif
                </span>
                <h2 class="mt-4 text-xl font-black text-slate-950">{{ $teacher->name }}</h2>
                <div class="mt-2 flex justify-center"><span class="rounded-full bg-green-50 px-3 py-1 text-[10px] font-black uppercase text-green-700">@lang('teacher-course::reviews.verified')</span></div>
                @if($profile->headline)<p class="mt-3 text-sm font-bold text-slate-500">{{ $profile->headline }}</p>@endif
            </section>

            <section class="grid grid-cols-3 gap-2 rounded-2xl border border-slate-200 bg-white p-4 text-center">
                <div><p class="text-xl font-black text-slate-950">{{ $courseCount }}</p><p class="text-[10px] font-black uppercase text-slate-400">@lang('teacher-course::reviews.courses')</p></div>
                <div><p class="text-xl font-black text-slate-950">{{ $studentCount }}</p><p class="text-[10px] font-black uppercase text-slate-400">@lang('teacher-course::reviews.students')</p></div>
                <div><p class="text-xl font-black text-slate-950">{{ number_format($ratingAverage, 1) }}</p><p class="text-[10px] font-black uppercase text-slate-400">@lang('teacher-course::reviews.average_rating')</p></div>
            </section>

            @if($profile->social_links)
                <section class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h2 class="font-black text-slate-950">@lang('teacher-course::reviews.social_links')</h2>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($profile->social_links as $network => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="rounded-full border border-slate-200 px-3 py-1.5 text-xs font-black text-slate-600 no-underline hover:border-green-200 hover:text-green-700">{{ ucfirst($network) }}</a>
                        @endforeach
                    </div>
                </section>
            @endif
        </aside>

        <div class="min-w-0 space-y-5">
            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="font-black text-slate-950">@lang('teacher-course::reviews.about')</h2>
                <p class="mt-3 text-sm font-semibold leading-6 text-slate-500">{{ $profile->biography ?: __('teacher-course::reviews.no_biography') }}</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @if($profile->specialization)<span class="rounded-full bg-green-50 px-3 py-1 text-xs font-black text-green-700">{{ $profile->specialization }}</span>@endif
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">{{ __('teacher-course::reviews.experience', ['count' => $profile->experience_years]) }}</span>
                </div>
            </section>

            @if($profile->qualifications)
                <section class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h2 class="font-black text-slate-950">@lang('teacher-course::reviews.qualifications')</h2>
                    <ul class="mt-3 grid gap-2 sm:grid-cols-2">
                        @foreach($profile->qualifications as $item)
                            <li class="flex gap-2 text-sm font-semibold leading-5 text-slate-600"><x-heroicon-o-check-badge class="mt-0.5 h-5 w-5 shrink-0 text-green-600" />{{ $item }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section>
                <h2 class="mb-3 text-lg font-black text-slate-950">@lang('teacher-course::reviews.courses')</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    @forelse($courses as $course)
                        @include('teacher-course::catalog.partials.course-card', ['course' => $course])
                    @empty
                        <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white py-14 text-center text-sm font-semibold text-slate-400">@lang('teacher-course::reviews.profile_empty')</div>
                    @endforelse
                </div>
                {{ $courses->links() }}
            </section>
        </div>
    </div>
</main>
@endsection
