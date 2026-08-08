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

        <div class="mt-3 grid grid-cols-2 gap-2">
            <button type="button" data-discussion-new-group class="flex h-11 items-center justify-center gap-2 rounded-xl bg-green-600 text-xs font-black text-white shadow-sm shadow-green-600/20 transition hover:bg-green-500">
                <x-heroicon-o-user-group class="h-4 w-4" />
                @lang('teacher-discussion::app.new_group')
            </button>
            <button type="button" data-discussion-new-direct class="flex h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white text-xs font-black text-slate-600 transition hover:bg-slate-50">
                <x-heroicon-o-chat-bubble-left-right class="h-4 w-4" />
                @lang('teacher-discussion::app.new_direct')
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
