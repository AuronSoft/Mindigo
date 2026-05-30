@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-question::app.title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="flex min-h-screen bg-slate-50">

    {{-- Narrow filter sidebar --}}
    <aside class="hidden w-56 shrink-0 border-r border-slate-200 bg-white xl:block">
        <div class="sticky top-0 max-h-screen overflow-y-auto p-4 space-y-4">
            <div>
                <p class="mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">@lang('teacher-question::app.col_status')</p>
                @foreach(['', 'draft', 'reviewing', 'approved'] as $st)
                    <a href="{{ route('teacher.questions.index', array_merge($filters, ['status' => $st])) }}"
                       class="flex min-h-8 items-center gap-2 rounded-xl px-2.5 text-sm font-bold no-underline transition
                           {{ ($filters['status'] ?? '') === $st ? 'bg-green-50 font-black text-green-700' : 'text-slate-600 hover:bg-slate-50' }}">
                        @if($st)
                            <span class="h-2 w-2 shrink-0 rounded-full {{ match($st) { 'approved'=>'bg-green-500', 'reviewing'=>'bg-amber-400', default=>'bg-slate-300' } }}"></span>
                            @lang('teacher-question::app.' . $st)
                        @else
                            <span class="h-2 w-2 shrink-0 rounded-full bg-slate-200"></span>
                            @lang('teacher-question::app.all_statuses')
                        @endif
                    </a>
                @endforeach
            </div>

            <div>
                <p class="mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">@lang('teacher-question::app.col_difficulty')</p>
                @foreach(['', 'easy', 'medium', 'hard'] as $diff)
                    <a href="{{ route('teacher.questions.index', array_merge($filters, ['difficulty' => $diff])) }}"
                       class="flex min-h-8 items-center gap-2 rounded-xl px-2.5 text-sm font-bold no-underline transition
                           {{ ($filters['difficulty'] ?? '') === $diff ? 'bg-green-50 font-black text-green-700' : 'text-slate-600 hover:bg-slate-50' }}">
                        @if($diff)
                            @lang('teacher-question::app.' . $diff)
                        @else
                            @lang('teacher-question::app.all_difficulties')
                        @endif
                    </a>
                @endforeach
            </div>

            <div>
                <p class="mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">@lang('teacher-question::app.col_folder')</p>
                <a href="{{ route('teacher.questions.index', array_merge($filters, ['folder_id' => ''])) }}"
                   class="flex min-h-8 items-center rounded-xl px-2.5 text-sm font-bold no-underline transition
                       {{ !($filters['folder_id'] ?? '') ? 'bg-green-50 font-black text-green-700' : 'text-slate-600 hover:bg-slate-50' }}">
                    @lang('teacher-question::app.all_folders')
                </a>
                @foreach($folders as $folder)
                    <a href="{{ route('teacher.questions.index', array_merge($filters, ['folder_id' => $folder->id])) }}"
                       class="flex min-h-8 items-center gap-2 rounded-xl px-2.5 text-sm font-bold no-underline transition
                           {{ ($filters['folder_id'] ?? '') == $folder->id ? 'bg-green-50 font-black text-green-700' : 'text-slate-600 hover:bg-slate-50' }}">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background:{{ $folder->color ?: '#94a3b8' }}"></span>
                        <span class="min-w-0 truncate">{{ $folder->name }}</span>
                        <span class="ml-auto shrink-0 text-[10px] text-slate-400">{{ $folder->questions_count }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex flex-1 flex-col">

        {{-- Header --}}
        <header class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white/95 px-5 py-3 backdrop-blur">
            <div class="flex items-center gap-3">
                {{-- Mobile filter toggle could go here --}}
                <div>
                    <h1 class="text-base font-black text-slate-950">@lang('teacher-question::app.title')</h1>
                    <p class="text-xs font-bold text-slate-400">{{ number_format($stats['total']) }} @lang('teacher-question::app.stat_total') · {{ $stats['approved'] }} @lang('teacher-question::app.stat_approved')</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <form method="GET" class="flex items-center">
                    <div class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 focus-within:border-green-300 focus-within:bg-white">
                        <x-heroicon-o-magnifying-glass class="h-3.5 w-3.5 text-slate-400" />
                        <input type="text" name="keyword" value="{{ $filters['keyword'] ?? '' }}"
                               placeholder="@lang('teacher-question::app.search_ph')"
                               class="w-44 bg-transparent text-sm font-bold text-slate-700 outline-none placeholder:text-slate-400">
                    </div>
                </form>
                <a href="{{ route('teacher.questions.import') }}"
                   class="inline-flex h-9 items-center gap-1.5 rounded-full border border-slate-200 bg-white px-4 text-sm font-black text-slate-600 no-underline transition hover:bg-slate-50">
                    <x-heroicon-o-arrow-up-tray class="h-4 w-4" />@lang('teacher-question::app.import')
                </a>
                <a href="{{ route('teacher.questions.create') }}"
                   class="inline-flex h-9 items-center gap-1.5 rounded-full bg-green-600 px-4 text-sm font-black text-white no-underline shadow-sm transition hover:bg-green-500">
                    <x-heroicon-o-plus class="h-4 w-4" />@lang('teacher-question::app.create')
                </a>
            </div>
        </header>

        {{-- Stats chips --}}
        <div class="flex items-center gap-2 border-b border-slate-100 bg-white px-5 py-2">
            @foreach([
                ['key'=>'stat_draft',     'val'=>$stats['draft'],     'dot'=>'bg-slate-300'],
                ['key'=>'stat_reviewing', 'val'=>$stats['reviewing'], 'dot'=>'bg-amber-400'],
                ['key'=>'stat_approved',  'val'=>$stats['approved'],  'dot'=>'bg-green-500'],
            ] as $chip)
                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-black text-slate-700">
                    <span class="h-1.5 w-1.5 rounded-full {{ $chip['dot'] }}"></span>
                    @lang('teacher-question::app.' . $chip['key'])
                    <span class="ml-0.5 rounded-full bg-slate-100 px-1.5 py-0.5 text-[10px] font-black text-slate-500">{{ $chip['val'] }}</span>
                </span>
            @endforeach
        </div>

        {{-- Question list --}}
        <div class="flex-1 p-5">
            @if($questions->isEmpty())
                <div class="flex flex-col items-center justify-center gap-4 rounded-3xl border border-dashed border-slate-200 bg-white py-24">
                    <span class="grid h-20 w-20 place-items-center rounded-full bg-slate-50 text-slate-300">
                        <x-heroicon-o-circle-stack class="h-10 w-10" />
                    </span>
                    <div class="text-center">
                        <p class="text-lg font-black text-slate-700">@lang('teacher-question::app.empty_title')</p>
                        <p class="mt-1 text-sm font-semibold text-slate-400">@lang('teacher-question::app.empty_desc')</p>
                    </div>
                    <a href="{{ route('teacher.questions.create') }}"
                       class="mt-1 inline-flex h-10 items-center gap-2 rounded-full bg-green-600 px-6 text-sm font-black text-white no-underline shadow-sm transition hover:bg-green-500">
                        <x-heroicon-o-plus class="h-4 w-4" />@lang('teacher-question::app.create')
                    </a>
                </div>
            @else
                @php
                    $diffColors = ['easy'=>'text-emerald-600 bg-emerald-50','medium'=>'text-amber-600 bg-amber-50','hard'=>'text-red-600 bg-red-50'];
                    $statusColors = ['approved'=>'bg-green-100 text-green-700','reviewing'=>'bg-amber-100 text-amber-700','draft'=>'bg-slate-100 text-slate-500','rejected'=>'bg-red-100 text-red-600'];
                @endphp
                <div class="space-y-2">
                    @foreach($questions as $q)
                        <div class="group flex items-start gap-4 rounded-2xl border border-slate-100 bg-white px-4 py-3.5 shadow-sm transition hover:border-green-100 hover:shadow-md">
                            {{-- Type badge --}}
                            <span class="mt-0.5 shrink-0 rounded-xl border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-black text-slate-500 whitespace-nowrap">
                                @lang('teacher-question::app.' . $q->type)
                            </span>

                            {{-- Content --}}
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('teacher.questions.show', $q) }}"
                                   class="text-sm font-black leading-snug text-slate-900 no-underline hover:text-green-700 line-clamp-2">
                                    {{ strip_tags($q->content) }}
                                </a>
                                <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                    @if($q->subject)
                                        <span class="text-xs font-bold text-slate-400">{{ $q->subject }}</span>
                                    @endif
                                    @if($q->topic)
                                        <span class="text-slate-300">·</span>
                                        <span class="text-xs font-bold text-slate-400">{{ $q->topic }}</span>
                                    @endif
                                    @if($q->folder)
                                        <span class="text-slate-300">·</span>
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-slate-400">
                                            <span class="h-1.5 w-1.5 rounded-full" style="background:{{ $q->folder->color ?: '#94a3b8' }}"></span>
                                            {{ $q->folder->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Meta + actions --}}
                            <div class="flex shrink-0 items-center gap-2">
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-black {{ $diffColors[$q->difficulty] ?? 'bg-slate-100 text-slate-500' }}">
                                    @lang('teacher-question::app.' . $q->difficulty)
                                </span>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-black {{ $statusColors[$q->status] ?? 'bg-slate-100 text-slate-500' }}">
                                    @lang('teacher-question::app.' . $q->status)
                                </span>

                                {{-- Actions --}}
                                <div class="flex items-center gap-0.5 opacity-0 transition group-hover:opacity-100">
                                    @if(in_array($q->status, ['draft']))
                                        <a href="{{ route('teacher.questions.edit', $q) }}"
                                           class="grid h-8 w-8 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                                            <x-heroicon-o-pencil-square class="h-4 w-4" />
                                        </a>
                                    @endif
                                    <form method="POST" action="{{ route('teacher.questions.destroy', $q) }}"
                                          data-mindigo-confirm-title="@lang('teacher-question::app.delete_title')"
                                          data-mindigo-confirm-message="@lang('teacher-question::app.delete_confirm')"
                                          data-mindigo-confirm-text="@lang('teacher-question::app.delete')"
                                          data-mindigo-confirm-cancel="{{ __('teacher-question::app.cancel') }}"
                                          data-mindigo-confirm-type="danger">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="grid h-8 w-8 place-items-center rounded-xl text-slate-400 transition hover:bg-red-50 hover:text-red-600">
                                            <x-heroicon-o-trash class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($questions->hasPages())
                    <div class="mt-4 flex justify-center">{{ $questions->links() }}</div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
