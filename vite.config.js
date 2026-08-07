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
                'resources/js/dialog.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // Diikat ke IPv4 loopback, bukan dibiarkan memilih sendiri.
        //
        // Vite bisa mengikat ke [::1], sehingga public/hot berisi
        // http://[::1]:5173. Tata bahasa Content-Security-Policy tidak
        // mengenal literal IPv6 dalam kurung siku, jadi asal itu mustahil
        // diizinkan — dan middleware TambahHeaderKeamanan terpaksa
        // membatalkan seluruh kebijakan agar halaman tidak kehilangan
        // seluruh gayanya. Dengan 127.0.0.1, CSP tetap menyala selama
        // pengembangan.
        host: '127.0.0.1',
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
