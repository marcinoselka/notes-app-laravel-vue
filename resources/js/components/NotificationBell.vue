<template>
    <div class="position-relative d-inline-block">
        <i
            :class="unreadCount > 0 ? 'bi bi-bell-fill text-warning' : 'bi bi-bell'"
            style="cursor: pointer; font-size: 1.3rem"
            @click="togglePanel"
        ></i>
        <span
            v-if="unreadCount > 0"
            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
        >
            {{ unreadCount > 99 ? '99+' : unreadCount }}
        </span>

        <div v-if="panelOpen" class="notification-panel card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Powiadomienia</span>
                <button class="btn btn-sm btn-link" @click="readAll">Oznacz wszystkie</button>
            </div>
            <div class="card-body p-0">
                <div v-if="isLoading" class="p-3">
                    <div v-for="n in 3" :key="n" class="skeleton-line mb-2"></div>
                </div>

                <div v-else-if="notifications.length === 0" class="text-muted text-center p-3">
                    Brak powiadomień.
                </div>

                <ul v-else class="list-group list-group-flush mb-0">
                    <li
                        v-for="item in notifications"
                        :key="item.id"
                        class="list-group-item notification-item"
                        :class="{ unread: !item.read_at }"
                        style="cursor: pointer"
                        @click="markAsRead(item)"
                    >
                        <div class="fw-semibold">{{ item.title }}</div>
                        <div class="small text-muted">{{ truncate(item.body, 80) }}</div>
                        <div class="small text-muted">{{ timeAgo(item.created_at) }}</div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'NotificationBell',
    data() {
        return {
            isLoading: false,
            panelOpen: false,
            notifications: [],
            pollTimer: null,
        };
    },
    computed: {
        unreadCount() {
            return this.notifications.filter((n) => n.read_at === null).length;
        },
    },
    mounted() {
        this.isLoading = true;
        this.getNewList();

        this.pollTimer = setInterval(() => {
            this.getNewList();
        }, 60000);

        document.addEventListener('click', this.handleOutsideClick);
    },
    beforeUnmount() {
        clearInterval(this.pollTimer);
        document.removeEventListener('click', this.handleOutsideClick);
    },
    methods: {
        getNewList() {
            return axios
                .get('/api/notifications')
                .then(({ data }) => {
                    this.notifications = data.data;
                })
                .catch((error) => {
                    console.error('Nie udało się pobrać powiadomień:', error);
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        togglePanel() {
            this.panelOpen = !this.panelOpen;
        },
        handleOutsideClick(event) {
            if (this.panelOpen && !this.$el.contains(event.target)) {
                this.panelOpen = false;
            }
        },
        markAsRead(item) {
            if (item.read_at) {
                return;
            }

            const previous = item.read_at;
            item.read_at = new Date().toISOString();

            axios.patch(`/api/notifications/${item.id}/read`).catch((error) => {
                item.read_at = previous;
                console.error('Nie udało się oznaczyć powiadomienia jako przeczytane:', error);
            });
        },
        readAll() {
            const previous = this.notifications.map((n) => n.read_at);
            this.notifications.forEach((n) => {
                n.read_at = n.read_at ?? new Date().toISOString();
            });

            axios.patch('/api/notifications/read-all').catch((error) => {
                this.notifications.forEach((n, i) => {
                    n.read_at = previous[i];
                });
                console.error('Nie udało się oznaczyć wszystkich jako przeczytane:', error);
            });
        },
        timeAgo(dateStr) {
            const diffMinutes = Math.floor((Date.now() - new Date(dateStr)) / 60000);

            if (diffMinutes < 1) {
                return 'przed chwilą';
            }
            if (diffMinutes < 60) {
                return `${diffMinutes} minut temu`;
            }

            const diffHours = Math.floor(diffMinutes / 60);
            if (diffHours < 24) {
                return `${diffHours} godz. temu`;
            }

            const diffDays = Math.floor(diffHours / 24);
            return `${diffDays} dni temu`;
        },
        truncate(text, n) {
            if (!text) {
                return '';
            }

            return text.length > n ? `${text.slice(0, n)}…` : text;
        },
    },
};
</script>
