@extends('Mindigo-dashboard::layouts')
@section('title', __('notification::app.title') . ' · Auronsoft LMS')
@section('meta_description', __('notification::app.subtitle'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
@php
    $tones = [
        'blue'  => 'bg-blue-50 text-blue-600',
        'green' => 'bg-green-50 text-green-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'slate' => 'bg-slate-100 text-slate-500',
    ];
@endphp
<div class="flex min-h-screen flex-col bg-slate-50">

    {{-- Header --}}
    <header class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white/90 px-6 py-4 backdrop-blur">
        <div>
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">@lang('notification::app.area')</p>
            <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('notification::app.title')</h1>
            <p class="text-xs font-semibold text-slate-400">@lang('notification::app.subtitle')</p>
        </div>
        <div class="flex items-center gap-2">
            @if($unreadAnnouncementCount > 0)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1.5 text-xs font-black text-blue-700">
                    <x-heroicon-o-megaphone class="h-4 w-4" />
                    {{ $unreadAnnouncementCount > 99 ? '99+' : $unreadAnnouncementCount }}
                    <span class="font-bold text-blue-500">@lang('notification::app.cat_announcement')</span>
                </span>
            @endif
            @if($unreadCount > 0)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-3 py-1.5 text-xs font-black text-green-700">
                    <x-heroicon-o-bell class="h-4 w-4" />
                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                </span>
            @endif
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex h-10 items-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 shadow-sm transition hover:border-green-200 hover:bg-green-50 hover:text-green-700">
                    <x-heroicon-o-check class="h-4 w-4" />
                    @lang('notification::app.mark_all_read')
                </button>
            </form>
        </div>
    </header>

    <div class="flex flex-1 flex-col gap-5 p-6">

        {{-- Filter tabs --}}
        @php
            $tab = function (string $label, ?string $cat, ?string $fil, array $extra = []) use ($category, $filter) {
                $active = ($cat === $category) && ($fil === $filter);
                $cls = $active ? 'bg-green-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50';
                $url = route('notifications.index', array_merge(['category' => $cat, 'filter' => $fil], $extra));
                return '<a href="'.e($url).'" class="inline-flex h-9 items-center gap-1.5 rounded-full px-4 text-xs font-black no-underline transition '.$cls.'">'.$label.'</a>';
            };
        @endphp
        <div class="flex flex-wrap items-center gap-2">
            {!! $tab(__('notification::app.filter_all'), null, null) !!}
            {!! $tab(__('notification::app.filter_unread'), $category, 'unread') !!}
            <span class="mx-1 h-6 w-px bg-slate-200"></span>
            {!! $tab(__('notification::app.cat_announcement'), 'announcement', $filter) !!}
            {!! $tab(__('notification::app.cat_system'), 'system', $filter) !!}
        </div>

        @if($notifications->isEmpty())
            <div class="flex flex-1 flex-col items-center justify-center gap-4 rounded-3xl border border-dashed border-slate-200 bg-white py-20">
                <span class="grid h-20 w-20 place-items-center rounded-full bg-slate-50 text-slate-300">
                    <x-heroicon-o-bell class="h-10 w-10" />
                </span>
                <div class="text-center">
                    <p class="text-lg font-black text-slate-700">@lang('notification::app.empty_title')</p>
                    <p class="mt-1 max-w-xs text-sm font-semibold leading-relaxed text-slate-400">@lang('notification::app.empty_desc')</p>
                </div>
            </div>
        @else
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                @foreach($notifications as $note)
                    @php
                        $d = $note->data;
                        $tone = $tones[$d['tone'] ?? 'slate'] ?? $tones['slate'];
                        $isUnread = is_null($note->read_at);
                        $isAnnouncement = (($d['category'] ?? null) === 'announcement');
                        $icon = match($d['icon'] ?? '') {
                            'megaphone'       => 'heroicon-o-megaphone',
                            'clipboard-check' => 'heroicon-o-clipboard-document-check',
                            'chat-bubble-left' => 'heroicon-o-chat-bubble-left-right',
                            default           => 'heroicon-o-bell',
                        };
                    @endphp
                    <a href="{{ route('notifications.read', $note->id) }}"
                       class="flex items-start gap-4 border-b border-slate-100 px-6 py-4 no-underline transition last:border-b-0 hover:bg-slate-50/70 {{ $isUnread ? 'bg-green-50/40' : '' }}">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl {{ $isAnnouncement ? 'bg-blue-50 text-blue-600' : $tone }}">
                            <x-dynamic-component :component="$icon" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="truncate font-black text-slate-800">{{ $d['title'] ?? '—' }}</p>
                                @if($isAnnouncement)
                                    <span class="shrink-0 rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-black text-blue-700">@lang('notification::app.cat_announcement')</span>
                                @endif
                                @if($isUnread)
                                    <span class="shrink-0 rounded-full bg-green-600 px-2 py-0.5 text-[10px] font-black text-white">@lang('notification::app.unread_badge')</span>
                                @endif
                            </div>
                            @if(($d['category'] ?? '') === 'assignment_graded')
                                <p class="text-sm font-semibold text-slate-500">
                                    @lang('notification::app.cat_assignment_graded')
                                    @if(isset($d['score']))· @lang('notification::app.score_label', ['score' => $d['score'], 'max' => $d['max_score'] ?? '—'])@endif
                                </p>
                            @else
                                @if(! empty($d['message']))
                                    <p class="line-clamp-2 text-sm font-semibold text-slate-500">{{ $d['message'] }}</p>
                                @endif
                                @if(! empty($d['teacher']))
                                    <p class="mt-0.5 text-xs font-bold text-slate-400">@lang('notification::app.from_teacher', ['name' => $d['teacher']])</p>
                                @endif
                            @endif
                        </div>
                        <span class="shrink-0 text-[11px] font-bold text-slate-400">{{ $note->created_at?->diffForHumans() }}</span>
                    </a>
                @endforeach
            </div>
            @if($notifications->hasPages())
                <div class="flex justify-center mt-4">{{ $notifications->links() }}</div>
            @endif
        @endif
    </div>
</div>
@endsection
