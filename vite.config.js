import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
const host = 'sikendis.test'; // Ganti dengan nama host proyek Anda
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js','resources/js/filament-chart-js-plugins.js','resources/css/filament/admin/theme.css'],
            refresh: true,
        }),
    ],
});
