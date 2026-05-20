<aside class="space-y-4">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70">
        <div class="flex items-start gap-4 xl:flex-col xl:items-center xl:text-center">
            <div class="relative shrink-0">
                <div class="grid h-24 w-24 place-items-center overflow-hidden rounded-2xl bg-green-100 text-2xl font-black text-green-700 ring-1 ring-green-200" id="av-preview">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                    @else
                        {{ mb_strtoupper(mb_substr($user->name ?? 'M', 0, 1)) }}
                    @endif
                </div>
                <label for="avatar-input" class="absolute -bottom-2 -right-2 grid h-9 w-9 cursor-pointer place-items-center rounded-xl bg-green-600 text-white shadow-lg shadow-green-900/20 transition hover:bg-green-500">
                    <svg class="h-4 w-4 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                </label>
                <input type="file" id="avatar-input" accept="image/*" class="hidden" form="profile-form" name="avatar">
            </div>

            <div class="min-w-0 flex-1">
                <h2 class="truncate text-lg font-black text-slate-950">{{ $user->name }}</h2>
                <p class="mt-1 truncate text-sm font-bold text-slate-500">{{ $user->email }}</p>
                <span class="mt-3 inline-flex rounded-full px-3 py-1 text-xs font-black ring-1 {{ $roleProfile['badge'] }}">
                    {{ $roleProfile['label'] }}
                </span>
            </div>
        </div>

        <div class="mt-5 space-y-3 border-t border-slate-100 pt-5">
            <div class="flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-slate-50 text-slate-500">
                    <svg class="h-5 w-5 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.8 19.8 0 0 1 1.58 3.32 2 2 0 0 1 3.55 1h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11l-.91.91a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
                </span>
                <div class="min-w-0">
                    <div class="text-xs font-black uppercase tracking-wide text-slate-400">@lang('Mindigo-profile::app.phone')</div>
                    <div class="truncate text-sm font-black text-slate-800">{{ $user->phone ?: __('Mindigo-profile::app.none') }}</div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-slate-50 text-slate-500">
                    <svg class="h-5 w-5 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/><path d="M8 2v4M16 2v4M4 10h16"/></svg>
                </span>
                <div class="min-w-0">
                    <div class="text-xs font-black uppercase tracking-wide text-slate-400">@lang('Mindigo-profile::app.joined_at')</div>
                    <div class="truncate text-sm font-black text-slate-800">{{ $user->created_at?->format('d/m/Y') ?: __('Mindigo-profile::app.none') }}</div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-slate-50 text-slate-500">
                    <svg class="h-5 w-5 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                </span>
                <div class="min-w-0">
                    <div class="text-xs font-black uppercase tracking-wide text-slate-400">@lang('Mindigo-profile::app.email_status')</div>
                    <div class="truncate text-sm font-black text-slate-800">
                        {{ $user->email_verified_at ? __('Mindigo-profile::app.verified') : __('Mindigo-profile::app.not_verified') }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</aside>
