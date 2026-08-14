@extends('Mindigo-dashboard::layouts')
@section('title', $plan->title . ' · Mindigo LMS')
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection
@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    @include('learning-tools::partials.header', ['eyebrow' => __('learning-tools::app.plans.title'), 'title' => $plan->title, 'subtitle' => $plan->description ?: __('learning-tools::app.plans.detail_subtitle')])
    <div class="grid gap-5 p-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <section>
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><p class="text-sm font-black text-slate-700">@lang('learning-tools::app.plans.progress')</p><p class="text-sm font-black text-green-700">{{ $completedCount }}/{{ $plan->tasks->count() }}</p></div><progress value="{{ $completedCount }}" max="{{ max(1, $plan->tasks->count()) }}" class="mt-3 h-2 w-full overflow-hidden rounded-full accent-green-500"></progress></div>
            <div class="mt-4 space-y-3">
                @forelse($plan->tasks as $task)
                    @php $done = $task->completedBy->isNotEmpty(); @endphp
                    <article class="flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <form method="POST" action="{{ route('learning-tools.plans.tasks.toggle', [$plan, $task]) }}">
                            @csrf
                            <button class="grid h-7 w-7 place-items-center rounded-full border {{ $done ? 'border-green-500 bg-green-500 text-white' : 'border-slate-300 text-transparent' }}">
                                <x-heroicon-o-check class="h-4 w-4" />
                            </button>
                        </form>
                        <div class="min-w-0 flex-1">
                            <h2 class="font-black {{ $done ? 'text-slate-400 line-through' : 'text-slate-800' }}">{{ $task->title }}</h2>
                            @if($task->description)
                                <p class="mt-1 text-sm font-semibold text-slate-400">{{ $task->description }}</p>
                            @endif
                            @if($task->due_date)
                                <p class="mt-2 text-xs font-black text-slate-400">{{ $task->due_date->format('d/m/Y') }}</p>
                            @endif
                        </div>
                        @if(auth()->id() === $plan->creator_id)
                            <form method="POST" action="{{ route('learning-tools.plans.tasks.destroy', [$plan, $task]) }}">
                                @csrf @method('DELETE')
                                <button class="text-xs font-black text-red-600">@lang('learning-tools::app.actions.delete')</button>
                            </form>
                        @endif
                    </article>
                @empty
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-white py-12 text-center font-black text-slate-500">@lang('learning-tools::app.plans.empty_tasks')</div>
                @endforelse
            </div>
        </section>
        <aside class="space-y-4">
            @if(auth()->id() === $plan->creator_id)
                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-black text-slate-900">@lang('learning-tools::app.plans.add_task')</h2><form method="POST" action="{{ route('learning-tools.plans.tasks.store', $plan) }}" class="mt-4 space-y-4">@csrf<input name="title" required maxlength="180" placeholder="{{ __('learning-tools::app.plans.task_title') }}" class="h-11 w-full rounded-xl border border-slate-200 px-4 text-sm font-bold"><textarea name="description" rows="3" placeholder="{{ __('learning-tools::app.fields.description') }}" class="w-full rounded-xl border border-slate-200 p-3 text-sm font-semibold"></textarea><input type="date" name="due_date" min="{{ $plan->start_date->format('Y-m-d') }}" max="{{ $plan->end_date->format('Y-m-d') }}" class="h-11 w-full rounded-xl border border-slate-200 px-4 text-sm font-bold"><button class="h-10 w-full rounded-full bg-green-600 text-sm font-black text-white">@lang('learning-tools::app.plans.add_task')</button></form></section>
                <a href="{{ route('learning-tools.plans.edit', $plan) }}" class="inline-flex h-10 w-full items-center justify-center rounded-full border border-slate-200 bg-white text-sm font-black text-slate-700 no-underline">@lang('learning-tools::app.actions.edit')</a>
            @endif
        </aside>
    </div>
</div>
@endsection
