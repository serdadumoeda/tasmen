import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'node_modules/tom-select/dist/css/tom-select.bootstrap5.min.css',
            ],
            refresh: true,
        }),
    ],
});
