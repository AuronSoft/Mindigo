'use strict';

const crypto = require('node:crypto');
const http = require('node:http');
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
};

if (!settings.secret || settings.secret.length < 32) throw new Error('LIVE_MEDIA_GATEWAY_SECRET must contain at least 32 characters.');

const redis = new Redis(settings.redisUrl, {lazyConnect: true, maxRetriesPerRequest: 1});
const subscriber = new Redis(settings.redisUrl, {lazyConnect: true, maxRetriesPerRequest: 1});
const rooms = new Map();
const sockets = new Set();
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

    http.createServer((_request, response) => {
        response.writeHead(worker.closed ? 503 : 200, {'content-type': 'application/json'});
        response.end(JSON.stringify({status: worker.closed ? 'down' : 'ok', redis: redisHealthy, rooms: rooms.size, connections: sockets.size}));
    }).listen(settings.healthPort, '0.0.0.0');
}

boot().catch(error => { console.error(error); process.exit(1); });
