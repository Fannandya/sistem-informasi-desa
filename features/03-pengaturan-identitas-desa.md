# Pengaturan & Identitas Desa

Mengelola identitas, visi-misi, sejarah, letak geografis, dan kontak resmi desa.

> **Fase 1 — Fondasi.** Bergantung pada: [01 Autentikasi Admin](01-autentikasi-admin.md), [02 Integrasi Penyimpanan R2](02-integrasi-penyimpanan-r2.md).
> Menjadi sumber data untuk kop halaman publik, footer, dan halaman Profil Desa.

## Spesifikasi

### Tujuan
Memberi admin satu tempat untuk mengatur seluruh identitas dan profil desa, sehingga nama desa, logo, visi-misi, kontak, dan peta yang tampil di halaman publik selalu berasal dari satu sumber dan mudah diperbarui.

### Selesai bila
- Admin dapat membuka halaman Pengaturan Desa dan mengubah seluruh data identitas, profil, kontak, serta media sosial dalam formulir yang terbagi menjadi beberapa bagian.
- Data pengaturan tersimpan sebagai satu baris tunggal; tidak mungkin ada dua baris pengaturan.
- Perubahan yang disimpan langsung terlihat di halaman publik tanpa perlu mengubah kode.
- Bila pengaturan belum pernah diisi, halaman publik tetap tampil tanpa error dan menampilkan teks pengganti yang wajar.

## Sub-fitur: Identitas Desa

Mengatur nama, logo, wilayah administratif, tahun berdiri, dan luas desa.

### Tujuan
Mengatur identitas dasar desa yang muncul di kop, footer, judul halaman, dan halaman Profil.

### Selesai bila
- Admin dapat mengisi nama desa, kecamatan, kabupaten, provinsi, kode pos, tahun berdiri, dan luas desa dalam hektare.
- Admin dapat mengunggah logo desa dan gambar sampul untuk bagian hero landing page.
- Logo dan gambar sampul tersimpan di R2 melalui layanan unggah terpusat.
- Nama desa yang tersimpan otomatis dipakai sebagai bagian judul seluruh halaman publik.
- Kolom luas desa hanya menerima angka; tahun berdiri hanya menerima tahun yang wajar.

## Sub-fitur: Visi, Misi, & Sejarah

Mengelola teks visi, misi, nilai/tujuan pembangunan, dan sejarah desa.

### Tujuan
Mengelola narasi profil desa agar pengunjung memahami arah dan latar belakang desa.

### Selesai bila
- Admin dapat mengisi dan mengubah teks visi, misi, nilai/tujuan pembangunan, dan sejarah desa.
- Kolom misi mendukung penulisan sebagai daftar bernomor atau berpoin, dan tampil sebagai daftar di halaman publik.
- Teks panjang tersimpan utuh tanpa terpotong.
- Bagian yang dikosongkan admin tidak muncul sebagai judul kosong di halaman publik.

## Sub-fitur: Letak Geografis & Peta

Mengatur deskripsi lokasi desa dan peta yang tertanam.

### Tujuan
Menyediakan gambaran lokasi dan batas wilayah desa, dilengkapi peta yang bisa dilihat langsung pengunjung.

### Selesai bila
- Admin dapat mengisi deskripsi letak geografis dan batas wilayah desa.
- Admin dapat menempelkan kode embed Google Maps, dan peta tersebut tampil di halaman Profil Desa.
- Sistem **tidak menyimpan lalu menampilkan ulang HTML mentah** dari admin. Yang diambil dan disimpan hanya alamat `src` di dalam kode embed, setelah diperiksa berasal dari domain Google Maps. Peta lalu dirender ulang oleh aplikasi memakai alamat itu di dalam iframe yang dikendalikan sistem sendiri.
- Kode embed yang tidak valid atau bukan berasal dari Google Maps ditolak dengan pesan yang jelas, dan tidak ada yang tersimpan.
- Bila kolom peta dikosongkan, bagian peta tidak muncul di halaman publik dan tidak menyisakan ruang kosong.

## Sub-fitur: Kontak & Media Sosial

Mengatur alamat kantor, telepon, email, website, dan tautan media sosial desa.

### Tujuan
Menampilkan kanal kontak resmi desa agar warga dan pihak luar mudah menghubungi.

### Selesai bila
- Admin dapat mengisi alamat kantor desa, nomor telepon kantor, email, dan alamat website.
- Admin dapat mengisi tautan Facebook, Instagram, YouTube, dan TikTok.
- Tautan media sosial yang kosong tidak menampilkan ikon apa pun di footer.
- Format email dan format tautan divalidasi sebelum disimpan.
- Seluruh kontak yang terisi tampil di footer setiap halaman publik.

## Sub-fitur: Pengaturan SEO

Mengatur deskripsi ringkas desa untuk hasil pencarian dan pratinjau berbagi.

### Tujuan
Membuat halaman desa tampil menarik dan informatif saat muncul di Google atau dibagikan di media sosial.

### Selesai bila
- Admin dapat mengisi meta description desa sepanjang maksimal 255 karakter, dengan penghitung sisa karakter.
- Meta description dan gambar sampul dipakai sebagai tag Open Graph pada landing page.
- Bila meta description dikosongkan, sistem memakai potongan otomatis dari deskripsi profil desa.

## Task

### 1. Buat migrasi tabel pengaturan_desa

### 2. Buat model PengaturanDesa dengan pola singleton

### 3. Buat seeder pengaturan desa awal

### 4. Buat halaman admin pengaturan desa dengan tab bagian

### 5. Buat form identitas desa dengan unggah logo dan sampul

### 6. Buat form visi misi nilai dan sejarah

### 7. Buat form letak geografis dan validasi embed peta

### 8. Buat form kontak dan tautan media sosial

### 9. Buat form meta description dengan penghitung karakter

### 10. Buat controller penyimpanan pengaturan desa

### 11. Buat view composer agar identitas desa tersedia di semua layout
