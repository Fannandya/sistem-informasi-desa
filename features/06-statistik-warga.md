# Statistik Warga (Dashboard Admin)

Menampilkan ringkasan dan grafik demografi warga desa di halaman utama panel admin.

> **Fase 2 — Data & Statistik.** Bergantung pada: [01 Autentikasi Admin](01-autentikasi-admin.md), [04 Data Penduduk](04-data-penduduk.md), [05 Rekap KK per Wilayah](05-rekap-kk-wilayah.md).
> Fitur ini hanya membaca data; tidak membuat tabel baru.

## Spesifikasi

### Tujuan
Menampilkan ringkasan dan grafik demografi warga desa di dashboard admin agar aparat desa langsung memahami kondisi kependudukan tanpa perlu membuka laporan panjang.

### Selesai bila
- Dashboard admin menampilkan kartu ringkasan total warga, jumlah KK, kelahiran, dan kematian.
- Dashboard admin menampilkan grafik sebaran umur, rasio gender, mata pencaharian, sebaran per wilayah, dan tren kelahiran–kematian per tahun.
- Seluruh angka dan grafik terisi otomatis dari data penduduk serta rekap KK, dan berubah mengikuti perubahan data.
- Seluruh angka dihitung melalui agregasi di database, bukan perulangan di PHP, agar tetap cepat pada data ribuan baris.
- Tampilan dashboard rapi, mudah dibaca, dan tetap nyaman dilihat di komputer maupun ponsel.
- Setiap grafik yang belum memiliki data menampilkan pesan kosong yang ramah, bukan grafik kosong tanpa keterangan.

## Sub-fitur: Ringkasan Jumlah

Melihat total warga, jumlah KK, kelahiran, dan kematian secara cepat.

### Tujuan
Memberi aparat desa empat angka kunci kondisi kependudukan dalam sekali pandang.

### Selesai bila
- Terdapat empat kartu ringkasan berlabel jelas: "Total Warga", "Jumlah KK", "Kelahiran", dan "Kematian".
- "Total Warga" berisi jumlah warga aktif, yaitu yang belum diarsipkan.
- "Jumlah KK" berisi penjumlahan seluruh rekap KK per RT untuk **tahun rekap terakhir yang memiliki data** sesuai PRD §2.9, mencantumkan tahunnya, dan menampilkan penanda bila masih ada RT yang belum diisi. Angka ini wajib identik dengan yang tampil di halaman statistik publik.
- "Kelahiran" berisi jumlah warga dengan cara masuk "Lahir" pada tahun berjalan, bukan sekadar jumlah data yang ditambahkan.
- "Kematian" berisi jumlah warga terarsip dengan alasan keluar "Meninggal" pada tahun berjalan, bukan sekadar jumlah data yang dihapus.
- Setiap kartu mencantumkan keterangan periode, misalnya "tahun 2026", agar angkanya tidak disalahartikan.
- Angka diperbarui otomatis setiap kali data warga atau rekap KK berubah.

## Sub-fitur: Grafik Sebaran Umur

Mengetahui komposisi penduduk berdasarkan kelompok umur.

### Tujuan
Mengetahui komposisi penduduk berdasarkan kelompok umur dalam bentuk grafik.

### Selesai bila
- Dashboard menampilkan grafik batang memakai kelompok umur baku pada **PRD §2.8**, tanpa mendefinisikan rentang sendiri.
- Umur dihitung dari tanggal lahir terhadap tanggal hari ini saat grafik dimuat, sehingga komposisi bergeser sendiri seiring waktu tanpa perlu memperbarui data.
- Setiap batang menampilkan jumlah warga dan persentasenya terhadap total warga aktif.
- Grafik otomatis menyesuaikan saat data warga berubah.
- Warga tanpa tanggal lahir masuk kelompok "Tidak Diketahui" dan tidak merusak perhitungan kelompok lain; kelompok itu hanya tampil bila jumlahnya lebih dari nol.

## Sub-fitur: Rasio Gender

Melihat perbandingan jumlah warga laki-laki dan perempuan.

### Tujuan
Melihat perbandingan jumlah warga laki-laki dan perempuan.

### Selesai bila
- Dashboard menampilkan grafik proporsi rasio gender dengan dua angka: laki-laki dan perempuan.
- Tampil perbandingan yang mudah dipahami, misalnya "L 52% : P 48%", disertai jumlah absolutnya.
- Angka rasio dihitung dari seluruh warga aktif dan diperbarui otomatis saat data berubah.
- Bila belum ada data warga, bagian ini menampilkan pesan kosong dan tidak menampilkan pembagian nol.

## Sub-fitur: Mata Pencaharian

Melihat sebaran jenis pekerjaan warga desa.

### Tujuan
Mengetahui komposisi mata pencaharian warga sebagai dasar perencanaan program ekonomi desa.

### Selesai bila
- Dashboard menampilkan grafik batang horizontal jumlah warga per jenis pekerjaan, diurutkan dari yang terbanyak.
- Grafik menampilkan sepuluh pekerjaan terbanyak, dan sisanya digabung sebagai "Lainnya".
- Warga yang belum memiliki data pekerjaan dikelompokkan sebagai "Belum diisi" dan jumlahnya terlihat.
- Perhitungan hanya melibatkan warga aktif.
- Setiap batang menampilkan jumlah warga dan persentasenya.

## Sub-fitur: Sebaran Per Wilayah

Menampilkan jumlah warga per Dusun, RW, dan RT.

### Tujuan
Menampilkan jumlah warga per wilayah agar aparat desa mengetahui sebaran penduduk antarwilayah.

### Selesai bila
- Dashboard menampilkan tabel jumlah warga per wilayah berdasarkan data wilayah baku, sehingga tidak ada nama wilayah ganda.
- Tabel menampilkan jumlah warga, jumlah KK, dan rata-rata jiwa per KK untuk setiap wilayah.
- Admin dapat memilih tingkat tampilan: per Dusun, per RW, atau per RT.
- Tabel dapat diurutkan dari jumlah warga terbesar ke terkecil maupun sebaliknya.
- Wilayah yang terdaftar namun belum memiliki warga tetap tampil dengan angka nol, bukan hilang dari daftar.

## Sub-fitur: Tren Kelahiran & Kematian

Melihat perkembangan kelahiran dan kematian dari tahun ke tahun.

### Tujuan
Menampilkan perkembangan kelahiran dan kematian per tahun agar aparat desa melihat arah pertumbuhan penduduk, bukan hanya angka tahun berjalan.

### Selesai bila
- Dashboard menampilkan grafik garis atau batang berpasangan untuk kelahiran dan kematian selama lima tahun terakhir.
- Grafik juga menampilkan pindah masuk dan pindah keluar sebagai pembanding, sehingga pertumbuhan penduduk terbaca utuh.
- Warga yang diarsipkan dengan alasan "Duplikat" tidak muncul pada grafik ini.
- Tahun tanpa kejadian tetap tampil dengan nilai nol, tidak dilewati.
- Admin dapat mengubah rentang tahun yang ditampilkan.

## Task

### 1. Buat layout dashboard statistik dengan kisi responsif

### 2. Buat kelas layanan perhitungan statistik dengan agregasi SQL

### 3. Buat empat kartu ringkasan total warga KK kelahiran kematian

### 4. Buat penanda rekap KK belum lengkap pada kartu jumlah KK

### 5. Buat endpoint JSON sebaran umur berdasarkan tanggal lahir

### 6. Buat grafik sebaran umur dengan Chart.js

### 7. Buat endpoint JSON rasio gender

### 8. Buat tampilan rasio gender dengan persentase dan jumlah

### 9. Buat endpoint JSON mata pencaharian sepuluh terbanyak

### 10. Buat grafik batang horizontal mata pencaharian

### 11. Buat endpoint JSON sebaran wilayah bertingkat dusun RW RT

### 12. Buat tabel sebaran wilayah dengan pengurutan dan pemilih tingkat

### 13. Buat endpoint JSON tren kelahiran kematian lima tahun

### 14. Buat grafik tren kelahiran kematian dan perpindahan

### 15. Buat pesan kosong ramah untuk setiap grafik tanpa data

### 16. Tambahkan index database untuk kolom yang diagregasi
