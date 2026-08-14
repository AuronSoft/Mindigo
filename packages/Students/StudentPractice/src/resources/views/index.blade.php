@extends('Mindigo-dashboard::layouts')

@section('title', __('student-practice::app.title').' - Mindigo LMS')
@section('meta_description', __('student-practice::app.subtitle'))

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="min-h-screen bg-slate-50">
    <header class="border-b border-slate-200 bg-white px-6 py-4">
        <p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('student-practice::app.area')</p>
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div><h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('student-practice::app.title')</h1><p class="text-xs font-semibold text-slate-400">@lang('student-practice::app.subtitle')</p></div>
            <div class="flex gap-2"><a href="{{ route('student.practice.analytics.index') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-700 no-underline hover:border-green-200 hover:text-green-700">@lang('student-practice::app.analytics.title')</a><a href="{{ route('student.practice.adaptive.index') }}" class="rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-xs font-black text-green-700 no-underline hover:bg-green-100">@lang('student-practice::app.adaptive.title')</a><a href="{{ route('student.practice.skills.index') }}" class="rounded-lg bg-green-600 px-4 py-2 text-xs font-black text-white no-underline hover:bg-green-700">@lang('student-practice::app.skill_practice.catalog')</a><a href="{{ route('student.practice.history') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-xs font-black text-slate-700 no-underline hover:border-green-300 hover:text-green-700">@lang('student-practice::app.history')</a></div>
        </div>
    </header>

    <main class="grid gap-6 p-6 xl:grid-cols-[22rem_minmax(0,1fr)]">
        <section class="self-start rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="text-base font-black text-slate-950">@lang('student-practice::app.start_new')</h2>
            @if($errors->any())<div role="alert" class="mt-4 border border-red-200 bg-red-50 px-4 py-3 text-xs font-semibold text-red-700">{{ $errors->first() }}</div>@endif
            <form action="{{ route('student.practice.start') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <label class="block"><span class="mb-1.5 block text-xs font-bold text-slate-600">@lang('student-practice::app.mode')</span><select name="mode" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700"><option value="mixed">@lang('student-practice::app.modes.mixed')</option><option value="subject">@lang('student-practice::app.modes.subject')</option><option value="topic">@lang('student-practice::app.modes.topic')</option><option value="skill">@lang('student-practice::app.modes.skill')</option></select></label>
                <label class="block"><span class="mb-1.5 block text-xs font-bold text-slate-600">@lang('student-practice::app.skills.singular')</span><select name="skill_id" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700"><option value="">@lang('student-practice::app.all')</option>@foreach($formData['skills'] as $skill)<option value="{{ $skill->id }}">{{ $skill->subject->name }} · {{ $skill->name }} ({{ $skill->questions_count }})</option>@endforeach</select></label>
                <label class="block"><span class="mb-1.5 block text-xs font-bold text-slate-600">@lang('student-practice::app.subject')</span><select name="subject" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700"><option value="">@lang('student-practice::app.all')</option>@foreach($formData['subjects'] as $subject)<option value="{{ $subject }}">{{ $subject }}</option>@endforeach</select></label>
                <label class="block"><span class="mb-1.5 block text-xs font-bold text-slate-600">@lang('student-practice::app.topic')</span><input name="topic" value="{{ old('topic') }}" class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm font-semibold text-slate-700"></label>
                <label class="block"><span class="mb-1.5 block text-xs font-bold text-slate-600">@lang('student-practice::app.difficulty')</span><select name="difficulty" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700"><option value="">@lang('student-practice::app.all')</option>@foreach($formData['difficulties'] as $difficulty)<option value="{{ $difficulty }}">{{ __('student-practice::app.difficulties.'.$difficulty) }}</option>@endforeach</select></label>
                <label class="block"><span class="mb-1.5 block text-xs font-bold text-slate-600">@lang('student-practice::app.question_count')</span><input type="number" name="question_count" value="{{ old('question_count', 10) }}" min="1" max="50" class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm font-semibold text-slate-700"></label>
                <button class="w-full rounded-lg bg-green-600 px-4 py-2.5 text-sm font-black text-white hover:bg-green-700">@lang('student-practice::app.start')</button>
            </form>
        </section>

        <section>
            @php
                $practiceFilterFields = ['subject', 'topic', 'skill_id', 'difficulty', 'type'];
                $practiceFilterCount = collect($practiceFilterFields)->filter(fn ($field) => request()->filled($field))->count();
            @endphp
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-black text-slate-950">@lang('student-practice::app.question_bank')</h2>
                <div class="flex w-full items-center gap-2 sm:w-auto">
                    <form method="GET" action="{{ route('student.practice.index') }}" role="search" class="relative min-w-0 flex-1 sm:w-72 sm:flex-none">
                        @foreach($practiceFilterFields as $field)@if(request()->filled($field))<input type="hidden" name="{{ $field }}" value="{{ request($field) }}">@endif @endforeach
                        <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-green-700" />
                        <input type="search" name="keyword" value="{{ request('keyword') }}" aria-label="@lang('student-practice::app.search')" placeholder="@lang('student-practice::app.search')" class="h-10 w-full rounded-xl border border-slate-300 bg-white pl-9 pr-3 text-sm font-semibold text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-100">
                    </form>
                    <button type="button" data-mindigo-drawer-open="student-practice-filter" class="inline-flex h-10 shrink-0 items-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 shadow-sm transition hover:border-green-200 hover:bg-green-50 hover:text-green-700">
                        <x-heroicon-o-adjustments-horizontal class="h-4 w-4" />@lang('student-practice::app.filter')
                        @if($practiceFilterCount > 0)<span class="grid h-5 min-w-5 place-items-center rounded-full bg-green-600 px-1.5 text-[11px] text-white">{{ $practiceFilterCount }}</span>@endif
                    </button>
                </div>
            </div>
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <div class="divide-y divide-slate-100">
                    @forelse($questions as $question)
                        <a href="{{ route('student.practice.show', $question) }}" class="flex items-center gap-4 px-5 py-4 text-slate-700 no-underline hover:bg-slate-50">
                            <x-heroicon-o-pencil-square class="h-6 w-6 shrink-0 text-green-600" />
                            <span class="min-w-0 flex-1"><strong class="block truncate text-sm font-bold text-slate-900">{{ strip_tags($question->content) }}</strong><span class="mt-1 block text-xs font-semibold text-slate-400">{{ $question->subject ?: __('student-practice::app.not_available') }} · {{ $question->topic ?: __('student-practice::app.not_available') }}</span></span>
                            <span class="text-xs font-bold text-slate-400">{{ __('student-practice::app.difficulties.'.$question->difficulty) }}</span>
                        </a>
                    @empty
                        <p class="px-6 py-12 text-center text-sm font-semibold text-slate-400">@lang('student-practice::app.no_questions')</p>
                    @endforelse
                </div>
            </div>
            <div class="mt-4">{{ $questions->links() }}</div>
        </section>
    </main>

    <div data-mindigo-drawer="student-practice-filter" class="fixed inset-0 z-40 hidden bg-slate-950/45 opacity-0 backdrop-blur-sm transition-opacity duration-200"></div>
    <aside data-mindigo-drawer-panel="student-practice-filter" aria-label="@lang('student-practice::app.filter_title')" class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col border-l border-slate-200 bg-white shadow-2xl shadow-slate-950/20 transition-transform duration-200" style="transform: translateX(100%);">
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4"><div><p class="text-xs font-black uppercase tracking-wider text-green-700">@lang('student-practice::app.area')</p><h2 class="mt-1 text-xl font-black text-slate-950">@lang('student-practice::app.filter_title')</h2><p class="mt-1 text-sm font-semibold leading-relaxed text-slate-500">@lang('student-practice::app.filter_subtitle')</p></div><button type="button" aria-label="@lang('student-practice::app.close')" data-mindigo-drawer-close="student-practice-filter" class="grid h-10 w-10 shrink-0 place-items-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-800"><x-heroicon-o-x-mark class="h-5 w-5" /></button></div>
        <form action="{{ route('student.practice.index') }}" method="GET" class="flex flex-1 flex-col">
            @if(request()->filled('keyword'))<input type="hidden" name="keyword" value="{{ request('keyword') }}">@endif
            <div class="flex-1 space-y-5 overflow-y-auto px-5 py-5">
                @php($drawerSelectClass = 'block h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none transition focus:border-green-300 focus:ring-4 focus:ring-green-50')
                <label class="block space-y-2"><span class="block text-xs font-black uppercase tracking-wider text-slate-500">@lang('student-practice::app.subject')</span><select name="subject" class="{{ $drawerSelectClass }}"><option value="">@lang('student-practice::app.filter_subject')</option>@foreach($formData['subjects'] as $subject)<option value="{{ $subject }}" @selected(request('subject') === $subject)>{{ $subject }}</option>@endforeach</select></label>
                <label class="block space-y-2"><span class="block text-xs font-black uppercase tracking-wider text-slate-500">@lang('student-practice::app.topic')</span><select name="topic" class="{{ $drawerSelectClass }}"><option value="">@lang('student-practice::app.filter_topic')</option>@foreach($formData['subjectTopics'] as $subject => $topics)@foreach($topics as $topic)<option value="{{ $topic }}" @selected(request('topic') === $topic)>{{ $subject }} · {{ $topic }}</option>@endforeach @endforeach</select></label>
                <label class="block space-y-2"><span class="block text-xs font-black uppercase tracking-wider text-slate-500">@lang('student-practice::app.skills.singular')</span><select name="skill_id" class="{{ $drawerSelectClass }}"><option value="">@lang('student-practice::app.filter_skill')</option>@foreach($formData['skills'] as $skill)<option value="{{ $skill->id }}" @selected((string) request('skill_id') === (string) $skill->id)>{{ $skill->subject->name }} · {{ $skill->name }}</option>@endforeach</select></label>
                <label class="block space-y-2"><span class="block text-xs font-black uppercase tracking-wider text-slate-500">@lang('student-practice::app.difficulty')</span><select name="difficulty" class="{{ $drawerSelectClass }}"><option value="">@lang('student-practice::app.filter_difficulty')</option>@foreach($formData['difficulties'] as $difficulty)<option value="{{ $difficulty }}" @selected(request('difficulty') === $difficulty)>@lang('student-practice::app.difficulties.'.$difficulty)</option>@endforeach</select></label>
                <label class="block space-y-2"><span class="block text-xs font-black uppercase tracking-wider text-slate-500">@lang('student-practice::app.question_type')</span><select name="type" class="{{ $drawerSelectClass }}"><option value="">@lang('student-practice::app.filter_type')</option>@foreach($formData['types'] as $type)<option value="{{ $type }}" @selected(request('type') === $type)>@lang('student-practice::app.types.'.$type)</option>@endforeach</select></label>
            </div>
            <div class="grid grid-cols-2 gap-3 border-t border-slate-100 p-5"><a href="{{ route('student.practice.index') }}" class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-600 no-underline transition hover:bg-slate-50">@lang('student-practice::app.clear_filter')</a><button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-green-600 px-4 text-sm font-black text-white shadow-sm shadow-green-200 transition hover:bg-green-500"><x-heroicon-o-funnel class="h-4 w-4" />@lang('student-practice::app.apply_filter')</button></div>
        </form>
    </aside>
</div>
@endsection
