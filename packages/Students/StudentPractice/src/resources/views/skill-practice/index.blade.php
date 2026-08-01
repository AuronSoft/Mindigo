@extends('Mindigo-dashboard::layouts')

@section('title', __('student-practice::app.skill_practice.title').' - Mindigo LMS')
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="min-h-screen bg-slate-50">
    <header class="border-b border-slate-200 bg-white px-6 py-4">
        <p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('student-practice::app.area')</p>
        <div class="flex items-center justify-between gap-4"><div><h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('student-practice::app.skill_practice.title')</h1><p class="text-xs font-semibold text-slate-400">@lang('student-practice::app.skill_practice.subtitle')</p></div><a href="{{ route('student.practice.index') }}" aria-label="@lang('student-practice::app.back')" class="grid h-10 w-10 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline hover:text-green-700"><x-heroicon-o-arrow-left class="h-5 w-5" /></a></div>
    </header>
    <main class="p-6">
        <form method="GET" class="mb-5 flex flex-wrap gap-2">
            <input type="search" name="keyword" value="{{ request('keyword') }}" placeholder="@lang('student-practice::app.skill_practice.search')" class="h-10 min-w-64 flex-1 rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100">
            <input name="grade_level" value="{{ request('grade_level') }}" placeholder="@lang('student-practice::app.skills.grade_level')" class="h-10 w-40 rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold outline-none focus:border-green-500">
            <button class="rounded-xl bg-green-600 px-5 text-sm font-black text-white hover:bg-green-700">@lang('student-practice::app.filter')</button>
        </form>
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            @forelse($skills as $skill)
                @php($item = $progressBySkill->get($skill->id))
                <a href="{{ route('student.practice.skills.show', $skill) }}" class="grid gap-3 border-b border-slate-100 px-5 py-4 text-slate-700 no-underline last:border-0 hover:bg-slate-50 md:grid-cols-[minmax(0,1fr)_8rem_8rem_2rem] md:items-center">
                    <div><span class="text-xs font-black uppercase tracking-wider text-green-700">{{ $skill->subject->name }}{{ $skill->grade_level ? ' · '.$skill->grade_level : '' }}</span><strong class="mt-1 block text-sm text-slate-950">{{ $skill->name }}</strong><span class="mt-1 block text-xs font-semibold text-slate-400">{{ $skill->description ?: __('student-practice::app.skill_practice.no_description') }}</span></div>
                    <div><span class="block text-lg font-black text-slate-950">{{ $skill->questions_count }}</span><span class="text-xs font-bold text-slate-400">@lang('student-practice::app.questions')</span></div>
                    <div><span class="block text-lg font-black text-slate-950">{{ number_format($item?->accuracy ?? 0, 1) }}%</span><span class="text-xs font-bold text-slate-400">@lang('student-practice::app.accuracy')</span></div>
                    <x-heroicon-o-chevron-right class="h-5 w-5 text-slate-400" />
                </a>
            @empty
                <p class="px-6 py-14 text-center text-sm font-semibold text-slate-400">@lang('student-practice::app.skill_practice.empty')</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $skills->links() }}</div>
    </main>
</div>
@endsection
