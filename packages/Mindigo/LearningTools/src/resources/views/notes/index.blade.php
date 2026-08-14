@extends('Mindigo-dashboard::layouts')
@section('title', __('learning-tools::app.notes.title') . ' · Auronsoft LMS')
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    @include('learning-tools::partials.header', [
        'eyebrow' => __('learning-tools::app.eyebrow'),
        'title' => __('learning-tools::app.notes.title'),
        'subtitle' => __('learning-tools::app.notes.subtitle'),
        'actionRoute' => route('learning-tools.notes.create'),
        'actionLabel' => __('learning-tools::app.notes.new'),
    ])
    <div class="p-6">
        <form method="GET" class="flex flex-col gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('learning-tools::app.notes.search') }}" class="h-11 min-w-0 flex-1 rounded-xl border border-slate-200 px-4 text-sm font-bold outline-none focus:border-green-400 focus:ring-4 focus:ring-green-50">
            <select name="subject" class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700">
                <option value="">@lang('learning-tools::app.fields.all_subjects')</option>
                @foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected(request('subject') == $subject->id)>{{ $subject->name }}</option>@endforeach
            </select>
            <button class="h-11 rounded-full bg-green-600 px-6 text-sm font-black text-white">@lang('learning-tools::app.actions.filter')</button>
        </form>
        <section class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($notes as $note)
                <a href="{{ route('learning-tools.notes.edit', $note) }}" class="group min-h-52 rounded-3xl border border-slate-200 bg-white p-5 text-slate-800 no-underline shadow-sm transition hover:border-green-200 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-green-50 text-green-700"><x-heroicon-o-book-open class="h-5 w-5" /></span>
                        @if($note->is_pinned)<x-heroicon-s-bookmark class="h-5 w-5 text-amber-500" />@endif
                    </div>
                    <h2 class="mt-4 line-clamp-1 font-black group-hover:text-green-700">{{ $note->title }}</h2>
                    <p class="mt-2 line-clamp-3 text-sm font-semibold leading-6 text-slate-400">{{ $note->content }}</p>
                    <div class="mt-4 flex items-center justify-between text-xs font-bold text-slate-400">
                        <span>{{ $note->subject?->name ?? __('learning-tools::app.notes.no_subject') }}</span>
                        <span>{{ $note->updated_at->diffForHumans() }}</span>
                    </div>
                </a>
            @empty
                <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white py-16 text-center">
                    <x-heroicon-o-book-open class="mx-auto h-10 w-10 text-slate-300" />
                    <p class="mt-3 font-black text-slate-600">@lang('learning-tools::app.notes.empty')</p>
                </div>
            @endforelse
        </section>
        <div class="mt-5">{{ $notes->links() }}</div>
    </div>
</div>
@endsection
