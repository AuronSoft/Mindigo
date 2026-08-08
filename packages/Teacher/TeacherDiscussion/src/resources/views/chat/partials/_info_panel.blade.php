<aside class="min-h-0 overflow-y-auto border-l border-slate-200 bg-white max-xl:absolute max-xl:bottom-0 max-xl:right-0 max-xl:top-0 max-xl:z-30 max-xl:hidden max-xl:w-80 max-xl:shadow-2xl" data-discussion-info-panel>
    <div class="flex h-16 items-center justify-between border-b border-slate-200 px-5">
        <h2 class="text-sm font-black text-slate-950">@lang('teacher-discussion::app.group_info')</h2>
        <button type="button" data-discussion-info-toggle class="grid h-9 w-9 place-items-center rounded-lg text-slate-500 transition hover:bg-slate-100 xl:hidden">
            <x-heroicon-o-x-mark class="h-5 w-5" />
        </button>
    </div>

    <div class="p-5 text-center">
        <div class="mx-auto grid h-20 w-20 place-items-center rounded-full {{ $selectedThread->theme_color ? 'bg-'.$selectedThread->theme_color.'-100 text-'.$selectedThread->theme_color.'-700' : 'bg-green-100 text-green-700' }} text-xl font-black">
            {{ $selectedInitial }}
        </div>
        <h3 class="mt-3 text-base font-black text-slate-950">{{ $selectedName }}</h3>
        <p class="mt-1 text-xs font-bold text-slate-400">{{ number_format($members->count()) }} @lang('teacher-discussion::app.members')</p>

        <div class="mt-5 grid grid-cols-3 gap-1">
            <form method="POST" action="{{ route($routes['preferences'], $selectedThread) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="is_muted" value="{{ $currentPreference?->is_muted ? 0 : 1 }}">
                <button class="flex w-full flex-col items-center gap-2 rounded-xl px-2 py-3 text-xs font-black text-slate-600 transition hover:bg-slate-50">
                    <span class="grid h-9 w-9 place-items-center rounded-full bg-slate-100 text-slate-600">
                        @if($currentPreference?->is_muted)
                            <x-heroicon-o-bell class="h-4 w-4" />
                        @else
                            <x-heroicon-o-bell-slash class="h-4 w-4" />
                        @endif
                    </span>
                    {{ $currentPreference?->is_muted ? __('teacher-discussion::app.enable_notifications') : __('teacher-discussion::app.mute_notifications') }}
                </button>
            </form>
            <form method="POST" action="{{ route($routes['preferences'], $selectedThread) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="is_pinned" value="{{ $currentPreference?->is_pinned ? 0 : 1 }}">
                <button class="flex w-full flex-col items-center gap-2 rounded-xl px-2 py-3 text-xs font-black text-slate-600 transition hover:bg-slate-50">
                    <span class="grid h-9 w-9 place-items-center rounded-full {{ $currentPreference?->is_pinned ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                        <x-heroicon-o-bookmark class="h-4 w-4" />
                    </span>
                    {{ $currentPreference?->is_pinned ? __('teacher-discussion::app.unpin_conversation') : __('teacher-discussion::app.pin_conversation') }}
                </button>
            </form>
            <button type="button" data-discussion-view="members" class="flex w-full flex-col items-center gap-2 rounded-xl px-1 py-3 text-xs font-black text-slate-600 transition hover:bg-slate-50">
                <span class="grid h-9 w-9 place-items-center rounded-full bg-slate-100 text-slate-600">
                    <x-heroicon-o-user-group class="h-4 w-4" />
                </span>
                @lang('teacher-discussion::app.members')
            </button>
        </div>
    </div>

    <div class="pb-6">
        <section class="border-t border-slate-100 px-5 py-5">
            <h3 class="mb-3 flex items-center gap-2 text-sm font-black text-slate-950">
                <x-heroicon-o-bookmark class="h-4 w-4 text-green-600" />
                @lang('teacher-discussion::app.pinned_messages')
            </h3>
            <div class="space-y-2">
                @forelse($pinnedMessages->take(3) as $pinnedMessage)
                    <a href="#discussion-message-{{ $pinnedMessage->id }}" class="block rounded-xl bg-slate-50 px-3 py-2 no-underline transition hover:bg-green-50">
                        <p class="truncate text-xs font-black text-slate-700">{{ $pinnedMessage->sender?->name }}</p>
                        <p class="mt-1 line-clamp-2 text-[11px] font-semibold leading-4 text-slate-500">{{ $pinnedMessage->body ?: __('teacher-discussion::app.attachment_message') }}</p>
                    </a>
                @empty
                    <p class="rounded-xl bg-slate-50 p-3 text-center text-xs font-bold text-slate-400">@lang('teacher-discussion::app.no_pinned_messages')</p>
                @endforelse
            </div>
        </section>

        <section class="border-t border-slate-100 px-5 py-5">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-950">@lang('teacher-discussion::app.members')</h3>
                <button type="button" data-discussion-view="members" class="flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-black text-green-700 transition hover:bg-green-50">
                    @lang('teacher-discussion::app.view_all')
                    <x-heroicon-o-arrow-right class="h-3.5 w-3.5" />
                </button>
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

        <section class="border-t border-slate-100 px-5 py-5">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-950">@lang('teacher-discussion::app.shared_files')</h3>
                <button type="button" data-discussion-view="images" class="flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-black text-green-700 transition hover:bg-green-50">
                    @lang('teacher-discussion::app.view_all')
                    <x-heroicon-o-arrow-right class="h-3.5 w-3.5" />
                </button>
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

        <section class="border-t border-slate-100 px-5 py-5">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-950">@lang('teacher-discussion::app.files')</h3>
                <button type="button" data-discussion-view="files" class="flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-black text-green-700 transition hover:bg-green-50">
                    @lang('teacher-discussion::app.view_all')
                    <x-heroicon-o-arrow-right class="h-3.5 w-3.5" />
                </button>
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
