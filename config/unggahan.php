<?php

/*
|--------------------------------------------------------------------------
| Pengaturan Unggah Gambar — fitur 02
|--------------------------------------------------------------------------
|
| Satu-satunya sumber aturan unggah gambar. Seluruh fitur (pengaturan desa,
| pengurus, potensi, fasilitas, prestasi, berita, galeri) memakai nilai di
| sini lewat LayananUnggahGambar — tidak boleh ada fitur yang menetapkan
| batas ukuran atau folder tujuannya sendiri.
|
*/

return [

    /*
     * Disk tujuan unggahan.
     *
     * `public`  — penyimpanan lokal, dipakai selama pengembangan.
     * `r2`      — Cloudflare R2, target akhir sesuai PRD 7.
     *
     * Berpindah ke R2 dilakukan sepenuhnya lewat .env; tidak ada kode yang
     * perlu diubah.
     */
    'disk' => env('UPLOAD_DISK', 'public'),

    /*
     * Batas berkas masuk.
     *
     * 4 MB dipilih karena `upload_max_filesize` pada shared hosting cPanel
     * umumnya 8–16 MB (PRD 8), dan satu pengiriman galeri bisa memuat
     * beberapa berkas sekaligus.
     */
    'maks_kb' => 4096,
    'maks_berkas_per_kiriman' => 10,

    'ekstensi_diizinkan' => ['jpg', 'jpeg', 'png', 'webp'],
    'mime_diizinkan' => ['image/jpeg', 'image/png', 'image/webp'],

    /*
     * Ukuran keluaran. Gambar yang lebih kecil dari batas ini tidak diperbesar.
     */
    'lebar_maks' => 1600,
    'lebar_thumbnail' => 400,
    'kualitas_webp' => 82,

    /*
     * Subfolder thumbnail di dalam folder gambar utama.
     * `potensi/abc.webp` → `potensi/thumb/abc.webp`
     */
    'folder_thumbnail' => 'thumb',

    /*
     * Folder tujuan per jenis konten. Kunci di sini juga dipakai untuk
     * memilih gambar pengganti bila sebuah data belum punya foto.
     */
    'folder' => [
        'logo' => 'logo',
        'sampul' => 'sampul',
        'pengurus' => 'pengurus',
        'potensi' => 'potensi',
        'fasilitas' => 'fasilitas',
        'prestasi' => 'prestasi',
        'berita' => 'berita',
        'acara' => 'acara',
    ],

    /*
     * Gambar pengganti saat data belum memiliki foto. Berkas SVG agar ringan
     * dan tidak ikut membebani bucket.
     */
    'placeholder' => [
        'bawaan' => 'images/placeholder/umum.svg',
        'pengurus' => 'images/placeholder/pengurus.svg',
        'potensi' => 'images/placeholder/potensi.svg',
        'fasilitas' => 'images/placeholder/fasilitas.svg',
        'prestasi' => 'images/placeholder/prestasi.svg',
        'berita' => 'images/placeholder/berita.svg',
        'acara' => 'images/placeholder/acara.svg',
    ],

];
