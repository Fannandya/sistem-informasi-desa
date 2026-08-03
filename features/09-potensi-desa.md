# Potensi Desa

Mengelola katalog UMKM, hasil bumi, wisata, dan kerajinan desa lengkap dengan foto.

> **Fase 3 — Konten Desa.** Bergantung pada: [01 Autentikasi Admin](01-autentikasi-admin.md), [02 Integrasi Penyimpanan R2](02-integrasi-penyimpanan-r2.md).

## Spesifikasi

### Tujuan
Mengelola daftar potensi desa beserta foto dan deskripsi agar potensi desa tampil rapi di halaman publik dan mudah diperbarui oleh admin.

### Selesai bila
- Admin dapat melihat daftar potensi desa dalam satu halaman kelola, lengkap dengan foto kecil, nama, kategori, dan status tampil.
- Admin dapat membuka formulir tambah, ubah, atau hapus potensi desa dari halaman yang sama.
- Data potensi yang dikelola muncul di halaman publik sesuai status tampil dan urutannya.
- Setiap potensi memiliki halaman detail publik dengan alamat URL sendiri yang ramah pencarian.

## Sub-fitur: Tambah Potensi

Menambahkan potensi desa baru beserta foto dan deskripsi.

### Tujuan
Memungkinkan admin menambahkan potensi desa baru lengkap dengan nama, kategori, foto, dan deskripsi agar langsung tersimpan dan siap ditampilkan.

### Selesai bila
- Ada formulir tambah potensi dengan isian nama, kategori, deskripsi singkat, deskripsi lengkap, kontak usaha, foto, dan status tampil.
- Kategori dipilih dari daftar tetap: UMKM, Hasil Bumi, Wisata, dan Kerajinan.
- Alamat URL halaman detail dibuat otomatis dari nama potensi mengikuti **PRD §6.17**, dan sistem menjaga agar tidak ada dua alamat yang sama.
- Deskripsi singkat dibatasi 255 karakter dengan penghitung sisa karakter, karena dipakai pada kartu dan meta description.
- Saat formulir disimpan, potensi baru muncul di daftar kelola dengan data yang benar.
- Tanpa foto pun potensi tetap bisa disimpan dan menampilkan gambar pengganti.

## Sub-fitur: Ubah & Hapus Potensi

Memperbarui atau menghapus potensi yang tidak relevan lagi.

### Tujuan
Memungkinkan admin memperbarui informasi potensi dan menghapus potensi yang sudah tidak relevan agar konten publik selalu akurat.

### Selesai bila
- Tombol ubah pada setiap potensi membuka formulir berisi data lama yang bisa disunting lalu disimpan.
- Tombol hapus meminta konfirmasi sebelum menghapus, dan potensi hilang dari daftar kelola serta halaman publik.
- Foto potensi yang dihapus ikut terhapus dari R2.
- Perubahan nama, kategori, atau deskripsi langsung tampil di halaman publik setelah disimpan.
- Mengubah nama potensi **tidak** mengubah alamat URL-nya, sesuai **PRD §6.17**, sehingga tautan yang sudah tersebar tetap hidup. Admin dapat menyunting alamat URL secara manual bila memang diinginkan, dengan peringatan bahwa tautan lama akan mati.

## Sub-fitur: Atur Foto Potensi

Mengunggah dan mengganti foto untuk mempercantik tampilan potensi.

### Tujuan
Memungkinkan admin mengunggah foto baru atau mengganti foto potensi agar tampilan katalog di halaman publik menarik.

### Selesai bila
- Saat tambah atau ubah, admin bisa memilih berkas gambar dari perangkat dan melihat pratinjaunya sebelum menyimpan.
- Foto tersimpan di R2 melalui layanan unggah terpusat, sudah dikompresi dan dibuatkan thumbnail.
- Admin dapat mengganti foto potensi yang sudah ada melalui formulir ubah tanpa mengubah data lain, dan foto lama terhapus dari R2.
- Format berkas yang tidak didukung atau berukuran lebih dari 4 MB ditolak dengan pesan jelas, dan data tidak tersimpan setengah jalan.

## Sub-fitur: Atur Tampil & Urutan

Memilih potensi mana yang muncul di halaman publik dan bagaimana urutannya.

### Tujuan
Memungkinkan admin memilih potensi mana yang muncul di halaman publik desa dan mengatur susunannya agar konten terbaik tampil lebih dulu.

### Selesai bila
- Setiap potensi punya status tampil yang bisa diubah langsung dari daftar kelola maupun dari formulir.
- Halaman publik hanya menampilkan potensi berstatus tampil; yang disembunyikan tidak muncul.
- Admin dapat mengatur nomor urut tampil potensi, dan urutan itu diikuti halaman publik.
- Jumlah potensi yang tampil di publik konsisten dengan status yang dipilih admin.

## Sub-fitur: Halaman Publik Katalog Potensi

Menampilkan potensi desa dalam bentuk kartu dan halaman detail.

### Tujuan
Menampilkan potensi desa dalam bentuk katalog yang menarik agar pengunjung tertarik mengenal dan mengunjungi desa.

### Selesai bila
- Halaman `/potensi` menampilkan seluruh potensi berstatus tampil sebagai kartu berisi foto, nama, kategori, dan deskripsi singkat.
- Pengunjung dapat menyaring katalog berdasarkan kategori.
- Menekan sebuah kartu membuka halaman detail `/potensi/{slug}` berisi foto besar, deskripsi lengkap, dan kontak usaha bila ada.
- Halaman detail memiliki judul, meta description, dan tag Open Graph yang terisi dari data potensi.
- Jika belum ada data, halaman menampilkan pesan kosong yang ramah.
- Katalog menggunakan pagination bila potensi lebih dari 12 item.

## Task

### 1. Buat migrasi tabel potensi_desa dengan slug dan urutan

### 2. Buat model PotensiDesa dengan pembuatan slug otomatis

### 3. Buat seeder potensi contoh untuk setiap kategori

### 4. Buat halaman admin kelola potensi dengan foto kecil dan status

### 5. Buat formulir tambah potensi dengan pratinjau foto

### 6. Buat penghitung karakter pada deskripsi singkat

### 7. Buat aksi ubah potensi dengan penggantian foto di R2

### 8. Buat aksi hapus potensi dengan konfirmasi dan hapus foto

### 9. Buat aksi ubah status tampil dan nomor urut dari daftar

### 10. Buat controller dan route publik katalog potensi

### 11. Buat halaman publik katalog dengan kartu dan saringan kategori

### 12. Buat halaman publik detail potensi dengan meta tag

### 13. Buat pagination katalog dan pesan kosong ramah
