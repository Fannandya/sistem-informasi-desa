# Ekspor Data Penduduk

Mengunduh data penduduk dalam format Excel/CSV untuk kebutuhan laporan.

> **Fase 2 — Data & Statistik.** Bergantung pada: [04 Data Penduduk](04-data-penduduk.md).
> Seluruh tugas ekspor berada di file ini; file Data Penduduk tidak lagi memuat tugas ekspor.

## Spesifikasi

### Tujuan
Memungkinkan admin mengunduh data penduduk dalam format Excel/CSV untuk keperluan laporan dan analisis, tanpa membawa serta data yang dilarang.

### Selesai bila
- Admin dapat menekan tombol ekspor di halaman data penduduk dan berkas berhasil diunduh.
- Berkas yang diunduh hanya berisi kolom yang diizinkan: Nama Lengkap, Umur, Jenis Kelamin, Dusun, RW, RT, Pekerjaan, dan Pendidikan.
- Admin dapat memilih format unduhan `.xlsx` atau `.csv` sebelum mengunduh.
- Berkas ekspor tidak pernah memuat NIK, Nomor Kartu Keluarga, nomor telepon, alamat rumah, maupun tanggal lahir mentah.
- Proses ekspor tidak membuat halaman berhenti tanpa keterangan; admin melihat indikator bahwa berkas sedang disiapkan.

## Sub-fitur: Ekspor Semua Data

Mengunduh seluruh data penduduk aktif dalam satu berkas.

### Tujuan
Mengunduh seluruh data penduduk desa yang berstatus aktif dalam satu berkas.

### Selesai bila
- Tombol "Ekspor Semua" tersedia dan mengunduh berkas berisi seluruh warga aktif.
- Jumlah baris pada berkas sama dengan jumlah warga aktif, tidak termasuk warga yang diarsipkan.
- Berkas dapat dibuka tanpa peringatan kerusakan pada Excel maupun LibreOffice.
- **Baris pertama berkas berisi judul kolom**, tanpa baris keterangan apa pun di atasnya — agar berkas hasil ekspor tetap bisa dipakai kembali sebagai berkas impor dan tidak merusak pembacaan CSV.
- Judul kolom ditulis dalam bahasa Indonesia yang mudah dipahami dan sama persis dengan judul kolom pada berkas contoh impor.
- Keterangan nama desa dan tanggal unduh diletakkan pada **nama berkas**, bukan di dalam isinya.

## Sub-fitur: Ekspor Hasil Saringan

Mengunduh data penduduk yang sedang disaring.

### Tujuan
Mengunduh hanya data penduduk yang sesuai saringan aktif, misalnya warga satu RT atau satu kelompok umur saja.

### Selesai bila
- Saat admin menerapkan saringan, tombol ekspor mengunduh berkas yang hanya berisi data sesuai saringan tersebut.
- Seluruh saringan yang aktif ikut diterapkan, termasuk kata kunci pencarian.
- Nama berkas mencantumkan saringan yang dipakai sehingga berbeda dari ekspor semua data, misalnya `penduduk-dusun-krajan-rt-002-2026-08-04.xlsx`.
- Jumlah baris pada berkas sama dengan jumlah hasil saringan yang tampil di layar.
- Bila hasil saringan kosong, ekspor dibatalkan dengan pesan bahwa tidak ada data untuk diunduh.

## Sub-fitur: Ekspor Data Arsip

Mengunduh data warga terarsip untuk keperluan laporan mutasi.

### Tujuan
Menyediakan berkas laporan warga yang keluar dari data aktif beserta sebabnya, untuk kebutuhan laporan mutasi penduduk.

### Selesai bila
- Halaman Arsip Warga memiliki tombol ekspor tersendiri.
- Berkas arsip memuat kolom yang diizinkan ditambah alasan keluar dan tanggal keluar.
- Saringan alasan keluar dan rentang tanggal pada halaman arsip ikut diterapkan pada berkas.
- Nama berkas menandai bahwa isinya adalah data arsip.

## Sub-fitur: Pilih Format Berkas

Memilih mengunduh data sebagai `.xlsx` atau `.csv`.

### Tujuan
Memberi admin pilihan format berkas unduhan sesuai kebutuhan pengolahan lanjutan.

### Selesai bila
- Terdapat pilihan format berupa dropdown atau tombol terpisah saat akan mengekspor data.
- Berkas yang diunduh sesuai format yang dipilih, dan isi datanya sama persis antar format.
- Berkas `.csv` menggunakan pengodean UTF-8 dengan BOM agar huruf beraksen tampil benar saat dibuka di Excel.
- Bila jumlah data melebihi 10.000 baris, sistem mengarahkan admin memakai format `.csv` dan menjelaskan alasannya.

## Sub-fitur: Batasi Kolom Ekspor

Memastikan berkas ekspor tidak pernah membawa data terlarang.

### Tujuan
Menjamin berkas yang keluar dari sistem tidak memuat data yang dilarang disimpan maupun disebarkan.

### Selesai bila
- Daftar kolom ekspor ditetapkan secara eksplisit dalam kode, bukan diambil dari seluruh kolom tabel.
- Menambah kolom baru pada tabel penduduk tidak membuat kolom itu ikut terekspor dengan sendirinya.
- Kolom umur diekspor sebagai angka hasil hitungan; tanggal lahir tidak pernah ikut terekspor.
- Terdapat pengujian otomatis yang memeriksa judul kolom berkas hasil ekspor dan gagal bila muncul kolom terlarang.

## Task

### 1. Buat kelas ekspor penduduk dengan daftar kolom eksplisit

### 2. Buat kolom umur terhitung dan pastikan tanggal lahir tidak ikut

### 3. Buat tombol dan dropdown pilihan format pada halaman penduduk

### 4. Buat endpoint ekspor seluruh warga aktif

### 5. Buat endpoint ekspor mengikuti saringan dan pencarian aktif

### 6. Buat penamaan berkas yang mencerminkan saringan dan tanggal

### 7. Buat endpoint ekspor data arsip beserta alasan keluar

### 8. Terapkan dukungan format xlsx dan csv UTF-8 dengan BOM

### 9. Buat pengalihan ke csv untuk data di atas sepuluh ribu baris

### 10. Buat indikator proses dan penanganan hasil saringan kosong

### 11. Buat pengujian otomatis judul kolom hasil ekspor
