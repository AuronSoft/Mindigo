@extends('core::layouts.home')
@section('title', __('teacher-course::reviews.profile_title').' — '.$teacher->name)
@section('content')
<main class="min-h-screen bg-slate-50">
    @include('core::partials.home.navbar')
    <header class="border-b border-slate-200 bg-white px-5 py-4 sm:px-6"><p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('teacher-course::reviews.profile_title')</p><h1 class="text-lg font-black text-slate-950">{{ $teacher->name }}</h1><p class="text-xs font-semibold text-slate-400">@lang('teacher-course::reviews.profile_subtitle')</p></header>
    <div class="mx-auto max-w-7xl space-y-5 p-4 sm:p-6">
        <section class="grid gap-5 rounded-xl border border-slate-200 bg-white p-5 md:grid-cols-[180px_minmax(0,1fr)]">
            <div class="grid place-items-center"><span class="grid h-28 w-28 place-items-center overflow-hidden rounded-full bg-green-50 text-3xl font-black text-green-700">@if($teacher->avatar)<img src="{{ asset('storage/'.$teacher->avatar) }}" alt="{{ $teacher->name }}" class="h-full w-full object-cover">@else{{ str($teacher->name)->substr(0, 1)->upper() }}@endif</span></div>
            <div><h2 class="text-2xl font-black text-slate-950">{{ $teacher->name }}</h2><p class="mt-1 font-bold text-green-700">{{ $profile->headline }}</p><p class="mt-3 text-sm font-semibold leading-6 text-slate-500">{{ $profile->biography }}</p><div class="mt-4 flex flex-wrap gap-2">@if($profile->specialization)<span class="rounded-full bg-green-50 px-3 py-1 text-xs font-black text-green-700">{{ $profile->specialization }}</span>@endif<span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">{{ __('teacher-course::reviews.experience', ['count' => $profile->experience_years]) }}</span></div></div>
        </section>
        <section class="grid gap-3 sm:grid-cols-3">@foreach([[__('teacher-course::reviews.students'), $studentCount], [__('teacher-course::reviews.average_rating'), number_format($ratingAverage, 1)], [__('teacher-course::reviews.reviews'), $ratingCount]] as [$label,$value])<div class="rounded-xl border border-slate-200 bg-white p-4"><p class="text-[10px] font-black uppercase text-slate-400">{{ $label }}</p><p class="mt-1 text-2xl font-black text-slate-950">{{ $value }}</p></div>@endforeach</section>
        @if($profile->qualifications)<section class="rounded-xl border border-slate-200 bg-white p-5"><h2 class="font-black text-slate-950">@lang('teacher-course::reviews.qualifications')</h2><ul class="mt-3 space-y-2">@foreach($profile->qualifications as $item)<li class="flex gap-2 text-sm font-semibold text-slate-600"><x-heroicon-o-check-circle class="h-5 w-5 text-green-600" />{{ $item }}</li>@endforeach</ul></section>@endif
        <section><h2 class="mb-3 text-lg font-black text-slate-950">@lang('teacher-course::reviews.courses')</h2><div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">@forelse($courses as $course)@include('teacher-course::catalog.partials.course-card', ['course' => $course])@empty<div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white py-14 text-center text-sm font-semibold text-slate-400">@lang('teacher-course::reviews.profile_empty')</div>@endforelse</div>{{ $courses->links() }}</section>
    </div>
</main>
@endsection
