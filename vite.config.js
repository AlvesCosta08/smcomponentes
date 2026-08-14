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
    // 🔥 ESSENCIAL: base para URLs relativas
    base: '/build/',
    server: {
        host: 'localhost',
        port: 5173,
        cors: true,
    },
    build: {
        assetsDir: 'build',
        manifest: true,
        rollupOptions: {
            output: {
                assetFileNames: 'assets/[name]-[hash].[ext]',
                chunkFileNames: 'assets/[name]-[hash].js',
                entryFileNames: 'assets/[name]-[hash].js',
            },
        },
        // 🔥 Forçar URLs relativas
        resolve: {
            alias: {
                '~bootstrap-icons': '/node_modules/bootstrap-icons',
            },
        },
    },
    // 🔥 Configurar CSS para usar URLs relativas
    css: {
        preprocessorOptions: {
            scss: {
                additionalData: `$font-url: '/build/assets/';`,
            },
        },
    },
});