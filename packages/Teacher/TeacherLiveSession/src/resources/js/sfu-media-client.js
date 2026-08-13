import {Device} from 'mediasoup-client';

export class SfuMediaClient {
    constructor({ticketProvider, onParticipant, onParticipantLeft, onTrack, onState}) {
        this.ticketProvider = ticketProvider;
        this.onParticipant = onParticipant;
        this.onParticipantLeft = onParticipantLeft;
        this.onTrack = onTrack;
        this.onState = onState;
        this.pending = new Map();
        this.producers = new Map();
        this.publishedTracks = new Map();
        this.sequence = 0;
        this.retry = 0;
        this.closed = false;
    }

    async connect() {
        window.clearTimeout(this.reconnectTimer);
        this.sendTransport?.close(); this.recvTransport?.close(); this.producers.clear();
        const credentials = await this.ticketProvider();
        this.socket = new WebSocket(credentials.gateway_url);
        await new Promise((resolve, reject) => {
            this.socket.addEventListener('open', resolve, {once: true});
            this.socket.addEventListener('error', reject, {once: true});
        });
        this.socket.addEventListener('message', event => this.handle(JSON.parse(event.data)));
        this.socket.addEventListener('close', () => this.reconnect());
        const state = await this.send('authenticate', {ticket: credentials.ticket});
        this.participantKey = state.participant_key;
        this.device = new Device();
        await this.device.load({routerRtpCapabilities: state.router_rtp_capabilities});
        await this.createTransports();
        for (const [source, track] of this.publishedTracks) {
            if (track.readyState === 'live') await this.produce(track, source);
        }
        state.participants.forEach(participant => this.onParticipant?.(participant));
        for (const producer of state.producers) await this.consume(producer);
        if (this.sequence) {
            const resumed = await this.send('resume', {after_sequence: this.sequence});
            for (const event of resumed.events || []) await this.handleEvent(event);
        }
        this.retry = 0;
        this.onState?.('connected');
    }

    async createTransports() {
        const sending = await this.send('create_transport');
        this.sendTransport = this.device.createSendTransport(this.transportOptions(sending));
        this.sendTransport.on('connect', ({dtlsParameters}, callback, errback) => this.send('connect_transport', {transport_id: this.sendTransport.id, dtls_parameters: dtlsParameters}).then(callback, errback));
        this.sendTransport.on('produce', ({kind, rtpParameters, appData}, callback, errback) => this.send('produce', {transport_id: this.sendTransport.id, kind, rtp_parameters: rtpParameters, source: appData.source}).then(({id}) => callback({id}), errback));

        const receiving = await this.send('create_transport');
        this.recvTransport = this.device.createRecvTransport(this.transportOptions(receiving));
        this.recvTransport.on('connect', ({dtlsParameters}, callback, errback) => this.send('connect_transport', {transport_id: this.recvTransport.id, dtls_parameters: dtlsParameters}).then(callback, errback));
    }

    transportOptions(data) {
        return {id: data.id, iceParameters: data.ice_parameters, iceCandidates: data.ice_candidates, dtlsParameters: data.dtls_parameters, sctpParameters: data.sctp_parameters};
    }

    async publish(track, source = track.kind) {
        this.publishedTracks.set(source, track);
        const current = this.producers.get(source);
        if (current && !current.closed) {
            await current.replaceTrack({track});
            return current;
        }
        return this.produce(track, source);
    }

    async produce(track, source) {
        const producer = await this.sendTransport.produce({track, appData: {source}, stopTracks: false});
        this.producers.set(source, producer);
        producer.on('transportclose', () => this.producers.delete(source));
        producer.on('trackended', () => this.unpublish(source));
        return producer;
    }

    async unpublish(source) {
        this.publishedTracks.delete(source);
        const producer = this.producers.get(source);
        if (!producer) return;
        this.producers.delete(source);
        await this.send('close_producer', {producer_id: producer.id}).catch(() => {});
        producer.close();
    }

    async consume(metadata) {
        if (!this.recvTransport || metadata.participant_key === this.participantKey) return;
        const data = await this.send('consume', {transport_id: this.recvTransport.id, producer_id: metadata.id, rtp_capabilities: this.device.rtpCapabilities});
        const consumer = await this.recvTransport.consume({id: data.id, producerId: data.producer_id, kind: data.kind, rtpParameters: data.rtp_parameters, appData: metadata});
        await this.send('resume_consumer', {consumer_id: consumer.id});
        this.onTrack?.(metadata, consumer.track);
    }

    send(type, payload = {}) {
        const requestId = crypto.randomUUID();
        return new Promise((resolve, reject) => {
            const timer = window.setTimeout(() => { this.pending.delete(requestId); reject(new Error('gateway_timeout')); }, 10_000);
            this.pending.set(requestId, {resolve, reject, timer});
            this.socket.send(JSON.stringify({type, request_id: requestId, ...payload}));
        });
    }

    async handle(message) {
        if (message.type === 'response' && message.request_id) {
            const pending = this.pending.get(message.request_id);
            if (!pending) return;
            window.clearTimeout(pending.timer); this.pending.delete(message.request_id);
            message.error ? pending.reject(new Error(message.error)) : pending.resolve(message.data);
            return;
        }
        await this.handleEvent(message);
    }

    async handleEvent(event) {
        this.sequence = Math.max(this.sequence, Number(event.sequence || 0));
        if (event.type === 'participant_joined') this.onParticipant?.(event.payload);
        if (event.type === 'participant_left') this.onParticipantLeft?.(event.payload.participant_key);
        if (event.type === 'producer_added') await this.consume(event.payload);
    }

    reconnect() {
        if (this.closed || this.reconnectTimer) return;
        this.onState?.('reconnecting');
        const delay = Math.min(10_000, 500 * (2 ** this.retry++)) + Math.random() * 300;
        this.reconnectTimer = window.setTimeout(() => {
            this.reconnectTimer = null;
            this.connect().catch(() => this.reconnect());
        }, delay);
    }

    close() {
        this.closed = true;
        window.clearTimeout(this.reconnectTimer);
        this.pending.forEach(item => { window.clearTimeout(item.timer); item.reject(new Error('gateway_closed')); });
        this.pending.clear(); this.publishedTracks.clear(); this.producers.forEach(producer => producer.close());
        this.sendTransport?.close(); this.recvTransport?.close(); this.socket?.close();
    }
}
