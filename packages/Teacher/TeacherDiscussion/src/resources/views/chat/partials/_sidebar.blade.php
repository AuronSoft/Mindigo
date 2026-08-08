<aside class="flex min-h-0 flex-col border-r border-slate-200 bg-white {{ $selectedThread ? 'max-lg:hidden' : '' }}">
    <div class="shrink-0 border-b border-slate-200 bg-white">
        <div class="flex items-center gap-2 px-3 pb-3 pt-4">
            <div class="flex h-10 min-w-0 flex-1 items-center gap-2 rounded-lg bg-slate-100 px-3">
                <x-heroicon-o-magnifying-glass class="h-4 w-4 shrink-0 text-slate-500" />
                <input type="search" data-discussion-search placeholder="@lang('teacher-discussion::app.search_conversations')" class="min-w-0 flex-1 border-0 bg-transparent text-sm font-semibold text-slate-800 outline-none placeholder:text-slate-500">
            </div>
            <button type="button" data-discussion-new-direct title="@lang('teacher-discussion::app.new_direct')" class="grid h-10 w-10 shrink-0 place-items-center rounded-lg text-slate-600 transition hover:bg-slate-100 hover:text-green-700">
                <x-heroicon-o-user-plus class="h-5 w-5" />
            </button>
            <button type="button" data-discussion-new-group title="@lang('teacher-discussion::app.new_group')" class="grid h-10 w-10 shrink-0 place-items-center rounded-lg text-slate-600 transition hover:bg-slate-100 hover:text-green-700">
                <x-heroicon-o-user-group class="h-5 w-5" />
            </button>
        </div>

        <div class="flex h-11 items-end gap-5 px-4">
            <button type="button" data-discussion-list-filter="all" class="h-full border-b-2 border-green-600 px-1 text-xs font-black text-green-700">
                @lang('teacher-discussion::app.all_conversations')
            </button>
            <button type="button" data-discussion-list-filter="unread" class="h-full border-b-2 border-transparent px-1 text-xs font-black text-slate-500 transition hover:text-green-700">
                @lang('teacher-discussion::app.unread')
            </button>
            <button type="button" data-discussion-list-filter="groups" class="ml-auto flex h-8 items-center gap-1 rounded-lg px-2 text-xs font-bold text-slate-500 transition hover:bg-slate-100 hover:text-green-700">
                @lang('teacher-discussion::app.groups')
                <x-heroicon-o-chevron-down class="h-3.5 w-3.5" />
            </button>
        </div>
    </div>

    <div class="min-h-0 flex-1 overflow-y-auto py-1" data-discussion-room-list>
        @forelse($threads as $thread)
            @php
                $latest = $thread->latestMessage;
                $isActive = $selectedThread?->id === $thread->id;
                $tName = $threadName($thread);
                $tInitial = $threadInitial($thread);
                $unread = $thread->unreadCountFor($currentUserId);
                $searchText = mb_strtolower($tName.' '.($latest?->body ?? ''));
                $isPinned = (bool) ($thread->viewer_is_pinned ?? false);
                $isMuted = (bool) ($thread->viewer_is_muted ?? false);
            @endphp
            <a href="{{ route($routes['index'], ['thread' => $thread->id]) }}"
               data-discussion-room
               data-unread="{{ $unread > 0 ? 'true' : 'false' }}"
               data-room-type="{{ $thread->type }}"
               data-search="{{ $searchText }}"
               class="group flex min-h-18 items-center gap-3 border-l-2 px-3 py-2.5 no-underline transition {{ $isActive ? 'border-green-600 bg-green-50/80' : 'border-transparent hover:bg-slate-50' }}">
                <div class="relative grid h-12 w-12 shrink-0 place-items-center rounded-full {{ $isActive ? 'bg-green-600 text-white' : 'bg-slate-100 text-slate-700' }}">
                    <span class="text-sm font-black">{{ $tInitial }}</span>
                    <span class="absolute bottom-0 right-0 h-3.5 w-3.5 rounded-full border-2 border-white bg-green-400"></span>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <p class="min-w-0 flex-1 truncate text-sm font-black text-slate-900">{{ $tName }}</p>
                        <span class="shrink-0 text-[10px] font-semibold {{ $unread > 0 ? 'text-green-700' : 'text-slate-400' }}">{{ $thread->last_message_at?->diffForHumans(short: true) ?? __('teacher-discussion::app.now') }}</span>
                    </div>
                    <div class="mt-1 flex items-center gap-1.5">
                        @if($isMuted)<x-heroicon-o-bell-slash class="h-3.5 w-3.5 shrink-0 text-slate-400" />@endif
                        <p class="min-w-0 flex-1 truncate text-xs {{ $unread > 0 ? 'font-bold text-slate-700' : 'font-semibold text-slate-500' }}">
                            {{ $latest?->body ?: __('teacher-discussion::app.no_messages_short') }}
                        </p>
                        @if($isPinned)<x-heroicon-s-bookmark class="h-3.5 w-3.5 shrink-0 text-green-600" />@endif
                        @if($unread > 0)
                            <span class="grid h-5 min-w-5 place-items-center rounded-full bg-green-600 px-1.5 text-[10px] font-black text-white">{{ $unread > 99 ? '99+' : $unread }}</span>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="mx-4 mt-6 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center">
                <x-heroicon-o-chat-bubble-left-right class="mx-auto h-9 w-9 text-slate-300" />
                <p class="mt-3 text-sm font-black text-slate-700">@lang('teacher-discussion::app.no_threads')</p>
            </div>
        @endforelse

        <div data-discussion-filter-empty class="mx-4 mt-6 hidden rounded-xl bg-slate-50 p-5 text-center text-xs font-bold text-slate-400">
            @lang('teacher-discussion::app.no_matching_conversations')
        </div>
    </div>
</aside>
