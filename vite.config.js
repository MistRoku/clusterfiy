import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    build: {
        sourcemap: false,
        chunkSizeWarningLimit: 250,
        rollupOptions: {
            output: {
                manualChunks: {
                    chart: ['chart.js/auto'],
                },
            },
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
