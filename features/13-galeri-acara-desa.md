# Galeri & Dokumentasi Acara

Mendokumentasikan kegiatan desa dalam bentuk galeri foto bernarasi.

> **Fase 3 — Konten Desa.** Bergantung pada: [01 Autentikasi Admin](01-autentikasi-admin.md), [02 Integrasi Penyimpanan R2](02-integrasi-penyimpanan-r2.md).
>
> **Berbeda dari [12 Berita Desa](12-berita-desa.md).** Galeri Acara berpusat pada kumpulan foto satu kegiatan dengan narasi singkat dan tanggal pelaksanaan. Berita berpusat pada artikel teks berkategori dengan satu gambar sampul. Keduanya sengaja dipisah dan tidak boleh digabung menjadi satu tabel.

## Spesifikasi

### Tujuan
Mendokumentasikan kegiatan desa secara visual agar warga dapat melihat kembali acara yang telah berlangsung dan pihak luar melihat keaktifan desa.

### Selesai bila
- Admin dapat membuat catatan acara, mengunggah banyak foto sekaligus, mengatur status terbit, menyunting, dan menghapus dokumentasi acara.
- Status memakai nilai yang sama persis dengan Berita — **Draf** dan **Terbit** — agar logika penyaringan dan pelabelan dapat dipakai ulang, bukan `tampil` seperti pada Potensi dan Fasilitas.
- Acara berstatus Terbit muncul di halaman publik sebagai galeri lengkap dengan narasi dan foto.
- Acara berstatus Draf hanya terlihat di daftar admin dan tidak dapat diakses publik meski alamat URL-nya ditebak.
- Setiap acara memiliki halaman detail dengan alamat URL sendiri.
- Menghapus acara ikut menghapus seluruh fotonya dari R2, tidak menyisakan berkas yatim.

## Sub-fitur: Tulis Dokumentasi Acara

Membuat catatan kegiatan desa lengkap dengan narasi.

### Tujuan
Membuat catatan acara desa berisi judul, waktu, tempat, dan cerita singkat pelaksanaannya.

### Selesai bila
- Admin dapat mengisi judul, tanggal pelaksanaan, lokasi, dan narasi acara.
- Alamat URL halaman detail dibuat otomatis dari judul mengikuti **PRD §6.17**, dan sistem menjaga agar tidak ada dua alamat yang sama.
- Tanggal acara tidak boleh melebihi hari ini lebih dari satu tahun ke depan.
- Acara tersimpan dan muncul di daftar dokumentasi admin.
- Narasi yang tersimpan dapat dibaca di halaman publik jika statusnya Tampil.
- Nama admin pencatat tersimpan pada acara.

## Sub-fitur: Unggah Banyak Foto Acara

Menambahkan dokumentasi visual kegiatan.

### Tujuan
Memungkinkan admin mengunggah banyak foto sekaligus untuk satu acara sebagai dokumentasi visual.

### Selesai bila
- Admin dapat memilih dan mengunggah beberapa foto sekaligus untuk sebuah acara, maksimal 10 berkas per pengiriman sesuai batas hosting.
- Setiap foto tersimpan di R2 melalui layanan unggah terpusat, sudah dikompresi dan dibuatkan thumbnail.
- Admin dapat mengisi keterangan untuk setiap foto.
- Admin dapat menandai satu foto sebagai foto sampul acara; bila tidak ditandai, foto pertama dipakai sebagai sampul.
- Admin dapat mengatur urutan tampil foto dalam galeri.
- Admin dapat menghapus foto satuan, dan berkasnya ikut terhapus dari R2.
- Acara tetap bisa disimpan tanpa foto sama sekali dan tidak menampilkan error di halaman publik.

## Sub-fitur: Atur Status Tampil

Menentukan apakah acara tampil langsung atau disimpan sebagai draf.

### Tujuan
Memberi admin kendali kapan sebuah dokumentasi siap dilihat publik.

### Selesai bila
- Admin dapat memilih status Draf atau Terbit saat menulis maupun menyunting acara.
- Acara berstatus Terbit langsung muncul di halaman publik.
- Acara berstatus Draf tidak dapat diakses publik meski alamat URL detailnya dibuka langsung.
- Status dapat diubah langsung dari daftar kelola tanpa membuka formulir.

## Sub-fitur: Ubah & Hapus Acara

Memperbaiki atau menghapus dokumentasi acara.

### Tujuan
Memperbaiki dokumentasi yang keliru atau menghapus acara yang tidak jadi didokumentasikan.

### Selesai bila
- Admin dapat mengubah judul, tanggal, lokasi, narasi, foto, dan status acara.
- Perubahan langsung terlihat di halaman publik sesuai statusnya.
- Penghapusan acara meminta konfirmasi dan menyebut berapa foto yang akan ikut terhapus.
- Acara yang dihapus tidak lagi muncul di halaman publik maupun daftar admin, dan seluruh fotonya terhapus dari R2.

## Sub-fitur: Halaman Publik Galeri

Menampilkan dokumentasi kegiatan kepada masyarakat.

### Tujuan
Menampilkan dokumentasi kegiatan desa dalam bentuk galeri yang enak dilihat di ponsel maupun komputer.

### Selesai bila
- Halaman `/galeri` menampilkan acara berstatus Terbit sebagai kartu berisi foto sampul, judul, tanggal, dan jumlah foto.
- Acara diurutkan dari tanggal pelaksanaan terbaru, dengan pagination 12 acara per halaman.
- Menekan sebuah kartu membuka halaman detail `/galeri/{slug}` berisi judul, tanggal, lokasi, narasi, dan seluruh foto dalam bentuk kisi.
- Menekan sebuah foto membukanya dalam tampilan besar (lightbox) yang bisa digeser ke foto berikutnya dan ditutup dengan tombol atau tombol Esc.
- Halaman detail memiliki judul, meta description dari narasi, dan tag Open Graph dari foto sampul.
- Jika belum ada acara, halaman menampilkan pesan kosong yang ramah.
- Foto dimuat bertahap (lazy load) agar halaman tetap ringan saat foto banyak.

## Task

### 1. Buat migrasi tabel acara_desa dan foto_acara

### 2. Buat model Acara dan FotoAcara dengan relasi dan cascade delete

### 3. Buat pembuatan slug otomatis dan penjagaan slug unik

### 4. Buat seeder acara contoh beserta fotonya

### 5. Buat halaman admin daftar dokumentasi dengan saringan status

### 6. Buat formulir tulis dan sunting acara

### 7. Buat antarmuka unggah banyak foto dengan pratinjau

### 8. Buat pengisian keterangan dan pengaturan urutan foto

### 9. Buat penandaan foto sampul acara

### 10. Buat aksi hapus foto satuan beserta berkas di R2

### 11. Buat aksi ubah status tampil langsung dari daftar

### 12. Buat aksi hapus acara dengan konfirmasi dan hapus seluruh foto

### 13. Buat controller dan route publik galeri

### 14. Buat halaman publik daftar galeri dengan kartu dan pagination

### 15. Buat halaman detail acara dengan kisi foto

### 16. Buat lightbox foto dengan navigasi dan tombol tutup

### 17. Terapkan lazy load pada foto galeri

### 18. Buat meta tag dan Open Graph pada halaman detail acara
