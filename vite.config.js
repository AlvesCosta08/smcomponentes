import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: 'localhost',  // ← ALTERADO: 127.0.0.1 → localhost
        port: 5173,
        https: false,
        cors: true,
        hmr: {
            host: 'localhost',  // ← ALTERADO: 127.0.0.1 → localhost
        },
    },
    preview: {
        host: 'localhost',  // ← ALTERADO: 127.0.0.1 → localhost
        port: 5173,
        https: false,
    },
});