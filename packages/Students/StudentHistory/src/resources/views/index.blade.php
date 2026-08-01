@extends('Mindigo-dashboard::layouts')
@section('title', __('student-history::app.title') . ' · Mindigo LMS')
@section('meta_description', __('student-history::app.subtitle'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
@php
    $tabs = [
        ''           => __('student-history::app.filter_all'),
        'assignment' => __('student-history::app.filter_assignment'),
        'exam'       => __('student-history::app.filter_exam'),
    ];
@endphp
<div class="flex min-h-screen flex-col bg-slate-50">

    {{-- Header --}}
    <header class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white/90 px-6 py-4 backdrop-blur">
        <div>
            <p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('student-history::app.area')</p>
            <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('student-history::app.title')</h1>
            <p class="text-xs font-semibold text-slate-400">@lang('student-history::app.subtitle')</p>
        </div>
        <span class="hidden sm:grid h-11 w-11 place-items-center rounded-2xl bg-green-50 text-green-600">
            <x-heroicon-o-clock class="h-6 w-6" />
        </span>
    </header>

    <div class="flex flex-1 flex-col gap-5 p-6">

        {{-- Summary cards --}}
        <section class="grid grid-cols-3 gap-5 max-lg:grid-cols-1">
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold text-slate-400">@lang('student-history::app.card_assignments')</p>
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-green-50 text-green-600"><x-heroicon-o-clipboard-document-list class="h-5 w-5" /></span>
                </div>
                <p class="mt-2 text-3xl font-black text-slate-800">{{ $summary['assignments'] }}</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold text-slate-400">@lang('student-history::app.card_exams')</p>
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-violet-50 text-violet-600"><x-heroicon-o-document-text class="h-5 w-5" /></span>
                </div>
                <p class="mt-2 text-3xl font-black text-slate-800">{{ $summary['exams'] }}</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold text-slate-400">@lang('student-history::app.card_avg')</p>
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-amber-50 text-amber-600"><x-heroicon-o-academic-cap class="h-5 w-5" /></span>
                </div>
                <p class="mt-2 text-3xl font-black text-slate-800">{{ $summary['avg_score'] }}%</p>
                <p class="text-[11px] font-bold text-slate-400">@lang('student-history::app.scale_100')</p>
            </div>
        </section>

        {{-- Filter tabs --}}
        <div class="flex items-center gap-2">
            @foreach($tabs as $value => $label)
                @php $active = (string) $type === (string) $value; @endphp
                <a href="{{ route('student.history.index', $value ? ['type' => $value] : []) }}"
                   class="inline-flex h-9 items-center rounded-full px-4 text-xs font-black no-underline transition {{ $active ? 'bg-green-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Timeline --}}
        @if($items->isEmpty())
            <div class="flex flex-1 flex-col items-center justify-center gap-4 rounded-3xl border border-dashed border-slate-200 bg-white py-20">
                <span class="grid h-20 w-20 place-items-center rounded-full bg-slate-50 text-slate-300"><x-heroicon-o-clock class="h-10 w-10" /></span>
                <div class="text-center">
                    <p class="text-lg font-black text-slate-700">@lang('student-history::app.empty_title')</p>
                    <p class="mt-1 max-w-xs text-sm font-semibold leading-relaxed text-slate-400">@lang('student-history::app.empty_desc')</p>
                </div>
            </div>
        @else
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                @foreach($items as $item)
                    @php $isExam = $item->type === 'exam'; @endphp
                    <div class="flex items-start gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl {{ $isExam ? 'bg-violet-50 text-violet-600' : 'bg-green-50 text-green-600' }}">
                            <x-dynamic-component :component="$isExam ? 'heroicon-o-document-text' : 'heroicon-o-clipboard-document-list'" class="h-5 w-5" />
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-black uppercase {{ $isExam ? 'bg-violet-50 text-violet-600' : 'bg-green-50 text-green-600' }}">
                                    @lang('student-history::app.type_' . $item->type)
                                </span>
                                @if($item->is_late)
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-black uppercase text-red-700">@lang('student-history::app.late')</span>
                                @endif
                                @if($isExam && ! is_null($item->passed))
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-black uppercase {{ $item->passed ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $item->passed ? __('student-history::app.passed') : __('student-history::app.failed') }}
                                    </span>
                                @elseif(! $isExam)
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black uppercase text-slate-500">@lang('student-history::app.status_' . $item->status)</span>
                                @endif
                            </div>
                            <p class="mt-1 truncate font-black text-slate-800">{{ $item->title }}</p>
                            <p class="text-xs font-bold text-slate-400">
                                {{ $isExam ? __('student-history::app.done_at') : __('student-history::app.submitted_at') }}:
                                {{ $item->at?->format('d/m/Y H:i') }}
                                @if($item->classroom) · {{ $item->classroom }} @endif
                            </p>
                        </div>

                        <div class="shrink-0 text-right">
                            @if(! $isExam && ! $item->graded)
                                <span class="text-xs font-bold text-slate-400">@lang('student-history::app.not_graded')</span>
                            @elseif(! is_null($item->percent))
                                <p class="text-lg font-black {{ $item->percent >= 50 ? 'text-green-600' : 'text-slate-500' }}">{{ $item->percent }}%</p>
                                @if(! is_null($item->score))
                                    <p class="text-[11px] font-bold text-slate-400">{{ rtrim(rtrim(number_format($item->score, 1), '0'), '.') }}@if($item->max)/{{ $item->max }}@endif</p>
                                @endif
                            @else
                                <span class="text-xs font-bold text-slate-300">—</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if($items->hasPages())
                <div class="flex justify-center mt-4">{{ $items->links() }}</div>
            @endif
        @endif
    </div>
</div>
@endsection
