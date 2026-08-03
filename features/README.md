# Daftar Fitur

Nomor file mencerminkan **urutan pengerjaan** berdasarkan dependensi teknis, bukan urutan kepentingan. Kerjakan dari atas ke bawah; setiap fase menghasilkan sesuatu yang bisa dijalankan.

Acuan lengkap ada di [PRD.md](../PRD.md).

## Fase 1 — Fondasi

| # | Fitur | Ringkasan |
|---|---|---|
| [01](01-autentikasi-admin.md) | Autentikasi Admin | Login, logout, timeout sesi, ganti kata sandi, proteksi rute. |
| [02](02-integrasi-penyimpanan-r2.md) | Integrasi Penyimpanan R2 | Disk `r2`, layanan unggah terpusat, kompresi gambar. **Wajib selesai sebelum fitur unggah foto mana pun.** |
| [03](03-pengaturan-identitas-desa.md) | Pengaturan & Identitas Desa | Identitas, visi-misi, sejarah, letak geografis, kontak, SEO. |

## Fase 2 — Data & Statistik

| # | Fitur | Ringkasan |
|---|---|---|
| [04](04-data-penduduk.md) | Data Penduduk | Wilayah, CRUD warga, mutasi, cari & saring, impor, arsip & pemulihan. |
| [05](05-rekap-kk-wilayah.md) | Rekap KK per Wilayah | Input jumlah KK per RT per tahun. Sumber tunggal angka Jumlah KK. |
| [06](06-statistik-warga.md) | Statistik Warga | Dashboard admin: ringkasan, umur, gender, pekerjaan, wilayah, tren. |
| [07](07-ekspor-data-penduduk.md) | Ekspor Data Penduduk | Ekspor semua / hasil saringan / arsip ke `.xlsx` atau `.csv`. |

## Fase 3 — Konten Desa

| # | Fitur | Ringkasan |
|---|---|---|
| [08](08-struktur-organisasi-desa.md) | Struktur Organisasi Desa | Pengurus, organisasi, jabatan, urutan, kontak resmi. |
| [09](09-potensi-desa.md) | Potensi Desa | Katalog UMKM, hasil bumi, wisata, kerajinan. |
| [10](10-fasilitas-umum.md) | Fasilitas Umum | Sekolah, kesehatan, ibadah, pemerintahan, olahraga. |
| [11](11-prestasi-desa.md) | Prestasi Desa | Penghargaan, program unggulan, inovasi. |
| [12](12-berita-desa.md) | Berita Desa | Artikel berkategori, slug, draf/terbit, berita utama. |
| [13](13-galeri-acara-desa.md) | Galeri & Dokumentasi Acara | Dokumentasi kegiatan multi-foto dengan lightbox. |

## Fase 4 — Etalase Publik

| # | Fitur | Ringkasan |
|---|---|---|
| [14](14-halaman-publik-desa.md) | Halaman Publik Desa | Layout, navigasi, landing satu halaman, halaman Profil, SEO. |
| [15](15-statistik-publik.md) | Statistik Publik | Grafik demografi agregat dengan perlindungan data. |

---

## Aturan yang berlaku untuk semua fitur

1. **Tidak ada data privasi.** NIK, Nomor Kartu Keluarga, nomor telepon warga, dan alamat rumah detail tidak boleh masuk ke formulir, database, impor, maupun ekspor. Satu-satunya pengecualian adalah nomor HP kontak resmi pengurus desa.
2. **Umur tidak disimpan.** Yang disimpan adalah tanggal lahir; umur selalu dihitung terhadap tanggal hari ini.
3. **Jumlah KK tidak dihitung dari nomor KK.** Angkanya berasal dari rekap manual per RT pada fitur 05.
4. **Semua unggahan ke R2.** Tidak ada gambar yang disimpan di penyimpanan lokal server.
5. **Semua daftar admin memakai pagination.** Tidak ada halaman yang memuat seluruh data sekaligus.
6. **Tanggal lahir tidak pernah keluar ke halaman publik.** Publik hanya menerima angka agregat.
7. **Daftar kosong menampilkan pesan ramah**, bukan halaman blank atau error.
8. **Setiap halaman publik punya `<title>` dan meta description** yang terisi dari data, bukan teks bawaan.
9. **Hanya `penduduk` yang memakai soft delete.** Sisanya hard delete + hapus gambar di R2. "Sembunyikan tanpa hapus" dijawab kolom `tampil`/`status`, bukan pengarsipan. Lihat PRD §6.16.
10. **Slug tidak berubah saat nama/judul diubah.** Dibuat sekali saat data dibuat, disunting manual bila perlu. Tidak ada pengalihan otomatis dari slug lama. Lihat PRD §6.17.
11. **Kelompok umur dan periode data KK punya definisi tunggal** di PRD §2.8 dan §2.9. Dashboard admin dan statistik publik wajib memakai definisi yang sama — angkanya tidak boleh berbeda.
12. **Isian yang jadi kategori statistik memakai tabel referensi**, bukan teks bebas: wilayah, pekerjaan, organisasi pengurus, kategori berita.

### Nilai status — jangan tertukar

| Entitas | Kolom | Nilai |
|---|---|---|
| Pengurus, Potensi, Fasilitas, Prestasi | `tampil` | boolean |
| Berita, Acara | `status` | `draf` / `terbit` |

Acara memakai `terbit`, bukan `tampil`, agar sama persis dengan Berita.

## Berita vs Galeri Acara

Dua entitas berbeda yang **tidak boleh digabung**:

| Aspek | Berita (12) | Galeri Acara (13) |
|---|---|---|
| Tujuan | Menyampaikan informasi & pengumuman | Mendokumentasikan kegiatan secara visual |
| Isi utama | Artikel teks panjang | Kumpulan foto + narasi singkat |
| Foto | Satu gambar sampul | Banyak foto per acara |
| Struktur | Kategori + slug SEO | Tanggal pelaksanaan + lokasi |
| Tampilan | Daftar artikel + halaman baca | Grid foto + lightbox |
