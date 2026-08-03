# Berita Desa

Menulis dan menerbitkan artikel berita serta pengumuman desa.

> **Fase 3 — Konten Desa.** Bergantung pada: [01 Autentikasi Admin](01-autentikasi-admin.md), [02 Integrasi Penyimpanan R2](02-integrasi-penyimpanan-r2.md).
>
> **Berbeda dari [13 Galeri & Dokumentasi Acara](13-galeri-acara-desa.md).** Berita adalah artikel teks berkategori dengan satu gambar sampul, untuk menyampaikan informasi dan pengumuman. Galeri Acara adalah kumpulan foto kegiatan dengan narasi singkat. Keduanya sengaja dipisah dan tidak boleh digabung menjadi satu tabel.

## Spesifikasi

### Tujuan
Menyediakan kanal resmi desa untuk menyampaikan berita dan pengumuman kepada warga, dengan artikel yang tertata rapi, berkategori, dan mudah ditemukan mesin pencari.

### Selesai bila
- Admin dapat menulis, menyunting, menerbitkan, dan menghapus berita.
- Berita berstatus Terbit muncul di halaman publik; berita berstatus Draf hanya terlihat di daftar admin.
- Setiap berita memiliki alamat URL sendiri yang ramah pencarian dan tidak berubah sembarangan.
- Admin dapat mengelola kategori berita dan menandai satu berita sebagai berita utama.
- Halaman publik menampilkan berita terbaru lebih dulu, dengan pagination.

## Sub-fitur: Kelola Kategori Berita

Mengatur pengelompokan artikel desa.

### Tujuan
Menyediakan pengelompokan berita agar pengunjung mudah menemukan jenis informasi yang dicarinya.

### Selesai bila
- Admin dapat menambah, mengubah, dan menghapus kategori berita.
- Nama kategori tidak boleh sama dengan kategori yang sudah ada.
- Alamat URL kategori dibuat otomatis dari namanya.
- Kategori yang masih dipakai oleh berita tidak dapat dihapus, dan sistem menjelaskan alasannya beserta jumlah berita yang memakainya.
- Setiap kategori menampilkan jumlah berita yang dimilikinya di daftar kelola.

## Sub-fitur: Tulis & Sunting Berita

Membuat dan memperbarui artikel berita desa.

### Tujuan
Memberi admin ruang menulis artikel berita secara utuh, lengkap dengan gambar sampul dan ringkasan.

### Selesai bila
- Formulir berita memuat isian judul, kategori, ringkasan, isi artikel, gambar sampul, status, dan penanda berita utama.
- Isi artikel ditulis melalui editor teks kaya Trix yang mendukung paragraf, tebal, miring, daftar, dan tautan.
- Isi artikel **dibersihkan dengan penyaring HTML sebelum disimpan**, menyisakan hanya tag yang diizinkan. Tag skrip, atribut event, dan `iframe` ditolak — tanpa ini isi berita menjadi celah XSS.
- Ringkasan dibatasi 300 karakter dengan penghitung sisa karakter. Untuk meta description, ringkasan dipotong pada 160 karakter.
- Alamat URL dibuat otomatis dari judul saat berita pertama kali dibuat dan dapat disunting admin, mengikuti **PRD §6.17**; mengubah judul tidak mengubah alamat URL secara otomatis.
- Nama admin penulis tercatat pada berita.
- Formulir menyimpan draf sementara di peramban agar tulisan tidak hilang bila halaman tertutup tidak sengaja.

## Sub-fitur: Gambar Sampul Berita

Melengkapi artikel dengan satu gambar utama.

### Tujuan
Memberi setiap berita gambar sampul agar tampilan daftar berita menarik dan pratinjau berbagi di media sosial terlihat baik.

### Selesai bila
- Admin dapat mengunggah satu gambar sampul per berita dan melihat pratinjaunya sebelum menyimpan.
- Gambar tersimpan di R2 melalui layanan unggah terpusat, sudah dikompresi dan dibuatkan thumbnail.
- Mengganti sampul menghapus gambar lama dari R2 setelah gambar baru tersimpan.
- Berita tanpa sampul tetap tampil dengan gambar pengganti dan tidak merusak tata letak kartu.
- Gambar sampul dipakai sebagai tag Open Graph pada halaman baca.

## Sub-fitur: Status Terbit & Berita Utama

Mengatur kapan berita muncul dan mana yang disorot.

### Tujuan
Memberi admin kendali penuh atas kapan sebuah tulisan terlihat publik dan berita mana yang paling disorot di halaman muka.

### Selesai bila
- Admin dapat memilih status Draf atau Terbit saat menulis maupun menyunting berita.
- Berita Terbit langsung muncul di halaman publik; berita Draf tidak dapat diakses publik meski alamat URL-nya ditebak.
- Waktu terbit tercatat saat berita pertama kali berstatus Terbit, dan dapat diubah admin.
- Admin dapat menandai berita sebagai berita utama; hanya satu berita yang bisa menjadi utama pada satu waktu, dan menandai berita baru otomatis melepas tanda dari berita sebelumnya.
- Status dapat diubah langsung dari daftar kelola tanpa membuka formulir.

## Sub-fitur: Halaman Publik Berita

Menampilkan daftar berita dan halaman baca.

### Tujuan
Menyajikan berita desa dalam bentuk yang nyaman dibaca di ponsel maupun komputer, dan mudah ditemukan lewat mesin pencari.

### Selesai bila
- Halaman `/berita` menampilkan berita berstatus Terbit sebagai kartu berisi gambar sampul, judul, kategori, tanggal terbit, dan ringkasan.
- Berita utama ditampilkan lebih menonjol di bagian atas daftar.
- Berita diurutkan dari tanggal terbit terbaru, dengan pagination 9 berita per halaman.
- Pengunjung dapat menyaring berita berdasarkan kategori melalui alamat `/berita/kategori/{slug}`.
- Menekan sebuah kartu membuka halaman baca `/berita/{slug}` berisi judul, tanggal, kategori, gambar sampul, dan isi artikel utuh.
- Halaman baca menampilkan tiga berita lain sebagai bacaan berikutnya.
- Jumlah pembacaan bertambah setiap halaman baca dibuka.
- Halaman baca memiliki judul, meta description dari ringkasan, dan tag Open Graph dari gambar sampul.
- Jika belum ada berita, halaman menampilkan pesan kosong yang ramah.

## Task

### 1. Buat migrasi tabel kategori_berita dan berita_desa

### 2. Buat model KategoriBerita dan BeritaDesa beserta relasinya

### 3. Buat pembuatan slug otomatis dan penjagaan slug unik

### 4. Buat seeder kategori dan berita contoh

### 5. Buat halaman admin kelola kategori dengan proteksi hapus

### 6. Buat halaman admin daftar berita dengan saringan status dan kategori

### 7. Buat formulir tulis dan sunting berita dengan editor Trix

### 8. Terapkan sanitasi HTML isi berita sebelum disimpan

### 9. Buat unggah gambar sampul dengan pratinjau dan penggantian di R2

### 10. Buat penghitung karakter pada ringkasan

### 11. Buat penyimpanan draf sementara di peramban

### 12. Buat aksi ubah status terbit langsung dari daftar

### 13. Buat penandaan berita utama yang saling meniadakan

### 14. Buat aksi hapus berita dengan konfirmasi dan hapus sampul

### 15. Buat controller dan route publik daftar berita

### 16. Buat halaman publik daftar berita dengan kartu dan pagination

### 17. Buat halaman publik saringan berita per kategori

### 18. Buat halaman baca berita dengan isi utuh dan berita terkait

### 19. Buat penghitung jumlah pembacaan berita

### 20. Buat meta tag dan Open Graph pada halaman baca
