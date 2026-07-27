<template>
    <div class="border rounded p-3 mb-3 bg-white">
        <form @submit.prevent="submit">
            <div class="mb-2">
                <label class="form-label">Tytuł</label>
                <input v-model="form.title" type="text" class="form-control" :class="{ 'is-invalid': errors.title }" />
                <div v-if="errors.title" class="invalid-feedback">{{ errors.title[0] }}</div>
            </div>

            <div class="mb-2">
                <label class="form-label">Treść</label>
                <textarea
                    v-model="form.content"
                    class="form-control"
                    rows="3"
                    :class="{ 'is-invalid': errors.content }"
                ></textarea>
                <div v-if="errors.content" class="invalid-feedback">{{ errors.content[0] }}</div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success" :disabled="submitting">
                    {{ note ? 'Zapisz zmiany' : 'Utwórz notatkę' }}
                </button>
                <button type="button" class="btn btn-outline-secondary" @click="$emit('cancel')">Anuluj</button>
            </div>
        </form>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'NoteForm',
    props: {
        note: { type: Object, default: null },
    },
    emits: ['saved', 'cancel'],
    data() {
        return {
            form: { title: '', content: '' },
            errors: {},
            submitting: false,
        };
    },
    watch: {
        note: {
            immediate: true,
            handler(newNote) {
                this.form = {
                    title: newNote?.title ?? '',
                    content: newNote?.content ?? '',
                };
                this.errors = {};
            },
        },
    },
    methods: {
        submit() {
            this.submitting = true;
            this.errors = {};

            const request = this.note
                ? axios.put(`/api/notes/${this.note.id}`, this.form)
                : axios.post('/api/notes', this.form);

            request
                .then(() => this.$emit('saved'))
                .catch((error) => {
                    if (error.response?.status === 422) {
                        this.errors = error.response.data.errors ?? {};
                    } else {
                        console.error('Nie udało się zapisać notatki:', error);
                    }
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
    },
};
</script>
