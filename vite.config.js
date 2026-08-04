import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // Entry terpisah supaya Chart.js hanya diunduh pada halaman
                // yang benar-benar menampilkan grafik, bukan di setiap
                // halaman admin dan publik.
                'resources/js/grafik.js',
                'resources/js/editor.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
