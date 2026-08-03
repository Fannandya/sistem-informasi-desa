# Halaman Publik Desa

Landing page satu halaman sebagai etalase desa, dengan navigasi ke halaman khusus tiap topik.

> **Fase 4 — Etalase Publik.** Bergantung pada: [03 Pengaturan Desa](03-pengaturan-identitas-desa.md), [08 Struktur Organisasi](08-struktur-organisasi-desa.md), [09 Potensi Desa](09-potensi-desa.md), [10 Fasilitas Umum](10-fasilitas-umum.md), [11 Prestasi Desa](11-prestasi-desa.md), [12 Berita Desa](12-berita-desa.md), [13 Galeri Acara](13-galeri-acara-desa.md).
>
> **Lingkup file ini hanya sisi publik:** layout, navigasi, landing page, dan halaman Profil Desa. Halaman khusus tiap topik (potensi, fasilitas, prestasi, berita, galeri, struktur organisasi) dibangun di file fiturnya masing-masing; file ini hanya menyediakan kerangka dan jalur navigasinya.

## Spesifikasi

### Tujuan
Menyajikan seluruh wajah desa dalam satu halaman muka yang mengalir, di mana pengunjung dapat memindai semua bagian sekaligus lalu masuk lebih dalam ke topik yang menarik minatnya melalui tombol navigasi.

### Selesai bila
- Halaman muka `/` berbentuk satu halaman panjang berisi seluruh bagian: hero identitas, profil singkat, statistik, potensi unggulan, berita terbaru, galeri kegiatan, fasilitas & prestasi, struktur organisasi, dan kontak.
- Setiap bagian memiliki tombol yang mengarahkan ke halaman khusus yang lebih lengkap.
- Seluruh isi halaman publik berasal dari data yang dikelola admin dan berubah mengikuti pembaruan terbaru.
- Pengunjung dapat membuka seluruh halaman publik tanpa login.
- Halaman publik tidak pernah menampilkan nama warga, tanggal lahir, atau data per individu.
- Seluruh halaman publik rapi dan terbaca pada lebar layar 360 piksel sampai 1440 piksel.

## Sub-fitur: Kerangka & Navigasi Publik

Menyediakan layout, menu, dan footer yang sama untuk seluruh halaman publik.

### Tujuan
Memberi seluruh halaman publik kerangka yang konsisten sehingga pengunjung tidak pernah tersesat.

### Selesai bila
- Terdapat satu layout publik berisi kop dengan logo dan nama desa, menu navigasi, serta footer.
- Menu navigasi memuat tautan ke Beranda, Profil, Potensi, Fasilitas, Prestasi, Berita, Galeri, Struktur Organisasi, dan Statistik.
- Pada layar ponsel, menu berubah menjadi tombol hamburger yang dapat dibuka dan ditutup.
- Kop menempel di bagian atas saat halaman digulir, sehingga navigasi selalu terjangkau.
- Menu menandai halaman yang sedang dibuka agar posisi pengunjung jelas.
- Footer menampilkan alamat kantor, telepon, email, tautan media sosial, dan tautan cepat ke seluruh halaman.
- Logo dan nama desa pada kop diambil dari Pengaturan Desa, bukan ditulis di dalam kode.

## Sub-fitur: Landing Page Satu Halaman

Menyusun seluruh bagian desa dalam satu halaman muka yang mengalir.

### Tujuan
Memberi pengunjung gambaran utuh tentang desa dalam sekali gulir, tanpa harus membuka banyak halaman lebih dulu.

### Selesai bila
- Halaman `/` menampilkan bagian-bagian berikut secara berurutan: Hero, Sekilas Desa, Statistik Penduduk, Potensi Unggulan, Berita Terbaru, Galeri Kegiatan, Fasilitas & Prestasi, Struktur Organisasi, dan Kontak.
- Bagian Hero menampilkan gambar sampul, nama desa, kecamatan/kabupaten/provinsi, dan kalimat sambutan singkat.
- Setiap bagian hanya menampilkan cuplikan, bukan seluruh data: 6 potensi teratas, 3 berita terbaru, 6 acara terbaru, 4 fasilitas, 3 prestasi terbaru, dan pengurus inti Pemerintah Desa.
- Menu navigasi pada halaman muka menggulir halus ke bagian yang dituju, bukan memuat ulang halaman.
- Bagian yang belum memiliki data tidak ditampilkan sama sekali, sehingga tidak ada judul bagian yang kosong.
- Halaman muka tetap ringan meski memuat banyak bagian; gambar dimuat bertahap dan hanya thumbnail yang dipakai pada cuplikan.

## Sub-fitur: Navigasi ke Halaman Khusus

Menghubungkan setiap bagian landing ke halaman topiknya yang lengkap.

### Tujuan
Memastikan pengunjung yang tertarik pada satu bagian dapat langsung membuka halaman khusus yang memuat data selengkapnya.

### Selesai bila
- Setiap bagian pada landing memiliki satu tombol ajakan yang jelas menuju halaman khususnya:

  | Bagian pada Landing | Tombol | Tujuan |
  |---|---|---|
  | Sekilas Desa | Lihat Profil Lengkap | `/profil` |
  | Statistik Penduduk | Lihat Statistik Lengkap | `/statistik` |
  | Potensi Unggulan | Lihat Semua Potensi | `/potensi` |
  | Berita Terbaru | Lihat Berita Lebih Lengkap | `/berita` |
  | Galeri Kegiatan | Lihat Galeri Lengkap | `/galeri` |
  | Fasilitas | Lihat Semua Fasilitas | `/fasilitas` |
  | Prestasi | Lihat Semua Prestasi | `/prestasi` |
  | Struktur Organisasi | Lihat Struktur Lengkap | `/struktur-organisasi` |

- Setiap halaman khusus memiliki alamat URL tersendiri sehingga dapat dibagikan, ditandai, dan diindeks mesin pencari.
- Setiap halaman khusus memiliki jejak navigasi (breadcrumb) dan tautan kembali ke beranda.
- Kartu pada cuplikan landing juga dapat ditekan langsung menuju halaman detail item tersebut.

## Sub-fitur: Halaman Profil Desa

Menyajikan informasi umum dan gambaran desa secara lengkap.

### Tujuan
Menyajikan identitas, visi-misi, sejarah, dan letak geografis desa dalam satu halaman yang runut.

### Selesai bila
- Halaman `/profil` menampilkan identitas desa: nama, logo, kecamatan, kabupaten, provinsi, tahun berdiri, dan luas desa.
- Halaman menampilkan visi, misi, nilai/tujuan pembangunan, dan sejarah desa.
- Misi ditampilkan sebagai daftar berpoin atau bernomor, bukan satu paragraf panjang.
- Halaman menampilkan deskripsi letak geografis dan peta embed bila admin telah mengisinya.
- Bagian yang belum diisi admin tidak muncul sebagai judul kosong.
- Seluruh informasi berasal dari Pengaturan Desa.

## Sub-fitur: Tampilan Responsif & Performa

Memastikan halaman nyaman dibuka dari perangkat apa pun.

### Tujuan
Memastikan seluruh halaman publik terbaca dan cepat pada ponsel, tablet, maupun komputer, karena sebagian besar warga membuka dari ponsel.

### Selesai bila
- Seluruh halaman publik terbaca tanpa gulir mendatar pada lebar layar 360 piksel.
- Kisi kartu menyesuaikan jumlah kolom mengikuti lebar layar.
- Tabel dan konten lebar dibungkus wadah yang bisa digulir mendatar sendiri, tanpa merusak tata letak halaman.
- Seluruh gambar memakai thumbnail pada daftar dan dimuat bertahap.
- Ukuran halaman muka tidak melebihi batas wajar untuk koneksi seluler, dan gambar hero dioptimalkan.
- Elemen yang dapat ditekan berukuran cukup besar untuk jari pada layar sentuh.

## Sub-fitur: SEO Halaman Publik

Membuat halaman desa mudah ditemukan dan menarik saat dibagikan.

### Tujuan
Mendukung tujuan utama promosi desa dengan memastikan halaman publik terbaca baik oleh mesin pencari dan tampil menarik saat dibagikan di media sosial.

### Selesai bila
- Setiap halaman publik memiliki `<title>` unik yang memuat nama desa.
- Setiap halaman publik memiliki meta description yang terisi dari data terkait, bukan teks bawaan yang sama di semua halaman.
- Setiap halaman publik memiliki tag Open Graph berisi judul, deskripsi, dan gambar.
- Tersedia `sitemap.xml` yang memuat seluruh halaman publik dan halaman detail berita, potensi, serta galeri.
- Tersedia `robots.txt` yang mengizinkan pengindeksan halaman publik dan melarang pengindeksan seluruh alamat admin.
- Alamat halaman detail memakai slug yang terbaca manusia, bukan angka ID.

## Task

### 1. Buat layout publik dengan kop, menu, dan footer

### 2. Buat menu hamburger responsif dengan Alpine.js

### 3. Buat kop menempel dan penanda halaman aktif

### 4. Buat footer dari data kontak dan media sosial pengaturan desa

### 5. Buat controller landing page yang mengambil cuplikan tiap bagian

### 6. Buat bagian Hero dengan gambar sampul dan identitas desa

### 7. Buat bagian Sekilas Desa dengan tombol ke halaman profil

### 8. Buat bagian cuplikan Potensi Unggulan dengan tombol lihat semua

### 9. Buat bagian cuplikan Berita Terbaru dengan tombol lihat lebih lengkap

### 10. Buat bagian cuplikan Galeri Kegiatan dengan tombol lihat lengkap

### 11. Buat bagian cuplikan Fasilitas dan Prestasi dengan tombolnya

### 12. Buat bagian cuplikan Struktur Organisasi dengan tombol lihat lengkap

### 13. Buat bagian Kontak dan peta pada landing

### 14. Terapkan penyembunyian otomatis bagian yang datanya kosong

### 15. Buat gulir halus antar bagian pada landing

### 16. Buat halaman Profil Desa lengkap dengan peta embed

### 17. Buat breadcrumb dan tautan kembali pada halaman khusus

### 18. Terapkan responsive design dan uji pada lebar 360 piksel

### 19. Terapkan lazy load dan pemakaian thumbnail pada seluruh cuplikan

### 20. Buat komponen meta tag dan Open Graph yang dipakai semua halaman

### 21. Buat sitemap.xml dinamis dan robots.txt

### 22. Buat halaman error 404 bergaya publik desa
