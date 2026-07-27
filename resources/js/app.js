import { createApp } from 'vue';
import axios from 'axios';
import NoteManager from './components/NoteManager.vue';
import NotificationBell from './components/NotificationBell.vue';

axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;
axios.defaults.headers.common.Accept = 'application/json';

const app = createApp({});
app.component('note-manager', NoteManager);
app.component('notification-bell', NotificationBell);
app.mount('#app');
