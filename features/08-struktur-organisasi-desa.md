# Struktur Organisasi Desa

Mengelola susunan pengurus desa beserta jabatan, organisasi, dan kontak resminya.

> **Fase 3 — Konten Desa.** Bergantung pada: [01 Autentikasi Admin](01-autentikasi-admin.md), [02 Integrasi Penyimpanan R2](02-integrasi-penyimpanan-r2.md), [04 Data Penduduk](04-data-penduduk.md) untuk daftar wilayah.
> Seluruh tugas pengurus — admin maupun publik — berada di file ini.

## Spesifikasi

### Tujuan
Memudahkan admin mengelola susunan pengurus desa beserta jabatan dan organisasinya sehingga halaman publik menampilkan struktur organisasi yang rapi dan mudah dihubungi.

### Selesai bila
- Admin dapat menambah, mengubah, dan menghapus data pengurus beserta jabatan, organisasi, foto, dan nomor HP.
- Admin dapat mengelompokkan pengurus per organisasi dan mengatur urutan tampilnya.
- Admin dapat menyembunyikan pengurus dari halaman publik tanpa menghapus datanya.
- Halaman publik menampilkan pengurus sesuai organisasi, urutan, dan status tampil yang diatur admin.
- Data pengurus yang dihapus tidak lagi muncul di halaman publik.

## Sub-fitur: Tambah Pengurus

Menambahkan profil pengurus desa baru.

### Tujuan
Menambahkan profil pengurus desa baru beserta jabatan, organisasi, foto, dan nomor HP kontak resmi.

### Selesai bila
- Admin dapat mengisi nama dan jabatan, memilih organisasi dari daftar referensi, mengisi nomor HP, dan mengunggah foto pengurus, lalu menyimpannya.
- Untuk pengurus RT atau RW, admin dapat memilih wilayah yang diwakili dari daftar wilayah baku.
- Formulir memuat keterangan tegas bahwa nomor HP yang diisi akan ditampilkan di halaman publik.
- Foto pengurus tersimpan di R2 melalui layanan unggah terpusat dan tampil sebagai pratinjau setelah dipilih.
- Data pengurus baru muncul di daftar kelola admin beserta organisasi dan jabatannya.
- Jika foto tidak diunggah, halaman publik menampilkan gambar pengganti dan tidak menampilkan error.

## Sub-fitur: Ubah & Hapus Pengurus

Memperbarui atau menghapus data pengurus bila terjadi pergantian jabatan.

### Tujuan
Memperbarui atau menghapus data pengurus bila terjadi pergantian jabatan atau kesalahan data.

### Selesai bila
- Admin dapat mengubah nama, jabatan, organisasi, wilayah, foto, dan nomor HP pengurus, lalu perubahannya tersimpan.
- Mengganti foto pengurus menghapus foto lama dari R2 setelah foto baru berhasil tersimpan.
- Ada konfirmasi sebelum penghapusan agar pengurus tidak terhapus secara tidak sengaja.
- Penghapusan bersifat permanen sesuai **PRD §6.16**; foto pengurus ikut terhapus dari R2. Kebutuhan menyembunyikan pengurus tanpa menghapusnya dijawab oleh status tampil, bukan oleh pengarsipan.
- Pengurus yang dihapus hilang dari halaman publik dan dari daftar kelola admin.
- Perubahan yang disimpan langsung terlihat di halaman publik.

## Sub-fitur: Atur Jabatan & Organisasi

Mengelompokkan pengurus berdasarkan organisasi dan jabatan.

### Tujuan
Mengelompokkan pengurus berdasarkan organisasi dan jabatan agar struktur organisasi desa mudah dipahami pengunjung.

### Selesai bila
- Organisasi dikelola sebagai **data referensi tersendiri** (tabel `ref_organisasi`), bukan diketik bebas pada tiap pengurus, agar tidak muncul kelompok ganda akibat beda penulisan.
- Admin dapat menambah, mengubah, dan menghapus organisasi, serta mengatur **urutan kelompoknya** pada halaman publik.
- Organisasi yang masih dipakai pengurus tidak dapat dihapus, dan sistem menyebut jumlah pengurus yang memakainya.
- Formulir pengurus memilih organisasi dari daftar tersebut.
- Jabatan tetap berupa isian teks bebas, karena variasinya terlalu banyak untuk dibakukan, misalnya Kepala Desa, Sekretaris, Ketua, Wakil, atau Anggota.
- Daftar kelola admin dapat disaring per organisasi.
- Halaman publik menampilkan pengurus yang sudah dikelompokkan per organisasi dan mencantumkan jabatan masing-masing.

## Sub-fitur: Atur Urutan & Status Tampil

Menentukan susunan dan siapa saja yang muncul di halaman publik.

### Tujuan
Menentukan urutan tampil pengurus agar susunan struktur organisasi di halaman publik rapi dan sesuai hierarki, serta memilih siapa yang ditampilkan.

### Selesai bila
- Admin dapat mengubah urutan pengurus dengan memindahkan ke atas atau ke bawah, atau dengan mengisi nomor urut.
- Urutan yang disimpan langsung tercermin pada daftar pengurus di halaman publik.
- Jika tidak ada urutan khusus, pengurus tampil dalam urutan bawaan sesuai waktu ditambahkan.
- Setiap pengurus memiliki status tampil yang dapat diubah langsung dari daftar kelola.
- Pengurus berstatus sembunyi tidak muncul di halaman publik namun tetap ada di daftar admin.

## Sub-fitur: Halaman Publik Struktur Organisasi

Menampilkan susunan pengurus kepada masyarakat.

### Tujuan
Menampilkan susunan pengurus desa lengkap dengan jabatan, foto, dan kontak resmi agar warga tahu kepada siapa harus menghubungi.

### Selesai bila
- Halaman `/struktur-organisasi` menampilkan pengurus yang berstatus tampil, dikelompokkan per organisasi.
- Setiap pengurus menampilkan nama, jabatan, foto, wilayah bila ada, dan nomor HP kontak resmi.
- Nomor HP dapat langsung ditekan untuk menelepon atau menghubungi lewat WhatsApp dari ponsel.
- Urutan kelompok organisasi mengikuti kolom `urutan` pada data referensi organisasi, sehingga admin dapat menentukan sendiri kelompok mana yang tampil lebih dulu tanpa mengubah kode.
- Jika belum ada data pengurus, halaman menampilkan pesan kosong yang ramah.
- Halaman rapi dibaca pada layar ponsel maupun komputer.

## Task

### 1. Buat migrasi tabel ref_organisasi dan pengurus_desa

### 2. Buat model RefOrganisasi dan Pengurus beserta relasinya

### 3. Buat seeder organisasi dan pengurus contoh lintas organisasi

### 4. Buat halaman admin kelola organisasi dengan urutan dan proteksi hapus

### 5. Buat halaman admin daftar pengurus dengan saringan organisasi

### 6. Buat form tambah dan ubah pengurus dengan unggah foto

### 7. Buat konfirmasi hapus pengurus beserta penghapusan foto di R2

### 8. Buat kontrol urutan naik turun dan isian nomor urut

### 9. Buat aksi ubah status tampil langsung dari daftar

### 10. Buat controller dan route publik struktur organisasi

### 11. Buat tampilan publik pengurus berkelompok per organisasi

### 12. Buat tautan telepon dan WhatsApp pada nomor pengurus

### 13. Buat gambar pengganti untuk pengurus tanpa foto
