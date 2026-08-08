{{-- MODAL TẠO NHÓM --}}
<div id="discussion-group-modal" data-discussion-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl">
        <form method="POST" action="{{ route($routes['groups']) }}">
            @csrf
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h3 class="text-base font-black text-slate-950">@lang('teacher-discussion::app.new_group')</h3>
                    <p class="mt-0.5 text-xs font-semibold text-slate-400">@lang('teacher-discussion::app.new_group_desc')</p>
                </div>
                <button type="button" data-discussion-modal-close="discussion-group-modal" class="grid h-9 w-9 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-50 hover:text-slate-600">
                    <x-heroicon-o-x-mark class="h-5 w-5" />
                </button>
            </div>

            <div class="space-y-5 px-6 py-5">
                <div class="text-center">
                    <div id="discussion-group-avatar-preview" class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-green-100 text-2xl font-black text-green-700">G</div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-discussion::app.group_name')</label>
                    <input type="text" name="name" required maxlength="80" placeholder="@lang('teacher-discussion::app.group_name_placeholder')" class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-800 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-discussion::app.group_description')</label>
                    <input type="text" name="description" maxlength="255" placeholder="@lang('teacher-discussion::app.group_description_placeholder')" class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-800 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-discussion::app.group_theme')</label>
                    <div class="flex flex-wrap gap-3">
                        @foreach(['green', 'blue', 'amber', 'red', 'violet', 'slate'] as $color)
                            <label class="cursor-pointer">
                                <input type="radio" name="theme_color" value="{{ $color }}" class="peer sr-only" {{ $color === 'green' ? 'checked' : '' }}>
                                <span class="grid h-10 w-10 place-items-center rounded-full bg-{{ $color }}-100 text-{{ $color }}-700 ring-2 ring-offset-2 ring-transparent transition peer-checked:ring-{{ $color }}-500">
                                    <x-heroicon-o-check class="h-4 w-4 opacity-0 transition peer-checked:opacity-100" />
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <label class="text-xs font-black text-slate-600">@lang('teacher-discussion::app.select_members')</label>
                        <span class="text-[11px] font-bold text-slate-400" id="discussion-group-count">0 @lang('teacher-discussion::app.selected')</span>
                    </div>
                    <div class="mb-2 flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3">
                        <x-heroicon-o-magnifying-glass class="h-4 w-4 shrink-0 text-slate-400" />
                        <input type="text" data-discussion-filter-input="#discussion-group-members" placeholder="@lang('teacher-discussion::app.search_members')" class="min-w-0 flex-1 border-0 bg-transparent text-sm font-semibold text-slate-700 outline-none placeholder:text-slate-400">
                    </div>
                    <div id="discussion-group-members" class="max-h-48 space-y-1 overflow-y-auto rounded-xl border border-slate-100 p-2">
                        @forelse($candidateUsers as $candidate)
                            <label data-discussion-option data-search="{{ mb_strtolower($candidate->name.' '.$candidate->email) }}" class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-1.5 transition hover:bg-slate-50">
                                <input type="checkbox" name="member_ids[]" value="{{ $candidate->id }}" data-discussion-member-check class="h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500">
                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-slate-100 text-xs font-black text-slate-600">{{ mb_strtoupper(mb_substr($candidate->name, 0, 1)) }}</span>
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-black text-slate-800">{{ $candidate->name }}</span>
                                    <span class="block truncate text-[11px] font-bold text-slate-400">{{ $candidate->email }}</span>
                                </span>
                            </label>
                        @empty
                            <p class="rounded-lg bg-slate-50 p-3 text-center text-xs font-bold text-slate-400">@lang('teacher-discussion::app.no_candidates')</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                <button type="button" data-discussion-modal-close="discussion-group-modal" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-600 transition hover:bg-slate-50">@lang('teacher-discussion::app.cancel')</button>
                <button type="submit" class="rounded-xl bg-green-600 px-5 py-2.5 text-xs font-black text-white shadow-sm shadow-green-600/20 transition hover:bg-green-500">@lang('teacher-discussion::app.create_group_btn')</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL CHAT 1-1 --}}
<div id="discussion-direct-modal" data-discussion-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl">
        <form method="POST" action="{{ route($routes['direct']) }}">
            @csrf
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h3 class="text-base font-black text-slate-950">@lang('teacher-discussion::app.new_direct')</h3>
                    <p class="mt-0.5 text-xs font-semibold text-slate-400">@lang('teacher-discussion::app.new_direct_desc')</p>
                </div>
                <button type="button" data-discussion-modal-close="discussion-direct-modal" class="grid h-9 w-9 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-50 hover:text-slate-600">
                    <x-heroicon-o-x-mark class="h-5 w-5" />
                </button>
            </div>

            <div class="px-6 py-5">
                <div class="mb-2 flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3">
                    <x-heroicon-o-magnifying-glass class="h-4 w-4 shrink-0 text-slate-400" />
                    <input type="text" data-discussion-filter-input="#discussion-direct-contacts" placeholder="@lang('teacher-discussion::app.search_contacts')" class="min-w-0 flex-1 border-0 bg-transparent text-sm font-semibold text-slate-700 outline-none placeholder:text-slate-400">
                </div>
                <div id="discussion-direct-contacts" class="max-h-72 space-y-1 overflow-y-auto rounded-xl border border-slate-100 p-2">
                    @forelse($candidateUsers as $candidate)
                        <label data-discussion-option data-search="{{ mb_strtolower($candidate->name.' '.$candidate->email) }}" class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-1.5 transition hover:bg-slate-50">
                            <input type="radio" name="user_id" value="{{ $candidate->id }}" class="h-4 w-4 border-slate-300 text-green-600 focus:ring-green-500">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-slate-100 text-xs font-black text-slate-600">{{ mb_strtoupper(mb_substr($candidate->name, 0, 1)) }}</span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center gap-2">
                                    <span class="truncate text-sm font-black text-slate-800">{{ $candidate->name }}</span>
                                    @if($candidate->role === 'teacher')
                                        <span class="shrink-0 rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-black text-green-700">@lang('teacher-discussion::app.role_teacher')</span>
                                    @elseif($candidate->role === 'admin')
                                        <span class="shrink-0 rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-black text-blue-700">@lang('teacher-discussion::app.role_admin')</span>
                                    @endif
                                </span>
                                <span class="block truncate text-[11px] font-bold text-slate-400">{{ $candidate->email }}</span>
                            </span>
                        </label>
                    @empty
                        <p class="rounded-lg bg-slate-50 p-3 text-center text-xs font-bold text-slate-400">@lang('teacher-discussion::app.no_candidates')</p>
                    @endforelse
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                <button type="button" data-discussion-modal-close="discussion-direct-modal" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-600 transition hover:bg-slate-50">@lang('teacher-discussion::app.cancel')</button>
                <button type="submit" class="rounded-xl bg-green-600 px-5 py-2.5 text-xs font-black text-white shadow-sm shadow-green-600/20 transition hover:bg-green-500">@lang('teacher-discussion::app.start_chat_btn')</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL XEM THÀNH VIÊN --}}
<div id="discussion-view-members-modal" data-discussion-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="text-base font-black text-slate-950">@lang('teacher-discussion::app.members')</h3>
            <button type="button" data-discussion-modal-close="discussion-view-members-modal" class="grid h-9 w-9 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-50 hover:text-slate-600">
                <x-heroicon-o-x-mark class="h-5 w-5" />
            </button>
        </div>
        <div class="max-h-[60vh] space-y-1 overflow-y-auto p-4">
            @forelse($members as $member)
                @php $memberInitial = mb_strtoupper(mb_substr($member->name ?? $member->email, 0, 1)); @endphp
                <div class="flex items-center gap-3 rounded-xl px-2 py-2 transition hover:bg-slate-50">
                    <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-slate-100 text-sm font-black text-slate-600">{{ $memberInitial }}</div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-black text-slate-800">{{ $member->name }}</p>
                        <p class="truncate text-[11px] font-bold text-slate-400">{{ $member->email }}</p>
                    </div>
                </div>
            @empty
                <p class="rounded-xl bg-slate-50 p-3 text-center text-xs font-bold text-slate-400">@lang('teacher-discussion::app.no_members')</p>
            @endforelse
        </div>
    </div>
</div>

{{-- MODAL XEM HÌNH ẢNH --}}
<div id="discussion-view-images-modal" data-discussion-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="text-base font-black text-slate-950">@lang('teacher-discussion::app.shared_files')</h3>
            <button type="button" data-discussion-modal-close="discussion-view-images-modal" class="grid h-9 w-9 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-50 hover:text-slate-600">
                <x-heroicon-o-x-mark class="h-5 w-5" />
            </button>
        </div>
        <div class="max-h-[60vh] overflow-y-auto p-4">
            @if($imageAttachments->isNotEmpty())
                <div class="grid grid-cols-3 gap-2">
                    @foreach($imageAttachments as $attachment)
                        <a href="{{ route($routes['attachment'], $attachment) }}" target="_blank" class="block overflow-hidden rounded-xl bg-slate-100">
                            <img src="{{ route($routes['attachment'], $attachment) }}" alt="{{ $attachment->original_name }}" class="aspect-square w-full object-cover">
                        </a>
                    @endforeach
                </div>
            @else
                <p class="rounded-xl bg-slate-50 p-4 text-center text-xs font-bold text-slate-400">@lang('teacher-discussion::app.no_shared_files')</p>
            @endif
        </div>
    </div>
</div>

{{-- MODAL XEM FILE --}}
<div id="discussion-view-files-modal" data-discussion-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="text-base font-black text-slate-950">@lang('teacher-discussion::app.files')</h3>
            <button type="button" data-discussion-modal-close="discussion-view-files-modal" class="grid h-9 w-9 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-50 hover:text-slate-600">
                <x-heroicon-o-x-mark class="h-5 w-5" />
            </button>
        </div>
        <div class="max-h-[60vh] space-y-1 overflow-y-auto p-4">
            @forelse($fileAttachments as $attachment)
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
    </div>
</div>
