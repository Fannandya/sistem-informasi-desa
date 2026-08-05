<?php

/**
 * CMS Teks Tampilan — fase 1: beranda + chrome bersama (footer, menu) +
 * kuota cuplikan beranda. Lihat ANALISIS-CMS.md untuk audit lengkap dan
 * docs/superpowers/specs/2026-08-04-cms-teks-tampilan-beranda-design.md
 * untuk rancangannya. Kunci di luar daftar ini TIDAK dikelola CMS.
 *
 * 'bagian' hanya dipakai untuk mengelompokkan field pada tab "Beranda" di
 * halaman admin — tidak berpengaruh pada penyimpanan atau validasi.
 */

return [

    // ---------------------------------------------------------------
    // BERANDA — resources/views/publik/beranda.blade.php
    // ---------------------------------------------------------------

    'beranda.hero.sapaan' => ['grup' => 'beranda', 'bagian' => 'Hero', 'label' => 'Sapaan (sebelum nama desa)', 'tipe' => 'teks', 'default' => 'Selamat Datang di', 'maks' => 60],
    'beranda.hero.cta_profil' => ['grup' => 'beranda', 'bagian' => 'Hero', 'label' => 'Tombol: kenali desa', 'tipe' => 'teks', 'default' => 'Kenali Desa Kami', 'maks' => 40],
    'beranda.hero.cta_potensi' => ['grup' => 'beranda', 'bagian' => 'Hero', 'label' => 'Tombol: lihat potensi', 'tipe' => 'teks', 'default' => 'Lihat Potensi Desa', 'maks' => 40],

    'beranda.statistik.label_penduduk' => ['grup' => 'beranda', 'bagian' => 'Statistik', 'label' => 'Label: jumlah penduduk', 'tipe' => 'teks', 'default' => 'Jumlah Penduduk', 'maks' => 40],
    'beranda.statistik.satuan_jiwa' => ['grup' => 'beranda', 'bagian' => 'Statistik', 'label' => 'Satuan: jiwa', 'tipe' => 'teks', 'default' => 'jiwa', 'maks' => 20],
    'beranda.statistik.label_kk' => ['grup' => 'beranda', 'bagian' => 'Statistik', 'label' => 'Label: kepala keluarga', 'tipe' => 'teks', 'default' => 'Kepala Keluarga', 'maks' => 40],
    'beranda.statistik.satuan_keluarga' => ['grup' => 'beranda', 'bagian' => 'Statistik', 'label' => 'Satuan: keluarga', 'tipe' => 'teks', 'default' => 'keluarga', 'maks' => 20],
    'beranda.statistik.label_laki' => ['grup' => 'beranda', 'bagian' => 'Statistik', 'label' => 'Label: laki-laki', 'tipe' => 'teks', 'default' => 'Laki-laki', 'maks' => 40],
    'beranda.statistik.label_perempuan' => ['grup' => 'beranda', 'bagian' => 'Statistik', 'label' => 'Label: perempuan', 'tipe' => 'teks', 'default' => 'Perempuan', 'maks' => 40],
    'beranda.statistik.tautan' => ['grup' => 'beranda', 'bagian' => 'Statistik', 'label' => 'Tautan: lihat statistik lengkap', 'tipe' => 'teks', 'default' => 'Lihat Statistik Lengkap →', 'maks' => 60],
    'beranda.statistik.hint_geser' => ['grup' => 'beranda', 'bagian' => 'Statistik', 'label' => 'Hint: geser ke kanan (mobile)', 'tipe' => 'teks', 'default' => 'Geser ke kanan untuk lihat selengkapnya', 'maks' => 80],

    'beranda.sekilas.prefix' => ['grup' => 'beranda', 'bagian' => 'Sekilas Desa', 'label' => 'Judul (sebelum nama desa)', 'tipe' => 'teks', 'default' => 'Sekilas', 'maks' => 40],
    'beranda.sekilas.cta' => ['grup' => 'beranda', 'bagian' => 'Sekilas Desa', 'label' => 'Tombol: lihat profil lengkap', 'tipe' => 'teks', 'default' => 'Lihat Profil Lengkap', 'maks' => 40],
    'beranda.sekilas.judul_misi' => ['grup' => 'beranda', 'bagian' => 'Sekilas Desa', 'label' => 'Judul kotak misi', 'tipe' => 'teks', 'default' => 'Misi Desa', 'maks' => 40],

    'beranda.potensi.judul' => ['grup' => 'beranda', 'bagian' => 'Potensi', 'label' => 'Judul seksi', 'tipe' => 'teks', 'default' => 'Potensi Unggulan', 'maks' => 60],
    'beranda.potensi.subjudul' => ['grup' => 'beranda', 'bagian' => 'Potensi', 'label' => 'Subjudul', 'tipe' => 'teks', 'default' => 'UMKM, hasil bumi, wisata, dan kerajinan desa.', 'maks' => 150],
    'beranda.potensi.tautan' => ['grup' => 'beranda', 'bagian' => 'Potensi', 'label' => 'Tautan: lihat semua potensi', 'tipe' => 'teks', 'default' => 'Lihat Semua Potensi →', 'maks' => 60],

    'beranda.berita.judul' => ['grup' => 'beranda', 'bagian' => 'Berita', 'label' => 'Judul seksi', 'tipe' => 'teks', 'default' => 'Berita Terbaru', 'maks' => 60],
    'beranda.berita.subjudul' => ['grup' => 'beranda', 'bagian' => 'Berita', 'label' => 'Subjudul', 'tipe' => 'teks', 'default' => 'Kabar dan pengumuman dari pemerintah desa.', 'maks' => 150],
    'beranda.berita.tautan' => ['grup' => 'beranda', 'bagian' => 'Berita', 'label' => 'Tautan: lihat berita lebih lengkap', 'tipe' => 'teks', 'default' => 'Lihat Berita Lebih Lengkap →', 'maks' => 60],

    'beranda.galeri.judul' => ['grup' => 'beranda', 'bagian' => 'Galeri', 'label' => 'Judul seksi', 'tipe' => 'teks', 'default' => 'Galeri Kegiatan', 'maks' => 60],
    'beranda.galeri.subjudul' => ['grup' => 'beranda', 'bagian' => 'Galeri', 'label' => 'Subjudul', 'tipe' => 'teks', 'default' => 'Dokumentasi acara dan kegiatan warga.', 'maks' => 150],
    'beranda.galeri.tautan' => ['grup' => 'beranda', 'bagian' => 'Galeri', 'label' => 'Tautan: lihat galeri lengkap', 'tipe' => 'teks', 'default' => 'Lihat Galeri Lengkap →', 'maks' => 60],
    'beranda.galeri.suffix_foto' => ['grup' => 'beranda', 'bagian' => 'Galeri', 'label' => 'Akhiran jumlah foto (mis. "3 foto")', 'tipe' => 'teks', 'default' => 'foto', 'maks' => 20],

    'beranda.fasilitas.judul' => ['grup' => 'beranda', 'bagian' => 'Fasilitas & Prestasi', 'label' => 'Judul seksi fasilitas', 'tipe' => 'teks', 'default' => 'Fasilitas Desa', 'maks' => 60],
    'beranda.fasilitas.label_toggle' => ['grup' => 'beranda', 'bagian' => 'Fasilitas & Prestasi', 'label' => 'Label toggle/expand fasilitas (mobile)', 'tipe' => 'teks', 'default' => 'Fasilitas Umum Desa', 'maks' => 60],
    'beranda.tautan_lihat_semua' => ['grup' => 'beranda', 'bagian' => 'Fasilitas & Prestasi', 'label' => 'Tautan "Lihat Semua" (fasilitas & prestasi)', 'tipe' => 'teks', 'default' => 'Lihat Semua →', 'maks' => 40],
    'beranda.prestasi.judul' => ['grup' => 'beranda', 'bagian' => 'Fasilitas & Prestasi', 'label' => 'Judul seksi prestasi', 'tipe' => 'teks', 'default' => 'Prestasi Desa', 'maks' => 60],
    'beranda.prestasi.label_toggle' => ['grup' => 'beranda', 'bagian' => 'Fasilitas & Prestasi', 'label' => 'Label toggle/expand prestasi (mobile)', 'tipe' => 'teks', 'default' => 'Penghargaan Desa', 'maks' => 60],

    'beranda.pengurus.judul' => ['grup' => 'beranda', 'bagian' => 'Struktur Organisasi', 'label' => 'Judul seksi', 'tipe' => 'teks', 'default' => 'Struktur Organisasi', 'maks' => 60],
    'beranda.pengurus.subjudul' => ['grup' => 'beranda', 'bagian' => 'Struktur Organisasi', 'label' => 'Subjudul', 'tipe' => 'teks', 'default' => 'Aparat desa yang melayani warga.', 'maks' => 150],
    'beranda.pengurus.tautan' => ['grup' => 'beranda', 'bagian' => 'Struktur Organisasi', 'label' => 'Tautan: lihat struktur lengkap', 'tipe' => 'teks', 'default' => 'Lihat Struktur Lengkap →', 'maks' => 60],

    'beranda.kontak.judul' => ['grup' => 'beranda', 'bagian' => 'Kontak', 'label' => 'Judul seksi', 'tipe' => 'teks', 'default' => 'Hubungi Kami', 'maks' => 60],
    'beranda.kontak.kalimat' => ['grup' => 'beranda', 'bagian' => 'Kontak', 'label' => 'Kalimat pembuka', 'tipe' => 'teks', 'default' => 'Kantor {nama} melayani warga pada hari kerja.', 'maks' => 150, 'placeholder_tersedia' => ['{nama}']],
    'beranda.kontak.label_alamat' => ['grup' => 'beranda', 'bagian' => 'Kontak', 'label' => 'Label: alamat', 'tipe' => 'teks', 'default' => 'Alamat', 'maks' => 20],
    'beranda.kontak.label_telepon' => ['grup' => 'beranda', 'bagian' => 'Kontak', 'label' => 'Label: telepon', 'tipe' => 'teks', 'default' => 'Telepon', 'maks' => 20],
    'beranda.kontak.label_email' => ['grup' => 'beranda', 'bagian' => 'Kontak', 'label' => 'Label: email', 'tipe' => 'teks', 'default' => 'Email', 'maks' => 20],

    // ---------------------------------------------------------------
    // FOOTER — resources/views/publik/layouts/app.blade.php
    // ---------------------------------------------------------------

    'footer.kolom_kontak' => ['grup' => 'footer', 'label' => 'Judul kolom "Kontak"', 'tipe' => 'teks', 'default' => 'Kontak', 'maks' => 30],
    'footer.kolom_halaman' => ['grup' => 'footer', 'label' => 'Judul kolom "Halaman"', 'tipe' => 'teks', 'default' => 'Halaman', 'maks' => 30],
    'footer.kolom_tautan_cepat' => ['grup' => 'footer', 'label' => 'Judul kolom "Tautan Cepat" (mobile)', 'tipe' => 'teks', 'default' => 'Tautan Cepat', 'maks' => 30],
    'footer.pengelola' => ['grup' => 'footer', 'label' => 'Baris hak cipta (setelah tahun & nama desa)', 'tipe' => 'teks', 'default' => 'Dikelola oleh Pemerintah Desa.', 'maks' => 80],
    'footer.buka_maps' => ['grup' => 'footer', 'label' => 'Tombol "Buka di Google Maps"', 'tipe' => 'teks', 'default' => 'Buka di Google Maps', 'maks' => 40],

    // ---------------------------------------------------------------
    // MENU — app/Support/MenuPublik.php
    // ---------------------------------------------------------------

    'menu.beranda' => ['grup' => 'menu', 'label' => 'Beranda', 'tipe' => 'teks', 'default' => 'Beranda', 'maks' => 30],
    'menu.profil' => ['grup' => 'menu', 'label' => 'Profil', 'tipe' => 'teks', 'default' => 'Profil', 'maks' => 30],
    'menu.potensi' => ['grup' => 'menu', 'label' => 'Potensi', 'tipe' => 'teks', 'default' => 'Potensi', 'maks' => 30],
    'menu.fasilitas' => ['grup' => 'menu', 'label' => 'Fasilitas', 'tipe' => 'teks', 'default' => 'Fasilitas', 'maks' => 30],
    'menu.prestasi' => ['grup' => 'menu', 'label' => 'Prestasi', 'tipe' => 'teks', 'default' => 'Prestasi', 'maks' => 30],
    'menu.berita' => ['grup' => 'menu', 'label' => 'Berita', 'tipe' => 'teks', 'default' => 'Berita', 'maks' => 30],
    'menu.galeri' => ['grup' => 'menu', 'label' => 'Galeri', 'tipe' => 'teks', 'default' => 'Galeri', 'maks' => 30],
    'menu.struktur' => ['grup' => 'menu', 'label' => 'Struktur Organisasi', 'tipe' => 'teks', 'default' => 'Struktur Organisasi', 'maks' => 30],
    'menu.statistik' => ['grup' => 'menu', 'label' => 'Statistik', 'tipe' => 'teks', 'default' => 'Statistik', 'maks' => 30],

    // ---------------------------------------------------------------
    // KUOTA — app/Http/Controllers/Publik/BerandaController.php +
    // resources/views/publik/beranda.blade.php
    // ---------------------------------------------------------------

    'kuota.beranda.potensi' => ['grup' => 'kuota', 'label' => 'Jumlah potensi di beranda', 'tipe' => 'angka', 'default' => 6, 'min' => 1, 'max' => 12],
    'kuota.beranda.berita' => ['grup' => 'kuota', 'label' => 'Jumlah berita di beranda', 'tipe' => 'angka', 'default' => 4, 'min' => 1, 'max' => 12],
    'kuota.beranda.berita_samping' => ['grup' => 'kuota', 'label' => 'Jumlah berita samping (di kartu berita utama)', 'tipe' => 'angka', 'default' => 3, 'min' => 1, 'max' => 6],
    'kuota.beranda.acara' => ['grup' => 'kuota', 'label' => 'Jumlah acara/galeri di beranda', 'tipe' => 'angka', 'default' => 6, 'min' => 1, 'max' => 12],
    'kuota.beranda.fasilitas' => ['grup' => 'kuota', 'label' => 'Jumlah fasilitas di beranda', 'tipe' => 'angka', 'default' => 4, 'min' => 1, 'max' => 12],
    'kuota.beranda.prestasi' => ['grup' => 'kuota', 'label' => 'Jumlah prestasi di beranda', 'tipe' => 'angka', 'default' => 3, 'min' => 1, 'max' => 12],
    'kuota.beranda.pengurus' => ['grup' => 'kuota', 'label' => 'Jumlah pengurus di beranda', 'tipe' => 'angka', 'default' => 4, 'min' => 1, 'max' => 12],
    'kuota.beranda.limit_sapaan' => ['grup' => 'kuota', 'label' => 'Panjang potongan kalimat hero (karakter)', 'tipe' => 'angka', 'default' => 200, 'min' => 50, 'max' => 500],
    'kuota.beranda.limit_sekilas' => ['grup' => 'kuota', 'label' => 'Panjang potongan "Sekilas Desa" (karakter)', 'tipe' => 'angka', 'default' => 480, 'min' => 100, 'max' => 1000],

];
