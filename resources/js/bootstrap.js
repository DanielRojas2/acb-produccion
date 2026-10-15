import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

let currentHost = window.location.hostname;
if (!currentHost || currentHost === '0.0.0.0') {
    currentHost = '127.0.0.1';
}

const wsHost = currentHost;
const wsPort = import.meta.env.VITE_REVERB_PORT ? parseInt(import.meta.env.VITE_REVERB_PORT, 10) : 6080;
const wsScheme = import.meta.env.VITE_REVERB_SCHEME || 'http';
const wsKey = import.meta.env.VITE_REVERB_APP_KEY || 'rlml9nwlxkswhus5t6ca';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: wsKey,
    wsHost: wsHost,
    wsPort: wsPort,
    wssPort: wsPort,
    forceTLS: wsScheme === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        },
    },
});