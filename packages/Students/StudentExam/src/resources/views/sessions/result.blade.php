@extends('Mindigo-dashboard::layouts')
@section('title', __('student-exam::app.session_workspace.result'))
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Students/StudentExam/src/resources/css/app.css'])
@endsection
@section('content')
<main class="min-h-screen bg-slate-50 p-4 sm:p-6"><div class="mx-auto max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm"><p class="text-xs font-black uppercase tracking-widest text-green-700">@lang('student-exam::app.session_workspace.result')</p><h1 class="mt-2 text-2xl font-black text-slate-950">{{ $attempt->session->title }}</h1>
    @if($attempt->needs_review)<p class="mt-6 rounded-xl bg-amber-50 p-4 text-sm font-bold text-amber-800">@lang('student-exam::app.session_workspace.pending_review')</p>
    @elseif($visible)<p class="mt-6 text-4xl font-black text-slate-950">{{ __('student-exam::app.session_workspace.score', ['score' => $attempt->score, 'max' => $attempt->max_score]) }}</p><p class="mt-2 text-sm font-black {{ $attempt->passed ? 'text-green-700' : 'text-rose-700' }}">@lang('student-exam::app.session_workspace.'.($attempt->passed ? 'passed' : 'failed'))</p>
    @else<p class="mt-6 rounded-xl bg-slate-50 p-4 text-sm font-bold text-slate-600">@lang('student-exam::app.session_workspace.result_hidden')</p>@endif
    <a href="{{ route('student.exam-sessions.index') }}" class="mt-6 inline-flex rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-black text-slate-700 no-underline hover:bg-slate-50">@lang('student-exam::app.session_workspace.back')</a>
</div></main>
@endsection
