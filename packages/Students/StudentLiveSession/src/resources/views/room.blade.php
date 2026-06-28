@extends('Mindigo-dashboard::layouts')
@section('title', $session->title . ' — ' . __('student-live-session::app.room_title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="flex h-screen flex-col bg-slate-900">

    {{-- Top bar --}}
    <header class="flex items-center justify-between gap-4 border-b border-slate-800 bg-slate-900 px-5 py-3">
        <div class="flex items-center gap-3 min-w-0">
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-red-600/20 text-red-400">
                <x-heroicon-o-video-camera class="h-5 w-5" />
            </span>
            <div class="min-w-0">
                <p class="truncate text-sm font-black text-white">{{ $session->title }}</p>
                <p class="truncate text-xs font-semibold text-slate-400">
                    {{ $session->classroom->name ?? '' }}
                    @if($session->teacher) · {{ __('student-live-session::app.teacher') }}: {{ $session->teacher->name }} @endif
                </p>
            </div>
        </div>
        <a href="{{ route('student.live-sessions.index') }}"
           class="inline-flex h-9 items-center gap-1.5 rounded-full border border-slate-700 bg-slate-800 px-4 text-xs font-black text-slate-200 no-underline transition hover:bg-slate-700">
            <x-heroicon-o-arrow-left-on-rectangle class="h-4 w-4" />
            @lang('student-live-session::app.leave_room')
        </a>
    </header>

    {{-- Jitsi container --}}
    <div id="jitsi-root" class="relative flex-1">
        <div id="jitsi-loading" class="absolute inset-0 flex flex-col items-center justify-center gap-3 text-slate-400">
            <span class="h-8 w-8 animate-spin rounded-full border-2 border-slate-600 border-t-green-500"></span>
            <p class="text-sm font-bold">@lang('student-live-session::app.room_loading')</p>
        </div>
    </div>
</div>

<script src="https://meet.jit.si/external_api.js"></script>
<script>
(function () {
    const indexUrl = @json(route('student.live-sessions.index'));

    function launch() {
        if (typeof JitsiMeetExternalAPI === 'undefined') {
            return setTimeout(launch, 1000);
        }

        const root = document.getElementById('jitsi-root');
        const loading = document.getElementById('jitsi-loading');

        const api = new JitsiMeetExternalAPI('meet.jit.si', {
            roomName: @json($session->room_name),
            parentNode: root,
            width: '100%',
            height: '100%',
            userInfo: { displayName: @json(auth()->user()->name) },
            configOverwrite: {
                startWithAudioMuted: true,
                startWithVideoMuted: true,
                prejoinPageEnabled: false,
                disableDeepLinking: true,
            },
            interfaceConfigOverwrite: {
                SHOW_JITSI_WATERMARK: false,
            },
        });

        api.addEventListener('videoConferenceJoined', function () {
            if (loading) loading.remove();
        });

        api.addEventListener('readyToClose', function () {
            window.location = indexUrl;
        });
    }

    launch();
})();
</script>
@endsection
