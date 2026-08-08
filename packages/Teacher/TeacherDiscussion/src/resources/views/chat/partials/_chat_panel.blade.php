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
