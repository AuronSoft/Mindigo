import {SfuMediaClient} from './sfu-media-client';

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
    let recorder = null;
    let recordingStartedAt = null;
    let recordingId = null;
    let recordingSequence = 0;
    let recordingUploads = Promise.resolve();
    let recordingFrameTimer = null;
    let recordingAudioContext = null;
    let recordingAudioDestination = null;
    const recordedAudioTracks = new Set();
    let lastWhiteboardActionId = 0;
    const whiteboardActions = [];
    let currentBreakoutRoomId;
    let loopIteration = 0;
    let loopRunning = false;
    let loopTimer = null;
    let whiteboardWritePending = false;
    let layoutMode = 'grid';
    const usesSfu = config.topology === 'sfu' && Boolean(config.gatewayTicketUrl);
    let sfu = null;
    const optimisticReactionCounts = new Map();
    try { layoutMode = ['grid', 'speaker', 'sidebar', 'classroom'].includes(localStorage.getItem('mindigo-live-layout')) ? localStorage.getItem('mindigo-live-layout') : 'grid'; } catch {}

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

    const mediaErrorMessage = error => {
        if (!window.isSecureContext || !navigator.mediaDevices) return config.labels.secureContextRequired;
        if (error?.name === 'NotFoundError' || error?.name === 'OverconstrainedError') return config.labels.deviceNotFound;
        if (error?.name === 'NotReadableError' || error?.name === 'AbortError') return config.labels.deviceBusy;
        return config.labels.permissionDenied;
    };

    const systemIcon = name => {
        const paths = {
            microphone: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 016 0v8.25a3 3 0 01-3 3z" />',
            muted: '<path stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75L19.5 12m0 0l2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25M12 18.75a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M9 6.75V4.5a3 3 0 016 0v4.875" />',
            hand: '<path stroke-linecap="round" stroke-linejoin="round" d="M10.05 4.575a1.575 1.575 0 10-3.15 0v3m3.15-3v-1.5a1.575 1.575 0 013.15 0v4.5m-3.15-3v4.5m3.15-4.5v-1.5a1.575 1.575 0 013.15 0v4.5m-3.15-3v3m3.15-1.5v-1.5a1.575 1.575 0 013.15 0v6.75a7.125 7.125 0 01-7.125 7.125h-1.5a7.125 7.125 0 01-7.125-7.125v-1.5a1.575 1.575 0 013.15 0v2.25" />',
            remove: '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />',
        };
        const wrapper = document.createElement('span');
        wrapper.className = 'inline-flex h-4 w-4 items-center justify-center';
        wrapper.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="h-4 w-4">${paths[name] || paths.remove}</svg>`;
        return wrapper;
    };

    const updateGridLayout = () => {
        const grid = root.querySelector('[data-remote-grid]');
        const screen = root.querySelector('[data-screen-preview]');
        const projector = root.querySelector('[data-classroom-projector]');
        const stage = root.querySelector('[data-classroom-stage]');
        const projectionSlot = root.querySelector('[data-classroom-projection-slot]');
        const teacherSlot = root.querySelector('[data-classroom-teacher-slot]');
        const screenActive = !screen.classList.contains('hidden');

        if (layoutMode === 'classroom') {
            const teacherTile = grid.querySelector('[data-participant-role="host"]') || grid.querySelector('[data-self-tile]');
            projectionSlot.append(projector, screen); if (teacherTile) teacherSlot.append(teacherTile);
            if (teacherTile) {
                teacherTile.style.gridColumn = 'auto'; teacherTile.style.gridRow = 'auto'; teacherTile.style.maxWidth = '220px';
                teacherTile.style.maxHeight = '145px'; teacherTile.style.justifySelf = 'center'; teacherTile.style.borderBottom = '';
                teacherTile.style.borderRadius = '1rem';
            }
            stage.classList.remove('hidden'); stage.classList.add('grid');
        } else {
            [...teacherSlot.querySelectorAll('[data-participant-tile]')].forEach(tile => grid.appendChild(tile));
            if (screen.parentElement !== grid) grid.appendChild(screen);
            stage.classList.add('hidden'); stage.classList.remove('grid');
        }

        const participantTiles = [...grid.querySelectorAll('[data-participant-tile]')];
        const count = participantTiles.length;

        projector.classList.toggle('hidden', layoutMode !== 'classroom' || screenActive);
        screen.style.gridColumn = screenActive ? '1 / -1' : 'auto';
        screen.style.maxHeight = screenActive ? (layoutMode === 'classroom' ? '58vh' : '55vh') : '';
        screen.style.maxWidth = 'none'; screen.style.justifySelf = 'stretch'; screen.style.order = screenActive ? '-1' : '0';
        screen.style.gridRow = 'auto';

        participantTiles.forEach(tile => {
            tile.style.gridColumn = 'auto'; tile.style.gridRow = 'auto'; tile.style.order = '0'; tile.style.justifySelf = 'center';
            tile.style.maxWidth = 'none'; tile.style.maxHeight = '260px';
            tile.style.borderBottom = ''; tile.style.borderRadius = '';
        });

        if (layoutMode === 'classroom') {
            stage.style.gridColumn = '1 / -1'; stage.style.order = '-2';
            grid.style.gridTemplateColumns = 'repeat(auto-fit, minmax(150px, 200px))';
            participantTiles.forEach(tile => {
                tile.style.maxWidth = '200px'; tile.style.maxHeight = '135px'; tile.style.borderRadius = '2rem 2rem 0.75rem 0.75rem'; tile.style.borderBottom = '12px solid #334155';
            });
        } else if (layoutMode === 'speaker') {
            grid.style.gridTemplateColumns = 'repeat(auto-fit, minmax(180px, 240px))';
            const focusTile = screenActive ? screen : participantTiles[0];
            if (focusTile) {
                focusTile.style.gridColumn = '1 / -1'; focusTile.style.order = '-1'; focusTile.style.maxWidth = '900px';
                focusTile.style.maxHeight = '55vh'; focusTile.style.justifySelf = 'center';
            }
            participantTiles.filter(tile => tile !== focusTile).forEach(tile => { tile.style.maxWidth = '240px'; tile.style.maxHeight = '150px'; });
        } else if (layoutMode === 'sidebar') {
            grid.style.gridTemplateColumns = 'minmax(0, 1fr) 240px';
            const focusTile = screenActive ? screen : participantTiles[0];
            if (focusTile) {
                focusTile.style.gridColumn = '1'; focusTile.style.gridRow = `1 / span ${Math.max(2, count)}`;
                focusTile.style.maxWidth = 'none'; focusTile.style.maxHeight = '65vh'; focusTile.style.justifySelf = 'stretch';
            }
            participantTiles.filter(tile => tile !== focusTile).forEach(tile => {
                tile.style.gridColumn = '2'; tile.style.maxWidth = '240px'; tile.style.maxHeight = '145px';
            });
        } else {
            if (screenActive) grid.style.gridTemplateColumns = 'repeat(auto-fit, minmax(220px, 1fr))';
            else if (count <= 1) grid.style.gridTemplateColumns = 'minmax(260px, 560px)';
            else if (count === 2) grid.style.gridTemplateColumns = 'repeat(2, minmax(240px, 1fr))';
            else grid.style.gridTemplateColumns = 'repeat(auto-fit, minmax(240px, 1fr))';
            participantTiles.forEach(tile => {
                tile.style.maxHeight = screenActive ? '180px' : (count <= 1 ? '315px' : '260px');
                tile.style.maxWidth = screenActive ? '320px' : 'none';
            });
        }

        root.querySelectorAll('[data-layout-mode]').forEach(button => {
            const active = button.dataset.layoutMode === layoutMode; button.setAttribute('aria-pressed', String(active));
            button.classList.toggle('bg-white', active); button.classList.toggle('text-slate-900', active); button.classList.toggle('text-white', !active);
        });
    };

    root.querySelectorAll('[data-layout-mode]').forEach(button => button.addEventListener('click', () => {
        layoutMode = button.dataset.layoutMode;
        try { localStorage.setItem('mindigo-live-layout', layoutMode); } catch {}
        updateGridLayout();
    }));

    const renderLocal = () => {
        const video = root.querySelector('[data-local-video]');
        video.srcObject = localStream;
        const cameraActive = localStream.getVideoTracks().some(track => track.enabled && track.readyState === 'live');
        video.classList.toggle('hidden', !cameraActive);
        root.querySelector('[data-local-placeholder]').classList.toggle('hidden', cameraActive);
        updateGridLayout();
    };

    const updateMediaButton = (button, active) => {
        button.dataset.active = String(active); button.setAttribute('aria-pressed', String(active));
        button.querySelector('[data-media-icon-on]')?.classList.toggle('hidden', !active);
        button.querySelector('[data-media-icon-off]')?.classList.toggle('hidden', active);
        button.classList.toggle('bg-green-600', active); button.classList.toggle('hover:bg-green-500', active);
        button.classList.toggle('bg-slate-700', !active); button.classList.toggle('hover:bg-slate-600', !active);
    };

    const remoteTile = (userId, name = '', role = 'student') => {
        let tile = root.querySelector(`[data-remote-user="${userId}"]`);
        if (tile) { tile.dataset.participantRole = role || tile.dataset.participantRole; return tile; }
        tile = document.createElement('article');
        tile.dataset.remoteUser = userId;
        tile.dataset.participantTile = '';
        tile.dataset.participantRole = role;
        tile.className = 'relative aspect-video w-full overflow-hidden rounded-2xl bg-slate-900';
        tile.innerHTML = `<video autoplay playsinline class="hidden h-full w-full object-cover"></video><div data-remote-placeholder class="grid h-full place-items-center bg-slate-800"><span class="grid h-14 w-14 place-items-center rounded-full bg-green-600 text-lg font-black text-white"></span></div><div data-remote-name class="absolute inset-x-0 bottom-0 bg-slate-950/70 px-3 py-2 text-xs font-bold text-white"></div>`;
        tile.querySelector('[data-remote-name]').textContent = name;
        tile.querySelector('[data-remote-placeholder] span').textContent = name?.slice(0, 1).toUpperCase() || '?';
        root.querySelector('[data-remote-grid]').appendChild(tile);
        updateGridLayout();
        return tile;
    };

    const sendSignal = (recipientKey, type, payload) => request(config.signalUrl, {recipient_key: recipientKey, type, payload});

    const addTrack = (peer, track) => {
        const sender = peer.addTrack(track, localStream);
        if (track.kind === 'video' && Number(config.maxBitrateKbps) > 0) {
            const parameters = sender.getParameters();
            parameters.encodings = parameters.encodings?.length ? parameters.encodings : [{}];
            parameters.encodings[0].maxBitrate = Number(config.maxBitrateKbps) * 1000;
            sender.setParameters(parameters).catch(() => {});
        }
        return sender;
    };

    const peerFor = (userId, name = '', role = 'student') => {
        if (peers.has(userId)) return peers.get(userId);
        const peer = new RTCPeerConnection({iceServers: config.iceServers});
        localStream.getTracks().forEach(track => addTrack(peer, track));
        peer.onicecandidate = event => event.candidate && sendSignal(userId, 'ice', event.candidate.toJSON()).catch(() => {});
        peer.ontrack = event => {
            const stream = remoteStreams.get(userId) || new MediaStream();
            if (!stream.getTracks().some(track => track.id === event.track.id)) stream.addTrack(event.track);
            remoteStreams.set(userId, stream);
            remoteTile(userId, name, role).querySelector('video').srcObject = stream;
            connectRecordingAudio(stream);
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
        updateGridLayout();
    };

    const createOffer = async participant => {
        const peer = peerFor(participant.user_id, participant.name, participant.role);
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
            if (participant.key === config.participantKey) continue;
            const tile = remoteTile(participant.key, participant.name, participant.role);
            const video = tile.querySelector('video'); const cameraActive = Boolean(participant.camera_enabled && video.srcObject);
            video.classList.toggle('hidden', !cameraActive); tile.querySelector('[data-remote-placeholder]').classList.toggle('hidden', cameraActive);
            if (usesSfu) continue;
            if (peers.has(participant.key)) continue;
            if (config.participantKey.localeCompare(participant.key) < 0) await createOffer({...participant, user_id: participant.key});
        }
        updateGridLayout();
        const onlineIds = new Set(participants.map(item => item.key));
        if (!usesSfu) for (const userId of peers.keys()) if (!onlineIds.has(userId)) removePeer(userId);
    };

    const pollSignals = async () => {
        const {signals} = await request(config.inboxUrl, {});
        for (const signal of signals) {
            const peer = peerFor(signal.sender_key || `user:${signal.sender_id}`);
            if (signal.type === 'offer') {
                await peer.setRemoteDescription(signal.payload);
                const answer = await peer.createAnswer();
                await peer.setLocalDescription(answer);
                await sendSignal(signal.sender_key || `user:${signal.sender_id}`, 'answer', answer);
            } else if (signal.type === 'answer') {
                await peer.setRemoteDescription(signal.payload);
            } else if (signal.type === 'ice') {
                await peer.addIceCandidate(signal.payload).catch(() => {});
            }
        }
    };

    const replaceVideoTrack = track => {
        if (usesSfu) {
            if (track) sfu?.publish(track, screenStream ? 'screen' : 'camera').catch(() => setStatus(config.labels.reconnecting, true));
            return;
        }
        for (const peer of peers.values()) {
            const sender = peer.getSenders().find(item => item.track?.kind === 'video');
            if (sender) sender.replaceTrack(track);
            else if (track) addTrack(peer, track);
        }
    };

    root.querySelector('[data-toggle-microphone]')?.addEventListener('click', async event => {
        const button = event.currentTarget; button.disabled = true;
        try {
            if (button.dataset.locked === 'true') {
                setStatus(config.labels.mutedByModerator);
                return;
            }
            let track = localStream.getAudioTracks()[0];
            if (!track) {
                cameraStream = await navigator.mediaDevices.getUserMedia({audio: true, video: false});
                track = cameraStream.getAudioTracks()[0];
                localStream.addTrack(track);
                if (usesSfu) await sfu.publish(track, 'microphone');
                else peers.forEach(peer => addTrack(peer, track));
            } else track.enabled = !track.enabled;
            updateMediaButton(button, track.enabled);
            setStatus(track.enabled ? config.labels.microphoneOn : config.labels.microphoneOff);
        } catch (error) { updateMediaButton(button, false); setStatus(mediaErrorMessage(error), true); }
        finally { button.disabled = false; }
    });

    root.querySelector('[data-toggle-camera]')?.addEventListener('click', async event => {
        const button = event.currentTarget; button.disabled = true;
        try {
            let track = cameraStream?.getVideoTracks()[0];
            if (!track || track.readyState === 'ended') {
                cameraStream = await navigator.mediaDevices.getUserMedia({video: true, audio: false});
                track = cameraStream.getVideoTracks()[0];
                localStream.addTrack(track);
                replaceVideoTrack(track);
            } else track.enabled = !track.enabled;
            updateMediaButton(button, track.enabled); renderLocal();
            setStatus(track.enabled ? config.labels.cameraOn : config.labels.cameraOff);
        } catch (error) { updateMediaButton(button, false); renderLocal(); setStatus(mediaErrorMessage(error), true); }
        finally { button.disabled = false; }
    });

    root.querySelector('[data-toggle-screen]')?.addEventListener('click', async event => {
        const button = event.currentTarget; button.disabled = true;
        const preview = root.querySelector('[data-screen-preview]'); const previewVideo = root.querySelector('[data-screen-preview-video]');
        const stopScreenSharing = () => {
            screenStream?.getTracks().forEach(track => { track.onended = null; track.stop(); });
            screenStream = null; previewVideo.srcObject = null; preview.classList.add('hidden');
            if (usesSfu) {
                sfu?.unpublish('screen');
                const cameraTrack = cameraStream?.getVideoTracks()[0];
                if (cameraTrack) sfu?.publish(cameraTrack, 'camera');
            } else replaceVideoTrack(cameraStream?.getVideoTracks()[0] || null);
            updateMediaButton(button, false);
            updateGridLayout();
            button.querySelector('[data-screen-button-label]').textContent = config.labels.shareScreen;
            setStatus(config.labels.screenShareStopped);
        };
        try {
            if (screenStream) {
                stopScreenSharing();
                return;
            }
            screenStream = await navigator.mediaDevices.getDisplayMedia({video: true});
            const track = screenStream.getVideoTracks()[0];
            previewVideo.srcObject = screenStream; preview.classList.remove('hidden'); replaceVideoTrack(track); updateMediaButton(button, true);
            updateGridLayout();
            button.querySelector('[data-screen-button-label]').textContent = config.labels.stopScreenShare;
            setStatus(config.labels.screenSharing); track.onended = stopScreenSharing;
        } catch (error) { updateMediaButton(button, false); setStatus(mediaErrorMessage(error), true); }
        finally { button.disabled = false; }
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
            const hand = systemIcon('hand'); hand.title = config.labels.handRaised; hand.classList.add('text-amber-500'); row.appendChild(hand);
        }
        if (canModerate && participant.user_id !== config.userId && participant.role === 'student') {
            const controls = document.createElement('div'); controls.className = 'flex gap-1';
            const microphoneAction = participant.force_muted ? ['allow_microphone', 'microphone'] : ['mute', 'muted'];
            for (const [action, icon] of [microphoneAction, ['lower_hand', 'hand'], ['remove', 'remove']]) {
                const button = document.createElement('button'); button.type = 'button'; button.className = 'grid h-8 w-8 place-items-center rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200'; button.appendChild(systemIcon(icon));
                button.addEventListener('click', () => request(config.moderateUrl, {target_user_id: participant.user_id, action}).catch(() => setStatus(config.labels.reconnecting, true)));
                controls.appendChild(button);
            }
            row.appendChild(controls);
        }
        return row;
    };

    const showReaction = event => {
        const sourceIcon = root.querySelector(`[data-reaction="${event.payload?.reaction}"] svg`);
        if (!sourceIcon) return;
        const item = document.createElement('span');
        item.className = 'absolute bottom-20 grid h-10 w-10 place-items-center rounded-full bg-white text-green-600 shadow-lg transition-all duration-[2500ms]';
        item.style.left = `${10 + Math.random() * 80}%`;
        const icon = sourceIcon.cloneNode(true); icon.classList.remove('h-4', 'w-4'); icon.classList.add('h-6', 'w-6'); item.appendChild(icon);
        item.title = event.payload?.name || '';
        root.querySelector('[data-reaction-layer]').appendChild(item);
        requestAnimationFrame(() => { item.style.transform = 'translateY(-240px)'; item.style.opacity = '0'; });
        window.setTimeout(() => item.remove(), 2600);
    };

    const updateHandButton = raised => {
        const button = root.querySelector('[data-toggle-hand]'); if (!button) return;
        button.dataset.active = String(raised); button.setAttribute('aria-pressed', String(raised));
        button.classList.toggle('bg-amber-500', raised); button.classList.toggle('hover:bg-amber-400', raised);
        button.classList.toggle('bg-white/10', !raised); button.classList.toggle('hover:bg-white/20', !raised);
        button.querySelector('[data-hand-label]').textContent = raised ? config.labels.lowerHand : config.labels.raiseHand;
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
            if (event.type === 'reaction') {
                const reaction = event.payload?.reaction; const optimisticCount = optimisticReactionCounts.get(reaction) || 0;
                if (event.actor_id === config.userId && optimisticCount > 0) optimisticReactionCounts.set(reaction, optimisticCount - 1);
                else showReaction(event);
            }
            if (event.type === 'mute') {
                localStream.getAudioTracks().forEach(track => { track.enabled = false; });
                updateMediaButton(root.querySelector('[data-toggle-microphone]'), false);
                setStatus(config.labels.mutedByModerator);
            }
        }
        const list = root.querySelector('[data-panel-content="participants"]');
        list.replaceChildren(...data.participants.map(participant => participantRow(participant, data.can_moderate)));
        updateHandButton(data.self.hand_raised);
        const microphone = root.querySelector('[data-toggle-microphone]');
        microphone.dataset.locked = String(data.self.force_muted);
        microphone.classList.toggle('opacity-50', data.self.force_muted);
        if (data.self.force_muted) {
            localStream.getAudioTracks().forEach(track => { track.enabled = false; });
            updateMediaButton(microphone, false);
        }
    };

    const refreshJoinToken = async () => {
        if (Date.now() - tokenIssuedAt < 8 * 60 * 1000) return;
        const data = await request(config.joinTokenUrl, {});
        config.token = data.token;
        tokenIssuedAt = Date.now();
    };

    const drawWhiteboard = () => {
        const canvas = root.querySelector('[data-whiteboard]'); if (!canvas) return;
        const rect = canvas.getBoundingClientRect();
        if (canvas.width !== Math.round(rect.width * devicePixelRatio) || canvas.height !== Math.round(rect.height * devicePixelRatio)) {
            canvas.width = Math.round(rect.width * devicePixelRatio); canvas.height = Math.round(rect.height * devicePixelRatio);
        }
        const context = canvas.getContext('2d'); context.setTransform(devicePixelRatio, 0, 0, devicePixelRatio, 0, 0); context.clearRect(0, 0, rect.width, rect.height);
        for (const action of whiteboardActions) {
            if (action.type === 'clear') { context.clearRect(0, 0, rect.width, rect.height); continue; }
            const points = action.payload?.points || []; if (points.length < 2) continue;
            context.strokeStyle = action.payload.color; context.lineWidth = action.payload.width; context.lineCap = 'round'; context.lineJoin = 'round'; context.beginPath();
            points.forEach((point, index) => { const x = point.x * rect.width; const y = point.y * rect.height; index ? context.lineTo(x, y) : context.moveTo(x, y); }); context.stroke();
        }
    };

    const drawWhiteboardAction = action => {
        const canvas = root.querySelector('[data-whiteboard]'); if (!canvas) return;
        const rect = canvas.getBoundingClientRect(); const context = canvas.getContext('2d');
        context.setTransform(devicePixelRatio, 0, 0, devicePixelRatio, 0, 0);
        if (action.type === 'clear') { context.clearRect(0, 0, rect.width, rect.height); return; }
        const points = action.payload?.points || []; if (points.length < 2) return;
        context.strokeStyle = action.payload.color; context.lineWidth = action.payload.width; context.lineCap = 'round'; context.lineJoin = 'round'; context.beginPath();
        points.forEach((point, index) => { const x = point.x * rect.width; const y = point.y * rect.height; index ? context.lineTo(x, y) : context.moveTo(x, y); }); context.stroke();
    };

    const renderPoll = poll => {
        const view = root.querySelector('[data-poll-view]'); if (!view) return; view.replaceChildren();
        if (!poll) { const empty = document.createElement('p'); empty.className = 'py-10 text-center text-sm font-semibold text-slate-400'; empty.textContent = config.labels.noPoll; view.appendChild(empty); return; }
        const title = document.createElement('h3'); title.className = 'text-base font-black text-slate-900'; title.textContent = poll.question; view.appendChild(title);
        const list = document.createElement('div'); list.className = 'mt-4 space-y-2';
        poll.options.forEach(option => {
            const button = document.createElement('button'); button.type = 'button'; button.disabled = poll.status !== 'open' || Boolean(poll.voted_option_id);
            button.className = `flex w-full items-center justify-between rounded-xl border px-4 py-3 text-left text-sm font-bold ${poll.voted_option_id === option.id ? 'border-green-500 bg-green-50 text-green-800' : 'border-slate-200 text-slate-700'}`;
            const label = document.createElement('span'); label.textContent = option.label; button.appendChild(label);
            if (option.votes !== null) { const votes = document.createElement('span'); votes.className = 'rounded-full bg-slate-100 px-2 py-1 text-xs'; votes.textContent = option.votes; button.appendChild(votes); }
            button.addEventListener('click', () => request(config.pollVoteUrl.replace('__POLL__', poll.id), {option_id: option.id}).then(pollTeachingTools)); list.appendChild(button);
        }); view.appendChild(list);
        if (config.pollCloseUrl && poll.status === 'open') { const close = document.createElement('button'); close.type = 'button'; close.className = 'mt-3 rounded-xl bg-slate-900 px-4 py-2 text-xs font-black text-white'; close.textContent = config.labels.closePoll; close.addEventListener('click', () => request(config.pollCloseUrl.replace('__POLL__', poll.id), {show_results: true}).then(pollTeachingTools)); view.appendChild(close); }
    };

    const renderResources = resources => {
        const list = root.querySelector('[data-resource-list]'); if (!list) return; list.replaceChildren();
        if (!resources.length) { const empty = document.createElement('p'); empty.className = 'py-10 text-center text-sm font-semibold text-slate-400'; empty.textContent = config.labels.noResources; list.appendChild(empty); return; }
        resources.forEach(resource => { const link = document.createElement('a'); link.href = resource.download_url; link.className = 'flex items-center justify-between rounded-xl border border-slate-200 p-3 text-sm font-bold text-slate-700 no-underline hover:bg-slate-50'; const name = document.createElement('span'); name.className = 'truncate'; name.textContent = resource.name; const size = document.createElement('span'); size.className = 'ml-3 shrink-0 text-xs text-slate-400'; size.textContent = `${Math.ceil(resource.size_bytes / 1024)} KB`; link.append(name, size); list.appendChild(link); });
    };

    const pollTeachingTools = async () => {
        if (!config.teachingToolsSyncUrl) return;
        const data = await request(config.teachingToolsSyncUrl, {after_action_id: lastWhiteboardActionId});
        for (const action of data.actions) {
            whiteboardActions.push(action); lastWhiteboardActionId = Math.max(lastWhiteboardActionId, action.id); drawWhiteboardAction(action);
        }
        renderPoll(data.poll); renderResources(data.resources);
    };

    const renderBreakouts = data => {
        const list = root.querySelector('[data-breakout-list]');
        if (!list) return;
        if (currentBreakoutRoomId !== undefined && currentBreakoutRoomId !== data.current_room_id) {
            [...peers.keys()].forEach(removePeer);
        }
        currentBreakoutRoomId = data.current_room_id;
        const current = data.rooms.find(room => room.id === data.current_room_id);
        root.querySelector('[data-breakout-current]').textContent = current?.name || config.labels.mainRoom;
        list.replaceChildren();
        if (!data.rooms.length) {
            const empty = document.createElement('p'); empty.className = 'py-10 text-center text-sm font-semibold text-slate-400'; empty.textContent = config.labels.noBreakoutRooms; list.appendChild(empty); return;
        }
        data.rooms.forEach(room => {
            const card = document.createElement('article'); card.className = `rounded-2xl border p-4 ${room.id === data.current_room_id ? 'border-green-400 bg-green-50' : 'border-slate-200'}`;
            const header = document.createElement('div'); header.className = 'flex items-center justify-between';
            const name = document.createElement('h3'); name.className = 'text-sm font-black text-slate-900'; name.textContent = room.name;
            const status = document.createElement('span'); status.className = `rounded-full px-2 py-1 text-[10px] font-black ${room.status === 'open' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600'}`; status.textContent = room.status;
            header.append(name, status); card.appendChild(header);
            const members = document.createElement('div'); members.className = 'mt-3 flex flex-wrap gap-1.5';
            room.members.forEach(member => { const badge = document.createElement('span'); badge.className = `rounded-full px-2.5 py-1 text-xs font-bold ${member.joined ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600'}`; badge.textContent = member.name || ''; members.appendChild(badge); });
            card.appendChild(members); list.appendChild(card);
            if (data.can_moderate && room.status === 'open') {
                const visit = document.createElement('button'); visit.type = 'button'; visit.className = 'mt-3 rounded-xl border border-green-200 px-3 py-2 text-xs font-black text-green-700'; visit.textContent = room.id === data.current_room_id ? config.labels.mainRoom : config.labels.visitRoom;
                visit.addEventListener('click', () => request(room.id === data.current_room_id ? config.breakoutMainUrl : config.breakoutVisitUrl.replace('__ROOM__', room.id), {}).then(pollBreakouts)); card.appendChild(visit);
            }
        });
    };

    const pollBreakouts = async () => {
        if (!config.breakoutSyncUrl) return;
        renderBreakouts(await request(config.breakoutSyncUrl, {}));
    };

    root.querySelector('[data-toggle-breakouts]')?.addEventListener('click', () => {
        root.querySelector('[data-breakout-panel]').classList.replace('hidden', 'flex');
        pollBreakouts().catch(() => {});
    });
    root.querySelector('[data-close-breakouts]')?.addEventListener('click', () => root.querySelector('[data-breakout-panel]').classList.replace('flex', 'hidden'));
    root.querySelector('[data-breakout-form]')?.addEventListener('submit', event => {
        event.preventDefault();
        request(config.breakoutCreateUrl, {room_count: Number(root.querySelector('[data-breakout-count]').value), duration_minutes: Number(root.querySelector('[data-breakout-duration]').value), auto_assign: root.querySelector('[data-breakout-auto]').checked}).then(pollBreakouts);
    });
    root.querySelector('[data-open-breakouts]')?.addEventListener('click', () => request(config.breakoutOpenUrl, {}).then(pollBreakouts));
    root.querySelector('[data-close-all-breakouts]')?.addEventListener('click', () => request(config.breakoutCloseUrl, {}).then(pollBreakouts));

    root.querySelector('[data-toggle-teaching-tools]')?.addEventListener('click', () => { root.querySelector('[data-teaching-tools-panel]').classList.replace('hidden', 'flex'); window.setTimeout(() => { drawWhiteboard(); pollTeachingTools().catch(() => {}); }); });
    root.querySelector('[data-close-teaching-tools]')?.addEventListener('click', () => root.querySelector('[data-teaching-tools-panel]').classList.replace('flex', 'hidden'));
    root.querySelectorAll('[data-tool-tab]').forEach(button => button.addEventListener('click', () => {
        root.querySelectorAll('[data-tool-tab]').forEach(tab => {
            const selected = tab === button; tab.setAttribute('aria-selected', String(selected));
            tab.classList.toggle('bg-green-50', selected); tab.classList.toggle('text-green-700', selected);
            tab.classList.toggle('bg-transparent', !selected); tab.classList.toggle('text-slate-500', !selected);
        });
        root.querySelectorAll('[data-tool-content]').forEach(panel => panel.classList.toggle('hidden', panel.dataset.toolContent !== button.dataset.toolTab));
        if (button.dataset.toolTab === 'whiteboard') window.setTimeout(drawWhiteboard);
    }));
    const board = root.querySelector('[data-whiteboard]'); let currentStroke = null; let lastDrawnPoint = null;
    const boardPoint = event => {
        const rect = board.getBoundingClientRect();
        return {x: Math.max(0, Math.min(1, (event.clientX - rect.left) / rect.width)), y: Math.max(0, Math.min(1, (event.clientY - rect.top) / rect.height))};
    };
    const drawBoardSegment = (from, to, stroke) => {
        const rect = board.getBoundingClientRect(); const context = board.getContext('2d');
        context.setTransform(devicePixelRatio, 0, 0, devicePixelRatio, 0, 0);
        context.strokeStyle = stroke.color; context.lineWidth = stroke.width; context.lineCap = 'round'; context.lineJoin = 'round';
        context.beginPath(); context.moveTo(from.x * rect.width, from.y * rect.height); context.lineTo(to.x * rect.width, to.y * rect.height); context.stroke();
    };
    board?.addEventListener('pointerdown', event => {
        board.setPointerCapture(event.pointerId); const point = boardPoint(event);
        currentStroke = {color: root.querySelector('[data-board-color]').value, width: Number(root.querySelector('[data-board-width]').value), points: [point]}; lastDrawnPoint = point;
    });
    board?.addEventListener('pointermove', event => {
        if (!currentStroke) return;
        for (const sample of event.getCoalescedEvents?.() || [event]) {
            const point = boardPoint(sample); const distance = Math.hypot(point.x - lastDrawnPoint.x, point.y - lastDrawnPoint.y);
            if (distance < 0.0008) continue;
            currentStroke.points.push(point); drawBoardSegment(lastDrawnPoint, point, currentStroke); lastDrawnPoint = point;
        }
    });
    const finishStroke = async () => {
        if (!currentStroke) return; const stroke = currentStroke; currentStroke = null; lastDrawnPoint = null;
        if (stroke.points.length < 2) return;
        if (stroke.points.length > 500) {
            const step = Math.ceil(stroke.points.length / 499);
            stroke.points = stroke.points.filter((point, index) => index % step === 0 || index === stroke.points.length - 1);
        }
        whiteboardWritePending = true;
        try { await request(config.whiteboardUrl, {type: 'stroke', payload: stroke}); await pollTeachingTools(); }
        catch { drawWhiteboard(); setStatus(config.labels.reconnecting, true); }
        finally { whiteboardWritePending = false; }
    };
    board?.addEventListener('pointerup', finishStroke); board?.addEventListener('pointercancel', finishStroke);
    root.querySelector('[data-clear-board]')?.addEventListener('click', () => request(config.whiteboardUrl, {type: 'clear'}).then(pollTeachingTools));
    root.querySelector('[data-poll-form]')?.addEventListener('submit', event => { event.preventDefault(); const question = root.querySelector('[data-poll-question]').value; const options = [...root.querySelectorAll('[data-poll-option]')].map(input => input.value.trim()); request(config.pollCreateUrl, {question, options}).then(() => { event.target.reset(); return pollTeachingTools(); }); });
    root.querySelector('[data-resource-form]')?.addEventListener('submit', async event => { event.preventDefault(); const file = root.querySelector('[data-resource-file]').files[0]; if (!file) return; const form = new FormData(); form.append('token', config.token); form.append('file', file); const response = await fetch(config.resourceUploadUrl, {method: 'POST', credentials: 'same-origin', headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': csrf}, body: form}); if (!response.ok) throw new Error('Resource upload failed'); event.target.reset(); await pollTeachingTools(); });

    const checksum = async blob => Array.from(new Uint8Array(await crypto.subtle.digest('SHA-256', await blob.arrayBuffer())))
        .map(byte => byte.toString(16).padStart(2, '0')).join('');

    const uploadRecordingChunk = async blob => {
        if (!blob.size) return;
        const form = new FormData();
        form.append('token', config.token); form.append('sequence', String(recordingSequence++));
        form.append('checksum', await checksum(blob)); form.append('chunk', blob, 'recording.part');
        const response = await fetch(config.recordingChunkUrl.replace('__RECORDING__', recordingId), {
            method: 'POST', credentials: 'same-origin', headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': csrf}, body: form,
        });
        if (!response.ok) throw new Error(`Recording upload failed (${response.status})`);
    };

    const createRecordingStream = () => {
        const canvas = document.createElement('canvas'); canvas.width = 1280; canvas.height = 720;
        const context = canvas.getContext('2d');
        const draw = () => {
            context.fillStyle = '#0f172a'; context.fillRect(0, 0, canvas.width, canvas.height);
            const videos = [...root.querySelectorAll('video')].filter(video => video.srcObject && video.readyState >= 2);
            const columns = Math.max(1, Math.ceil(Math.sqrt(Math.max(1, videos.length))));
            const rows = Math.max(1, Math.ceil(videos.length / columns));
            videos.forEach((video, index) => {
                const width = canvas.width / columns; const height = canvas.height / rows;
                context.drawImage(video, (index % columns) * width, Math.floor(index / columns) * height, width, height);
            });
            recordingFrameTimer = window.setTimeout(draw, 100);
        };
        draw();
        const stream = canvas.captureStream(10);
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
        if (AudioContextClass) {
            const audioContext = new AudioContextClass(); const destination = audioContext.createMediaStreamDestination();
            recordingAudioContext = audioContext; recordingAudioDestination = destination;
            const sources = [localStream, ...remoteStreams.values()].filter(item => item.getAudioTracks().length);
            sources.forEach(connectRecordingAudio);
            destination.stream.getAudioTracks().forEach(track => stream.addTrack(track));
            stream.__audioContext = audioContext;
        }
        return stream;
    };

    function connectRecordingAudio(stream) {
        if (!recordingAudioContext || !recordingAudioDestination) return;
        const tracks = stream.getAudioTracks().filter(track => !recordedAudioTracks.has(track.id));
        if (!tracks.length) return;
        tracks.forEach(track => recordedAudioTracks.add(track.id));
        recordingAudioContext.createMediaStreamSource(new MediaStream(tracks)).connect(recordingAudioDestination);
    }

    const startRecording = async button => {
        const mimeType = ['video/webm;codecs=vp9,opus', 'video/webm;codecs=vp8,opus', 'video/webm']
            .find(type => MediaRecorder.isTypeSupported(type));
        if (!mimeType) throw new Error('MediaRecorder is not supported');
        const response = await request(config.recordingStartUrl, {mime_type: mimeType});
        recordingId = response.recording_id; recordingStartedAt = Date.now(); recordingSequence = 0;
        const stream = createRecordingStream(); recorder = new MediaRecorder(stream, {mimeType});
        recorder.ondataavailable = event => { if (event.data.size) recordingUploads = recordingUploads.then(() => uploadRecordingChunk(event.data)); };
        recorder.start(5000); button.dataset.active = 'true';
        root.querySelector('[data-recording-label]').textContent = config.labels.stopRecording;
        root.querySelector('[data-recording-indicator]').classList.replace('hidden', 'inline-flex');
    };

    const stopRecording = async button => {
        await new Promise(resolve => { recorder.addEventListener('stop', resolve, {once: true}); recorder.stop(); });
        await recordingUploads;
        const duration = Math.max(1, Math.round((Date.now() - recordingStartedAt) / 1000));
        await request(config.recordingFinalizeUrl.replace('__RECORDING__', recordingId), {duration_seconds: duration, expected_chunks: recordingSequence});
        window.clearTimeout(recordingFrameTimer); recorder.stream.getTracks().forEach(track => track.stop()); await recordingAudioContext?.close();
        recordingAudioContext = null; recordingAudioDestination = null; recordedAudioTracks.clear(); recorder = null; recordingId = null;
        button.dataset.active = 'false'; root.querySelector('[data-recording-label]').textContent = config.labels.startRecording;
        root.querySelector('[data-recording-indicator]').classList.replace('inline-flex', 'hidden');
    };

    root.querySelector('[data-toggle-recording]')?.addEventListener('click', async event => {
        event.currentTarget.disabled = true;
        try { if (recorder?.state === 'recording') await stopRecording(event.currentTarget); else await startRecording(event.currentTarget); }
        catch {
            if (recordingId) await request(config.recordingAbortUrl.replace('__RECORDING__', recordingId), {}).catch(() => {});
            recorder?.stream?.getTracks().forEach(track => track.stop()); recorder = null; recordingId = null;
            setStatus(config.labels.recordingFailed, true);
        }
        finally { event.currentTarget.disabled = false; }
    });

    window.setInterval(() => {
        if (!recordingStartedAt || !recorder) return;
        const seconds = Math.floor((Date.now() - recordingStartedAt) / 1000);
        root.querySelector('[data-recording-time]').textContent = `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
    }, 1000);

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
    root.querySelector('[data-toggle-hand]')?.addEventListener('click', async event => {
        const button = event.currentTarget; const wasRaised = button.dataset.active === 'true'; const nextRaised = !wasRaised;
        button.disabled = true; updateHandButton(nextRaised);
        try { await request(config.actionUrl, {action: nextRaised ? 'raise_hand' : 'lower_hand'}); await pollCollaboration(); }
        catch { updateHandButton(wasRaised); setStatus(config.labels.reconnecting, true); }
        finally { button.disabled = false; }
    });
    root.querySelectorAll('[data-reaction]').forEach(button => button.addEventListener('click', async () => {
        const reaction = button.dataset.reaction; button.disabled = true;
        button.classList.add('bg-green-600', 'text-white', 'scale-110');
        showReaction({payload: {reaction}}); optimisticReactionCounts.set(reaction, (optimisticReactionCounts.get(reaction) || 0) + 1);
        try { await request(config.actionUrl, {action: 'reaction', reaction}); setStatus(config.labels.reactionSent); }
        catch {
            optimisticReactionCounts.set(reaction, Math.max(0, (optimisticReactionCounts.get(reaction) || 1) - 1));
            setStatus(config.labels.reconnecting, true);
        }
        finally {
            window.setTimeout(() => { button.classList.remove('bg-green-600', 'text-white', 'scale-110'); button.disabled = false; }, 350);
        }
    }));

    const loop = async () => {
        if (stopped || loopRunning) return;
        loopRunning = true; loopIteration += 1;
        try {
            if (config.joinTokenUrl) await refreshJoinToken();
            if (loopIteration === 1 || loopIteration % 4 === 0) await pollPresence();
            if (!usesSfu) await pollSignals();
            if (!usesSfu) setStatus(config.labels.connected);

            if (config.collaborationSyncUrl && loopIteration % 3 === 0) await pollCollaboration().catch(() => {});
            if (config.breakoutSyncUrl && loopIteration % 3 === 0 && !root.querySelector('[data-breakout-panel]')?.classList.contains('hidden')) await pollBreakouts().catch(() => {});
            if (config.teachingToolsSyncUrl && loopIteration % 2 === 0 && !currentStroke && !whiteboardWritePending && !root.querySelector('[data-teaching-tools-panel]')?.classList.contains('hidden')) await pollTeachingTools().catch(() => {});
        }
        catch (error) {
            if ([403, 404, 409].includes(error.status)) { stopped = true; window.location.assign(config.leaveUrl); return; }
            setStatus(config.labels.reconnecting, true);
        }
        finally {
            loopRunning = false;
            if (!stopped) {
                window.clearTimeout(loopTimer);
                loopTimer = window.setTimeout(loop, document.hidden ? 5000 : 1000);
            }
        }
    };

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && !stopped && !loopRunning) {
            window.clearTimeout(loopTimer); loop();
        }
    });

    window.addEventListener('beforeunload', event => {
        if (recorder?.state === 'recording') { event.preventDefault(); event.returnValue = ''; }
        stopped = true;
        window.clearTimeout(loopTimer);
        if (config.mediaLeaveUrl) fetch(config.mediaLeaveUrl, {method: 'POST', credentials: 'same-origin', keepalive: true, headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf}, body: JSON.stringify({token: config.token})}).catch(() => {});
        screenStream?.getTracks().forEach(track => track.stop()); localStream.getTracks().forEach(track => track.stop()); peers.forEach(peer => peer.close()); sfu?.close();
    });
    const startMedia = async () => {
        if (usesSfu) {
            sfu = new SfuMediaClient({
                ticketProvider: () => request(config.gatewayTicketUrl, {}),
                onParticipant: participant => {
                    if (participant.participant_key !== config.participantKey) remoteTile(participant.participant_key, participant.name || participant.participant_key, participant.role);
                },
                onParticipantLeft: participantKey => removePeer(participantKey),
                onTrack: (producer, track) => {
                    const stream = remoteStreams.get(producer.participant_key) || new MediaStream();
                    stream.addTrack(track); remoteStreams.set(producer.participant_key, stream);
                    const tile = remoteTile(producer.participant_key, producer.participant_key);
                    const video = tile.querySelector('video'); video.srcObject = stream; video.classList.remove('hidden');
                    tile.querySelector('[data-remote-placeholder]').classList.add('hidden');
                    connectRecordingAudio(stream); updateGridLayout();
                },
                onState: state => setStatus(state === 'connected' ? config.labels.connected : config.labels.reconnecting, state !== 'connected'),
            });
            await sfu.connect();
        }
        renderLocal(); loop();
    };
    startMedia().catch(() => { setStatus(config.labels.reconnecting, true); sfu?.reconnect(); loop(); });
}
