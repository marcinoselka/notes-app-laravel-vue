import { createApp } from 'vue';
import axios from 'axios';
import App from './App.vue';

axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;
axios.defaults.headers.common.Accept = 'application/json';

createApp(App).mount('#app');
