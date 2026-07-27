<template>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Notatki ({{ count }} | Przypięte: {{ countPinned }})</span>
        </div>
        <div class="card-body">
            <div class="d-flex gap-2 mb-3">
                <input v-model="search" type="text" class="form-control" placeholder="Filtruj po tytule..." />
                <button class="btn btn-primary text-nowrap" @click="openForm(null)">+ Dodaj</button>
            </div>

            <NoteForm v-if="showForm" :note="editNote" @saved="onSaved" @cancel="closeForm" />

            <div v-if="isLoading">
                <div v-for="n in 4" :key="n" class="skeleton-line mb-2"></div>
            </div>

            <div v-else-if="filteredList.length === 0" class="text-muted text-center py-4">
                Brak notatek do wyświetlenia.
            </div>

            <table v-else class="table align-middle">
                <thead>
                    <tr>
                        <th>Tytuł</th>
                        <th>Treść</th>
                        <th class="text-center">Przypięta</th>
                        <th class="text-end">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in filteredList" :key="item.id">
                        <td>{{ item.title }}</td>
                        <td class="text-truncate" style="max-width: 300px">{{ item.content }}</td>
                        <td class="text-center">
                            <button
                                class="btn btn-sm"
                                :class="item.is_pinned ? 'btn-warning' : 'btn-outline-secondary'"
                                title="Przełącz przypięcie"
                                @click="togglePin(item)"
                            >
                                <i :class="item.is_pinned ? 'bi bi-pin-fill' : 'bi bi-pin'"></i>
                            </button>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1" @click="openForm(item)">Edytuj</button>
                            <button class="btn btn-sm btn-outline-danger" @click="deleteNote(item.id)">Usuń</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <nav v-if="!isLoading && lastPage > 1" aria-label="Paginacja notatek">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item" :class="{ disabled: currentPage === 1 }">
                        <button class="page-link" @click="goToPage(currentPage - 1)">&laquo; Poprzednia</button>
                    </li>
                    <li v-for="p in lastPage" :key="p" class="page-item" :class="{ active: p === currentPage }">
                        <button class="page-link" @click="goToPage(p)">{{ p }}</button>
                    </li>
                    <li class="page-item" :class="{ disabled: currentPage === lastPage }">
                        <button class="page-link" @click="goToPage(currentPage + 1)">Następna &raquo;</button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import NoteForm from './NoteForm.vue';

export default {
    name: 'NoteManager',
    components: { NoteForm },
    data() {
        return {
            isLoading: false,
            note_list: [],
            search: '',
            showForm: false,
            editNote: null,
            count: 0,
            countPinned: 0,
            currentPage: 1,
            lastPage: 1,
            perPage: 15,
            pollTimer: null,
        };
    },
    mounted() {
        this.isLoading = true;
        this.getNewList();

        // Automatyczne odświeżanie listy co 3 minuty.
        this.pollTimer = setInterval(() => this.getNewList(), 3 * 60 * 1000);
    },
    beforeUnmount() {
        clearInterval(this.pollTimer);
    },
    computed: {
        filteredList() {
            const term = this.search.trim().toLowerCase();

            if (!term) {
                return this.note_list;
            }

            // Filtrowanie działa tylko na aktualnie wczytanej stronie —
            // bez dodatkowego zapytania do API (zgodnie z wymaganiem).
            return this.note_list.filter((note) => note.title.toLowerCase().includes(term));
        },
    },
    methods: {
        getNewList(page = this.currentPage) {
            return axios
                .get('/api/notes', { params: { page, per_page: this.perPage } })
                .then(({ data }) => {
                    this.note_list = data.data;
                    this.count = data.meta?.total ?? this.note_list.length;
                    this.countPinned = this.note_list.filter((n) => n.is_pinned).length;
                    this.currentPage = data.meta?.current_page ?? 1;
                    this.lastPage = data.meta?.last_page ?? 1;
                })
                .catch((error) => {
                    console.error('Nie udało się pobrać notatek:', error);
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        goToPage(page) {
            if (page < 1 || page > this.lastPage || page === this.currentPage) {
                return;
            }

            this.isLoading = true;
            this.getNewList(page);
        },
        togglePin(item) {
            const previous = item.is_pinned;
            item.is_pinned = !item.is_pinned;
            this.countPinned += item.is_pinned ? 1 : -1;

            axios.put(`/api/notes/${item.id}`, { is_pinned: item.is_pinned }).catch((error) => {
                item.is_pinned = previous;
                this.countPinned += previous ? 1 : -1;
                console.error('Nie udało się zmienić stanu przypięcia:', error);
            });
        },
        deleteNote(id) {
            if (!confirm('Na pewno usunąć tę notatkę?')) {
                return;
            }

            axios
                .delete(`/api/notes/${id}`)
                .then(() => {
                    const isLastItemOnPage = this.note_list.length === 1;
                    const targetPage = isLastItemOnPage && this.currentPage > 1
                        ? this.currentPage - 1
                        : this.currentPage;

                    this.getNewList(targetPage);
                })
                .catch((error) => console.error('Nie udało się usunąć notatki:', error));
        },
        openForm(note) {
            this.editNote = note;
            this.showForm = true;
        },
        closeForm() {
            this.showForm = false;
            this.editNote = null;
        },
        onSaved() {
            const wasCreating = this.editNote === null;
            this.closeForm();
            this.getNewList(wasCreating ? 1 : this.currentPage);
        },
    },
};
</script>
