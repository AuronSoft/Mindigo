@extends('Mindigo-dashboard::layouts')

@section('title', __('student-practice::app.adaptive.title').' - Mindigo LMS')
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="min-h-screen bg-slate-50">
    <header class="border-b border-slate-200 bg-white px-6 py-4">
        <div class="flex items-center justify-between gap-4"><div><p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('student-practice::app.area')</p><h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('student-practice::app.adaptive.title')</h1><p class="text-xs font-semibold text-slate-400">@lang('student-practice::app.adaptive.subtitle')</p></div><a href="{{ route('student.practice.index') }}" aria-label="@lang('student-practice::app.back')" class="grid h-10 w-10 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline hover:text-green-700"><x-heroicon-o-arrow-left class="h-5 w-5" /></a></div>
    </header>
    <main class="space-y-6 p-6">
        <section><div class="mb-3"><h2 class="text-base font-black text-slate-950">@lang('student-practice::app.adaptive.next_path')</h2><p class="text-xs font-semibold text-slate-400">@lang('student-practice::app.adaptive.next_path_hint')</p></div>
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                @forelse($recommendations as $recommendation)
                    <div class="grid gap-4 border-b border-slate-100 px-5 py-4 last:border-0 md:grid-cols-[minmax(0,1fr)_9rem_10rem_auto] md:items-center">
                        <div><span class="text-[11px] font-black uppercase tracking-wider text-green-700">{{ $recommendation->skill->subject->name }}</span><strong class="mt-1 block text-sm text-slate-950">{{ $recommendation->skill->name }}</strong><span class="mt-1 block text-xs font-semibold text-slate-400">{{ __('student-practice::app.adaptive.reasons.'.$recommendation->reason_code) }}</span></div>
                        <div><span class="block text-xs font-bold text-slate-400">@lang('student-practice::app.adaptive.recommendation')</span><strong class="text-sm text-slate-700">{{ __('student-practice::app.adaptive.types.'.$recommendation->type) }}</strong></div>
                        <div><span class="block text-xs font-bold text-slate-400">@lang('student-practice::app.adaptive.target_difficulty')</span><strong class="text-sm text-slate-700">{{ __('student-practice::app.difficulties.'.$recommendation->target_difficulty) }}</strong></div>
                        <form action="{{ route('student.practice.adaptive.start', $recommendation->skill) }}" method="POST">@csrf<input type="hidden" name="question_count" value="10"><button class="h-10 rounded-lg bg-green-600 px-4 text-xs font-black text-white hover:bg-green-700">@lang('student-practice::app.adaptive.start')</button></form>
                    </div>
                @empty
                    <div class="px-6 py-14 text-center"><p class="text-sm font-bold text-slate-500">@lang('student-practice::app.adaptive.empty')</p><a href="{{ route('student.practice.skills.index') }}" class="mt-3 inline-flex text-sm font-black text-green-700 no-underline">@lang('student-practice::app.adaptive.choose_skill')</a></div>
                @endforelse
            </div>
        </section>
        <section><h2 class="mb-3 text-base font-black text-slate-950">@lang('student-practice::app.adaptive.mastery_overview')</h2><div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            @forelse($progress as $item)<a href="{{ route('student.practice.skills.show', $item->skill) }}" class="grid gap-3 border-b border-slate-100 px-5 py-4 text-slate-700 no-underline last:border-0 hover:bg-slate-50 sm:grid-cols-[minmax(0,1fr)_8rem_8rem_2rem] sm:items-center"><div><span class="text-xs font-black uppercase tracking-wider text-green-700">{{ $item->skill->subject->name }}</span><strong class="mt-1 block text-sm text-slate-950">{{ $item->skill->name }}</strong></div><div><strong class="block text-lg text-slate-950">{{ number_format($item->mastery_score, 1) }}%</strong><span class="text-xs font-bold text-slate-400">@lang('student-practice::app.adaptive.mastery')</span></div><div><strong class="block text-sm text-slate-700">{{ __('student-practice::app.adaptive.levels.'.$item->mastery_level) }}</strong><span class="text-xs font-bold text-slate-400">@lang('student-practice::app.adaptive.confidence') {{ number_format($item->confidence_score, 0) }}%</span></div><x-heroicon-o-chevron-right class="h-5 w-5 text-slate-400" /></a>@empty<p class="px-6 py-12 text-center text-sm font-semibold text-slate-400">@lang('student-practice::app.adaptive.no_progress')</p>@endforelse
        </div><div class="mt-4">{{ $progress->links() }}</div></section>
    </main>
</div>
@endsection
