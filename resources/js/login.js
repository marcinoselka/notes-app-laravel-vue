import { createApp, h } from 'vue';
import axios from 'axios';
import AuthForm from './components/AuthForm.vue';

axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;
axios.defaults.xsrfCookieName =
    document.querySelector('meta[name="xsrf-cookie-name"]')?.content ?? 'XSRF-TOKEN';
axios.defaults.headers.common.Accept = 'application/json';

createApp({
    render: () => h(AuthForm, {
        onLoggedIn: () => {
            window.location.href = '/notes';
        },
    }),
}).mount('#auth-app');
