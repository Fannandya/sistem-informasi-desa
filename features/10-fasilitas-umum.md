# Fasilitas Umum

Mengelola data sarana desa seperti sekolah, puskesmas, tempat ibadah, dan lapangan.

> **Fase 3 — Konten Desa.** Bergantung pada: [01 Autentikasi Admin](01-autentikasi-admin.md), [02 Integrasi Penyimpanan R2](02-integrasi-penyimpanan-r2.md), [04 Data Penduduk](04-data-penduduk.md) untuk daftar wilayah.

## Spesifikasi

### Tujuan
Mendata sarana dan prasarana yang dimiliki desa agar warga dan pihak luar mengetahui fasilitas yang tersedia beserta lokasinya.

### Selesai bila
- Admin dapat menambah, mengubah, dan menghapus data fasilitas umum beserta kategori, deskripsi, lokasi, dan foto.
- Admin dapat menyaring daftar fasilitas berdasarkan kategori dan wilayah.
- Halaman publik menampilkan fasilitas yang berstatus tampil, dikelompokkan per kategori.
- Setiap kategori menampilkan jumlah fasilitas yang dimilikinya.

## Sub-fitur: Kelola Fasilitas

Menambah, mengubah, dan menghapus data sarana desa.

### Tujuan
Memberi admin satu tempat untuk mendata seluruh sarana desa secara lengkap dan konsisten.

### Selesai bila
- Formulir fasilitas memuat isian nama, kategori, deskripsi, alamat, wilayah, foto, dan status tampil.
- Kategori dipilih dari daftar tetap: Pendidikan, Kesehatan, Ibadah, Pemerintahan, Olahraga, dan Umum.
- Wilayah dipilih dari daftar wilayah baku agar penulisan lokasi tidak berbeda-beda.
- Data yang disimpan langsung muncul di daftar kelola dan di halaman publik sesuai statusnya.
- Penghapusan meminta konfirmasi lebih dulu, dan foto fasilitas ikut terhapus dari R2.

## Sub-fitur: Foto Fasilitas

Melengkapi data fasilitas dengan dokumentasi visual.

### Tujuan
Menampilkan wujud nyata fasilitas desa agar informasi lebih meyakinkan bagi pengunjung.

### Selesai bila
- Admin dapat mengunggah satu foto per fasilitas dan melihat pratinjaunya sebelum menyimpan.
- Foto tersimpan di R2 melalui layanan unggah terpusat, sudah dikompresi dan dibuatkan thumbnail.
- Mengganti foto menghapus foto lama dari R2 setelah foto baru tersimpan.
- Fasilitas tanpa foto tetap tampil di halaman publik dengan gambar pengganti sesuai kategorinya.

## Sub-fitur: Daftar & Saringan Fasilitas

Menemukan fasilitas dengan cepat pada daftar admin.

### Tujuan
Memudahkan admin menemukan dan memeriksa kelengkapan data fasilitas desa.

### Selesai bila
- Halaman kelola menampilkan fasilitas dalam tabel berisi foto kecil, nama, kategori, wilayah, dan status tampil.
- Admin dapat menyaring daftar berdasarkan kategori dan wilayah, serta mencari berdasarkan nama.
- Daftar menggunakan pagination bila data lebih dari 25 baris.
- Status tampil dapat diubah langsung dari daftar tanpa membuka formulir.

## Sub-fitur: Halaman Publik Fasilitas

Menampilkan sarana desa kepada masyarakat.

### Tujuan
Menampilkan sarana yang dimiliki desa agar warga mengetahui fasilitas yang tersedia dan di mana letaknya.

### Selesai bila
- Halaman `/fasilitas` menampilkan fasilitas berstatus tampil, dikelompokkan berdasarkan kategori.
- Setiap kategori menampilkan judul, jumlah fasilitas, dan kartu berisi foto, nama, lokasi, serta deskripsi singkat.
- Pengunjung dapat menyaring tampilan berdasarkan kategori.
- Kategori yang belum memiliki data tidak ditampilkan sebagai judul kosong.
- Jika belum ada data sama sekali, halaman menampilkan pesan kosong yang ramah.
- Halaman memiliki judul dan meta description yang terisi.

## Task

### 1. Buat migrasi tabel fasilitas_umum

### 2. Buat model FasilitasUmum dengan relasi wilayah dan scope tampil

### 3. Buat seeder fasilitas contoh untuk setiap kategori

### 4. Buat halaman admin daftar fasilitas dengan saringan dan pagination

### 5. Buat formulir tambah dan ubah fasilitas dengan unggah foto

### 6. Buat aksi hapus fasilitas dengan konfirmasi dan hapus foto R2

### 7. Buat aksi ubah status tampil langsung dari daftar

### 8. Buat gambar pengganti per kategori fasilitas

### 9. Buat controller dan route publik fasilitas

### 10. Buat halaman publik fasilitas berkelompok per kategori

### 11. Buat saringan kategori dan pesan kosong pada halaman publik
