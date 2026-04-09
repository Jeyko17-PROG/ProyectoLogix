import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/sessions-edit.css',
                'resources/css/sessions-index.css',
                'resources/css/sessions-live.css',
                'resources/css/sessions-mobile.css',
                'resources/js/app.js',
                'resources/js/master.js',
            ],
            refresh: true,
        }),
    ],
});
