@php
    $labels = [
        'connected' => __('teacher-live-session::app.media_connected'),
        'reconnecting' => __('teacher-live-session::app.media_reconnecting'),
        'permissionDenied' => __('teacher-live-session::app.media_permission_denied'),
        'microphoneOn' => __('teacher-live-session::app.microphone_on'),
        'microphoneOff' => __('teacher-live-session::app.microphone_off'),
    ];
    $clientConfig = [...$mediaConfig, 'labels' => $labels];
@endphp
<section data-live-media-room='@json($clientConfig)' class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-slate-950 shadow-sm">
    <div class="flex items-center justify-between border-b border-white/10 px-4 py-3 text-white">
        <div class="flex items-center gap-2 text-xs font-bold"><span class="h-2 w-2 rounded-full bg-green-400"></span><span data-media-status>@lang('teacher-live-session::app.media_connecting')</span></div>
        <div class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-bold"><x-heroicon-o-user-group class="h-4 w-4" /><span data-participant-count>1</span></div>
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
    </div>
</section>
