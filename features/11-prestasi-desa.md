# Prestasi Desa

Mengelola penghargaan, program unggulan, dan inovasi yang diraih desa.

> **Fase 3 — Konten Desa.** Bergantung pada: [01 Autentikasi Admin](01-autentikasi-admin.md), [02 Integrasi Penyimpanan R2](02-integrasi-penyimpanan-r2.md).

## Spesifikasi

### Tujuan
Mendokumentasikan penghargaan dan capaian desa agar tampil sebagai bukti kualitas desa di halaman publik dan mudah diperbarui setiap ada capaian baru.

### Selesai bila
- Admin dapat menambah, mengubah, dan menghapus data prestasi beserta tahun, tingkat, penyelenggara, deskripsi, dan foto.
- Halaman publik menampilkan prestasi berstatus tampil, diurutkan dari tahun terbaru.
- Prestasi dapat disaring berdasarkan tingkat penghargaan.
- Jumlah prestasi per tingkat terlihat sebagai ringkasan.

## Sub-fitur: Kelola Prestasi

Menambah, mengubah, dan menghapus catatan prestasi desa.

### Tujuan
Memberi admin cara mencatat setiap capaian desa secara lengkap agar tidak ada penghargaan yang terlewat didokumentasikan.

### Selesai bila
- Formulir prestasi memuat isian judul, tahun, tingkat, penyelenggara, deskripsi, foto, dan status tampil.
- Tingkat dipilih dari daftar tetap: Desa, Kecamatan, Kabupaten, Provinsi, dan Nasional.
- Tahun hanya menerima tahun yang wajar dan tidak melebihi tahun berjalan.
- Data yang disimpan langsung muncul di daftar kelola dan halaman publik sesuai statusnya.
- Penghapusan meminta konfirmasi lebih dulu, dan foto prestasi ikut terhapus dari R2.

## Sub-fitur: Foto Piagam & Dokumentasi

Melampirkan bukti visual penghargaan.

### Tujuan
Menampilkan foto piagam, plakat, atau momen penyerahan penghargaan sebagai bukti capaian desa.

### Selesai bila
- Admin dapat mengunggah satu foto per prestasi dan melihat pratinjaunya sebelum menyimpan.
- Foto tersimpan di R2 melalui layanan unggah terpusat, sudah dikompresi dan dibuatkan thumbnail.
- Mengganti foto menghapus foto lama dari R2 setelah foto baru tersimpan.
- Prestasi tanpa foto tetap tampil dengan ikon penghargaan sebagai pengganti.

## Sub-fitur: Halaman Publik Prestasi

Menampilkan capaian desa kepada masyarakat.

### Tujuan
Menampilkan penghargaan dan inovasi desa secara runut agar pengunjung melihat perkembangan capaian desa dari waktu ke waktu.

### Selesai bila
- Halaman `/prestasi` menampilkan prestasi berstatus tampil dalam bentuk lini masa, diurutkan dari tahun terbaru ke terlama.
- Setiap prestasi menampilkan judul, tahun, tingkat, penyelenggara, deskripsi, dan foto.
- Tingkat penghargaan ditandai dengan label warna yang berbeda agar mudah dibedakan.
- Bagian atas halaman menampilkan ringkasan jumlah prestasi per tingkat.
- Pengunjung dapat menyaring prestasi berdasarkan tingkat.
- Jika belum ada data, halaman menampilkan pesan kosong yang ramah.
- Halaman memiliki judul dan meta description yang terisi.

## Task

### 1. Buat migrasi tabel prestasi_desa

### 2. Buat model PrestasiDesa dengan scope tampil dan urut tahun

### 3. Buat seeder prestasi contoh lintas tingkat dan tahun

### 4. Buat halaman admin daftar prestasi dengan pagination

### 5. Buat formulir tambah dan ubah prestasi dengan unggah foto

### 6. Buat validasi tahun tidak melebihi tahun berjalan

### 7. Buat aksi hapus prestasi dengan konfirmasi dan hapus foto R2

### 8. Buat aksi ubah status tampil langsung dari daftar

### 9. Buat controller dan route publik prestasi

### 10. Buat halaman publik prestasi bentuk lini masa

### 11. Buat label warna per tingkat dan ringkasan jumlah

### 12. Buat saringan tingkat dan pesan kosong pada halaman publik
