import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/login.js'],
            refresh: true,
        }),
        vue(),
    ],
    resolve: {
        alias: {
            // notes.blade.php mounts Vue on a plain HTML tag (<note-manager>)
            // written directly in Blade, not inside a .vue SFC — that requires
            // the runtime template compiler, not just the runtime-only build.
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
