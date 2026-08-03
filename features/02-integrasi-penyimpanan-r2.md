# Integrasi Penyimpanan Cloudflare R2

Menyimpan seluruh unggahan gambar ke Cloudflare R2, bukan ke server hosting.

> **Fase 1 — Fondasi.** Bergantung pada: [01 Autentikasi Admin](01-autentikasi-admin.md).
> Fitur ini harus selesai **sebelum** fitur apa pun yang mengunggah foto (Pengaturan Desa, Pengurus, Potensi, Fasilitas, Prestasi, Berita, Galeri). Membangunnya belakangan berarti seluruh kode unggah harus ditulis ulang.

## Spesifikasi

### Tujuan
Menyediakan satu jalur penyimpanan berkas terpusat ke Cloudflare R2 sehingga seluruh fitur unggah foto memakai mekanisme yang sama, hemat ruang server, dan aman dari kebocoran berkas yatim.

### Selesai bila
- Aplikasi memiliki disk penyimpanan bernama `r2` yang terhubung ke bucket Cloudflare R2 melalui protokol S3.
- Seluruh gambar yang diunggah admin tersimpan di R2, bukan di folder `storage` server.
- Halaman publik dan admin menampilkan gambar melalui URL publik R2.
- Kredensial R2 dibaca dari berkas `.env` dan tidak pernah tertulis di dalam kode.
- Bila R2 tidak dapat dijangkau, pengiriman formulir gagal dengan pesan yang jelas dan data teks tidak tersimpan setengah jalan.

## Sub-fitur: Konfigurasi Disk R2

Menyiapkan koneksi aplikasi ke bucket Cloudflare R2.

### Tujuan
Menyiapkan koneksi aplikasi ke bucket R2 agar seluruh fitur unggah cukup memanggil satu disk yang sama.

### Selesai bila
- Berkas konfigurasi filesystem memiliki entri disk `r2` dengan endpoint, kunci akses, kunci rahasia, nama bucket, dan URL publik yang diambil dari `.env`.
- Berkas `.env.example` memuat seluruh nama variabel R2 dengan nilai kosong sebagai contoh.
- Tersedia perintah pemeriksaan koneksi yang mengunggah dan menghapus satu berkas uji, lalu melaporkan berhasil atau gagal.

## Sub-fitur: Layanan Unggah Gambar Terpusat

Satu layanan yang dipakai semua fitur untuk mengunggah gambar.

### Tujuan
Menyediakan satu layanan unggah yang menangani validasi, penamaan, kompresi, dan penyimpanan, agar tidak ada fitur yang membuat aturannya sendiri.

### Selesai bila
- Tersedia satu layanan yang menerima berkas gambar dan nama folder tujuan, lalu mengembalikan path berkas di R2.
- Layanan menolak berkas yang bukan jpg, jpeg, png, atau webp dengan pesan yang jelas.
- Layanan menolak berkas berukuran lebih dari 4 MB dengan pesan yang menyebut batas ukurannya.
- Nama berkas diacak agar tidak bentrok dan tidak membocorkan nama berkas asli dari perangkat admin.
- Berkas disimpan dalam folder terpisah per jenis konten, misalnya `logo/`, `pengurus/`, `potensi/`, `berita/`, `acara/`.

## Sub-fitur: Kompresi & Ubah Ukuran Gambar

Mengecilkan gambar saat diunggah agar halaman publik tetap ringan.

### Tujuan
Menjaga halaman publik tetap cepat dengan memastikan tidak ada gambar berukuran mentah dari kamera ponsel yang tersimpan apa adanya.

### Selesai bila
- Gambar yang lebih lebar dari 1600 piksel dikecilkan ke lebar 1600 piksel dengan rasio tetap.
- Gambar dikonversi ke format webp dengan kualitas yang menjaga tampilan tetap layak.
- Untuk setiap gambar dibuat pula versi thumbnail lebar 400 piksel untuk daftar dan kartu.
- Gambar yang sudah kecil tidak diperbesar.

## Sub-fitur: Penghapusan Berkas Yatim

Menghapus gambar di R2 saat data pemiliknya dihapus atau diganti.

### Tujuan
Mencegah bucket R2 penuh oleh gambar yang tidak lagi dipakai data mana pun.

### Selesai bila
- Seluruh tabel yang memiliki gambar memakai penghapusan permanen (hard delete) sesuai **PRD §6.16**; saat datanya dihapus, berkas gambar beserta thumbnailnya ikut terhapus dari R2.
- Saat admin mengganti gambar pada formulir ubah, gambar lama terhapus dari R2 **setelah** gambar baru berhasil tersimpan, tidak sebaliknya.
- Kegagalan menghapus berkas lama dicatat ke log dan tidak membatalkan penyimpanan data.
- Satu-satunya tabel bersoft delete adalah `penduduk`, dan tabel itu tidak memiliki gambar — sehingga tidak ada kasus data terarsip yang gambarnya terhapus.
- Tersedia perintah artisan pemeriksa berkas yatim yang membandingkan isi bucket dengan path yang tercatat di database, lalu melaporkan selisihnya tanpa langsung menghapus.

## Task

### 1. Pasang paket flysystem S3 dan Intervention Image

### 2. Tambahkan konfigurasi disk r2 dan variabel env

### 3. Buat perintah artisan uji koneksi R2

### 4. Buat layanan unggah gambar terpusat dengan validasi

### 5. Terapkan kompresi, resize, dan pembuatan thumbnail

### 6. Buat helper URL publik gambar dengan fallback placeholder

### 7. Buat layanan penghapusan berkas dan thumbnail di R2

### 8. Buat trait atau observer penghapusan gambar saat data dihapus

### 9. Buat perintah artisan pemeriksa berkas yatim di bucket

### 10. Tulis dokumentasi setup bucket R2 di README
