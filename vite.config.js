import laravel, { refreshPaths } from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/filament-chart-js-plugins.js',
                'resources/css/filament/admin/theme.css',
            ],
            // Selain hot-reload CSS, Vite juga akan refresh browser
            // ketika file PHP Filament/Livewire berubah.
            refresh: [
                ...refreshPaths,
                'app/Livewire/**',
                'app/Filament/**',
                'app/Providers/Filament/**',
            ],
        }),
    ],

    server: {
        // Dengarkan di semua interface agar bisa diakses dari host Windows
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,

        // HMR: browser harus konek ke 'localhost' (bukan 0.0.0.0)
        hmr: {
            host: 'localhost',
            port: 5173,
        },

        // usePolling WAJIB untuk WSL2 / Docker Desktop bind mount.
        // inotify tidak propagate melewati bind mount Windows/WSL.
        // ignored membatasi pemindaian agar tidak membebani CPU.
        watch: {
            usePolling: true,
            interval: 800, // ms - balance optimal kecepatan HMR & utilisasi CPU rendah
            ignored: [
                '**/vendor/**',
                '**/storage/**',
                '**/.git/**',
                '**/node_modules/**',
                '**/bootstrap/cache/**',
            ],
        },
    },
});
