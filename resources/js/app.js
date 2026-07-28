import { createApp } from 'vue';
import axios from 'axios';
import NoteManager from './components/NoteManager.vue';
import NotificationBell from './components/NotificationBell.vue';

axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;
// Server derives the CSRF cookie name from APP_KEY (see
// App\Http\Middleware\PreventRequestForgery) so two installs of this app
// running on different local ports never collide. The Blade view renders
// that name into a <meta> tag so axios knows which cookie to read.
axios.defaults.xsrfCookieName =
    document.querySelector('meta[name="xsrf-cookie-name"]')?.content ?? 'XSRF-TOKEN';
axios.defaults.headers.common.Accept = 'application/json';

const app = createApp({});
app.component('note-manager', NoteManager);
app.component('notification-bell', NotificationBell);
app.mount('#app');
