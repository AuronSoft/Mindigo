const root = document.querySelector('[data-live-media-room]');

if (root) {
    const config = JSON.parse(root.dataset.liveMediaRoom);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const peers = new Map();
    const remoteStreams = new Map();
    let localStream = new MediaStream();
    let cameraStream = null;
    let screenStream = null;
    let stopped = false;

    const request = async (url, payload) => {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf},
            body: JSON.stringify({...payload, token: config.token}),
        });
        if (!response.ok) throw new Error(`Live media request failed (${response.status})`);
        return response.status === 204 ? {} : response.json();
    };

    const setStatus = (message, error = false) => {
        const node = root.querySelector('[data-media-status]');
        node.textContent = message;
        node.classList.toggle('text-red-600', error);
    };

    const renderLocal = () => {
        const video = root.querySelector('[data-local-video]');
        video.srcObject = localStream;
        video.classList.toggle('hidden', localStream.getVideoTracks().length === 0);
        root.querySelector('[data-local-placeholder]').classList.toggle('hidden', localStream.getVideoTracks().length > 0);
    };

    const remoteTile = (userId, name = '') => {
        let tile = root.querySelector(`[data-remote-user="${userId}"]`);
        if (tile) return tile;
        tile = document.createElement('article');
        tile.dataset.remoteUser = userId;
        tile.className = 'relative min-h-48 overflow-hidden rounded-2xl bg-slate-900';
        tile.innerHTML = `<video autoplay playsinline class="h-full w-full object-cover"></video><div class="absolute inset-x-0 bottom-0 bg-slate-950/70 px-3 py-2 text-xs font-bold text-white"></div>`;
        tile.querySelector('div').textContent = name;
        root.querySelector('[data-remote-grid]').appendChild(tile);
        return tile;
    };

    const sendSignal = (recipientId, type, payload) => request(config.signalUrl, {recipient_id: recipientId, type, payload});

    const peerFor = (userId, name = '') => {
        if (peers.has(userId)) return peers.get(userId);
        const peer = new RTCPeerConnection({iceServers: config.iceServers});
        localStream.getTracks().forEach(track => peer.addTrack(track, localStream));
        peer.onicecandidate = event => event.candidate && sendSignal(userId, 'ice', event.candidate.toJSON()).catch(() => {});
        peer.ontrack = event => {
            const stream = remoteStreams.get(userId) || new MediaStream();
            if (!stream.getTracks().some(track => track.id === event.track.id)) stream.addTrack(event.track);
            remoteStreams.set(userId, stream);
            remoteTile(userId, name).querySelector('video').srcObject = stream;
        };
        peer.onconnectionstatechange = () => {
            if (['failed', 'closed'].includes(peer.connectionState)) removePeer(userId);
        };
        peers.set(userId, peer);
        return peer;
    };

    const removePeer = userId => {
        peers.get(userId)?.close();
        peers.delete(userId);
        remoteStreams.delete(userId);
        root.querySelector(`[data-remote-user="${userId}"]`)?.remove();
    };

    const createOffer = async participant => {
        const peer = peerFor(participant.user_id, participant.name);
        const offer = await peer.createOffer();
        await peer.setLocalDescription(offer);
        await sendSignal(participant.user_id, 'offer', offer);
    };

    const pollPresence = async () => {
        const state = {
            connection_id: config.connectionId,
            microphone_enabled: localStream.getAudioTracks().some(track => track.enabled),
            camera_enabled: Boolean(cameraStream?.getVideoTracks().some(track => track.enabled)),
            screen_sharing: Boolean(screenStream),
        };
        const {participants} = await request(config.presenceUrl, state);
        root.querySelector('[data-participant-count]').textContent = participants.length;
        for (const participant of participants) {
            if (participant.user_id === config.userId || peers.has(participant.user_id)) continue;
            if (config.userId < participant.user_id) await createOffer(participant);
        }
        const onlineIds = new Set(participants.map(item => item.user_id));
        for (const userId of peers.keys()) if (!onlineIds.has(userId)) removePeer(userId);
    };

    const pollSignals = async () => {
        const {signals} = await request(config.inboxUrl, {});
        for (const signal of signals) {
            const peer = peerFor(signal.sender_id);
            if (signal.type === 'offer') {
                await peer.setRemoteDescription(signal.payload);
                const answer = await peer.createAnswer();
                await peer.setLocalDescription(answer);
                await sendSignal(signal.sender_id, 'answer', answer);
            } else if (signal.type === 'answer') {
                await peer.setRemoteDescription(signal.payload);
            } else if (signal.type === 'ice') {
                await peer.addIceCandidate(signal.payload).catch(() => {});
            }
        }
    };

    const replaceVideoTrack = track => {
        for (const peer of peers.values()) {
            const sender = peer.getSenders().find(item => item.track?.kind === 'video');
            if (sender) sender.replaceTrack(track);
            else if (track) peer.addTrack(track, localStream);
        }
    };

    root.querySelector('[data-toggle-microphone]')?.addEventListener('click', async event => {
        try {
            let track = localStream.getAudioTracks()[0];
            if (!track) {
                cameraStream = await navigator.mediaDevices.getUserMedia({audio: true, video: false});
                track = cameraStream.getAudioTracks()[0];
                localStream.addTrack(track);
                peers.forEach(peer => peer.addTrack(track, localStream));
            } else track.enabled = !track.enabled;
            event.currentTarget.dataset.active = String(track.enabled);
            setStatus(track.enabled ? config.labels.microphoneOn : config.labels.microphoneOff);
        } catch { setStatus(config.labels.permissionDenied, true); }
    });

    root.querySelector('[data-toggle-camera]')?.addEventListener('click', async event => {
        try {
            let track = cameraStream?.getVideoTracks()[0];
            if (!track || track.readyState === 'ended') {
                cameraStream = await navigator.mediaDevices.getUserMedia({video: true, audio: false});
                track = cameraStream.getVideoTracks()[0];
                localStream.addTrack(track);
                replaceVideoTrack(track);
            } else track.enabled = !track.enabled;
            event.currentTarget.dataset.active = String(track.enabled);
            renderLocal();
        } catch { setStatus(config.labels.permissionDenied, true); }
    });

    root.querySelector('[data-toggle-screen]')?.addEventListener('click', async event => {
        try {
            if (screenStream) {
                screenStream.getTracks().forEach(track => track.stop());
                screenStream = null;
                replaceVideoTrack(cameraStream?.getVideoTracks()[0] || null);
                event.currentTarget.dataset.active = 'false';
                return;
            }
            screenStream = await navigator.mediaDevices.getDisplayMedia({video: true});
            const track = screenStream.getVideoTracks()[0];
            replaceVideoTrack(track);
            event.currentTarget.dataset.active = 'true';
            track.onended = () => { screenStream = null; replaceVideoTrack(cameraStream?.getVideoTracks()[0] || null); };
        } catch { setStatus(config.labels.permissionDenied, true); }
    });

    const loop = async () => {
        if (stopped) return;
        try { await pollPresence(); await pollSignals(); setStatus(config.labels.connected); }
        catch (error) { setStatus(config.labels.reconnecting, true); }
        window.setTimeout(loop, 2000);
    };

    window.addEventListener('beforeunload', () => { stopped = true; localStream.getTracks().forEach(track => track.stop()); peers.forEach(peer => peer.close()); });
    renderLocal();
    loop();
}
