<template>
    <div class="card mx-auto mt-5" style="max-width: 420px">
        <div class="card-body">
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <button
                        type="button"
                        class="nav-link"
                        :class="{ active: mode === 'login' }"
                        @click="switchMode('login')"
                    >
                        Logowanie
                    </button>
                </li>
                <li class="nav-item">
                    <button
                        type="button"
                        class="nav-link"
                        :class="{ active: mode === 'register' }"
                        @click="switchMode('register')"
                    >
                        Rejestracja
                    </button>
                </li>
            </ul>

            <form @submit.prevent="submit">
                <div v-if="mode === 'register'" class="mb-2">
                    <label class="form-label">Imię</label>
                    <input v-model="form.name" class="form-control" :class="{ 'is-invalid': errors.name }" />
                    <div v-if="errors.name" class="invalid-feedback">{{ errors.name[0] }}</div>
                </div>

                <div class="mb-2">
                    <label class="form-label">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="form-control"
                        :class="{ 'is-invalid': errors.email }"
                    />
                    <div v-if="errors.email" class="invalid-feedback">{{ errors.email[0] }}</div>
                </div>

                <div class="mb-2">
                    <label class="form-label">Hasło</label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="form-control"
                        :class="{ 'is-invalid': errors.password }"
                    />
                    <div v-if="errors.password" class="invalid-feedback">{{ errors.password[0] }}</div>
                </div>

                <div v-if="mode === 'register'" class="mb-3">
                    <label class="form-label">Powtórz hasło</label>
                    <input v-model="form.password_confirmation" type="password" class="form-control" />
                </div>

                <button type="submit" class="btn btn-primary w-100" :disabled="submitting">
                    {{ mode === 'login' ? 'Zaloguj się' : 'Zarejestruj się' }}
                </button>
            </form>

            <p class="text-muted small mt-3 mb-0">Konto testowe: test@example.com / password</p>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'AuthForm',
    emits: ['logged-in'],
    data() {
        return {
            mode: 'login',
            form: { name: '', email: '', password: '', password_confirmation: '' },
            errors: {},
            submitting: false,
        };
    },
    methods: {
        switchMode(mode) {
            this.mode = mode;
            this.errors = {};
        },
        submit() {
            this.submitting = true;
            this.errors = {};

            const url = this.mode === 'login' ? '/api/login' : '/api/register';

            axios
                .post(url, this.form)
                .then(({ data }) => this.$emit('logged-in', data.data))
                .catch((error) => {
                    if (error.response?.status === 422) {
                        this.errors = error.response.data.errors ?? {};
                    } else {
                        console.error('Błąd logowania/rejestracji:', error);
                    }
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
    },
};
</script>
