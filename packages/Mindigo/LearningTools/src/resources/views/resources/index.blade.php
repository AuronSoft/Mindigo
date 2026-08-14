@extends('Mindigo-dashboard::layouts')
@section('title', __('learning-tools::app.resources.title') . ' · Auronsoft LMS')
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    @include('learning-tools::partials.header', [
        'eyebrow' => __('learning-tools::app.eyebrow'),
        'title' => __('learning-tools::app.resources.title'),
        'subtitle' => __('learning-tools::app.resources.subtitle'),
        'actionRoute' => auth()->user()->hasPermissionTo('learning-resources.manage') ? route('learning-tools.resources.create') : null,
        'actionLabel' => auth()->user()->hasPermissionTo('learning-resources.manage') ? __('learning-tools::app.resources.new') : null,
    ])
    <div class="p-6">
        <form method="GET" class="flex flex-col gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('learning-tools::app.resources.search') }}" class="h-11 min-w-0 flex-1 rounded-xl border border-slate-200 px-4 text-sm font-bold outline-none focus:border-green-400 focus:ring-4 focus:ring-green-50">
            <select name="subject" class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700">
                <option value="">@lang('learning-tools::app.fields.all_subjects')</option>
                @foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected(request('subject') == $subject->id)>{{ $subject->name }}</option>@endforeach
            </select>
            <button class="h-11 rounded-full bg-green-600 px-6 text-sm font-black text-white">@lang('learning-tools::app.actions.filter')</button>
        </form>
        <section class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($resources as $resource)
                <a href="{{ route('learning-tools.resources.show', $resource) }}" class="group flex min-h-64 flex-col rounded-3xl border border-slate-200 bg-white p-5 text-slate-800 no-underline shadow-sm transition hover:border-green-200 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <span class="grid h-11 w-11 place-items-center rounded-xl bg-amber-50 text-amber-600"><x-heroicon-o-light-bulb class="h-6 w-6" /></span>
                        <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider {{ $resource->status === 'published' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                            @lang('learning-tools::app.statuses.' . $resource->status)
                        </span>
                    </div>
                    <h2 class="mt-4 line-clamp-2 font-black group-hover:text-green-700">{{ $resource->title }}</h2>
                    <p class="mt-2 line-clamp-3 flex-1 text-sm font-semibold leading-6 text-slate-400">{{ $resource->summary }}</p>
                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4 text-xs font-bold text-slate-400">
                        <span>{{ $resource->subject?->name ?? __('learning-tools::app.resources.general') }}</span>
                        <span class="inline-flex items-center gap-1"><x-heroicon-o-bookmark class="h-4 w-4" /> {{ $resource->favorited_by_count }}</span>
                    </div>
                </a>
            @empty
                <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white py-16 text-center">
                    <x-heroicon-o-light-bulb class="mx-auto h-10 w-10 text-slate-300" />
                    <p class="mt-3 font-black text-slate-600">@lang('learning-tools::app.resources.empty')</p>
                </div>
            @endforelse
        </section>
        <div class="mt-5">{{ $resources->links() }}</div>
    </div>
</div>
@endsection
