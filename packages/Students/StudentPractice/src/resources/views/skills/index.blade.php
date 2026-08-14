@extends('Mindigo-dashboard::layouts')

@section('title', __('student-practice::app.skills.title').' - Auronsoft LMS')
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="min-h-screen bg-slate-50">
    <header class="border-b border-slate-200 bg-white px-6 py-4">
        <p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('student-practice::app.skills.area')</p>
        <div class="flex flex-wrap items-end justify-between gap-3"><div><h1 class="text-lg font-black text-slate-950">@lang('student-practice::app.skills.title')</h1><p class="text-xs font-semibold text-slate-400">@lang('student-practice::app.skills.subtitle')</p></div><a href="{{ route('practice.skills.create') }}" class="rounded-lg bg-green-600 px-4 py-2.5 text-sm font-black text-white no-underline hover:bg-green-700">@lang('student-practice::app.skills.create')</a></div>
    </header>
    <main class="p-6">
        @if(session('success'))<div class="mb-5 border border-green-200 bg-green-50 px-4 py-3 text-sm font-bold text-green-800">{{ session('success') }}</div>@endif
        <form method="GET" class="mb-5 flex flex-wrap gap-3 rounded-xl border border-slate-200 bg-white p-4">
            <input name="keyword" value="{{ request('keyword') }}" placeholder="@lang('student-practice::app.skills.search')" class="h-10 min-w-64 flex-1 rounded-lg border border-slate-300 px-3 text-sm font-semibold">
            <select name="subject_id" class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold"><option value="">@lang('student-practice::app.all')</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected((string) request('subject_id') === (string) $subject->id)>{{ $subject->name }}</option>@endforeach</select>
            <select name="status" class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold"><option value="">@lang('student-practice::app.all')</option>@foreach($statuses as $status)<option value="{{ $status }}" @selected(request('status') === $status)>@lang('student-practice::app.skills.statuses.'.$status)</option>@endforeach</select>
            <button class="rounded-lg border border-slate-300 px-4 text-xs font-black text-slate-700">@lang('student-practice::app.filter')</button>
        </form>
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white"><div class="overflow-x-auto"><table class="w-full min-w-215 text-left"><thead><tr class="border-b border-slate-200 text-xs font-bold text-slate-500"><th class="px-5 py-4">@lang('student-practice::app.skills.code')</th><th class="px-5 py-4">@lang('student-practice::app.skills.name')</th><th class="px-5 py-4">@lang('student-practice::app.subject')</th><th class="px-5 py-4">@lang('student-practice::app.topic')</th><th class="px-5 py-4">@lang('student-practice::app.questions')</th><th class="px-5 py-4">@lang('student-practice::app.skills.status')</th><th class="px-5 py-4"></th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($skills as $item)<tr class="text-sm font-semibold text-slate-600"><td class="px-5 py-4 font-black text-green-700">{{ $item->code }}</td><td class="px-5 py-4"><strong class="block text-slate-900">{{ $item->name }}</strong><span class="text-xs text-slate-400">{{ $item->grade_level ?: __('student-practice::app.not_available') }}</span></td><td class="px-5 py-4">{{ $item->subject->name }}</td><td class="px-5 py-4">{{ $item->topic?->name ?? __('student-practice::app.not_available') }}</td><td class="px-5 py-4">{{ $item->questions_count }}</td><td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-black {{ $item->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500' }}">@lang('student-practice::app.skills.statuses.'.$item->status)</span></td><td class="px-5 py-4 text-right">@can('update', $item)<a href="{{ route('practice.skills.edit', $item) }}" class="text-xs font-black text-green-700 no-underline">@lang('student-practice::app.skills.edit')</a>@endcan</td></tr>@empty<tr><td colspan="7" class="px-6 py-12 text-center text-sm font-semibold text-slate-400">@lang('student-practice::app.skills.empty')</td></tr>@endforelse
        </tbody></table></div></section><div class="mt-4">{{ $skills->links() }}</div>
    </main>
</div>
@endsection
