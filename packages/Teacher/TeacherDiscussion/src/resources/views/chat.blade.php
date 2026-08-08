@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-discussion::app.title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pane = document.querySelector('[data-discussion-messages]');
            if (pane) {
                pane.scrollTop = pane.scrollHeight;
            }

            const input = document.querySelector('[data-discussion-input]');
            if (input) {
                input.addEventListener('input', function () {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 128) + 'px';
                });
            }

            const search = document.querySelector('[data-discussion-search]');
            const rooms = document.querySelectorAll('[data-discussion-room]');
            if (search) {
                search.addEventListener('input', function () {
                    const keyword = this.value.trim().toLowerCase();
                    rooms.forEach(function (room) {
                        room.classList.toggle('hidden', !room.dataset.search.includes(keyword));
                    });
                });
            }

            const tabs = document.querySelectorAll('[data-discussion-tab]');
            const tabPanes = document.querySelectorAll('[data-discussion-tab-pane]');
            if (tabs.length) {
                tabs.forEach(function (tab) {
                    tab.addEventListener('click', function () {
                        const target = tab.dataset.discussionTab;
                        tabs.forEach(function (t) {
                            t.classList.toggle('bg-green-50', t.dataset.discussionTab === target);
                            t.classList.toggle('text-green-700', t.dataset.discussionTab === target);
                            t.classList.toggle('border-green-100', t.dataset.discussionTab === target);
                        });
                        tabPanes.forEach(function (pane) {
                            pane.classList.toggle('hidden', pane.dataset.discussionTabPane !== target);
                        });
                    });
                });
            }

            const fileInput = document.querySelector('[data-discussion-files]');
            const fileTrigger = document.querySelector('[data-discussion-file-trigger]');
            const imageTrigger = document.querySelector('[data-discussion-image-trigger]');
            const filePreview = document.querySelector('[data-discussion-file-preview]');
            const openFiles = function (accept) {
                if (!fileInput) return;
                fileInput.setAttribute('accept', accept);
                fileInput.click();
            };
            if (fileInput) {
                if (fileTrigger) {
                    fileTrigger.addEventListener('click', function () {
                        openFiles('image/*,.pdf,.txt,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar');
                    });
                }
                if (imageTrigger) {
                    imageTrigger.addEventListener('click', function () {
                        openFiles('image/*');
                    });
                }
                fileInput.addEventListener('change', function () {
                    if (!filePreview) return;
                    const files = Array.from(fileInput.files || []);
                    filePreview.innerHTML = '';
                    filePreview.classList.toggle('hidden', files.length === 0);
                    files.slice(0, 6).forEach(function (file) {
                        const item = document.createElement('span');
                        item.className = 'inline-flex max-w-52 items-center gap-1 rounded-full bg-green-50 px-3 py-1 text-[11px] font-black text-green-700';
                        item.textContent = file.name;
                        filePreview.appendChild(item);
                    });
                });
            }

            const infoToggle = document.querySelector('[data-discussion-info-toggle]');
            const infoPanel = document.querySelector('[data-discussion-info-panel]');
            if (infoToggle && infoPanel) {
                infoToggle.addEventListener('click', function () {
                    infoPanel.classList.toggle('hidden');
                });
            }
        });
    </script>

    @if($selectedThread)
        <script>
            (function () {
                if (!window.Echo) return;
                const pane = document.querySelector('[data-discussion-messages]');
                const form = document.querySelector('[data-discussion-form]');
                const threadId = pane ? pane.dataset.threadId : null;
                const currentUserId = form ? form.dataset.currentUser : null;
                if (!threadId) return;

                const buildBubble = function (data) {
                    const mine = String(data.sender_id) === String(currentUserId);
                    const senderName = (data.sender && data.sender.name) || '';
                    const time = data.created_at ? new Date(data.created_at) : new Date();
                    const timeLabel = time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    const initial = senderName.charAt(0).toUpperCase();

                    const row = document.createElement('div');
                    row.className = 'flex items-end gap-2.5 ' + (mine ? 'justify-end' : 'justify-start');

                    if (!mine) {
                        const av = document.createElement('div');
                        av.className = 'grid h-9 w-9 shrink-0 place-items-center rounded-full bg-slate-100 text-xs font-black text-slate-600';
                        av.textContent = initial;
                        row.appendChild(av);
                    }

                    const inner = document.createElement('div');
                    inner.className = 'max-w-[min(35rem,78%)]';

                    const meta = document.createElement('div');
                    meta.className = 'mb-1 flex items-center gap-2 px-1 ' + (mine ? 'justify-end' : '');
                    if (!mine) {
                        const nm = document.createElement('span');
                        nm.className = 'text-xs font-black text-slate-700';
                        nm.textContent = senderName;
                        meta.appendChild(nm);
                    }
                    const tm = document.createElement('span');
                    tm.className = 'text-[11px] font-bold text-slate-400';
                    tm.textContent = timeLabel;
                    meta.appendChild(tm);

                    const bubble = document.createElement('div');
                    bubble.className = 'rounded-2xl px-4 py-3 ' + (mine ? 'rounded-br-md bg-green-600 text-white' : 'rounded-bl-md bg-slate-100 text-slate-800');
                    const p = document.createElement('p');
                    p.className = 'whitespace-pre-line wrap-break-word text-sm font-semibold leading-6';
                    p.textContent = data.body || '';
                    bubble.appendChild(p);

                    inner.appendChild(meta);
                    inner.appendChild(bubble);
                    row.appendChild(inner);
                    return row;
                };

                const appendMessage = function (data) {
                    if (!pane) return;
                    const list = pane.querySelector('.space-y-4');
                    if (!list) return;
                    if (list.querySelector('[data-msg-id="' + data.id + '"]')) return;
                    const row = buildBubble(data);
                    row.setAttribute('data-msg-id', data.id);
                    list.appendChild(row);
                    pane.scrollTop = pane.scrollHeight;
                };

                window.Echo.private('discussion.' + threadId)
                    .listen('.message.sent', function (event) {
                        appendMessage(event.message);
                    });
            })();
        </script>
    @endif
@endsection

@section('content')
@php
    $currentUserId = (int) auth()->id();
    $currentRole = auth()->user()->role;

    $threadName = function ($thread) use ($currentUserId) {
        if ($thread->type === 'class' && $thread->classroom) {
            return $thread->classroom->name;
        }
        if ($thread->name) {
            return $thread->name;
        }
        if ($thread->type === 'direct') {
            $other = $thread->participants
                ->filter(fn ($p) => (int) $p->user_id !== $currentUserId)
                ->first();
            return $other?->user?->name ?? __('teacher-discussion::app.direct_chat');
        }
        return __('teacher-discussion::app.unknown_class');
    };

    $threadSub = function ($thread) use ($currentUserId) {
        if ($thread->type === 'direct') {
            return __('teacher-discussion::app.direct_chat');
        }
        if ($thread->classroom) {
            return number_format($thread->classroom->students_count ?? 0).' '.mb_strtolower(__('teacher-discussion::app.students'));
        }
        $count = $thread->participants_count ?? $thread->participants->count();
        return number_format($count).' '.__('teacher-discussion::app.members');
    };

    $threadInitial = function ($thread) use ($threadName) {
        return mb_strtoupper(mb_substr($threadName($thread), 0, 1));
    };

    $selectedName = $selectedThread ? $threadName($selectedThread) : '';
    $selectedInitial = $selectedThread ? $threadInitial($selectedThread) : '';
    $selectedSub = $selectedThread ? $threadSub($selectedThread) : '';

    $imageAttachments = $attachments->filter(fn ($a) => $a->isImage())->values();
    $fileAttachments = $attachments->reject(fn ($a) => $a->isImage())->values();
    $links = collect();
    foreach ($messages as $message) {
        preg_match_all('/https?:\/\/[^\s<]+/i', (string) $message->body, $matches);
        foreach ($matches[0] ?? [] as $url) {
            $links->push(['url' => $url, 'label' => parse_url($url, PHP_URL_HOST) ?: $url, 'sender' => $message->sender?->name, 'date' => $message->created_at]);
        }
    }
    $links = $links->unique('url')->take(6)->values();
@endphp

<div class="h-screen overflow-hidden bg-[#f7f8fb]">
    <div class="grid h-full grid-cols-[20rem_minmax(0,1fr)_19rem] max-2xl:grid-cols-[19rem_minmax(0,1fr)_18rem] max-xl:grid-cols-[18rem_minmax(0,1fr)] max-lg:grid-cols-1">

        {{-- ============ SIDEBAR ============ --}}
        <aside class="flex min-h-0 flex-col border-r border-slate-200 bg-white">
            <div class="shrink-0 border-b border-slate-100 p-4">
                <div class="flex h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3">
                    <x-heroicon-o-magnifying-glass class="h-4 w-4 shrink-0 text-slate-400" />
                    <input type="search" data-discussion-search placeholder="Search" class="min-w-0 flex-1 border-0 bg-transparent text-sm font-bold text-slate-700 outline-none placeholder:text-slate-400">
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2">
                    <button type="button" data-discussion-tab data-discussion-tab="inbox" class="flex h-11 items-center justify-center gap-2 rounded-xl bg-green-50 text-xs font-black text-green-700">
                        <x-heroicon-o-inbox class="h-4 w-4" />
                        @lang('teacher-discussion::app.inbox')
                    </button>
                    <button type="button" data-discussion-tab data-discussion-tab="groups" class="flex h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white text-xs font-black text-slate-600">
                        <x-heroicon-o-tag class="h-4 w-4" />
                        @lang('teacher-discussion::app.groups')
                    </button>
                </div>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto p-3">
                {{-- Inbox pane: tất cả hội thoại --}}
                <div data-discussion-tab-pane="inbox" class="space-y-0.5">
                    @forelse($threads as $thread)
                        @php
                            $latest = $thread->latestMessage;
                            $isActive = $selectedThread?->id === $thread->id;
                            $tName = $threadName($thread);
                            $tInitial = $threadInitial($thread);
                            $unread = $thread->unreadCountFor($currentUserId);
                            $searchText = mb_strtolower($tName . ' ' . ($latest?->body ?? ''));
                        @endphp
                        <a href="{{ route($routes['index'], ['thread' => $thread->id]) }}"
                           data-discussion-room
                           data-search="{{ $searchText }}"
                           class="group mb-1 flex min-h-19 items-center gap-3 rounded-2xl px-3 py-2.5 no-underline transition {{ $isActive ? 'bg-slate-100 shadow-sm' : 'hover:bg-slate-50' }}">
                            <div class="relative grid h-12 w-12 shrink-0 place-items-center rounded-full {{ $isActive ? 'bg-green-600 text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-green-100 group-hover:text-green-700' }}">
                                <span class="text-sm font-black">{{ $tInitial }}</span>
                                <span class="absolute -bottom-0.5 -right-0.5 h-3.5 w-3.5 rounded-full border-2 border-white bg-green-400"></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="min-w-0 flex-1 truncate text-sm font-black text-slate-950">{{ $tName }}</p>
                                    <span class="shrink-0 text-[10px] font-bold text-slate-400">{{ $thread->last_message_at?->diffForHumans() ?? 'Now' }}</span>
                                </div>
                                <div class="mt-1 flex items-center gap-2">
                                    <p class="min-w-0 flex-1 truncate text-xs font-semibold {{ $latest ? 'text-slate-500' : 'text-slate-400' }}">
                                        {{ $latest?->body ?: __('teacher-discussion::app.no_messages_short') }}
                                    </p>
                                    @if($unread > 0)
                                        <span class="grid h-5 min-w-5 place-items-center rounded-full bg-green-600 px-1.5 text-[10px] font-black text-white">{{ $unread > 99 ? '99+' : $unread }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center">
                            <x-heroicon-o-user-group class="mx-auto h-9 w-9 text-slate-300" />
                            <p class="mt-3 text-sm font-black text-slate-700">@lang('teacher-discussion::app.no_threads')</p>
                        </div>
                    @endforelse
                </div>

                {{-- Groups pane: chỉ nhóm / lớp --}}
                <div data-discussion-tab-pane="groups" class="hidden space-y-0.5">
                    @forelse($threads->whereIn('type', ['group', 'class']) as $thread)
                        @php
                            $latest = $thread->latestMessage;
                            $isActive = $selectedThread?->id === $thread->id;
                            $tName = $threadName($thread);
                            $tInitial = $threadInitial($thread);
                            $unread = $thread->unreadCountFor($currentUserId);
                        @endphp
                        <a href="{{ route($routes['index'], ['thread' => $thread->id]) }}"
                           class="group mb-1 flex min-h-19 items-center gap-3 rounded-2xl px-3 py-2.5 no-underline transition {{ $isActive ? 'bg-slate-100 shadow-sm' : 'hover:bg-slate-50' }}">
                            <div class="relative grid h-12 w-12 shrink-0 place-items-center rounded-full {{ $isActive ? 'bg-green-600 text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-green-100 group-hover:text-green-700' }}">
                                <span class="text-sm font-black">{{ $tInitial }}</span>
                                <span class="absolute -bottom-0.5 -right-0.5 h-3.5 w-3.5 rounded-full border-2 border-white bg-green-400"></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="min-w-0 truncate text-sm font-black text-slate-950">{{ $tName }}</p>
                                <div class="mt-1 flex items-center gap-2">
                                    <p class="min-w-0 flex-1 truncate text-xs font-semibold text-slate-400">{{ $threadSub($thread) }}</p>
                                    @if($unread > 0)
                                        <span class="grid h-5 min-w-5 place-items-center rounded-full bg-green-600 px-1.5 text-[10px] font-black text-white">{{ $unread > 99 ? '99+' : $unread }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center">
                            <x-heroicon-o-user-group class="mx-auto h-9 w-9 text-slate-300" />
                            <p class="mt-3 text-sm font-black text-slate-700">@lang('teacher-discussion::app.no_groups')</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </aside>

        {{-- ============ CHAT ============ --}}
        <section class="relative flex min-h-0 flex-col bg-white">
            @if($selectedThread)
                <header class="shrink-0 border-b border-slate-100 bg-white px-5 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="relative grid h-12 w-12 shrink-0 place-items-center rounded-full {{ $selectedThread->theme_color ? 'bg-'.$selectedThread->theme_color.'-100 text-'.$selectedThread->theme_color.'-700' : 'bg-green-100 text-green-700' }}">
                                <span class="text-sm font-black">{{ $selectedInitial }}</span>
                                <span class="absolute bottom-0 right-0 h-3.5 w-3.5 rounded-full border-2 border-white bg-green-400"></span>
                            </div>
                            <div class="min-w-0">
                                <h2 class="truncate text-base font-black text-slate-950">{{ $selectedName }}</h2>
                                <p class="truncate text-xs font-bold text-slate-400">{{ $selectedSub }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50">
                                <x-heroicon-o-phone class="h-4 w-4" />
                            </button>
                            <button type="button" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50">
                                <x-heroicon-o-video-camera class="h-4 w-4" />
                            </button>
                            <button type="button" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 xl:hidden" data-discussion-info-toggle>
                                <x-heroicon-o-information-circle class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </header>

                <div class="min-h-0 flex-1 overflow-y-auto bg-[#f7f8fb] px-6 py-5" data-discussion-messages data-thread-id="{{ $selectedThread->id }}">
                    <div class="mx-auto flex max-w-3xl flex-col space-y-4">
                        @forelse($messages as $message)
                            @php
                                $mine = (int) $message->sender_id === $currentUserId;
                                $senderName = $message->sender?->name ?? __('teacher-discussion::app.unknown_sender');
                                $senderInitial = mb_strtoupper(mb_substr($senderName, 0, 1));
                            @endphp
                            <div class="flex items-end gap-2.5 {{ $mine ? 'justify-end' : 'justify-start' }}">
                                @unless($mine)
                                    <div class="relative grid h-9 w-9 shrink-0 place-items-center rounded-full bg-slate-100 text-xs font-black text-slate-600">
                                        {{ $senderInitial }}
                                        <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white bg-green-400"></span>
                                    </div>
                                @endunless

                                <div class="max-w-[min(35rem,78%)]">
                                    <div class="mb-1 flex items-center gap-2 px-1 {{ $mine ? 'justify-end' : '' }}">
                                        @unless($mine)
                                            <span class="text-xs font-black text-slate-700">{{ $senderName }}</span>
                                        @endunless
                                        <span class="text-[11px] font-bold text-slate-400">{{ $message->created_at?->format('D H:i') }}</span>
                                    </div>

                                    <div class="space-y-2 rounded-2xl px-4 py-3 {{ $mine ? 'rounded-br-md bg-green-600 text-white' : 'rounded-bl-md bg-slate-100 text-slate-800' }}">
                                        @if($message->body !== '')
                                            <p class="whitespace-pre-line wrap-break-word text-sm font-semibold leading-6">{{ $message->body }}</p>
                                        @endif

                                        @if($message->attachments->isNotEmpty())
                                            <div class="grid gap-2 {{ $message->attachments->filter(fn ($a) => $a->isImage())->count() > 1 ? 'grid-cols-2' : '' }}">
                                                @foreach($message->attachments as $attachment)
                                                    @if($attachment->isImage())
                                                        <a href="{{ route($routes['attachment'], $attachment) }}" target="_blank" class="block overflow-hidden rounded-2xl bg-black/5 no-underline">
                                                            <img src="{{ route($routes['attachment'], $attachment) }}" alt="{{ $attachment->original_name }}" class="max-h-56 w-full object-cover">
                                                        </a>
                                                    @else
                                                        <a href="{{ route($routes['attachment'], $attachment) }}" target="_blank" class="flex items-center gap-3 rounded-2xl {{ $mine ? 'bg-white/15 text-white' : 'bg-white text-slate-700' }} p-3 no-underline">
                                                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl {{ $mine ? 'bg-white/20' : 'bg-slate-100' }}">
                                                                <x-heroicon-o-document class="h-5 w-5" />
                                                            </span>
                                                            <span class="min-w-0 flex-1">
                                                                <span class="block truncate text-xs font-black">{{ $attachment->original_name }}</span>
                                                                <span class="block text-[11px] font-bold opacity-70">{{ $attachment->sizeLabel() }}</span>
                                                            </span>
                                                        </a>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="mx-auto mt-16 max-w-sm rounded-3xl bg-white px-8 py-10 text-center shadow-sm">
                                <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-green-50 text-green-700">
                                    <x-heroicon-o-chat-bubble-oval-left-ellipsis class="h-7 w-7" />
                                </div>
                                <p class="mt-4 text-sm font-black text-slate-900">@lang('teacher-discussion::app.no_messages')</p>
                                <p class="mt-2 text-xs font-bold leading-5 text-slate-400">@lang('teacher-discussion::app.no_messages_desc')</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <form method="POST" action="{{ route($routes['store'], $selectedThread) }}" enctype="multipart/form-data" data-discussion-form data-current-user="{{ auth()->id() }}" class="shrink-0 border-t border-slate-100 bg-white px-5 py-3">
                    @csrf
                    <input type="file" name="attachments[]" class="hidden" data-discussion-files multiple accept="image/*,.pdf,.txt,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar">
                    <div class="mx-auto max-w-3xl">
                        <div class="mb-2 hidden flex-wrap gap-2" data-discussion-file-preview></div>
                        <div class="flex items-end gap-2 rounded-2xl border border-slate-200 bg-white p-2">
                            <textarea name="body" rows="1" maxlength="2000" placeholder="@lang('teacher-discussion::app.message_placeholder')" data-discussion-input class="block max-h-32 min-h-10 flex-1 resize-none border-0 bg-transparent px-3 py-2.5 text-sm font-semibold leading-5 text-slate-800 outline-none placeholder:text-slate-400">{{ old('body') }}</textarea>
                            <button type="button" class="grid h-10 w-10 shrink-0 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-50 hover:text-green-700" data-discussion-file-trigger title="@lang('teacher-discussion::app.attach_files')">
                                <x-heroicon-o-paper-clip class="h-5 w-5" />
                            </button>
                            <button type="button" class="grid h-10 w-10 shrink-0 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-50 hover:text-green-700" data-discussion-image-trigger title="Images">
                                <x-heroicon-o-photo class="h-5 w-5" />
                            </button>
                            <button class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-green-600 text-white shadow-sm transition hover:bg-green-500">
                                <x-heroicon-o-paper-airplane class="h-5 w-5" />
                            </button>
                        </div>
                        @error('body')<p class="mt-1.5 px-2 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
                        @error('attachments')<p class="mt-1.5 px-2 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
                        @error('attachments.*')<p class="mt-1.5 px-2 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
                    </div>
                </form>
            @else
                <div class="grid flex-1 place-items-center p-6">
                    <div class="max-w-md rounded-3xl bg-white px-8 py-10 text-center shadow-sm">
                        <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-green-50 text-green-700">
                            <x-heroicon-o-chat-bubble-left-right class="h-8 w-8" />
                        </div>
                        <h2 class="mt-4 text-lg font-black text-slate-950">@lang('teacher-discussion::app.empty_title')</h2>
                        <p class="mt-2 text-sm font-bold leading-6 text-slate-400">@lang('teacher-discussion::app.empty_desc')</p>
                    </div>
                </div>
            @endif
        </section>

        {{-- ============ INFO PANEL ============ --}}
        @if($selectedThread)
            <aside class="min-h-0 overflow-y-auto border-l border-slate-200 bg-white max-xl:absolute max-xl:bottom-0 max-xl:right-0 max-xl:top-0 max-xl:z-30 max-xl:hidden max-xl:w-80 max-xl:shadow-2xl" data-discussion-info-panel>
                <div class="border-b border-slate-100 p-5">
                    <h2 class="text-sm font-black text-slate-950">@lang('teacher-discussion::app.group_info')</h2>
                </div>

                <div class="p-5 text-center">
                    <div class="mx-auto grid h-20 w-20 place-items-center rounded-full {{ $selectedThread->theme_color ? 'bg-'.$selectedThread->theme_color.'-100 text-'.$selectedThread->theme_color.'-700' : 'bg-green-100 text-green-700' }} text-xl font-black">
                        {{ $selectedInitial }}
                    </div>
                    <h3 class="mt-3 text-base font-black text-slate-950">{{ $selectedName }}</h3>
                    <p class="mt-1 text-xs font-bold text-slate-400">{{ number_format($members->count()) }} @lang('teacher-discussion::app.members')</p>
                </div>

                <div class="space-y-6 px-5 pb-6">
                    <section>
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-sm font-black text-slate-950">@lang('teacher-discussion::app.members')</h3>
                            <span class="text-xs font-black text-green-700">@lang('teacher-discussion::app.view_all')</span>
                        </div>
                        <div class="space-y-2">
                            @forelse($members->take(5) as $member)
                                @php $memberInitial = mb_strtoupper(mb_substr($member->name ?? $member->email, 0, 1)); @endphp
                                <div class="flex items-center gap-3">
                                    <div class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-slate-100 text-xs font-black text-slate-600">{{ $memberInitial }}</div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-black text-slate-800">{{ $member->name }}</p>
                                        <p class="truncate text-[11px] font-bold text-slate-400">{{ $member->email }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="rounded-xl bg-slate-50 p-3 text-center text-xs font-bold text-slate-400">@lang('teacher-discussion::app.no_members')</p>
                            @endforelse
                        </div>
                    </section>

                    <section>
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-sm font-black text-slate-950">@lang('teacher-discussion::app.shared_files')</h3>
                            <span class="text-xs font-black text-green-700">@lang('teacher-discussion::app.view_all')</span>
                        </div>
                        @if($imageAttachments->isNotEmpty())
                            <div class="grid grid-cols-3 gap-2">
                                @foreach($imageAttachments->take(6) as $attachment)
                                    <a href="{{ route($routes['attachment'], $attachment) }}" target="_blank" class="block overflow-hidden rounded-xl bg-slate-100">
                                        <img src="{{ route($routes['attachment'], $attachment) }}" alt="{{ $attachment->original_name }}" class="aspect-square w-full object-cover">
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="grid grid-cols-3 gap-2">
                                @foreach(range(1, 6) as $i)
                                    <div class="grid aspect-square place-items-center rounded-xl bg-slate-50 text-slate-300">
                                        <x-heroicon-o-photo class="h-5 w-5" />
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    <section>
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-sm font-black text-slate-950">@lang('teacher-discussion::app.files')</h3>
                            <span class="text-xs font-black text-green-700">@lang('teacher-discussion::app.view_all')</span>
                        </div>
                        <div class="space-y-2">
                            @forelse($fileAttachments->take(5) as $attachment)
                                <a href="{{ route($routes['attachment'], $attachment) }}" target="_blank" class="flex items-center gap-3 rounded-xl p-2 no-underline transition hover:bg-slate-50">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-500">
                                        <x-heroicon-o-document class="h-5 w-5" />
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-xs font-black text-slate-800">{{ $attachment->original_name }}</span>
                                        <span class="block text-[11px] font-bold text-slate-400">{{ $attachment->sizeLabel() }}</span>
                                    </span>
                                </a>
                            @empty
                                <p class="rounded-xl bg-slate-50 p-3 text-center text-xs font-bold text-slate-400">@lang('teacher-discussion::app.no_shared_files')</p>
                            @endforelse
                        </div>
                    </section>
                </div>
            </aside>
        @endif
    </div>
</div>
@endsection
