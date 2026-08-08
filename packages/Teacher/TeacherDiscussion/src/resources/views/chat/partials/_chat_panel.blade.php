<section class="relative flex min-h-0 flex-col bg-white">
    @if($selectedThread)
        <header class="shrink-0 border-b border-slate-200 bg-white px-4 py-2.5">
            <div class="flex items-center justify-between gap-3">
                <div class="flex min-w-0 items-center gap-3">
                    <a href="{{ route($routes['index']) }}" class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-slate-500 no-underline transition hover:bg-slate-100 lg:hidden">
                        <x-heroicon-o-chevron-left class="h-5 w-5" />
                    </a>
                    <div class="relative grid h-11 w-11 shrink-0 place-items-center rounded-full {{ $selectedThread->theme_color ? 'bg-'.$selectedThread->theme_color.'-100 text-'.$selectedThread->theme_color.'-700' : 'bg-green-100 text-green-700' }}">
                        <span class="text-sm font-black">{{ $selectedInitial }}</span>
                        <span class="absolute bottom-0 right-0 h-3.5 w-3.5 rounded-full border-2 border-white bg-green-400"></span>
                    </div>
                    <div class="min-w-0">
                        <h2 class="truncate text-base font-black text-slate-950">{{ $selectedName }}</h2>
                        <p class="truncate text-xs font-semibold text-slate-500" data-discussion-typing-status>{{ $currentPreference?->is_muted ? __('teacher-discussion::app.notifications_muted') : $selectedSub }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" title="@lang('teacher-discussion::app.voice_call')" class="grid h-10 w-10 place-items-center rounded-lg text-slate-600 transition hover:bg-slate-100 hover:text-green-700">
                        <x-heroicon-o-phone class="h-4 w-4" />
                    </button>
                    <button type="button" title="@lang('teacher-discussion::app.video_call')" class="grid h-10 w-10 place-items-center rounded-lg text-slate-600 transition hover:bg-slate-100 hover:text-green-700">
                        <x-heroicon-o-video-camera class="h-4 w-4" />
                    </button>
                    <button type="button" data-discussion-message-search-toggle title="@lang('teacher-discussion::app.search_messages')" class="grid h-10 w-10 place-items-center rounded-lg text-slate-600 transition hover:bg-slate-100 hover:text-green-700">
                        <x-heroicon-o-magnifying-glass class="h-4 w-4" />
                    </button>
                    <button type="button" class="grid h-10 w-10 place-items-center rounded-lg text-slate-600 transition hover:bg-slate-100 hover:text-green-700 xl:hidden" data-discussion-info-toggle>
                        <x-heroicon-o-information-circle class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </header>

        <div data-discussion-message-search class="hidden shrink-0 border-b border-slate-200 bg-white px-5 py-2">
            <div class="ml-auto flex h-10 max-w-md items-center gap-2 rounded-lg bg-slate-100 px-3">
                <x-heroicon-o-magnifying-glass class="h-4 w-4 text-slate-500" />
                <input type="search" data-discussion-message-search-input placeholder="@lang('teacher-discussion::app.search_messages')" class="min-w-0 flex-1 border-0 bg-transparent text-sm font-semibold text-slate-800 outline-none">
                <button type="button" data-discussion-message-search-close class="text-slate-500 hover:text-slate-800"><x-heroicon-o-x-mark class="h-4 w-4" /></button>
            </div>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto bg-slate-50 px-5 py-5" data-discussion-messages data-thread-id="{{ $selectedThread->id }}" data-attachment-url="{{ route($routes['attachment'], ['attachment' => '__ATTACHMENT_ID__']) }}" data-older-url="{{ route($routes['messageOlder'], $selectedThread) }}" data-has-older="{{ ($hasOlderMessages ?? false) ? 'true' : 'false' }}">
            <div class="mx-auto flex max-w-3xl flex-col space-y-4">
                <div class="flex justify-center" data-discussion-older-wrap>
                    <button type="button" data-discussion-older-btn class="hidden rounded-full border border-slate-200 bg-white px-4 py-2 text-[11px] font-black text-slate-600 shadow-sm transition hover:border-green-300 hover:text-green-700">
                        @lang('teacher-discussion::app.view_older_messages')
                    </button>
                </div>
                @forelse($messages as $message)
                    @php
                        $mine = (int) $message->sender_id === $currentUserId;
                        $senderName = $message->sender?->name ?? __('teacher-discussion::app.unknown_sender');
                        $senderInitial = mb_strtoupper(mb_substr($senderName, 0, 1));
                    @endphp
                    <div id="discussion-message-{{ $message->id }}" data-discussion-message-row data-message-text="{{ mb_strtolower((string) $message->body) }}" data-msg-id="{{ $message->id }}" data-msg-sender="{{ $message->sender_id }}" data-msg-own="{{ $mine ? '1' : '0' }}" class="group/message flex items-end gap-2.5 {{ $mine ? 'justify-end' : 'justify-start' }}">
                        @unless($mine)
                            <div class="relative grid h-9 w-9 shrink-0 place-items-center rounded-full bg-slate-100 text-xs font-black text-slate-600">
                                {{ $senderInitial }}
                                <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white bg-green-400"></span>
                            </div>
                        @endunless

                        <div class="relative max-w-[min(35rem,78%)]">
                            <div class="mb-1 flex items-center gap-2 px-1 {{ $mine ? 'justify-end' : '' }}">
                                @unless($mine)
                                    <span class="text-xs font-black text-slate-700">{{ $senderName }}</span>
                                @endunless
                                <span class="text-[11px] font-bold text-slate-400">{{ $message->created_at?->format('D H:i') }}</span>
                                @if($mine && $message->edited_at)
                                    <span class="text-[11px] font-bold text-slate-400">· @lang('teacher-discussion::app.edited')</span>
                                @endif
                            </div>

                            <div class="space-y-2 rounded-xl border px-3.5 py-2.5 shadow-sm {{ $mine ? 'rounded-br-sm border-green-600 bg-green-600 text-white' : 'rounded-bl-sm border-slate-200 bg-white text-slate-800' }}">
                                @if($message->is_pinned)
                                    <div class="flex items-center gap-1 text-[10px] font-black {{ $mine ? 'text-green-100' : 'text-green-700' }}">
                                        <x-heroicon-s-bookmark class="h-3 w-3" />
                                        @lang('teacher-discussion::app.pinned')
                                    </div>
                                @endif
                                @if($message->repliesTo)
                                    <div class="rounded-lg border-l-2 {{ $mine ? 'border-green-300 bg-white/10' : 'border-slate-200 bg-slate-50' }} px-2.5 py-1.5">
                                        <span class="block truncate text-[10px] font-black {{ $mine ? 'text-green-100' : 'text-slate-500' }}">{{ $message->repliesTo->sender?->name }}</span>
                                        <span class="line-clamp-1 text-[11px] font-semibold {{ $mine ? 'text-green-100/80' : 'text-slate-500' }}">{{ $message->repliesTo->body ?: __('teacher-discussion::app.attachment_message') }}</span>
                                    </div>
                                @endif
                                <div data-message-body>
                                    @if($message->body !== '')
                                        <p class="whitespace-pre-line wrap-break-word text-sm font-semibold leading-6">{{ $message->body }}</p>
                                    @endif
                                </div>

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

                            @if($message->reactions->isNotEmpty())
                                <div data-reactions-display class="mt-1 flex flex-wrap gap-1 {{ $mine ? 'justify-end' : '' }}">
                                    @foreach($message->reactionSummary() as $summary)
                                        <button type="button" data-discussion-react data-emoji="{{ $summary['emoji'] }}" class="flex items-center gap-1 rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[11px] font-bold text-slate-700 shadow-sm transition hover:border-green-300">
                                            <span>{{ $summary['emoji'] }}</span>
                                            <span>{{ $summary['count'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <div data-reactions-display class="mt-1 hidden"></div>
                            @endif

                            @if($mine && $message->created_at)
                                @php $messageRead = $members->filter(fn ($m) => (int) $m->id !== $currentUserId && $selectedThread->lastReadFor((int) $m->id) && $selectedThread->lastReadFor((int) $m->id)->gte($message->created_at)); @endphp
                                @if($messageRead->isNotEmpty())
                                    <div class="mt-0.5 flex justify-end px-1 text-[10px] font-black text-green-600">@lang('teacher-discussion::app.seen')</div>
                                @endif
                            @endif

                            <div class="absolute top-7 flex items-center gap-1 {{ $mine ? '-left-9' : '-right-9' }} rounded-full border border-slate-200 bg-white p-0.5 opacity-0 shadow-sm transition group-hover/message:opacity-100 focus-within:opacity-100">
                                <button type="button" data-discussion-reply data-msg-id="{{ $message->id }}" data-msg-sender="{{ $senderName }}" data-msg-preview="{{ Str::limit((string) $message->body, 60) }}" title="@lang('teacher-discussion::app.reply')" class="grid h-7 w-7 place-items-center rounded-full text-slate-500 transition hover:text-green-700">
                                    <x-heroicon-o-arrow-uturn-left class="h-3.5 w-3.5" />
                                </button>
                                <button type="button" data-discussion-react-picker title="@lang('teacher-discussion::app.reacted')" class="grid h-7 w-7 place-items-center rounded-full text-slate-500 transition hover:text-green-700">
                                    <x-heroicon-o-face-smile class="h-3.5 w-3.5" />
                                </button>
                                @if($mine)
                                    @if(! $message->isReadByOthers())
                                        <button type="button" data-discussion-edit data-msg-id="{{ $message->id }}" data-msg-body="{{ $message->body }}" title="@lang('teacher-discussion::app.edit_message')" class="grid h-7 w-7 place-items-center rounded-full text-slate-500 transition hover:text-green-700">
                                            <x-heroicon-o-pencil class="h-3.5 w-3.5" />
                                        </button>
                                    @endif
                                    <form method="POST" action="{{ route($routes['messageDestroy'], [$selectedThread, $message]) }}" data-discussion-delete-form data-delete-mode="{{ $message->isReadByOthers() ? 'self' : 'recall' }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" data-discussion-delete title="@lang('teacher-discussion::app.delete_message')" class="grid h-7 w-7 place-items-center rounded-full text-slate-500 transition hover:text-red-600">
                                            <x-heroicon-o-trash class="h-3.5 w-3.5" />
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route($routes['messagePin'], [$selectedThread, $message]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_pinned" value="{{ $message->is_pinned ? 0 : 1 }}">
                                    <button title="{{ $message->is_pinned ? __('teacher-discussion::app.unpin_message') : __('teacher-discussion::app.pin_message') }}" class="grid h-7 w-7 place-items-center rounded-full text-slate-500 transition hover:text-green-700">
                                        <x-heroicon-o-bookmark class="h-3.5 w-3.5" />
                                    </button>
                                </form>
                            </div>
                            <div data-discussion-reactions class="absolute right-0 top-7 z-10 hidden items-center gap-1 rounded-full border border-slate-200 bg-white px-2 py-1 shadow-md">
                                @foreach(['👍','❤️','😂','😮','😢','🙏'] as $emoji)
                                    <form method="POST" action="{{ route($routes['messageReact'], [$selectedThread, $message]) }}">
                                        @csrf
                                        <input type="hidden" name="emoji" value="{{ $emoji }}">
                                        <button type="submit" class="grid h-7 w-7 place-items-center rounded-full text-sm transition hover:bg-slate-100" title="{{ $emoji }}">{{ $emoji }}</button>
                                    </form>
                                @endforeach
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

        <form method="POST" action="{{ route($routes['store'], $selectedThread) }}" enctype="multipart/form-data" data-discussion-form data-current-user="{{ auth()->id() }}" data-update-url="{{ route($routes['messageUpdate'], [$selectedThread, '__MESSAGE_ID__']) }}" data-react-url="{{ route($routes['messageReact'], [$selectedThread, '__MESSAGE_ID__']) }}" class="shrink-0 border-t border-slate-200 bg-white px-5 py-3">
            @csrf
            <input type="file" name="attachments[]" class="hidden" data-discussion-files multiple accept="image/*,.pdf,.txt,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar">
            <input type="hidden" name="reply_to_id" value="" data-discussion-reply-id>
            <div class="mx-auto max-w-3xl">
                <div class="mb-2 hidden items-center justify-between rounded-xl border border-green-200 bg-green-50 px-3 py-2" data-discussion-reply-bar>
                    <span class="min-w-0">
                        <span class="block text-[10px] font-black text-green-700">@lang('teacher-discussion::app.replying_to') <span data-discussion-reply-sender class="text-slate-700"></span></span>
                        <span class="block truncate text-[11px] font-semibold text-slate-500" data-discussion-reply-preview></span>
                    </span>
                    <button type="button" data-discussion-reply-cancel class="grid h-6 w-6 shrink-0 place-items-center rounded-full text-slate-400 transition hover:bg-green-100 hover:text-green-700" title="@lang('teacher-discussion::app.cancel_reply')">
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                    </button>
                </div>
                <div class="mb-2 hidden flex-wrap gap-2" data-discussion-file-preview></div>
                <div class="flex items-end gap-2 rounded-xl border border-slate-200 bg-white p-1.5 focus-within:border-green-400 focus-within:ring-2 focus-within:ring-green-100">
                    <textarea name="body" rows="1" maxlength="2000" placeholder="@lang('teacher-discussion::app.message_placeholder')" data-discussion-input class="block max-h-32 min-h-10 flex-1 resize-none border-0 bg-transparent px-3 py-2.5 text-sm font-semibold leading-5 text-slate-800 outline-none placeholder:text-slate-400">{{ old('body') }}</textarea>
                    <button type="button" class="grid h-10 w-10 shrink-0 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-50 hover:text-green-700" data-discussion-file-trigger title="@lang('teacher-discussion::app.attach_files')">
                        <x-heroicon-o-paper-clip class="h-5 w-5" />
                    </button>
                    <button type="button" class="grid h-10 w-10 shrink-0 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-50 hover:text-green-700" data-discussion-image-trigger title="@lang('teacher-discussion::app.images')">
                        <x-heroicon-o-photo class="h-5 w-5" />
                    </button>
                    <button type="button" class="relative grid h-10 w-10 shrink-0 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-50 hover:text-green-700" data-discussion-emoji-toggle title="Emoji">
                        <x-heroicon-o-face-smile class="h-5 w-5" />
                        <div data-discussion-emoji-picker class="absolute bottom-12 right-0 z-20 hidden w-72 rounded-2xl border border-slate-200 bg-white p-3 shadow-2xl">
                            <div class="mb-2 grid grid-cols-2 gap-1.5">
                                <input type="search" data-discussion-emoji-search placeholder="@lang('teacher-discussion::app.search_messages')" class="col-span-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 outline-none focus:border-green-400">
                            </div>
                            <div class="flex max-h-56 flex-wrap content-start gap-1 overflow-y-auto" data-discussion-emoji-list></div>
                        </div>
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
