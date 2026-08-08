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

        <section>
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

        <section>
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
