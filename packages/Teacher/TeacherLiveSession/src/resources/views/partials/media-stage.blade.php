@php
    $labels = [
        'connected' => __('teacher-live-session::app.media_connected'),
        'reconnecting' => __('teacher-live-session::app.media_reconnecting'),
        'permissionDenied' => __('teacher-live-session::app.media_permission_denied'),
        'microphoneOn' => __('teacher-live-session::app.microphone_on'),
        'microphoneOff' => __('teacher-live-session::app.microphone_off'),
        'handRaised' => __('teacher-live-session::app.hand_raised'),
        'mutedByModerator' => __('teacher-live-session::app.muted_by_moderator'),
        'you' => __('teacher-live-session::app.you'),
    ];
    $clientConfig = [...$mediaConfig, 'labels' => $labels];
@endphp
<section data-live-media-room='@json($clientConfig)' class="relative flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-slate-950 shadow-sm">
    <div class="flex items-center justify-between border-b border-white/10 px-4 py-3 text-white">
        <div class="flex items-center gap-2 text-xs font-bold"><span class="h-2 w-2 rounded-full bg-green-400"></span><span data-media-status>@lang('teacher-live-session::app.media_connecting')</span></div>
        <button type="button" data-toggle-collaboration class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-bold"><x-heroicon-o-user-group class="h-4 w-4" /><span data-participant-count>1</span></button>
    </div>
    <div data-remote-grid class="grid min-h-0 flex-1 auto-rows-fr grid-cols-1 gap-3 overflow-y-auto p-3 md:grid-cols-2 xl:grid-cols-3">
        <article class="relative min-h-48 overflow-hidden rounded-2xl bg-slate-900 ring-1 ring-white/10">
            <video data-local-video autoplay muted playsinline class="hidden h-full w-full object-cover"></video>
            <div data-local-placeholder class="grid h-full min-h-48 place-items-center"><span class="grid h-16 w-16 place-items-center rounded-full bg-green-600 text-xl font-black text-white">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span></div>
            <div class="absolute inset-x-0 bottom-0 flex items-center justify-between bg-slate-950/70 px-3 py-2 text-xs font-bold text-white"><span>{{ auth()->user()->name }} (@lang('teacher-live-session::app.you'))</span></div>
        </article>
    </div>
    <div class="flex flex-wrap items-center justify-center gap-2 border-t border-white/10 bg-slate-900 px-4 py-3">
        <button type="button" data-toggle-microphone data-active="false" class="inline-flex h-11 items-center gap-2 rounded-xl bg-white/10 px-4 text-xs font-black text-white hover:bg-white/20"><x-heroicon-o-microphone class="h-5 w-5" />@lang('teacher-live-session::app.microphone')</button>
        <button type="button" data-toggle-camera data-active="false" class="inline-flex h-11 items-center gap-2 rounded-xl bg-white/10 px-4 text-xs font-black text-white hover:bg-white/20"><x-heroicon-o-video-camera class="h-5 w-5" />@lang('teacher-live-session::app.camera')</button>
        <button type="button" data-toggle-screen data-active="false" class="inline-flex h-11 items-center gap-2 rounded-xl bg-white/10 px-4 text-xs font-black text-white hover:bg-white/20"><x-heroicon-o-computer-desktop class="h-5 w-5" />@lang('teacher-live-session::app.share_screen')</button>
        <button type="button" data-toggle-hand data-active="false" class="inline-flex h-11 items-center gap-2 rounded-xl bg-white/10 px-4 text-xs font-black text-white hover:bg-white/20"><x-heroicon-o-hand-raised class="h-5 w-5" />@lang('teacher-live-session::app.raise_hand')</button>
        <div class="flex items-center gap-1 rounded-xl bg-white/10 p-1" data-reactions>
            <button type="button" data-reaction="clap" class="grid h-9 w-9 place-items-center rounded-lg text-lg hover:bg-white/10" title="@lang('teacher-live-session::app.reaction_clap')">👏</button>
            <button type="button" data-reaction="heart" class="grid h-9 w-9 place-items-center rounded-lg text-lg hover:bg-white/10" title="@lang('teacher-live-session::app.reaction_heart')">❤️</button>
            <button type="button" data-reaction="celebrate" class="grid h-9 w-9 place-items-center rounded-lg text-lg hover:bg-white/10" title="@lang('teacher-live-session::app.reaction_celebrate')">🎉</button>
            <button type="button" data-reaction="question" class="grid h-9 w-9 place-items-center rounded-lg text-lg hover:bg-white/10" title="@lang('teacher-live-session::app.reaction_question')">❓</button>
        </div>
    </div>
    <aside data-collaboration-panel class="absolute inset-y-0 right-0 z-20 hidden w-full max-w-sm flex-col border-l border-slate-200 bg-white text-slate-900 shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 p-4"><div><h2 class="text-sm font-black">@lang('teacher-live-session::app.room_collaboration')</h2><p class="text-xs font-semibold text-slate-500">@lang('teacher-live-session::app.room_collaboration_hint')</p></div><button type="button" data-close-collaboration class="grid h-9 w-9 place-items-center rounded-xl text-slate-500 hover:bg-slate-100"><x-heroicon-o-x-mark class="h-5 w-5" /></button></div>
        <div class="grid grid-cols-2 border-b border-slate-100 p-2"><button type="button" data-panel-tab="participants" class="rounded-lg bg-green-50 px-3 py-2 text-xs font-black text-green-700">@lang('teacher-live-session::app.participants')</button><button type="button" data-panel-tab="chat" class="rounded-lg px-3 py-2 text-xs font-black text-slate-500">@lang('teacher-live-session::app.room_chat')</button></div>
        <div data-panel-content="participants" class="min-h-0 flex-1 space-y-2 overflow-y-auto p-3"></div>
        <div data-panel-content="chat" class="hidden min-h-0 flex-1 flex-col"><div data-chat-messages class="min-h-0 flex-1 space-y-3 overflow-y-auto p-4"></div><form data-chat-form class="flex gap-2 border-t border-slate-100 p-3"><input data-chat-input maxlength="2000" required class="h-10 min-w-0 flex-1 rounded-xl border border-slate-200 px-3 text-sm outline-none focus:border-green-400" placeholder="@lang('teacher-live-session::app.chat_placeholder')"><button class="grid h-10 w-10 place-items-center rounded-xl bg-green-600 text-white" title="@lang('teacher-live-session::app.send_message')"><x-heroicon-o-paper-airplane class="h-4 w-4" /></button></form></div>
    </aside>
    <div data-reaction-layer class="pointer-events-none absolute inset-0 z-10 overflow-hidden"></div>
</section>
