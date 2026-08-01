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
            <a href="{{ route('student.practice.history') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-xs font-black text-slate-700 no-underline hover:border-green-300 hover:text-green-700">@lang('student-practice::app.history')</a>
        </div>
    </header>

    <main class="grid gap-6 p-6 xl:grid-cols-[22rem_minmax(0,1fr)]">
        <section class="self-start rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="text-base font-black text-slate-950">@lang('student-practice::app.start_new')</h2>
            @if($errors->any())<div class="mt-4 border border-red-200 bg-red-50 px-4 py-3 text-xs font-semibold text-red-700">{{ $errors->first() }}</div>@endif
            <form action="{{ route('student.practice.start') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <label class="block"><span class="mb-1.5 block text-xs font-bold text-slate-600">@lang('student-practice::app.mode')</span><select name="mode" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700"><option value="mixed">@lang('student-practice::app.modes.mixed')</option><option value="subject">@lang('student-practice::app.modes.subject')</option><option value="topic">@lang('student-practice::app.modes.topic')</option></select></label>
                <label class="block"><span class="mb-1.5 block text-xs font-bold text-slate-600">@lang('student-practice::app.subject')</span><select name="subject" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700"><option value="">@lang('student-practice::app.all')</option>@foreach($formData['subjects'] as $subject)<option value="{{ $subject }}">{{ $subject }}</option>@endforeach</select></label>
                <label class="block"><span class="mb-1.5 block text-xs font-bold text-slate-600">@lang('student-practice::app.topic')</span><input name="topic" value="{{ old('topic') }}" class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm font-semibold text-slate-700"></label>
                <label class="block"><span class="mb-1.5 block text-xs font-bold text-slate-600">@lang('student-practice::app.difficulty')</span><select name="difficulty" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700"><option value="">@lang('student-practice::app.all')</option>@foreach($formData['difficulties'] as $difficulty)<option value="{{ $difficulty }}">{{ __('student-practice::app.difficulties.'.$difficulty) }}</option>@endforeach</select></label>
                <label class="block"><span class="mb-1.5 block text-xs font-bold text-slate-600">@lang('student-practice::app.question_count')</span><input type="number" name="question_count" value="{{ old('question_count', 10) }}" min="1" max="50" class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm font-semibold text-slate-700"></label>
                <button class="w-full rounded-lg bg-green-600 px-4 py-2.5 text-sm font-black text-white hover:bg-green-700">@lang('student-practice::app.start')</button>
            </form>
        </section>

        <section>
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3"><h2 class="text-lg font-black text-slate-950">@lang('student-practice::app.question_bank')</h2><form method="GET" class="flex gap-2"><input name="keyword" value="{{ request('keyword') }}" placeholder="@lang('student-practice::app.search')" class="h-10 w-64 rounded-lg border border-slate-300 px-3 text-sm font-semibold"><button class="rounded-lg border border-slate-300 px-4 text-xs font-black text-slate-700">@lang('student-practice::app.filter')</button></form></div>
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
</div>
@endsection
