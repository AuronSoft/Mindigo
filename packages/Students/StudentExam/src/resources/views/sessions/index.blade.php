@extends('Mindigo-dashboard::layouts')
@section('title', __('student-exam::app.session_workspace.title'))
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Students/StudentExam/src/resources/css/app.css'])
@endsection
@section('content')
<main class="min-h-screen bg-slate-50 p-4 sm:p-6"><div class="mx-auto flex max-w-7xl flex-col gap-6">
    <header class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><p class="text-xs font-black uppercase tracking-widest text-green-700">@lang('student-exam::app.session_workspace.eyebrow')</p><h1 class="mt-1 text-2xl font-black text-slate-950">@lang('student-exam::app.session_workspace.title')</h1><p class="mt-2 text-sm font-semibold text-slate-500">@lang('student-exam::app.session_workspace.description')</p></header>
    @foreach(['available', 'upcoming', 'completed'] as $group)
        <section class="rounded-2xl border border-slate-200 bg-white p-5"><h2 class="text-base font-black text-slate-900">@lang('student-exam::app.session_workspace.'.$group)</h2>
            @if($$group->isEmpty())<p class="mt-4 rounded-xl bg-slate-50 p-4 text-sm font-semibold text-slate-500">@lang('student-exam::app.session_workspace.empty')</p>
            @else<div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">@foreach($$group as $session) @php($active = $session->attempts->first(fn ($attempt) => $attempt->isActive()))
                <article class="rounded-xl border border-slate-200 p-4"><p class="text-xs font-bold text-green-700">{{ $session->version->template->title }}</p><h3 class="mt-1 text-base font-black text-slate-900">{{ $session->title }}</h3><div class="mt-3 space-y-1 text-xs font-semibold text-slate-500"><p>{{ __('student-exam::app.session_workspace.starts_at', ['time' => $session->starts_at?->format('d/m/Y H:i')]) }}</p><p>{{ __('student-exam::app.session_workspace.duration', ['minutes' => $session->duration_minutes]) }} · {{ __('student-exam::app.session_workspace.attempts', ['count' => $session->max_attempts]) }}</p></div>
                    @if($group === 'available')<form method="POST" action="{{ route('student.exam-sessions.start', $session) }}" class="mt-4">@csrf<button class="inline-flex h-10 items-center rounded-xl bg-green-600 px-4 text-sm font-black text-white">@lang('student-exam::app.session_workspace.'.($active ? 'continue' : 'start'))</button></form>@endif
                </article>
            @endforeach</div>@endif
        </section>
    @endforeach
</div></main>
@endsection
