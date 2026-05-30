@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-announcement::app.title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
@php
    $typeConfig = [
        'info'       => ['bg' => 'bg-sky-100', 'text' => 'text-sky-700',    'dot' => 'bg-sky-400',   'icon' => 'heroicon-o-information-circle'],
        'warning'    => ['bg' => 'bg-amber-100','text' => 'text-amber-700', 'dot' => 'bg-amber-400', 'icon' => 'heroicon-o-exclamation-triangle'],
        'reminder'   => ['bg' => 'bg-violet-100','text'=> 'text-violet-700','dot' => 'bg-violet-400','icon' => 'heroicon-o-bell'],
        'assignment' => ['bg' => 'bg-green-100', 'text'=> 'text-green-700', 'dot' => 'bg-green-500', 'icon' => 'heroicon-o-clipboard-document-list'],
    ];
@endphp
<div class="flex min-h-screen flex-col bg-slate-50">

    <header class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white/95 px-6 py-3 backdrop-blur">
        <div>
            <h1 class="text-base font-black text-slate-950">@lang('teacher-announcement::app.title')</h1>
            <p class="text-xs font-bold text-slate-400">@lang('teacher-announcement::app.subtitle')</p>
        </div>
        <div class="flex items-center gap-2">
            {{-- Type filter --}}
            <select onchange="location.href=this.value"
                    class="h-9 rounded-full border border-slate-200 bg-white px-3 text-xs font-bold text-slate-600 outline-none">
                <option value="{{ route('teacher.announcements.index') }}" @selected(!($filters['type']??''))>@lang('teacher-announcement::app.all_types')</option>
                @foreach(Mindigo\TeacherAnnouncement\Models\Announcement::TYPES as $t)
                    <option value="{{ route('teacher.announcements.index', ['type'=>$t]) }}" @selected(($filters['type']??'')===$t)>@lang('teacher-announcement::app.type_'.$t)</option>
                @endforeach
            </select>
            <a href="{{ route('teacher.announcements.create') }}"
               class="inline-flex h-9 items-center gap-1.5 rounded-full bg-green-600 px-4 text-sm font-black text-white no-underline shadow-sm transition hover:bg-green-500">
                <x-heroicon-o-plus class="h-4 w-4" />@lang('teacher-announcement::app.create')
            </a>
        </div>
    </header>

    <div class="grid gap-4 p-5 lg:grid-cols-[minmax(0,1fr)_14rem]">

        {{-- Timeline --}}
        <div class="space-y-3">
            @if($announcements->isEmpty())
                <div class="flex flex-col items-center justify-center gap-4 rounded-3xl border border-dashed border-slate-200 bg-white py-24">
                    <span class="grid h-20 w-20 place-items-center rounded-full bg-slate-50 text-slate-300">
                        <x-heroicon-o-megaphone class="h-10 w-10" />
                    </span>
                    <div class="text-center">
                        <p class="text-lg font-black text-slate-700">@lang('teacher-announcement::app.empty_title')</p>
                        <p class="mt-1 text-sm font-semibold text-slate-400">@lang('teacher-announcement::app.empty_desc')</p>
                    </div>
                    <a href="{{ route('teacher.announcements.create') }}"
                       class="inline-flex h-10 items-center gap-2 rounded-full bg-green-600 px-6 text-sm font-black text-white no-underline shadow-sm transition hover:bg-green-500">
                        <x-heroicon-o-plus class="h-4 w-4" />@lang('teacher-announcement::app.create')
                    </a>
                </div>
            @else
                @foreach($announcements as $ann)
                    @php $tc = $typeConfig[$ann->type] ?? $typeConfig['info']; @endphp
                    <article class="group overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition hover:border-slate-200 hover:shadow-md">
                        <div class="flex items-start gap-4 px-5 py-4">
                            {{-- Type icon --}}
                            <span class="mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-xl {{ $tc['bg'] }} {{ $tc['text'] }}">
                                <x-dynamic-component :component="$tc['icon']" class="h-5 w-5" />
                            </span>

                            {{-- Content --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            @if($ann->is_pinned)
                                                <x-heroicon-s-map-pin class="h-3.5 w-3.5 shrink-0 text-amber-500" />
                                            @endif
                                            <h3 class="truncate text-sm font-black text-slate-900">
                                                <a href="{{ route('teacher.announcements.show', $ann) }}" class="no-underline hover:text-green-700">{{ $ann->title }}</a>
                                            </h3>
                                        </div>
                                        <p class="mt-1 line-clamp-2 text-xs font-semibold leading-relaxed text-slate-500">{{ $ann->content }}</p>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        <span class="rounded-full {{ $ann->isPublished() ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }} px-2.5 py-1 text-[11px] font-black">
                                            @lang('teacher-announcement::app.' . ($ann->isPublished() ? 'published' : 'draft'))
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-2.5 flex flex-wrap items-center gap-3">
                                    {{-- Classes --}}
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($ann->classrooms as $cls)
                                            <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-black text-slate-600">
                                                <x-heroicon-s-user-group class="h-3 w-3" />{{ $cls->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs font-bold text-slate-400">@lang('teacher-announcement::app.no_classrooms')</span>
                                        @endforelse
                                    </div>
                                    <span class="text-xs font-bold text-slate-400">
                                        {{ $ann->isPublished() ? $ann->published_at->diffForHumans() : $ann->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex shrink-0 items-center gap-1 opacity-0 transition group-hover:opacity-100">
                                @if($ann->isDraft())
                                    <a href="{{ route('teacher.announcements.edit', $ann) }}"
                                       class="grid h-8 w-8 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                                        <x-heroicon-o-pencil-square class="h-4 w-4" />
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('teacher.announcements.destroy', $ann) }}"
                                      data-mindigo-confirm-title="{{ __('teacher-announcement::app.delete_title') }}"
                                      data-mindigo-confirm-message="{{ __('teacher-announcement::app.delete_confirm') }}"
                                      data-mindigo-confirm-text="{{ __('teacher-announcement::app.delete') }}"
                                      data-mindigo-confirm-cancel="{{ __('teacher-announcement::app.cancel') }}"
                                      data-mindigo-confirm-type="danger">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="grid h-8 w-8 place-items-center rounded-xl text-slate-400 transition hover:bg-red-50 hover:text-red-600">
                                        <x-heroicon-o-trash class="h-4 w-4" />
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
                @if($announcements->hasPages())
                    <div class="flex justify-center pt-2">{{ $announcements->links() }}</div>
                @endif
            @endif
        </div>

        {{-- Right sidebar: stats --}}
        <aside class="hidden lg:block">
            <div class="sticky top-0 space-y-3 pt-0">
                @foreach([
                    ['key'=>'stat_total',  'val'=>$stats['total'],    'color'=>'text-slate-900'],
                    ['key'=>'stat_sent',   'val'=>$stats['published'],'color'=>'text-green-700'],
                    ['key'=>'stat_draft',  'val'=>$stats['draft'],    'color'=>'text-slate-500'],
                    ['key'=>'stat_pinned', 'val'=>$stats['pinned'],   'color'=>'text-amber-600'],
                ] as $s)
                    <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white px-4 py-3 shadow-sm">
                        <p class="text-xs font-bold text-slate-500">@lang('teacher-announcement::app.' . $s['key'])</p>
                        <strong class="text-xl font-black {{ $s['color'] }}">{{ $s['val'] }}</strong>
                    </div>
                @endforeach

                {{-- Quick type breakdown --}}
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <p class="mb-2.5 text-[10px] font-black uppercase tracking-widest text-slate-400">@lang('teacher-announcement::app.field_type')</p>
                    @foreach(Mindigo\TeacherAnnouncement\Models\Announcement::TYPES as $t)
                        @php $tc = $typeConfig[$t]; @endphp
                        <a href="{{ route('teacher.announcements.index', ['type'=>$t]) }}"
                           class="flex items-center justify-between rounded-xl px-2 py-1.5 text-sm no-underline transition hover:bg-slate-50 {{ ($filters['type']??'')===$t ? 'bg-slate-50 font-black' : 'font-bold text-slate-600' }}">
                            <span class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full {{ $tc['dot'] }}"></span>
                                @lang('teacher-announcement::app.type_' . $t)
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
