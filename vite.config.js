import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        proxy: {
            // Proxy API requests to Laravel backend
            '/attendance': {
                target: 'http://localhost:8000',
                changeOrigin: true,
                secure: false,
            },
            '/reports': {
                target: 'http://localhost:8000',
                changeOrigin: true,
                secure: false,
            },
            '/employee': {
                target: 'http://localhost:8000',
                changeOrigin: true,
                secure: false,
            },
            '/admin': {
                target: 'http://localhost:8000',
                changeOrigin: true,
                secure: false,
            },
            '/leave-management': {
                target: 'http://localhost:8000',
                changeOrigin: true,
                secure: false,
            },
        },
    },
});
