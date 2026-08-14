@extends('Mindigo-dashboard::layouts')
@section('title', __('learning-tools::app.mistakes.title') . ' · Auronsoft LMS')
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection
@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    @include('learning-tools::partials.header', ['eyebrow' => __('learning-tools::app.eyebrow'), 'title' => __('learning-tools::app.mistakes.title'), 'subtitle' => __('learning-tools::app.mistakes.subtitle')])
    <main class="p-6">
        <form method="GET" class="flex flex-wrap justify-end gap-2">
            <select name="subject" class="h-10 rounded-full border border-slate-200 bg-white px-4 text-xs font-black text-slate-600"><option value="">@lang('learning-tools::app.fields.all_subjects')</option>@foreach($subjects as $subject)<option value="{{ $subject }}" @selected(request('subject') === $subject)>{{ $subject }}</option>@endforeach</select>
            <label class="inline-flex h-10 items-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-xs font-black text-slate-600"><input type="checkbox" name="unresolved" value="1" @checked(request()->boolean('unresolved')) class="rounded border-slate-300 text-green-600">@lang('learning-tools::app.mistakes.unresolved_only')</label>
            <button class="h-10 rounded-full bg-green-600 px-5 text-xs font-black text-white">@lang('learning-tools::app.actions.filter')</button>
        </form>
        <section class="mt-4 grid gap-4 xl:grid-cols-2">
            @forelse($mistakes as $mistake)
                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-center gap-2"><span class="rounded-full bg-red-50 px-3 py-1 text-[10px] font-black uppercase text-red-700">@lang('learning-tools::app.mistakes.sources.' . $mistake['source_type'])</span><span class="text-xs font-bold text-slate-400">{{ collect([$mistake['subject'], $mistake['topic']])->filter()->join(' · ') }}</span></div>
                    <h2 class="mt-4 font-black text-slate-900">{{ $mistake['content'] }}</h2>
                    <dl class="mt-4 grid gap-3 rounded-2xl bg-slate-50 p-4 text-sm sm:grid-cols-2"><div><dt class="text-xs font-black uppercase text-slate-400">@lang('learning-tools::app.mistakes.your_answer')</dt><dd class="mt-1 font-bold text-red-700">{{ collect($mistake['student_answer'])->flatten()->join(', ') ?: '—' }}</dd></div><div><dt class="text-xs font-black uppercase text-slate-400">@lang('learning-tools::app.mistakes.correct_answer')</dt><dd class="mt-1 font-bold text-green-700">{{ collect($mistake['correct_answers'])->flatten()->join(', ') ?: '—' }}</dd></div></dl>
                    @if($mistake['explanation'])<p class="mt-3 text-sm font-semibold text-slate-500">{{ $mistake['explanation'] }}</p>@endif
                    <form method="POST" action="{{ route('learning-tools.mistakes.update') }}" class="mt-4 space-y-3">@csrf @method('PATCH')<input type="hidden" name="source_type" value="{{ $mistake['source_type'] }}"><input type="hidden" name="source_answer_id" value="{{ $mistake['source_answer_id'] }}"><textarea name="note" rows="2" placeholder="{{ __('learning-tools::app.mistakes.note_placeholder') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold">{{ $mistake['review']?->note }}</textarea><div class="flex items-center justify-between"><label class="inline-flex items-center gap-2 text-xs font-black text-slate-600"><input type="checkbox" name="is_resolved" value="1" @checked($mistake['review']?->is_resolved) class="rounded border-slate-300 text-green-600">@lang('learning-tools::app.mistakes.resolved')</label><button class="rounded-full bg-slate-900 px-5 py-2 text-xs font-black text-white">@lang('learning-tools::app.actions.save')</button></div></form>
                </article>
            @empty<div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white py-16 text-center"><x-heroicon-o-check-circle class="mx-auto h-10 w-10 text-green-400" /><p class="mt-3 font-black text-slate-600">@lang('learning-tools::app.mistakes.empty')</p></div>@endforelse
        </section>
    </main>
</div>
@endsection
