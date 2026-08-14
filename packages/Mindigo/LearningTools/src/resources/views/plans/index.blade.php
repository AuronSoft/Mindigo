@extends('Mindigo-dashboard::layouts')
@section('title', __('learning-tools::app.plans.title') . ' · Auronsoft LMS')
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection
@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    @include('learning-tools::partials.header', [
        'eyebrow' => __('learning-tools::app.eyebrow'),
        'title' => __('learning-tools::app.plans.title'),
        'subtitle' => __('learning-tools::app.plans.subtitle'),
        'actionRoute' => route('learning-tools.plans.create'),
        'actionLabel' => __('learning-tools::app.plans.new'),
    ])
    <div class="p-6">
        <form method="GET" class="flex justify-end"><select name="status" onchange="this.form.submit()" class="h-10 rounded-full border border-slate-200 bg-white px-4 text-xs font-black text-slate-600"><option value="">@lang('learning-tools::app.plans.all_statuses')</option>@foreach(['active', 'completed', 'archived'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>@lang('learning-tools::app.statuses.' . $status)</option>@endforeach</select></form>
        <section class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($plans as $plan)
                <a href="{{ route('learning-tools.plans.show', $plan) }}" class="group flex min-h-56 flex-col rounded-3xl border border-slate-200 bg-white p-5 text-slate-800 no-underline shadow-sm transition hover:border-green-200 hover:shadow-md">
                    <div class="flex items-start justify-between"><span class="grid h-11 w-11 place-items-center rounded-xl bg-sky-50 text-sky-600"><x-heroicon-o-calendar-days class="h-6 w-6" /></span><span class="rounded-full bg-green-50 px-3 py-1 text-[10px] font-black uppercase text-green-700">@lang('learning-tools::app.statuses.' . $plan->status)</span></div>
                    <h2 class="mt-4 line-clamp-2 font-black group-hover:text-green-700">{{ $plan->title }}</h2>
                    <p class="mt-2 line-clamp-2 flex-1 text-sm font-semibold text-slate-400">{{ $plan->description }}</p>
                    <div class="mt-4 border-t border-slate-100 pt-4"><p class="text-xs font-bold text-slate-400">{{ $plan->start_date->format('d/m/Y') }} – {{ $plan->end_date->format('d/m/Y') }}</p><p class="mt-1 text-xs font-black text-slate-600">{{ trans_choice('learning-tools::app.plans.task_count', $plan->tasks_count, ['count' => $plan->tasks_count]) }} · {{ $plan->classroom?->name ?? __('learning-tools::app.plans.personal') }}</p></div>
                </a>
            @empty
                <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white py-16 text-center"><x-heroicon-o-calendar-days class="mx-auto h-10 w-10 text-slate-300" /><p class="mt-3 font-black text-slate-600">@lang('learning-tools::app.plans.empty')</p></div>
            @endforelse
        </section>
        <div class="mt-5">{{ $plans->links() }}</div>
    </div>
</div>
@endsection
