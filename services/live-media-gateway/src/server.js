'use strict';

const crypto = require('node:crypto');
const http = require('node:http');
const fs = require('node:fs');
const path = require('node:path');
const {spawn} = require('node:child_process');
const mediasoup = require('mediasoup');
const Redis = require('ioredis');
const {WebSocketServer} = require('ws');

const settings = {
    port: Number(process.env.LIVE_MEDIA_GATEWAY_PORT || 8090),
    healthPort: Number(process.env.LIVE_MEDIA_GATEWAY_HEALTH_PORT || 8091),
    secret: process.env.LIVE_MEDIA_GATEWAY_SECRET || '',
    redisUrl: process.env.LIVE_MEDIA_REDIS_URL || 'redis://redis:6379',
    announcedAddress: process.env.LIVE_MEDIA_ANNOUNCED_IP || '127.0.0.1',
    rtcMinPort: Number(process.env.LIVE_MEDIA_RTC_MIN_PORT || 40000),
    rtcMaxPort: Number(process.env.LIVE_MEDIA_RTC_MAX_PORT || 40100),
    recordingDir: process.env.LIVE_MEDIA_RECORDING_DIR || '/recordings',
    recordingMinPort: Number(process.env.LIVE_MEDIA_RECORDING_MIN_PORT || 41000),
};

if (!settings.secret || settings.secret.length < 32) throw new Error('LIVE_MEDIA_GATEWAY_SECRET must contain at least 32 characters.');

const redis = new Redis(settings.redisUrl, {lazyConnect: true, maxRetriesPerRequest: 1});
const subscriber = new Redis(settings.redisUrl, {lazyConnect: true, maxRetriesPerRequest: 1});
const rooms = new Map();
const sockets = new Set();
const recordings = new Map();
let nextRecordingPort = settings.recordingMinPort;
let worker;
let redisHealthy = false;

const codecs = [
    {kind: 'audio', mimeType: 'audio/opus', clockRate: 48000, channels: 2},
    {kind: 'video', mimeType: 'video/VP8', clockRate: 90000, parameters: {'x-google-start-bitrate': 1000}},
    {kind: 'video', mimeType: 'video/H264', clockRate: 90000, parameters: {'packetization-mode': 1, 'profile-level-id': '42e01f', 'level-asymmetry-allowed': 1}},
];

function decode(value) {
    return Buffer.from(value.replace(/-/g, '+').replace(/_/g, '/'), 'base64').toString('utf8');
}

async function authenticate(ticket) {
    const [payload, signature] = String(ticket || '').split('.');
    if (!payload || !signature) throw new Error('invalid_ticket');
    const expected = crypto.createHmac('sha256', settings.secret).update(payload).digest('base64url');
    if (signature.length !== expected.length || !crypto.timingSafeEqual(Buffer.from(signature), Buffer.from(expected))) throw new Error('invalid_ticket');
    const claims = JSON.parse(decode(payload));
    if (claims.aud !== 'mindigo-live-media' || Number(claims.exp) <= Math.floor(Date.now() / 1000)) throw new Error('expired_ticket');
    if (!/^(user|guest):[1-9][0-9]*$/.test(claims.participant_key || '')) throw new Error('invalid_participant');
    if (redisHealthy) {
        const accepted = await redis.set(`live:ticket:${claims.jti}`, '1', 'EX', Math.max(1, claims.exp - Math.floor(Date.now() / 1000)), 'NX');
        if (accepted !== 'OK') throw new Error('replayed_ticket');
    }
    return claims;
}

function roomKey(claims) {
    return `session:${claims.session_id}:breakout:${claims.breakout_room_id || 'main'}`;
}

async function getRoom(key) {
    if (rooms.has(key)) return rooms.get(key);
    const router = await worker.createRouter({mediaCodecs: codecs});
    const room = {key, router, peers: new Map(), sequence: 0};
    rooms.set(key, room);
    return room;
}

function verifyControl(request) {
    const timestamp = String(request.headers['x-mindigo-timestamp'] || '');
    const signature = String(request.headers['x-mindigo-signature'] || '');
    if (!timestamp || Math.abs(Date.now() / 1000 - Number(timestamp)) > 300) return false;
    const expected = crypto.createHmac('sha256', settings.secret).update(timestamp).digest('hex');
    return signature.length === expected.length && crypto.timingSafeEqual(Buffer.from(signature), Buffer.from(expected));
}

function readJson(request) {
    return new Promise((resolve, reject) => {
        const chunks = []; let size = 0;
        request.on('data', chunk => { size += chunk.length; if (size > 64 * 1024) reject(new Error('payload_too_large')); else chunks.push(chunk); });
        request.on('end', () => { try { resolve(JSON.parse(Buffer.concat(chunks).toString() || '{}')); } catch (error) { reject(error); } });
        request.on('error', reject);
    });
}

function codecSdp(consumer, port) {
    const codec = consumer.rtpParameters.codecs[0];
    const encoding = consumer.rtpParameters.encodings[0];
    const payload = codec.payloadType;
    const name = codec.mimeType.split('/')[1];
    const parameters = Object.entries(codec.parameters || {}).map(([key, value]) => `${key}=${value}`).join(';');
    return ['v=0', 'o=- 0 0 IN IP4 127.0.0.1', 's=Mindigo Recording', 'c=IN IP4 127.0.0.1', 't=0 0', `m=${consumer.kind} ${port} RTP/AVP ${payload}`, `a=rtpmap:${payload} ${name}/${codec.clockRate}${codec.channels ? `/${codec.channels}` : ''}`, parameters ? `a=fmtp:${payload} ${parameters}` : null, `a=ssrc:${encoding.ssrc} cname:mindigo`, 'a=recvonly', ''].filter(Boolean).join('\r\n');
}

async function attachRecordingProducer(recording, producer, participantKey) {
    if (recording.tracks.has(producer.id)) return;
    const room = recording.room; const port = nextRecordingPort; nextRecordingPort += 2;
    const transport = await room.router.createPlainTransport({listenInfo: {protocol: 'udp', ip: '127.0.0.1', portRange: {min: settings.rtcMinPort, max: settings.rtcMaxPort}}, rtcpMux: true, comedia: false});
    await transport.connect({ip: '127.0.0.1', port});
    const consumer = await transport.consume({producerId: producer.id, rtpCapabilities: room.router.rtpCapabilities, paused: true});
    const directory = path.join(settings.recordingDir, recording.id); fs.mkdirSync(directory, {recursive: true});
    const sdpPath = path.join(directory, `${producer.id}.sdp`); const outputPath = path.join(directory, `${producer.id}.mkv`);
    fs.writeFileSync(sdpPath, codecSdp(consumer, port));
    const process = spawn('ffmpeg', ['-protocol_whitelist', 'file,udp,rtp', '-fflags', '+genpts', '-i', sdpPath, '-map', '0', '-c', 'copy', '-y', outputPath], {stdio: ['ignore', 'ignore', 'pipe']});
    const track = {producerId: producer.id, participantKey, kind: producer.kind, source: producer.appData.source, transport, consumer, process, outputPath};
    recordings.get(recording.id)?.tracks.set(producer.id, track);
    process.stderr.on('data', data => { track.lastError = data.toString().slice(-500); });
    process.on('exit', code => { track.exitCode = code; });
    await consumer.resume();
}

async function startServerRecording(sessionId, requestedId) {
    const room = rooms.get(`session:${sessionId}:breakout:main`);
    if (!room) throw new Error('room_not_found');
    if ([...recordings.values()].some(item => item.sessionId === String(sessionId))) throw new Error('recording_already_active');
    const id = `server-${requestedId}-${crypto.randomUUID()}`;
    const recording = {id, sessionId: String(sessionId), room, tracks: new Map(), startedAt: Date.now()}; recordings.set(id, recording);
    for (const peer of room.peers.values()) for (const producer of peer.producers.values()) await attachRecordingProducer(recording, producer, peer.key);
    return recording;
}

async function stopServerRecording(recording) {
    for (const track of recording.tracks.values()) { track.consumer.close(); track.transport.close(); track.process.kill('SIGINT'); }
    await Promise.race([
        Promise.all([...recording.tracks.values()].map(track => new Promise(resolve => track.process.exitCode === null ? track.process.once('exit', resolve) : resolve()))),
        new Promise(resolve => setTimeout(resolve, 5000)),
    ]);
    const manifest = {recording_id: recording.id, session_id: recording.sessionId, duration_seconds: Math.max(1, Math.round((Date.now() - recording.startedAt) / 1000)), tracks: [...recording.tracks.values()].filter(track => fs.existsSync(track.outputPath)).map(track => ({kind: track.kind, source: track.source, participant_key: track.participantKey, path: track.outputPath}))};
    const manifestPath = path.join(settings.recordingDir, recording.id, 'manifest.json'); fs.writeFileSync(manifestPath, JSON.stringify(manifest)); recordings.delete(recording.id);
    return {...manifest, source_path: `server-recordings/${recording.id}/manifest.json`};
}

async function emit(room, type, payload, except) {
    const sequence = redisHealthy ? await redis.incr(`live:sequence:${room.key}`) : ++room.sequence;
    const event = {type, payload, sequence, occurred_at: new Date().toISOString()};
    const serialized = JSON.stringify(event);
    if (redisHealthy) {
        const stream = `live:events:${room.key}`;
        await redis.xadd(stream, 'MAXLEN', '~', 2000, '*', 'event', serialized);
        await redis.expire(stream, 3600);
        await redis.expire(`live:sequence:${room.key}`, 3600);
        await redis.publish(`live:room:${room.key}`, serialized);
    } else {
        broadcast(room.key, serialized, except);
    }
}

function broadcast(key, serialized, except) {
    for (const socket of sockets) {
        if (socket !== except && socket.room?.key === key && socket.readyState === socket.OPEN) socket.send(serialized);
    }
}

async function createTransport(room, peer) {
    const transport = await room.router.createWebRtcTransport({
        listenInfos: [
            {protocol: 'udp', ip: '0.0.0.0', announcedAddress: settings.announcedAddress, portRange: {min: settings.rtcMinPort, max: settings.rtcMaxPort}},
            {protocol: 'tcp', ip: '0.0.0.0', announcedAddress: settings.announcedAddress, portRange: {min: settings.rtcMinPort, max: settings.rtcMaxPort}},
        ],
        enableUdp: true,
        enableTcp: true,
        preferUdp: true,
        initialAvailableOutgoingBitrate: 1_000_000,
    });
    peer.transports.set(transport.id, transport);
    transport.on('dtlsstatechange', state => state === 'closed' && transport.close());
    transport.on('routerclose', () => peer.transports.delete(transport.id));
    return transport;
}

function response(socket, requestId, data = {}, error = null) {
    if (socket.readyState === socket.OPEN) socket.send(JSON.stringify({type: 'response', request_id: requestId, data, error}));
}

async function closePeer(socket) {
    const peer = socket.peer;
    const room = socket.room;
    if (!peer || !room) return;
    peer.transports.forEach(transport => transport.close());
    room.peers.delete(peer.key);
    await emit(room, 'participant_left', {participant_key: peer.key}, socket);
    if (room.peers.size === 0) {
        room.router.close();
        rooms.delete(room.key);
    }
}

async function handle(socket, message) {
    const requestId = message.request_id;
    if (message.type === 'authenticate') {
        if (socket.peer) throw new Error('already_authenticated');
        const claims = await authenticate(message.ticket);
        const room = await getRoom(roomKey(claims));
        const replaced = room.peers.get(claims.participant_key);
        if (replaced?.socket && replaced.socket !== socket) replaced.socket.close(4001, 'connection_replaced');
        const peer = {key: claims.participant_key, name: claims.display_name, role: claims.role, socket, transports: new Map(), producers: new Map(), consumers: new Map()};
        socket.claims = claims; socket.room = room; socket.peer = peer;
        room.peers.set(peer.key, peer);
        response(socket, requestId, {
            participant_key: peer.key,
            router_rtp_capabilities: room.router.rtpCapabilities,
            participants: [...room.peers.values()].map(item => ({participant_key: item.key, name: item.name, role: item.role})),
            producers: [...room.peers.values()].flatMap(item => [...item.producers.values()].map(producer => ({id: producer.id, participant_key: item.key, kind: producer.kind, source: producer.appData.source}))),
        });
        await emit(room, 'participant_joined', {participant_key: peer.key, name: peer.name, role: peer.role}, socket);
        return;
    }
    if (!socket.peer) throw new Error('authentication_required');
    const {room, peer} = socket;
    switch (message.type) {
        case 'create_transport': {
            const transport = await createTransport(room, peer);
            response(socket, requestId, {id: transport.id, ice_parameters: transport.iceParameters, ice_candidates: transport.iceCandidates, dtls_parameters: transport.dtlsParameters, sctp_parameters: transport.sctpParameters});
            break;
        }
        case 'connect_transport': {
            const transport = peer.transports.get(message.transport_id);
            if (!transport) throw new Error('transport_not_found');
            await transport.connect({dtlsParameters: message.dtls_parameters});
            response(socket, requestId);
            break;
        }
        case 'produce': {
            const transport = peer.transports.get(message.transport_id);
            if (!transport) throw new Error('transport_not_found');
            const producer = await transport.produce({kind: message.kind, rtpParameters: message.rtp_parameters, appData: {source: message.source || message.kind}});
            peer.producers.set(producer.id, producer);
            producer.on('transportclose', () => peer.producers.delete(producer.id));
            response(socket, requestId, {id: producer.id});
            await emit(room, 'producer_added', {id: producer.id, participant_key: peer.key, kind: producer.kind, source: producer.appData.source}, socket);
            for (const recording of recordings.values()) if (recording.room === room) await attachRecordingProducer(recording, producer, peer.key);
            break;
        }
        case 'close_producer': {
            const producer = peer.producers.get(message.producer_id);
            if (producer) { producer.close(); peer.producers.delete(producer.id); await emit(room, 'producer_closed', {id: producer.id, participant_key: peer.key}, socket); }
            response(socket, requestId);
            break;
        }
        case 'consume': {
            const transport = peer.transports.get(message.transport_id);
            if (!transport || !room.router.canConsume({producerId: message.producer_id, rtpCapabilities: message.rtp_capabilities})) throw new Error('cannot_consume');
            const consumer = await transport.consume({producerId: message.producer_id, rtpCapabilities: message.rtp_capabilities, paused: true});
            peer.consumers.set(consumer.id, consumer);
            consumer.on('transportclose', () => peer.consumers.delete(consumer.id));
            consumer.on('producerclose', () => { peer.consumers.delete(consumer.id); response(socket, null, {consumer_id: consumer.id}, 'producer_closed'); });
            response(socket, requestId, {id: consumer.id, producer_id: consumer.producerId, kind: consumer.kind, rtp_parameters: consumer.rtpParameters});
            break;
        }
        case 'resume_consumer': {
            const consumer = peer.consumers.get(message.consumer_id);
            if (!consumer) throw new Error('consumer_not_found');
            await consumer.resume(); response(socket, requestId); break;
        }
        case 'heartbeat': response(socket, requestId, {server_time: Date.now()}); break;
        case 'resume': {
            if (!redisHealthy) { response(socket, requestId, {events: [], resync_required: true}); break; }
            const entries = await redis.xrange(`live:events:${room.key}`, '-', '+', 'COUNT', 2000);
            const events = entries.map(([, fields]) => JSON.parse(fields[fields.indexOf('event') + 1]))
                .filter(event => Number(event.sequence) > Number(message.after_sequence || 0));
            response(socket, requestId, {events, resync_required: events.length >= 2000});
            break;
        }
        default: throw new Error('unsupported_message');
    }
}

async function boot() {
    worker = await mediasoup.createWorker({rtcMinPort: settings.rtcMinPort, rtcMaxPort: settings.rtcMaxPort, logLevel: process.env.NODE_ENV === 'production' ? 'warn' : 'error'});
    worker.on('died', () => process.exit(1));
    try {
        await Promise.all([redis.connect(), subscriber.connect()]);
        redisHealthy = true;
        await subscriber.psubscribe('live:room:*');
        subscriber.on('pmessage', (_pattern, channel, serialized) => broadcast(channel.slice('live:room:'.length), serialized));
    } catch (error) {
        console.warn('Redis unavailable; gateway is running in single-node mode:', error.message);
    }

    const server = http.createServer();
    const wss = new WebSocketServer({server, maxPayload: 128 * 1024, perMessageDeflate: false});
    wss.on('connection', socket => {
        sockets.add(socket);
        socket.isAlive = true;
        socket.on('pong', () => { socket.isAlive = true; });
        socket.on('message', async raw => {
            let message = {};
            try { message = JSON.parse(raw.toString()); await handle(socket, message); }
            catch (error) { response(socket, message.request_id, {}, error.message); }
        });
        socket.on('close', () => { sockets.delete(socket); closePeer(socket).catch(console.error); });
    });
    setInterval(() => sockets.forEach(socket => { if (!socket.isAlive) return socket.terminate(); socket.isAlive = false; socket.ping(); }), 15_000).unref();
    server.listen(settings.port, '0.0.0.0');

    http.createServer(async (request, response) => {
        try {
            if (request.url === '/health' && request.method === 'GET') {
                response.writeHead(worker.closed ? 503 : 200, {'content-type': 'application/json'});
                response.end(JSON.stringify({status: worker.closed ? 'down' : 'ok', redis: redisHealthy, rooms: rooms.size, connections: sockets.size, recordings: recordings.size})); return;
            }
            if (!verifyControl(request)) { response.writeHead(401).end(); return; }
            if (request.url === '/recordings/start' && request.method === 'POST') {
                const body = await readJson(request); const recording = await startServerRecording(body.session_id, body.recording_id);
                response.writeHead(201, {'content-type': 'application/json'}); response.end(JSON.stringify({recording_id: recording.id})); return;
            }
            const stop = request.url.match(/^\/recordings\/([^/]+)\/stop$/);
            if (stop && request.method === 'POST') {
                const recording = recordings.get(decodeURIComponent(stop[1])); if (!recording) throw new Error('recording_not_found');
                response.writeHead(200, {'content-type': 'application/json'}); response.end(JSON.stringify(await stopServerRecording(recording))); return;
            }
            response.writeHead(404).end();
        } catch (error) { response.writeHead(422, {'content-type': 'application/json'}); response.end(JSON.stringify({error: error.message})); }
    }).listen(settings.healthPort, '0.0.0.0');
}

boot().catch(error => { console.error(error); process.exit(1); });
