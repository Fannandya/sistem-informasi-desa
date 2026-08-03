# Statistik Publik

Menampilkan statistik demografi desa secara agregat untuk transparansi kepada warga.

> **Fase 4 — Etalase Publik.** Bergantung pada: [04 Data Penduduk](04-data-penduduk.md), [05 Rekap KK per Wilayah](05-rekap-kk-wilayah.md), [06 Statistik Warga](06-statistik-warga.md), [14 Halaman Publik Desa](14-halaman-publik-desa.md).
>
> Fitur ini tunduk penuh pada **aturan perlindungan §2.6 PRD**. Bila ragu antara menampilkan angka atau menyembunyikannya, sembunyikan.

## Spesifikasi

### Tujuan
Menampilkan gambaran demografi desa kepada masyarakat sebagai bentuk keterbukaan informasi, tanpa memungkinkan siapa pun mengenali individu dari angka yang ditampilkan.

### Selesai bila
- Halaman `/statistik` menampilkan ringkasan jumlah penduduk, jumlah KK, dan grafik demografi dasar.
- Seluruh angka yang ditampilkan berbentuk agregat; tidak ada satu pun nama warga, tanggal lahir, atau baris data individu yang terkirim ke peramban pengunjung.
- Statistik hanya ditampilkan sampai tingkat Desa dan Dusun, tidak pernah sampai tingkat RT.
- Kelompok berjumlah kurang dari 5 warga tidak ditampilkan sebagai angka pasti.
- Halaman mencantumkan tanggal pembaruan data dan sumber datanya.
- Angka pada halaman publik selalu konsisten dengan angka pada dashboard admin untuk tingkat agregat yang sama.

## Sub-fitur: Ringkasan Penduduk Publik

Menampilkan angka pokok kependudukan desa.

### Tujuan
Memberi warga gambaran cepat tentang besaran penduduk desanya.

### Selesai bila
- Halaman menampilkan kartu ringkasan: Jumlah Penduduk, Jumlah Kepala Keluarga, Jumlah Laki-laki, dan Jumlah Perempuan.
- Jumlah Penduduk berisi total warga aktif; Jumlah KK berisi penjumlahan rekap KK seluruh RT untuk **tahun rekap terakhir yang memiliki data** sesuai PRD §2.9.
- Ditampilkan pula angka rata-rata jiwa per keluarga.
- Setiap kartu mencantumkan keterangan periode data, misalnya "data tahun 2026".
- Angka Jumlah KK di halaman ini **identik** dengan yang tampil di dashboard admin. Perbedaannya hanya pada penanda "belum lengkap", yang muncul di dashboard admin saja dan tidak ditampilkan ke publik.
- Bila belum ada satu pun rekap KK, kartu Jumlah KK tidak ditampilkan sama sekali, bukan menampilkan angka nol.

## Sub-fitur: Grafik Demografi Publik

Menampilkan komposisi umur, gender, dan pendidikan warga.

### Tujuan
Menampilkan komposisi penduduk desa dalam bentuk grafik yang mudah dipahami warga awam.

### Selesai bila
- Halaman menampilkan grafik sebaran kelompok umur, rasio gender, dan tingkat pendidikan.
- Kelompok umur memakai definisi baku **PRD §2.8**, sama persis dengan dashboard admin, sehingga kedua halaman tidak pernah menampilkan pengelompokan yang berbeda.
- Setiap grafik menampilkan persentase; jumlah absolut hanya ditampilkan bila kelompoknya berisi 5 warga atau lebih.
- Kelompok berjumlah kurang dari 5 ditampilkan sebagai "<5" atau digabung ke kategori "Lainnya", tidak pernah sebagai angka pasti.
- Tidak ada grafik yang menyilangkan lebih dari satu dimensi sekaligus, misalnya umur dikali gender dikali pekerjaan.
- Grafik terbaca jelas pada layar ponsel dan memiliki keterangan kategori.
- Setiap grafik memiliki padanan tabel teks agar tetap dapat dibaca pembaca layar dan pengguna dengan keterbatasan penglihatan.

## Sub-fitur: Sebaran per Dusun

Menampilkan persebaran penduduk antarwilayah.

### Tujuan
Menunjukkan persebaran penduduk antar dusun agar warga memahami komposisi wilayah desanya.

### Selesai bila
- Halaman menampilkan tabel jumlah penduduk dan jumlah KK per Dusun.
- Data tidak pernah dirinci sampai tingkat RW maupun RT pada halaman publik.
- Dusun dengan jumlah penduduk kurang dari 5 digabung menjadi baris "Lainnya".
- Tabel menampilkan persentase setiap dusun terhadap total penduduk desa.
- Tabel dapat digulir mendatar pada layar sempit tanpa merusak tata letak halaman.

## Sub-fitur: Perlindungan Data Agregat

Menjamin angka publik tidak dapat dipakai mengenali individu.

### Tujuan
Menjamin bahwa tidak ada kombinasi angka pada halaman publik yang memungkinkan seseorang menyimpulkan data warga tertentu.

### Selesai bila
- Aturan penyembunyian kelompok kecil diterapkan di lapisan data, bukan hanya disembunyikan di tampilan, sehingga angka aslinya tidak ikut terkirim ke peramban.
- Endpoint JSON statistik publik hanya mengembalikan data yang sudah diagregasi dan sudah disaring; memanggilnya langsung tidak memberi data lebih rinci daripada yang tampil di layar.
- Endpoint statistik publik tidak menerima parameter yang dapat mempersempit hasil sampai tingkat RT atau individu.
- Terdapat pengujian otomatis yang memverifikasi bahwa respons statistik publik tidak pernah memuat nama, tanggal lahir, maupun ID warga.
- Terdapat pengujian otomatis yang memverifikasi aturan penyembunyian kelompok kurang dari 5 benar-benar berjalan.

## Task

### 1. Buat layanan statistik publik dengan aturan agregasi terbatas

### 2. Terapkan penyembunyian kelompok di bawah lima di lapisan data

### 3. Buat endpoint JSON ringkasan penduduk publik

### 4. Buat endpoint JSON sebaran umur, gender, dan pendidikan publik

### 5. Buat endpoint JSON sebaran per dusun

### 6. Buat halaman publik statistik dengan kartu ringkasan

### 7. Buat grafik demografi publik dengan Chart.js

### 8. Buat padanan tabel teks untuk setiap grafik

### 9. Buat tabel sebaran per dusun dengan persentase

### 10. Tampilkan keterangan periode dan tanggal pembaruan data

### 11. Buat penanganan rekap KK tahun berjalan yang belum lengkap

### 12. Buat pengujian otomatis larangan data individu pada respons publik

### 13. Buat pengujian otomatis aturan penyembunyian kelompok kecil

### 14. Buat bagian cuplikan statistik pada landing page
