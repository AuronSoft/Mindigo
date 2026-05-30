@extends('Mindigo-dashboard::layouts')

@section('title', $announcement->title)

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
@php
    $typeConfig = [
        'info'       => ['bg'=>'bg-sky-100',    'text'=>'text-sky-700',    'icon'=>'heroicon-o-information-circle', 'border'=>'border-sky-200'],
        'warning'    => ['bg'=>'bg-amber-100',  'text'=>'text-amber-700',  'icon'=>'heroicon-o-exclamation-triangle','border'=>'border-amber-200'],
        'reminder'   => ['bg'=>'bg-violet-100', 'text'=>'text-violet-700', 'icon'=>'heroicon-o-bell',               'border'=>'border-violet-200'],
        'assignment' => ['bg'=>'bg-green-100',  'text'=>'text-green-700',  'icon'=>'heroicon-o-clipboard-document-list','border'=>'border-green-200'],
    ];
    $tc = $typeConfig[$announcement->type] ?? $typeConfig['info'];
@endphp
<div class="flex min-h-screen flex-col bg-slate-50">
    <header class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white/95 px-6 py-3 backdrop-blur">
        <div class="flex items-center gap-3">
            <a href="{{ route('teacher.announcements.index') }}"
               class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline transition hover:bg-green-50 hover:text-green-700">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
            </a>
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">@lang('teacher-announcement::app.title')</p>
                <h1 class="text-base font-black text-slate-950">{{ $announcement->title }}</h1>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($announcement->isDraft())
                <a href="{{ route('teacher.announcements.edit', $announcement) }}"
                   class="inline-flex h-9 items-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 no-underline transition hover:bg-slate-50">
                    <x-heroicon-o-pencil-square class="h-4 w-4" />@lang('teacher-announcement::app.edit')
                </a>
                <form method="POST" action="{{ route('teacher.announcements.publish', $announcement) }}"
                      data-mindigo-confirm-title="{{ __('teacher-announcement::app.publish_title') }}"
                      data-mindigo-confirm-message="{{ __('teacher-announcement::app.publish_confirm') }}"
                      data-mindigo-confirm-text="{{ __('teacher-announcement::app.publish') }}"
                      data-mindigo-confirm-cancel="{{ __('teacher-announcement::app.cancel') }}"
                      data-mindigo-confirm-type="info">
                    @csrf
                    <button type="submit"
                            class="inline-flex h-9 items-center gap-2 rounded-full bg-green-600 px-4 text-xs font-black text-white shadow-sm transition hover:bg-green-500">
                        <x-heroicon-o-paper-airplane class="h-4 w-4" />@lang('teacher-announcement::app.publish')
                    </button>
                </form>
            @endif
            <form method="POST" action="{{ route('teacher.announcements.destroy', $announcement) }}"
                  data-mindigo-confirm-title="{{ __('teacher-announcement::app.delete_title') }}"
                  data-mindigo-confirm-message="{{ __('teacher-announcement::app.delete_confirm') }}"
                  data-mindigo-confirm-text="{{ __('teacher-announcement::app.delete') }}"
                  data-mindigo-confirm-cancel="{{ __('teacher-announcement::app.cancel') }}"
                  data-mindigo-confirm-type="danger">
                @csrf @method('DELETE')
                <button type="submit"
                        class="inline-flex h-9 items-center gap-2 rounded-full border border-red-200 bg-red-50 px-4 text-xs font-black text-red-600 transition hover:bg-red-100">
                    <x-heroicon-o-trash class="h-4 w-4" />@lang('teacher-announcement::app.delete')
                </button>
            </form>
        </div>
    </header>

    <div class="mx-auto w-full max-w-2xl space-y-4 p-6">

        {{-- Type + status chips --}}
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full {{ $tc['bg'] }} {{ $tc['text'] }} px-3 py-1 text-xs font-black">
                <x-dynamic-component :component="$tc['icon']" class="h-4 w-4" />
                @lang('teacher-announcement::app.type_' . $announcement->type)
            </span>
            <span class="rounded-full px-3 py-1 text-xs font-black {{ $announcement->isPublished() ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                @lang('teacher-announcement::app.' . ($announcement->isPublished() ? 'published' : 'draft'))
            </span>
            @if($announcement->is_pinned)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-black text-amber-700">
                    <x-heroicon-s-map-pin class="h-3.5 w-3.5" />@lang('teacher-announcement::app.pinned')
                </span>
            @endif
            @if($announcement->isPublished())
                <span class="text-xs font-bold text-slate-400">{{ $announcement->published_at->diffForHumans() }}</span>
            @endif
        </div>

        {{-- Content --}}
        <div class="rounded-3xl border {{ $tc['border'] }} {{ $tc['bg'] }} p-6 shadow-sm">
            <div class="prose prose-sm max-w-none text-slate-800">{!! nl2br(e($announcement->content)) !!}</div>
        </div>

        {{-- Classrooms --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="mb-3 text-xs font-black uppercase tracking-wider text-slate-400">@lang('teacher-announcement::app.sent_to')</p>
            @if($announcement->classrooms->isEmpty())
                <p class="text-sm font-bold text-slate-400">@lang('teacher-announcement::app.no_classrooms')</p>
            @else
                <div class="flex flex-wrap gap-2">
                    @foreach($announcement->classrooms as $cls)
                        <span class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-black text-slate-800">
                            <x-heroicon-o-user-group class="h-4 w-4 text-slate-400" />
                            {{ $cls->name }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
