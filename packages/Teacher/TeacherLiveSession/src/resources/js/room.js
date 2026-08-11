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
    let lastMessageId = 0;
    let lastEventId = 0;
    let tokenIssuedAt = Date.now();

    const request = async (url, payload) => {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf},
            body: JSON.stringify({...payload, token: config.token}),
        });
        if (!response.ok) {
            const error = new Error(`Live media request failed (${response.status})`);
            error.status = response.status;
            throw error;
        }
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
            if (event.currentTarget.dataset.locked === 'true') {
                setStatus(config.labels.mutedByModerator);
                return;
            }
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

    const participantRow = (participant, canModerate) => {
        const row = document.createElement('div');
        row.className = 'flex items-center gap-3 rounded-xl border border-slate-100 p-3';
        const avatar = document.createElement('span');
        avatar.className = 'grid h-9 w-9 shrink-0 place-items-center rounded-full bg-green-50 text-xs font-black text-green-700';
        avatar.textContent = participant.name?.slice(0, 1).toUpperCase() || '?';
        const info = document.createElement('div');
        info.className = 'min-w-0 flex-1';
        const name = document.createElement('p');
        name.className = 'truncate text-sm font-black text-slate-900';
        name.textContent = `${participant.name || ''}${participant.user_id === config.userId ? ` (${config.labels.you})` : ''}`;
        const state = document.createElement('p');
        state.className = 'mt-0.5 text-xs font-semibold text-slate-500';
        state.textContent = participant.hand_raised ? config.labels.handRaised : (participant.microphone_enabled ? config.labels.microphoneOn : config.labels.microphoneOff);
        info.append(name, state);
        row.append(avatar, info);
        if (participant.hand_raised) {
            const hand = document.createElement('span'); hand.textContent = '✋'; hand.title = config.labels.handRaised; row.appendChild(hand);
        }
        if (canModerate && participant.user_id !== config.userId && participant.role === 'student') {
            const controls = document.createElement('div'); controls.className = 'flex gap-1';
            const microphoneAction = participant.force_muted ? ['allow_microphone', '🎙️'] : ['mute', '🔇'];
            for (const [action, label] of [microphoneAction, ['lower_hand', '✋'], ['remove', '×']]) {
                const button = document.createElement('button'); button.type = 'button'; button.className = 'grid h-8 w-8 place-items-center rounded-lg bg-slate-100 text-xs hover:bg-slate-200'; button.textContent = label;
                button.addEventListener('click', () => request(config.moderateUrl, {target_user_id: participant.user_id, action}).catch(() => setStatus(config.labels.reconnecting, true)));
                controls.appendChild(button);
            }
            row.appendChild(controls);
        }
        return row;
    };

    const showReaction = event => {
        const emoji = {clap: '👏', heart: '❤️', celebrate: '🎉', question: '❓'}[event.payload?.reaction];
        if (!emoji) return;
        const item = document.createElement('span');
        item.className = 'absolute bottom-20 text-3xl transition-all duration-[2500ms]';
        item.style.left = `${10 + Math.random() * 80}%`;
        item.textContent = emoji;
        item.title = event.payload?.name || '';
        root.querySelector('[data-reaction-layer]').appendChild(item);
        requestAnimationFrame(() => { item.style.transform = 'translateY(-240px)'; item.style.opacity = '0'; });
        window.setTimeout(() => item.remove(), 2600);
    };

    const appendMessage = message => {
        const wrapper = document.createElement('div');
        const meta = document.createElement('p'); meta.className = 'text-xs font-black text-slate-700'; meta.textContent = message.sender_name || '';
        const body = document.createElement('p'); body.className = 'mt-1 rounded-xl bg-slate-50 px-3 py-2 text-sm leading-5 text-slate-700'; body.textContent = message.body;
        wrapper.append(meta, body); root.querySelector('[data-chat-messages]').appendChild(wrapper);
    };

    const pollCollaboration = async () => {
        const data = await request(config.collaborationSyncUrl, {after_message_id: lastMessageId, after_event_id: lastEventId});
        for (const message of data.messages) { appendMessage(message); lastMessageId = Math.max(lastMessageId, message.id); }
        for (const event of data.events) {
            lastEventId = Math.max(lastEventId, event.id);
            if (event.type === 'reaction') showReaction(event);
            if (event.type === 'mute') {
                localStream.getAudioTracks().forEach(track => { track.enabled = false; });
                root.querySelector('[data-toggle-microphone]').dataset.active = 'false';
                setStatus(config.labels.mutedByModerator);
            }
        }
        const list = root.querySelector('[data-panel-content="participants"]');
        list.replaceChildren(...data.participants.map(participant => participantRow(participant, data.can_moderate)));
        const hand = root.querySelector('[data-toggle-hand]');
        hand.dataset.active = String(data.self.hand_raised);
        hand.classList.toggle('bg-amber-500', data.self.hand_raised);
        const microphone = root.querySelector('[data-toggle-microphone]');
        microphone.dataset.locked = String(data.self.force_muted);
        microphone.classList.toggle('opacity-50', data.self.force_muted);
        if (data.self.force_muted) localStream.getAudioTracks().forEach(track => { track.enabled = false; });
    };

    const refreshJoinToken = async () => {
        if (Date.now() - tokenIssuedAt < 8 * 60 * 1000) return;
        const data = await request(config.joinTokenUrl, {});
        config.token = data.token;
        tokenIssuedAt = Date.now();
    };

    root.querySelector('[data-toggle-collaboration]')?.addEventListener('click', () => root.querySelector('[data-collaboration-panel]').classList.replace('hidden', 'flex'));
    root.querySelector('[data-close-collaboration]')?.addEventListener('click', () => root.querySelector('[data-collaboration-panel]').classList.replace('flex', 'hidden'));
    root.querySelectorAll('[data-panel-tab]').forEach(button => button.addEventListener('click', () => {
        root.querySelectorAll('[data-panel-content]').forEach(panel => panel.classList.toggle('hidden', panel.dataset.panelContent !== button.dataset.panelTab));
        const chat = root.querySelector('[data-panel-content="chat"]'); if (!chat.classList.contains('hidden')) chat.classList.add('flex'); else chat.classList.remove('flex');
    }));
    root.querySelector('[data-chat-form]')?.addEventListener('submit', async event => {
        event.preventDefault(); const input = root.querySelector('[data-chat-input]'); const body = input.value.trim(); if (!body) return;
        await request(config.messageUrl, {body}); input.value = ''; await pollCollaboration();
    });
    root.querySelector('[data-toggle-hand]')?.addEventListener('click', event => request(config.actionUrl, {action: event.currentTarget.dataset.active === 'true' ? 'lower_hand' : 'raise_hand'}).then(pollCollaboration));
    root.querySelectorAll('[data-reaction]').forEach(button => button.addEventListener('click', () => request(config.actionUrl, {action: 'reaction', reaction: button.dataset.reaction})));

    const loop = async () => {
        if (stopped) return;
        try { await refreshJoinToken(); await pollPresence(); await pollSignals(); await pollCollaboration(); setStatus(config.labels.connected); }
        catch (error) {
            if ([403, 404, 409].includes(error.status)) { stopped = true; window.location.assign(config.leaveUrl); return; }
            setStatus(config.labels.reconnecting, true);
        }
        window.setTimeout(loop, 2000);
    };

    window.addEventListener('beforeunload', () => { stopped = true; localStream.getTracks().forEach(track => track.stop()); peers.forEach(peer => peer.close()); });
    renderLocal();
    loop();
}
