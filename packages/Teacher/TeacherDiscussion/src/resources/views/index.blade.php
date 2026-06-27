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
            const messagePane = document.querySelector('[data-discussion-messages]');
            if (messagePane) {
                messagePane.scrollTop = messagePane.scrollHeight;
            }

            const input = document.querySelector('[data-discussion-input]');
            if (input) {
                input.addEventListener('input', function () {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 132) + 'px';
                });
            }

            const infoToggle = document.querySelector('[data-discussion-info-toggle]');
            const infoPanel = document.querySelector('[data-discussion-info-panel]');
            if (infoToggle && infoPanel) {
                infoToggle.addEventListener('click', function () {
                    const isHidden = infoPanel.classList.toggle('hidden');
                    infoToggle.classList.toggle('bg-green-50', !isHidden);
                    infoToggle.classList.toggle('text-green-700', !isHidden);
                    infoToggle.setAttribute('aria-expanded', String(!isHidden));
                });
            }
        });
    </script>
@endsection

@section('content')
<div class="h-screen overflow-hidden bg-slate-100">
    <div class="grid h-full grid-cols-[22rem_minmax(0,1fr)] max-lg:grid-cols-1">
        <aside class="flex min-h-0 flex-col border-r border-slate-200 bg-white max-lg:h-[18rem] max-lg:border-b max-lg:border-r-0">
            <div class="shrink-0 border-b border-slate-100 px-4 py-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[11px] font-black uppercase tracking-wider text-green-600">@lang('teacher-discussion::app.scope')</p>
                        <h1 class="mt-1 truncate text-xl font-black text-slate-950">@lang('teacher-discussion::app.title')</h1>
                    </div>
                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-green-50 text-green-600">
                        <x-heroicon-o-chat-bubble-left-right class="h-5 w-5" />
                    </div>
                </div>

                <div class="mt-4 flex h-10 items-center gap-2 rounded-full bg-slate-100 px-4 text-slate-400">
                    <x-heroicon-o-magnifying-glass class="h-4 w-4 shrink-0" />
                    <span class="truncate text-xs font-bold">@lang('teacher-discussion::app.class_threads')</span>
                    <span class="ml-auto rounded-full bg-white px-2 py-0.5 text-[11px] font-black text-slate-500">{{ number_format($threads->count()) }}</span>
                </div>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto p-2">
                @forelse($threads as $thread)
                    @php
                        $latest = $thread->latestMessage;
                        $isActive = $selectedThread?->id === $thread->id;
                        $className = $thread->classroom?->name ?? __('teacher-discussion::app.unknown_class');
                        $initial = mb_strtoupper(mb_substr($className, 0, 1));
                    @endphp
                    <a href="{{ route('teacher.discussions.index', ['thread' => $thread->id]) }}"
                       class="group mb-1 flex min-h-[4.75rem] items-center gap-3 rounded-2xl px-3 py-2.5 no-underline transition {{ $isActive ? 'bg-green-50' : 'hover:bg-slate-50' }}">
                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-full {{ $isActive ? 'bg-green-600 text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-green-100 group-hover:text-green-700' }}">
                            <span class="text-sm font-black">{{ $initial }}</span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="min-w-0 flex-1 truncate text-sm font-black {{ $isActive ? 'text-green-900' : 'text-slate-950' }}">{{ $className }}</p>
                                <span class="shrink-0 text-[11px] font-bold text-slate-400">
                                    {{ $thread->last_message_at?->diffForHumans() }}
                                </span>
                            </div>
                            <div class="mt-1 flex items-center gap-2">
                                <p class="min-w-0 flex-1 truncate text-xs font-semibold {{ $latest ? 'text-slate-500' : 'text-slate-400' }}">
                                    {{ $latest?->body ?: __('teacher-discussion::app.no_messages_short') }}
                                </p>
                                @if($thread->messages_count > 0)
                                    <span class="shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black text-slate-500">{{ number_format($thread->messages_count) }}</span>
                                @endif
                            </div>
                            <p class="mt-1 text-[11px] font-bold text-slate-400">
                                {{ number_format($thread->classroom?->students_count ?? 0) }} @lang('teacher-discussion::app.students')
                            </p>
                        </div>
                    </a>
                @empty
                    <div class="m-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center">
                        <x-heroicon-o-user-group class="mx-auto h-9 w-9 text-slate-300" />
                        <p class="mt-3 text-sm font-black text-slate-700">@lang('teacher-discussion::app.no_classrooms')</p>
                        <p class="mt-1 text-xs font-bold leading-5 text-slate-400">@lang('teacher-discussion::app.no_classrooms_desc')</p>
                    </div>
                @endforelse
            </div>
        </aside>

        <section class="relative flex min-h-0 flex-col bg-[#eef2f5]">
            @if($selectedThread)
                @php
                    $selectedName = $selectedThread->classroom?->name ?? __('teacher-discussion::app.unknown_class');
                    $selectedInitial = mb_strtoupper(mb_substr($selectedName, 0, 1));
                @endphp

                <header class="shrink-0 border-b border-slate-200 bg-white/95 px-5 py-3 shadow-sm backdrop-blur">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-green-600 text-white">
                                <span class="text-sm font-black">{{ $selectedInitial }}</span>
                            </div>
                            <div class="min-w-0">
                                <h2 class="truncate text-base font-black text-slate-950">{{ $selectedName }}</h2>
                                <p class="truncate text-xs font-bold text-slate-400">
                                    {{ number_format($selectedThread->classroom?->students_count ?? 0) }} @lang('teacher-discussion::app.students') · {{ number_format($messages->count()) }} @lang('teacher-discussion::app.messages')
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" class="grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-green-50 hover:text-green-700">
                                <x-heroicon-o-magnifying-glass class="h-4 w-4" />
                            </button>
                            <button type="button" class="grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-green-50 hover:text-green-700" data-discussion-info-toggle aria-expanded="false">
                                <x-heroicon-o-information-circle class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </header>

                <div class="flex min-h-0 flex-1">
                    <div class="flex min-w-0 flex-1 flex-col">
                        <div class="min-h-0 flex-1 overflow-y-auto px-5 py-6" data-discussion-messages>
                            <div class="mx-auto flex max-w-4xl flex-col gap-2">
                                @forelse($messages as $message)
                                    @php
                                        $mine = (int) $message->sender_id === (int) auth()->id();
                                        $senderName = $message->sender?->name ?? __('teacher-discussion::app.unknown_sender');
                                        $senderInitial = mb_strtoupper(mb_substr($senderName, 0, 1));
                                    @endphp
                                    <div class="flex items-end gap-2 {{ $mine ? 'justify-end' : 'justify-start' }}">
                                        @unless($mine)
                                            <div class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white text-xs font-black text-slate-500 shadow-sm">{{ $senderInitial }}</div>
                                        @endunless

                                        <div class="max-w-[min(38rem,76%)]">
                                            @unless($mine)
                                                <p class="mb-1 px-2 text-[11px] font-black text-slate-500">{{ $senderName }}</p>
                                            @endunless

                                            <div class="rounded-[1.35rem] px-4 py-2.5 shadow-sm {{ $mine ? 'rounded-br-md bg-green-600 text-white' : 'rounded-bl-md bg-white text-slate-800' }}">
                                                <p class="whitespace-pre-line break-words text-sm font-semibold leading-6">{{ $message->body }}</p>
                                            </div>
                                            <p class="mt-1 px-2 text-[11px] font-bold {{ $mine ? 'text-right text-slate-400' : 'text-slate-400' }}">{{ $message->created_at?->format('d/m H:i') }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="mx-auto mt-16 max-w-sm rounded-3xl bg-white px-8 py-10 text-center shadow-sm">
                                        <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-green-50 text-green-600">
                                            <x-heroicon-o-chat-bubble-oval-left-ellipsis class="h-7 w-7" />
                                        </div>
                                        <p class="mt-4 text-sm font-black text-slate-900">@lang('teacher-discussion::app.no_messages')</p>
                                        <p class="mt-2 text-xs font-bold leading-5 text-slate-400">@lang('teacher-discussion::app.no_messages_desc')</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <form method="POST" action="{{ route('teacher.discussions.messages.store', $selectedThread) }}" class="shrink-0 border-t border-slate-200 bg-white px-5 py-3">
                            @csrf
                            <div class="mx-auto flex max-w-4xl items-end gap-2">
                                <button type="button" class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-green-50 hover:text-green-700">
                                    <x-heroicon-o-plus class="h-5 w-5" />
                                </button>

                                <div class="min-w-0 flex-1">
                                    <textarea name="body" rows="1" maxlength="2000" placeholder="@lang('teacher-discussion::app.message_placeholder')" data-discussion-input class="block max-h-32 min-h-11 w-full resize-none rounded-3xl border-0 bg-slate-100 px-4 py-3 text-sm font-semibold leading-5 text-slate-800 outline-none transition placeholder:text-slate-400 focus:bg-slate-50 focus:ring-2 focus:ring-green-100">{{ old('body') }}</textarea>
                                    @error('body')<p class="mt-1.5 px-2 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <button class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-green-600 text-white shadow-sm transition hover:bg-green-500">
                                    <x-heroicon-o-paper-airplane class="h-5 w-5" />
                                </button>
                            </div>
                        </form>
                    </div>

                    <aside class="hidden w-80 shrink-0 overflow-y-auto border-l border-slate-200 bg-white max-xl:absolute max-xl:bottom-0 max-xl:right-0 max-xl:top-[4.25rem] max-xl:z-20 max-xl:shadow-2xl max-sm:w-full" data-discussion-info-panel>
                        <div class="border-b border-slate-100 p-5 text-center">
                            <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-green-600 text-white">
                                <span class="text-lg font-black">{{ $selectedInitial }}</span>
                            </div>
                            <h3 class="mt-3 text-base font-black text-slate-950">{{ $selectedName }}</h3>
                            <p class="mt-1 text-xs font-bold text-slate-400">{{ $selectedThread->classroom?->code }}</p>
                        </div>

                        <div class="space-y-3 p-4">
                            <section class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-wide text-slate-400">@lang('teacher-discussion::app.class_info')</p>
                                <div class="mt-3 grid grid-cols-2 gap-2">
                                    <div class="rounded-xl bg-white p-3">
                                        <p class="text-[11px] font-bold text-slate-400">@lang('teacher-discussion::app.students')</p>
                                        <p class="mt-1 text-lg font-black text-slate-950">{{ number_format($selectedThread->classroom?->students_count ?? 0) }}</p>
                                    </div>
                                    <div class="rounded-xl bg-white p-3">
                                        <p class="text-[11px] font-bold text-slate-400">@lang('teacher-discussion::app.messages')</p>
                                        <p class="mt-1 text-lg font-black text-slate-950">{{ number_format($messages->count()) }}</p>
                                    </div>
                                </div>
                            </section>

                            <details class="group rounded-2xl bg-slate-50 p-4" open>
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm font-black text-slate-900">
                                    <span>@lang('teacher-discussion::app.members')</span>
                                    <x-heroicon-o-chevron-down class="h-4 w-4 text-slate-400 transition group-open:rotate-180" />
                                </summary>
                                <div class="mt-3 max-h-64 space-y-2 overflow-y-auto">
                                    @forelse($members as $member)
                                        @php
                                            $memberInitial = mb_strtoupper(mb_substr($member->name ?? $member->email, 0, 1));
                                        @endphp
                                        <div class="flex items-center gap-3 rounded-xl bg-white p-2.5">
                                            <div class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-green-50 text-xs font-black text-green-700">{{ $memberInitial }}</div>
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-sm font-black text-slate-900">{{ $member->name }}</p>
                                                <p class="truncate text-[11px] font-bold text-slate-400">{{ $member->email }}</p>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="rounded-xl bg-white p-3 text-center text-xs font-bold text-slate-400">@lang('teacher-discussion::app.no_members')</p>
                                    @endforelse
                                </div>
                            </details>

                            <details class="group rounded-2xl bg-slate-50 p-4" open>
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm font-black text-slate-900">
                                    <span>@lang('teacher-discussion::app.shared_files')</span>
                                    <x-heroicon-o-chevron-down class="h-4 w-4 text-slate-400 transition group-open:rotate-180" />
                                </summary>
                                <div class="mt-3 grid grid-cols-3 gap-2">
                                    <div class="grid aspect-square place-items-center rounded-xl bg-white text-slate-300">
                                        <x-heroicon-o-photo class="h-6 w-6" />
                                    </div>
                                    <div class="grid aspect-square place-items-center rounded-xl bg-white text-slate-300">
                                        <x-heroicon-o-document class="h-6 w-6" />
                                    </div>
                                    <div class="grid aspect-square place-items-center rounded-xl bg-white text-slate-300">
                                        <x-heroicon-o-folder class="h-6 w-6" />
                                    </div>
                                </div>
                                <p class="mt-3 text-xs font-bold leading-5 text-slate-400">@lang('teacher-discussion::app.no_shared_files')</p>
                            </details>
                        </div>
                    </aside>
                </div>
            @else
                <div class="grid flex-1 place-items-center p-6">
                    <div class="max-w-md rounded-3xl bg-white px-8 py-10 text-center shadow-sm">
                        <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-green-50 text-green-600">
                            <x-heroicon-o-chat-bubble-left-right class="h-8 w-8" />
                        </div>
                        <h2 class="mt-4 text-lg font-black text-slate-950">@lang('teacher-discussion::app.empty_title')</h2>
                        <p class="mt-2 text-sm font-bold leading-6 text-slate-400">@lang('teacher-discussion::app.empty_desc')</p>
                    </div>
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
