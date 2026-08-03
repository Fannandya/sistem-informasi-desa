# Data Penduduk

Mencatat dan memperbarui data dasar warga tanpa menyimpan identitas rahasia.

> **Fase 2 — Data & Statistik.** Bergantung pada: [01 Autentikasi Admin](01-autentikasi-admin.md).
> Menjadi sumber data untuk [06 Statistik Warga](06-statistik-warga.md), [07 Ekspor Data Penduduk](07-ekspor-data-penduduk.md), dan [15 Statistik Publik](15-statistik-publik.md).

## Spesifikasi

### Tujuan
Mencatat dan memperbarui data dasar warga tanpa menyimpan identitas rahasia, sekaligus mencatat sebab warga masuk dan keluar agar statistik kelahiran dan kematian terhitung benar.

### Selesai bila
- Admin dapat melihat daftar warga aktif dengan kolom: Nama Lengkap, Umur, Jenis Kelamin, Wilayah (Dusun/RW/RT), Pekerjaan, dan Pendidikan.
- Admin dapat menambah, mengubah, dan mengarsipkan data warga melalui antarmuka yang mudah digunakan.
- Setiap warga baru wajib memiliki keterangan cara masuk, dan setiap pengarsipan wajib memiliki alasan keluar.
- Data warga yang diarsipkan tidak hilang permanen dan dapat dilihat serta dipulihkan admin.
- Sistem tidak pernah meminta, menyimpan, atau menampilkan NIK, Nomor Kartu Keluarga, nomor telepon warga, maupun alamat rumah detail.
- Umur tidak pernah disimpan sebagai angka; umur selalu dihitung dari tanggal lahir terhadap tanggal hari ini.

## Sub-fitur: Kelola Wilayah

Mengelola daftar Dusun, RW, dan RT sebagai data referensi.

### Tujuan
Menyediakan daftar wilayah baku agar tidak ada penulisan nama dusun atau nomor RT yang berbeda-beda untuk wilayah yang sama.

### Selesai bila
- Admin dapat menambah, mengubah, dan menghapus kombinasi Dusun/RW/RT.
- Sistem menolak penyimpanan kombinasi Dusun/RW/RT yang sudah ada.
- Wilayah yang masih dipakai oleh data warga atau rekap KK tidak dapat dihapus, dan sistem menjelaskan alasannya.
- Formulir warga memilih wilayah dari daftar ini, bukan mengetik bebas.
- Admin dapat mengatur urutan tampil wilayah agar daftar statistik terbaca rapi.

## Sub-fitur: Kelola Daftar Pekerjaan

Mengelola daftar mata pencaharian sebagai data referensi.

### Tujuan
Menyediakan daftar pekerjaan baku agar grafik mata pencaharian tidak pecah karena beda penulisan, misalnya "Petani" dan "petani" terhitung sebagai dua kategori.

### Selesai bila
- Admin dapat menambah, mengubah, dan menghapus jenis pekerjaan.
- Sistem menolak penyimpanan nama pekerjaan yang sudah ada, tanpa memandang besar-kecil huruf.
- Pekerjaan yang masih dipakai data warga tidak dapat dihapus, dan sistem menyebut jumlah warga yang memakainya.
- Formulir warga memilih pekerjaan dari daftar ini, bukan mengetik bebas.
- Kolom pekerjaan boleh dikosongkan pada data warga, dan warga tersebut terhitung sebagai "Belum diisi" pada statistik.

## Sub-fitur: Tambah Warga

Mengisi data warga baru lewat formulir sederhana.

### Tujuan
Mengisi data warga baru melalui formulir sederhana, lengkap dengan keterangan cara warga tersebut tercatat.

### Selesai bila
- Formulir tambah warga hanya menampilkan kolom yang diizinkan: Nama Lengkap, Tanggal Lahir, Jenis Kelamin, Wilayah, Pekerjaan, dan Pendidikan.
- Tanggal lahir wajib diisi pada formulir manual, meski kolomnya boleh kosong di database untuk menampung data hasil impor.
- Formulir memuat pilihan cara masuk: Lahir, Pindah Masuk, atau Pendataan Awal, beserta tanggal kejadiannya.
- Saat memilih "Lahir", tanggal masuk otomatis terisi sama dengan tanggal lahir dan masih bisa diubah admin.
- Setelah tanggal lahir diisi, formulir menampilkan umur hasil hitungan sebagai konfirmasi bagi admin.
- Data yang dikirim tersimpan dan langsung muncul di daftar warga.
- Kolom wajib divalidasi; tanggal lahir tidak boleh melebihi hari ini dan tanggal masuk tidak boleh lebih awal dari tanggal lahir.

## Sub-fitur: Ubah Warga

Memperbarui data warga bila ada perubahan.

### Tujuan
Memperbarui data warga saat terjadi perubahan pekerjaan, pendidikan, wilayah, atau koreksi penulisan.

### Selesai bila
- Formulir ubah terisi dengan data lama dan perubahan dapat disimpan.
- Perubahan wilayah warga langsung tercermin pada statistik sebaran wilayah.
- Perubahan tanggal lahir langsung mengubah umur dan kelompok umur warga tersebut.
- Data mutasi masuk hanya dapat diubah bila warga masih berstatus aktif.

## Sub-fitur: Arsipkan Warga dengan Alasan

Mengarsipkan warga yang sudah tidak aktif, disertai sebabnya.

### Tujuan
Mengeluarkan warga dari daftar aktif tanpa menghapus datanya, dengan mencatat sebab keluar agar statistik kematian tidak tercampur dengan kepindahan.

### Selesai bila
- Saat mengarsipkan, muncul dialog konfirmasi yang mewajibkan admin memilih alasan keluar: Meninggal, Pindah Keluar, atau Duplikat, beserta tanggal kejadian.
- Warga yang diarsipkan tidak lagi tampil di daftar warga aktif dan tidak ikut terhitung sebagai total warga.
- Hanya warga dengan alasan "Meninggal" yang dihitung sebagai kematian pada statistik.
- Warga dengan alasan "Duplikat" tidak dihitung pada statistik kematian maupun kepindahan.
- Tanggal keluar tidak boleh lebih awal dari tanggal masuk.

## Sub-fitur: Arsip & Pemulihan Warga

Melihat data warga yang telah diarsipkan dan memulihkannya bila salah.

### Tujuan
Memberi admin cara memeriksa dan membatalkan pengarsipan yang keliru, agar data tidak hilang karena salah klik.

### Selesai bila
- Tersedia halaman Arsip Warga yang menampilkan warga terarsip beserta alasan dan tanggal keluarnya.
- Halaman arsip dapat dicari dan disaring berdasarkan alasan keluar serta rentang tanggal.
- Admin dapat memulihkan warga terarsip kembali menjadi warga aktif melalui konfirmasi.
- Warga yang dipulihkan kehilangan data alasan dan tanggal keluar, serta kembali terhitung pada statistik.
- Data terarsip tidak dapat diubah isinya selama masih berstatus arsip.

## Sub-fitur: Cari, Saring, & Halaman Data

Menemukan warga dengan cepat pada data berjumlah ribuan.

### Tujuan
Menemukan warga dengan cepat berdasarkan nama, wilayah, atau kategori demografi, tanpa membuat halaman menjadi berat.

### Selesai bila
- Admin dapat mengetik nama warga untuk mencari data yang cocok, termasuk pencocokan sebagian kata.
- Admin dapat menyaring warga berdasarkan Dusun, RW, RT, jenis kelamin, kelompok umur, pekerjaan, dan pendidikan.
- Beberapa saringan dapat digunakan bersamaan, dan saringan yang aktif terlihat jelas serta bisa dihapus satu per satu.
- Daftar warga menggunakan pagination dengan 50 baris per halaman; tidak pernah memuat seluruh data sekaligus.
- Kombinasi pencarian, saringan, dan halaman tetap bertahan pada alamat URL sehingga bisa disalin atau dimuat ulang.
- Jumlah hasil yang cocok ditampilkan di atas daftar.

## Sub-fitur: Impor Data Warga

Mengisi data warga secara massal dari file Excel/CSV.

### Tujuan
Mengisi data warga secara massal dari file Excel/CSV pada saat pendataan awal, tanpa memasukkan data ganda.

### Selesai bila
- Admin dapat mengunduh berkas contoh (template) berisi judul kolom yang benar sebelum mengimpor.
- Admin dapat mengunggah berkas `.xlsx` atau `.csv` dengan batas 2.000 baris dan 5 MB per berkas; berkas yang melebihi batas ditolak dengan pesan yang menyebut batasnya.
- Berkas yang memuat kolom terlarang seperti NIK, Nomor KK, atau nomor telepon ditolak sebelum satu baris pun diproses.
- Baris yang valid masuk ke daftar warga; baris yang tidak valid ditampilkan kembali beserta nomor baris dan alasan kegagalannya.
- Sistem mendeteksi kemungkinan duplikat menggunakan kombinasi Nama Lengkap + Tanggal Lahir + Wilayah, lalu menampilkannya sebagai daftar konfirmasi; admin memutuskan melewati atau tetap mengimpor.
- Seluruh baris yang berhasil diimpor tercatat dengan cara masuk "Pendataan Awal" kecuali berkas menyebut lain, dengan tanggal masuk bawaan sama dengan tanggal impor dijalankan.
- Baris tanpa tanggal lahir tetap boleh diimpor; warga tersebut masuk kelompok umur "Tidak Diketahui" pada statistik hingga tanggal lahirnya dilengkapi admin.
- Baris yang menyebut wilayah atau pekerjaan di luar daftar referensi ditolak dan dilaporkan, bukan diam-diam membuat data referensi baru.
- Bila proses impor gagal di tengah jalan, tidak ada data separuh jadi yang tersimpan.

## Sub-fitur: Jaga Privasi Data

Memastikan identitas rahasia tidak pernah masuk ke sistem.

### Tujuan
Memastikan NIK, Nomor Kartu Keluarga, nomor telepon warga, dan alamat rumah detail tidak diminta, disimpan, diimpor, maupun diekspor.

### Selesai bila
- Tidak ada formulir, kolom tabel database, hasil impor, atau berkas ekspor yang memuat NIK, Nomor KK, nomor telepon warga, atau alamat rumah detail.
- Kolom data warga yang tersedia hanya Nama Lengkap, Tanggal Lahir, Jenis Kelamin, Wilayah, Pekerjaan, Pendidikan, dan data mutasi.
- Tanggal lahir tidak pernah dikirim ke halaman publik dalam bentuk apa pun; halaman publik hanya menerima angka agregat.
- Terdapat pengujian otomatis yang gagal bila kolom terlarang muncul di skema tabel penduduk atau di hasil ekspor.

## Task

### 1. Buat migrasi tabel wilayah dan ref_pekerjaan

### 2. Buat migrasi tabel penduduk dengan kolom mutasi dan soft delete

### 3. Buat model Wilayah, RefPekerjaan, dan Penduduk beserta relasinya

### 4. Buat accessor umur dan kelompok umur dari tanggal lahir

### 5. Buat scope query warga aktif, kelahiran, dan kematian

### 6. Buat seeder wilayah, pekerjaan, dan data warga contoh

### 7. Buat halaman admin kelola wilayah dengan proteksi hapus

### 8. Buat halaman admin kelola pekerjaan dengan proteksi hapus

### 9. Buat halaman daftar warga dengan pagination lima puluh baris

### 10. Buat form tambah warga dengan pratinjau umur dan validasi

### 11. Buat form ubah warga

### 12. Buat dialog arsipkan warga dengan pilihan alasan keluar

### 13. Buat halaman arsip warga dengan saringan alasan dan tanggal

### 14. Buat aksi pemulihan warga terarsip

### 15. Buat pencarian dan saringan gabungan yang tersimpan di URL

### 16. Buat unduhan berkas contoh impor

### 17. Buat antarmuka impor dengan laporan baris gagal

### 18. Buat proses impor dengan deteksi duplikat dan transaksi

### 19. Buat penolakan berkas impor yang memuat kolom terlarang

### 20. Buat pengujian otomatis larangan kolom privasi
