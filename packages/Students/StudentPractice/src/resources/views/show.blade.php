@extends('Mindigo-dashboard::layouts')

@section('title', __('student-practice::app.question').' - Auronsoft LMS')
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="min-h-screen bg-slate-50"><header class="border-b border-slate-200 bg-white px-6 py-4"><a href="{{ route('student.practice.index') }}" class="text-xs font-black text-green-700 no-underline">← @lang('student-practice::app.back')</a><h1 class="mt-2 text-lg font-black text-slate-950">@lang('student-practice::app.question_bank')</h1></header><main class="mx-auto max-w-4xl p-6"><article class="rounded-xl border border-slate-200 bg-white p-6"><div class="flex flex-wrap gap-2 text-xs font-bold text-slate-400"><span>{{ $question->subject }}</span><span>·</span><span>{{ $question->topic }}</span><span>·</span><span>{{ __('student-practice::app.difficulties.'.$question->difficulty) }}</span></div><div class="mt-5 text-base font-semibold leading-7 text-slate-900">{!! $question->content !!}</div>@if($question->options)<div class="mt-5 space-y-2">@foreach($question->options as $option)<div class="rounded-lg border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700">{{ data_get($option, 'content', data_get($option, 'text')) }}</div>@endforeach</div>@endif</article></main></div>
@endsection
