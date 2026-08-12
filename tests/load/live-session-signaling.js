import http from 'k6/http';
import {check, sleep} from 'k6';

export const options = {
    scenarios: {
        signaling: {
            executor: 'ramping-vus',
            stages: [
                {duration: '15s', target: Number(__ENV.LIVE_VUS || 10)},
                {duration: '30s', target: Number(__ENV.LIVE_VUS || 10)},
                {duration: '10s', target: 0},
            ],
        },
    },
    thresholds: {
        http_req_failed: ['rate<0.01'],
        http_req_duration: ['p(95)<500'],
    },
};

export default function () {
    const response = http.post(__ENV.LIVE_PRESENCE_URL, JSON.stringify({
        token: __ENV.LIVE_JOIN_TOKEN,
        connection_id: `${__VU}-${__ITER}`,
        microphone_enabled: false,
        camera_enabled: false,
        screen_sharing: false,
    }), {headers: {'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': __ENV.LIVE_CSRF_TOKEN, Cookie: __ENV.LIVE_SESSION_COOKIE}});

    check(response, {'presence accepted': result => result.status === 200});
    sleep(1);
}
