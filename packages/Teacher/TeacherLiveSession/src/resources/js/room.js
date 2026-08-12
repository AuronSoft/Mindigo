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

    const sendSignal = (recipientKey, type, payload) => request(config.signalUrl, {recipient_key: recipientKey, type, payload});

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
            if (participant.key === config.participantKey || peers.has(participant.key)) continue;
            if (config.participantKey.localeCompare(participant.key) < 0) await createOffer({...participant, user_id: participant.key});
        }
        const onlineIds = new Set(participants.map(item => item.key));
        for (const userId of peers.keys()) if (!onlineIds.has(userId)) removePeer(userId);
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
        for (const action of data.actions) { whiteboardActions.push(action); lastWhiteboardActionId = Math.max(lastWhiteboardActionId, action.id); }
        if (data.actions.length) drawWhiteboard(); renderPoll(data.poll); renderResources(data.resources);
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

    root.querySelector('[data-toggle-breakouts]')?.addEventListener('click', () => root.querySelector('[data-breakout-panel]').classList.replace('hidden', 'flex'));
    root.querySelector('[data-close-breakouts]')?.addEventListener('click', () => root.querySelector('[data-breakout-panel]').classList.replace('flex', 'hidden'));
    root.querySelector('[data-breakout-form]')?.addEventListener('submit', event => {
        event.preventDefault();
        request(config.breakoutCreateUrl, {room_count: Number(root.querySelector('[data-breakout-count]').value), duration_minutes: Number(root.querySelector('[data-breakout-duration]').value), auto_assign: root.querySelector('[data-breakout-auto]').checked}).then(pollBreakouts);
    });
    root.querySelector('[data-open-breakouts]')?.addEventListener('click', () => request(config.breakoutOpenUrl, {}).then(pollBreakouts));
    root.querySelector('[data-close-all-breakouts]')?.addEventListener('click', () => request(config.breakoutCloseUrl, {}).then(pollBreakouts));

    root.querySelector('[data-toggle-teaching-tools]')?.addEventListener('click', () => { root.querySelector('[data-teaching-tools-panel]').classList.replace('hidden', 'flex'); window.setTimeout(drawWhiteboard); });
    root.querySelector('[data-close-teaching-tools]')?.addEventListener('click', () => root.querySelector('[data-teaching-tools-panel]').classList.replace('flex', 'hidden'));
    root.querySelectorAll('[data-tool-tab]').forEach(button => button.addEventListener('click', () => {
        root.querySelectorAll('[data-tool-content]').forEach(panel => panel.classList.toggle('hidden', panel.dataset.toolContent !== button.dataset.toolTab)); if (button.dataset.toolTab === 'whiteboard') window.setTimeout(drawWhiteboard);
    }));
    const board = root.querySelector('[data-whiteboard]'); let currentStroke = null;
    board?.addEventListener('pointerdown', event => { board.setPointerCapture(event.pointerId); const rect = board.getBoundingClientRect(); currentStroke = {color: root.querySelector('[data-board-color]').value, width: Number(root.querySelector('[data-board-width]').value), points: [{x: (event.clientX - rect.left) / rect.width, y: (event.clientY - rect.top) / rect.height}]}; });
    board?.addEventListener('pointermove', event => { if (!currentStroke) return; const rect = board.getBoundingClientRect(); currentStroke.points.push({x: Math.max(0, Math.min(1, (event.clientX - rect.left) / rect.width)), y: Math.max(0, Math.min(1, (event.clientY - rect.top) / rect.height))}); whiteboardActions.push({type: 'stroke', payload: currentStroke}); drawWhiteboard(); whiteboardActions.pop(); });
    const finishStroke = async () => { if (!currentStroke) return; const stroke = currentStroke; currentStroke = null; if (stroke.points.length >= 2) await request(config.whiteboardUrl, {type: 'stroke', payload: stroke}); };
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
    root.querySelector('[data-toggle-hand]')?.addEventListener('click', event => request(config.actionUrl, {action: event.currentTarget.dataset.active === 'true' ? 'lower_hand' : 'raise_hand'}).then(pollCollaboration));
    root.querySelectorAll('[data-reaction]').forEach(button => button.addEventListener('click', () => request(config.actionUrl, {action: 'reaction', reaction: button.dataset.reaction})));

    const loop = async () => {
        if (stopped) return;
        try { if (config.joinTokenUrl) await refreshJoinToken(); if (config.breakoutSyncUrl) await pollBreakouts(); await pollPresence(); await pollSignals(); if (config.collaborationSyncUrl) await pollCollaboration(); if (config.teachingToolsSyncUrl) await pollTeachingTools(); setStatus(config.labels.connected); }
        catch (error) {
            if ([403, 404, 409].includes(error.status)) { stopped = true; window.location.assign(config.leaveUrl); return; }
            setStatus(config.labels.reconnecting, true);
        }
        window.setTimeout(loop, 2000);
    };

    window.addEventListener('beforeunload', event => {
        if (recorder?.state === 'recording') { event.preventDefault(); event.returnValue = ''; }
        stopped = true;
        if (config.mediaLeaveUrl) fetch(config.mediaLeaveUrl, {method: 'POST', credentials: 'same-origin', keepalive: true, headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf}, body: JSON.stringify({token: config.token})}).catch(() => {});
        localStream.getTracks().forEach(track => track.stop()); peers.forEach(peer => peer.close());
    });
    renderLocal();
    loop();
}
