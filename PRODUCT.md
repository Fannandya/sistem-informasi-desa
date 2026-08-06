# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

1. **Pengunjung Publik / Warga Desa & Perantau**: Warga desa, perantau, calon wisatawan, atau pihak luar yang membutuhkan informasi resmi profil desa, potensi unggulan (UMKM, hasil bumi, wisata), fasilitas umum, galeri kegiatan, prestasi, berita desa, dan statistik agregat demografi tanpa perlu login.
2. **Admin Desa / Aparat Desa**: Perangkat desa yang mengelola pendataan demografi penduduk, menginput rekap KK per RT, mengelola konten publik, serta mengekspor/mengimpor laporan statistik kependudukan.

## Product Purpose

Aplikasi berbasis web dua sisi (Portal Publik & Panel Admin) yang dirancang untuk:
- Mempromosikan potensi, fasilitas, prestasi, dan kegiatan desa secara digital dan transparan.
- Mempermudah pencatatan, pemantauan demografi warga, dan statistik kependudukan yang akurat tanpa pelatihan panjang.
- Mempercepat pembuatan dan ekspor laporan demografi (Excel/CSV) untuk pengambil keputusan desa.

## Positioning

**Privacy-First Sistem Informasi Desa**: Berbeda dari aplikasi sistem informasi desa umum yang menyimpan identitas kependudukan sensitif, sistem ini **sengaja tidak menyimpan NIK, Nomor KK, Nomor Telepon warga, maupun Alamat Rumah Detail**. Data jumlah KK dikelola via angka rekap per RT, dan statistik publik menerapkan penyembunyian sel kecil (<5 warga) agar data kependudukan tetap aman dan terlindungi.

## Operating Context

- **Penggunaan Publik**: Diakses melalui web browser di HP/ponsel dan komputer oleh masyarakat umum.
- **Penggunaan Admin**: Diakses oleh perangkat desa di kantor desa maupun lapangan untuk mengelola data kependudukan, rekap RT, dan pembaruan berita/konten.
- **Lingkungan Deployment**: Laravel 12 + Vite (PHP 8.2+, MySQL/MariaDB) yang dapat dideploy di shared hosting cPanel maupun VPS server.

## Capabilities and Constraints

- **Pendataan Terbatas**: Kolom individu hanya Nama Lengkap, Tanggal Lahir (umur calculated live), Jenis Kelamin, Wilayah (Dusun/RW/RT), Pekerjaan, Pendidikan, dan Data Mutasi (masuk/keluar).
- **Larangan Data**: Dilarang keras menyimpan/menampilkan NIK, No KK, No HP warga (kecuali kontak pengurus resmi), alamat detail rumah, agama, atau golongan darah.
- **Kelompok Umur Baku**: Balita & Anak (0-5), Anak Sekolah (6-12), Remaja (13-18), Dewasa Muda (19-35), Dewasa (36-60), Lansia (61+).
- **Rekap KK**: Jumlah KK diturunkan dari input angka rekap per RT per tahun, bukan nomor KK.
- **Aturan Statistik Publik**: Hanya sampai level Desa dan Dusun (bukan RT). Sel kecil <5 warga tidak dikirim/ditampilkan per angka spesifik.

## Brand Commitments

- **Bahasa**: Bahasa Indonesia yang baku, ramah, dan mudah dipahami oleh aparat desa dan masyarakat umum.
- **Identitas**: Sistem Informasi Desa Modern (Profil & Demografi Desa).

## Evidence on Hand

- Dokumen Kebutuhan Produk (`PRD.md`), Dokumen Desain UI/UX (`DESAIN-FIGMA.md`), Analisis CMS (`ANALISIS-CMS.md`), Panduan Pengkodean (`CLAUDE.md`), dan Daftar Modul Fitur (`features/`).

## Product Principles

1. **Privasi & Keamanan Data Utama**: Jangan pernah mengorbankan privasi kependudukan warga demi kemudahan pengumpulan data.
2. **Keterbukaan & Promosi Digital**: Halaman publik harus memukau, kaya informasi, dan mempromosikan potensi serta prestasi desa secara menarik.
3. **Kemudahan Akses Perangkat Desa**: Antarmuka panel admin harus intuitif, responsif, dan langsung memberikan statistik agregat tanpa kerumitan.
4. **Konsistensi Data Demografi**: Pengelompokan statistik dan periode rekap KK wajib selaras 100% antara panel admin dan portal publik.

## Accessibility & Inclusion

- Antarmuka 100% berbahasa Indonesia, responsif untuk perangkat mobile, tablet, dan PC.
- Memenuhi kaidah SEO (meta description, Open Graph, semantic HTML) untuk memastikan kemudahan pencarian informasi profil desa oleh publik.
