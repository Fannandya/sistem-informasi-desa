# Rekap KK per Wilayah

Menginput jumlah Kepala Keluarga tiap RT sebagai sumber angka "Jumlah KK".

> **Fase 2 — Data & Statistik.** Bergantung pada: [01 Autentikasi Admin](01-autentikasi-admin.md), [04 Data Penduduk](04-data-penduduk.md) untuk daftar wilayah.
> Menjadi satu-satunya sumber angka Jumlah KK di [06 Statistik Warga](06-statistik-warga.md) dan [15 Statistik Publik](15-statistik-publik.md).

## Spesifikasi

### Tujuan
Menyediakan angka Jumlah Kepala Keluarga tanpa menyimpan Nomor Kartu Keluarga, dengan cara admin menginput jumlah KK tiap RT lalu sistem menjumlahkannya untuk seluruh desa.

### Selesai bila
- Admin dapat menginput dan memperbarui jumlah KK untuk setiap RT pada tahun tertentu.
- Sistem menjumlahkan seluruh angka RT menjadi total KK desa dan menampilkannya di dashboard.
- Satu RT hanya boleh memiliki satu angka rekap per tahun.
- Sistem tidak pernah meminta atau menyimpan Nomor Kartu Keluarga.
- Terlihat jelas RT mana saja yang belum diisi pada tahun berjalan, agar total tidak diam-diam kurang.

## Sub-fitur: Input Rekap KK per RT

Mengisi jumlah KK setiap RT dalam satu halaman.

### Tujuan
Memudahkan admin mengisi jumlah KK seluruh RT sekaligus tanpa harus membuka formulir satu per satu.

### Selesai bila
- Halaman rekap menampilkan seluruh RT yang terdaftar pada tabel wilayah, dikelompokkan per Dusun dan RW.
- Setiap baris RT memiliki satu kolom isian angka jumlah KK yang dapat langsung diketik.
- Admin dapat menyimpan seluruh isian dalam satu kali penyimpanan.
- Isian hanya menerima bilangan bulat nol atau lebih; nilai negatif dan huruf ditolak dengan pesan jelas.
- RT baru yang ditambahkan pada data wilayah otomatis muncul di halaman rekap dengan isian kosong.

## Sub-fitur: Rekap per Tahun

Menyimpan angka KK terpisah untuk setiap tahun.

### Tujuan
Menyimpan riwayat jumlah KK per tahun agar perkembangan jumlah keluarga desa dapat ditelusuri.

### Selesai bila
- Halaman rekap memiliki pemilih tahun, dan secara bawaan menampilkan tahun berjalan.
- Menyimpan rekap pada satu tahun tidak mengubah angka tahun lainnya.
- Satu RT tidak dapat memiliki dua angka rekap pada tahun yang sama.
- Tersedia tombol untuk menyalin seluruh angka tahun sebelumnya sebagai titik awal pengisian tahun baru, yang masih bisa disunting sebelum disimpan.
- Waktu pembaruan terakhir dan nama admin penginput tercatat dan terlihat pada halaman.

## Sub-fitur: Total & Kelengkapan Rekap

Menampilkan total KK dan menandai wilayah yang belum diisi.

### Tujuan
Memastikan total KK yang tampil di dashboard dapat dipercaya, dengan menunjukkan secara terbuka bagian mana yang belum lengkap.

### Selesai bila
- Halaman rekap menampilkan subtotal KK per Dusun dan total keseluruhan desa.
- RT yang belum memiliki angka pada tahun terpilih ditandai jelas dan dihitung sebagai "belum diisi", bukan sebagai nol.
- Halaman menampilkan keterangan kelengkapan, misalnya "38 dari 42 RT sudah diisi".
- Bila masih ada RT yang belum diisi, kartu Jumlah KK di dashboard menampilkan penanda bahwa data belum lengkap.
- Rata-rata jiwa per KK ditampilkan sebagai angka bantu, dihitung dari total warga aktif dibagi total KK.

## Task

### 1. Buat migrasi tabel rekap_kk dengan unique wilayah dan tahun

### 2. Buat model RekapKk beserta relasi wilayah dan admin

### 3. Buat seeder rekap KK contoh untuk tahun berjalan

### 4. Buat halaman admin rekap KK berkelompok per dusun dan RW

### 5. Buat pemilih tahun pada halaman rekap

### 6. Buat penyimpanan massal seluruh isian dalam satu aksi

### 7. Buat validasi angka bulat non negatif dan cegah duplikat tahun

### 8. Buat perhitungan subtotal per dusun dan total desa

### 9. Buat penanda RT belum diisi dan keterangan kelengkapan

### 10. Buat aksi salin rekap dari tahun sebelumnya

### 11. Buat perhitungan rata-rata jiwa per KK
