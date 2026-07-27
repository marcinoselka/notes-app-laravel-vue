<template>
    <div>
        <nav class="navbar navbar-expand navbar-light bg-white border-bottom px-3 mb-4">
            <span class="navbar-brand mb-0 h1">📝 Notatki</span>

            <div v-if="user" class="ms-auto d-flex align-items-center gap-3">
                <NotificationBell />
                <span class="text-muted small">{{ user.name }}</span>
                <button class="btn btn-sm btn-outline-secondary" @click="logout">Wyloguj</button>
            </div>
        </nav>

        <div class="container" style="max-width: 900px">
            <div v-if="checkingSession" class="text-center text-muted py-5">Ładowanie…</div>
            <AuthForm v-else-if="!user" @logged-in="onLoggedIn" />
            <NoteManager v-else />
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import AuthForm from './components/AuthForm.vue';
import NoteManager from './components/NoteManager.vue';
import NotificationBell from './components/NotificationBell.vue';

export default {
    name: 'App',
    components: { AuthForm, NoteManager, NotificationBell },
    data() {
        return {
            user: null,
            checkingSession: true,
        };
    },
    mounted() {
        axios
            .get('/api/me')
            .then(({ data }) => {
                this.user = data.data;
            })
            .catch(() => {
                this.user = null;
            })
            .finally(() => {
                this.checkingSession = false;
            });
    },
    methods: {
        onLoggedIn(user) {
            this.user = user;
        },
        logout() {
            axios.post('/api/logout').finally(() => {
                this.user = null;
            });
        },
    },
};
</script>
