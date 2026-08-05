# CLAUDE.md — Peta Proyek Sistem Informasi Desa

> **MANDAT — wajib dipatuhi:** Berkas ini adalah **acuan utama proyek**. Di
> sesi baru cukup baca berkas ini untuk memahami proyek — **jangan memindai
> ulang seluruh folder** hanya untuk "memverifikasi" isi berkas ini; hemat
> token dengan menjadikannya satu-satunya konteks awal.
>
> Seluruh aturan di §3 dan konvensi di berkas ini **bersifat mengikat** —
> jangan dilanggar kecuali pengguna memintanya secara eksplisit. Bila
> struktur/rute/aturan berubah, **perbarui berkas ini di commit yang sama**;
> menyinkronkannya adalah bagian dari definisi "selesai", bukan pekerjaan
> tambahan.
>
> Status terakhir diverifikasi: 115 rute, 23 commit, 473 tes lulus (1315+ asersi).

---

## 1. Ringkasan

Website profil + sistem informasi desa. Dua sisi:

| Sisi | Akses | Isi |
|---|---|---|
| **Publik** | tanpa login | beranda, profil, statistik, struktur organisasi, potensi, fasilitas, prestasi, berita, galeri |
| **Admin** | guard `admin` | CRUD data warga, rekap KK, konten desa, pengaturan desa |

**Bahasa kode: Indonesia.** Nama kelas, method, variabel, kolom DB, route name,
dan komentar semuanya bahasa Indonesia (`simpan`, `tampilkan`, `pratinjau`,
`ubahTampil`, `geser`, `hapusPermanen`). Ikuti konvensi ini — jangan mencampur
dengan `store`/`show`/`toggle` kecuali di method resource bawaan yang memang
sudah bernama Inggris (`index`, `create`, `store`, `edit`, `update`, `destroy`).

Komentar kode menjelaskan **kenapa**, bukan **apa**. Pertahankan gaya ini.

---

## 2. Tumpukan Teknologi

```
PHP 8.2.12 (XAMPP)        Laravel 12.64          MySQL (produksi) / SQLite :memory: (tes)
Blade + Tailwind CSS 4    Alpine.js 3            Vite 7
```

**Composer (produksi):** `intervention/image ^3.11`, `league/flysystem-aws-s3-v3 ^3.35`,
`maatwebsite/excel ^3.1`, `mews/purifier ^3.4`, `laravel/tinker`.
**Dev:** `pint`, `phpunit ^11.5`, `collision`, `pail`, `faker`, `mockery`, `sail` *(sail tidak dipakai)*.

**NPM:** `alpinejs`, `chart.js`, `trix`, `tailwindcss`, `@tailwindcss/vite`,
`laravel-vite-plugin`, `vite`, `concurrently`, `axios` *(axios ter-bundle tapi tidak dipakai)*.

**Batasan hosting (PRD §8):** shared hosting cPanel — **tidak ada queue worker,
tidak ada cron andal**. Semua proses berjalan sinkron dalam satu request.
Karena itu pembersihan berkas impor kedaluwarsa dijalankan saat ada unggahan baru
(`ImporPendudukController::bersihkanBerkasKedaluwarsa`), bukan lewat scheduler.

**Laravel 13 tidak bisa dipakai** — butuh PHP 8.3+.

---

## 3. Aturan Wajib (jangan dilanggar)

### 3.1 Privasi — daftar kolom terlarang
Tabel `penduduk` **tidak boleh** memuat: **NIK, nomor Kartu Keluarga, nomor
telepon warga, alamat rumah detail**. Ada tes whitelist di
`tests/Feature/Penduduk/PrivasiDataTest.php` yang **gagal begitu kolom baru muncul**
di tabel `penduduk`. Kalau perlu kolom baru, tambahkan juga ke whitelist tes —
dan pastikan bukan data privasi.

Impor juga menolak kolom terlarang lewat `KolomImporPenduduk::terlarang()`.
Ekspor hanya mengeluarkan kolom di `EksporPenduduk::kolomDiizinkan()`.

### 3.2 Statistik publik — ambang 5
`App\Queries\StatistikPublik` mengirim `null` untuk kelompok berisi **< 5 orang**
(konstanta `AMBANG`), dan detail wilayah berhenti di tingkat **dusun** (tidak
sampai RT/RW). Jangan menambah endpoint publik yang melewati kelas ini.

### 3.3 Umur tidak pernah disimpan
Yang disimpan `tanggal_lahir`. Umur dihitung saat dibutuhkan lewat
`Penduduk::umur()` / `kelompokUmur()`. Kelompok umur **didefinisikan sekali** di
`App\Enums\KelompokUmur` — jangan menulis ulang rentangnya di tempat lain.
`KelompokUmur::terapkanFilter()` menyaring pakai **rentang tanggal**, bukan
aritmetika umur di SQL (portabel MySQL ↔ SQLite).

### 3.4 Kelahiran & kematian dari kolom mutasi
Angka kelahiran = `jenis_masuk = lahir`. Angka kematian = `alasan_keluar = meninggal`.
**Bukan** dari baris yang dibuat/dihapus. Lihat scope `lahirPadaTahun`,
`meninggalPadaTahun`, `masukPadaTahun`, `keluarPadaTahun` di `Penduduk`.

### 3.5 Jumlah KK dari rekap manual
Nomor KK tidak disimpan, jadi jumlah keluarga **tidak bisa** diturunkan dari data
penduduk. Admin mengisi jumlah KK per RT per tahun di tabel `rekap_kk`.
Satu-satunya sumber angka KK: **`App\Queries\RingkasanKk`**. Baris yang tidak ada
= "belum diisi" (berbeda dari `jumlah_kk = 0` = "sudah diperiksa, memang nol").

### 3.6 Soft delete hanya di `penduduk`
Semua tabel lain **hard delete** + pembersihan gambar lewat trait
`App\Models\Concerns\PunyaGambar`. Trait itu sengaja **menolak** dipasang bersama
`SoftDeletes` (gambar tidak boleh hilang saat data masih bisa dipulihkan).
Kebutuhan "sembunyikan tanpa hapus" dijawab kolom `tampil` / `status`.

### 3.7 Slug dibuat sekali, tidak pernah diregenerasi
`App\Models\Concerns\PunyaSlug` membuat slug saat `creating` saja. Ganti judul
**tidak** mengganti URL — supaya tautan lama tidak mati.

### 3.8 Semua konfirmasi lewat pusat dialog
Dilarang `confirm()` / `alert()` bawaan peramban. Ada tes yang memeriksa ini
(`DialogKonfirmasiTest::test_tidak_ada_confirm_bawaan_peramban_di_halaman_admin`).
Lihat §8.

### 3.9 Alur commit
**Satu commit per fitur.** Kerjakan fitur → commit → lanjut fitur berikutnya.
Pesan commit bahasa Indonesia, gaya: `Fitur 09 — Potensi Desa`.

### 3.10 Session timeout 1 jam
Admin session berakhir setelah **60 menit inaktif**. Middleware `EnsureAdminSessionNotExpired`
menangani: (1) auto logout untuk regular request → redirect ke login dengan pesan, (2) AJAX request → return 401 JSON.
`last_activity` session key di-update setiap request.

---

## 4. Rute Lengkap

### 4.1 Publik (`routes/web.php`, tanpa middleware auth)

| Method | URI | Nama | Aksi |
|---|---|---|---|
| GET | `/` | `beranda` | `Publik\BerandaController` (invokable) |
| GET | `sitemap.xml` | `sitemap` | `Publik\SitemapController` (invokable) |
| GET | `profil` | `publik.profil` | `Route::view('publik.profil')` |
| GET | `statistik` | `publik.statistik` | `Publik\StatistikController::halaman` |
| GET | `statistik/data/{jenis}` | `publik.statistik.data` | `Publik\StatistikController::data` (JSON grafik) |
| GET | `struktur-organisasi` | `publik.struktur` | `Publik\StrukturOrganisasiController` (invokable) |
| GET | `potensi` | `publik.potensi.index` | `Publik\PotensiController::index` |
| GET | `potensi/{potensi}` | `publik.potensi.show` | `Publik\PotensiController::show` (binding slug) |
| GET | `fasilitas` | `publik.fasilitas` | `Publik\FasilitasController` (invokable) |
| GET | `prestasi` | `publik.prestasi` | `Publik\PrestasiController` (invokable) |
| GET | `berita` | `publik.berita.index` | `Publik\BeritaController::index` |
| GET | `berita/kategori/{kategori}` | `publik.berita.kategori` | `Publik\BeritaController::index` (method sama) |
| GET | `berita/{berita}` | `publik.berita.show` | `Publik\BeritaController::show` |
| GET | `galeri` | `publik.galeri.index` | `Publik\GaleriController::index` |
| GET | `galeri/{acara}` | `publik.galeri.show` | `Publik\GaleriController::show` |

Beranda adalah **landing satu halaman** dengan tombol navigasi ke halaman
khusus ("lihat berita lebih lengkap" → `publik.berita.index`).

### 4.2 Admin — tamu (`prefix admin`, middleware `tamu.admin`)

| Method | URI | Nama | Aksi |
|---|---|---|---|
| GET | `admin/login` | `admin.login` | `Admin\Auth\LoginController::tampilkan` |
| POST | `admin/login` | `admin.login.proses` | `Admin\Auth\LoginController::masuk` |

**Tidak ada** registrasi mandiri dan **tidak ada** halaman lupa kata sandi publik
(fitur 01). Akun dikelola lewat perintah artisan (§10).

### 4.3 Admin — terautentikasi
Middleware: `auth:admin`, `Illuminate\Session\Middleware\AuthenticateSession`,
`sesi.segar`, `tanpa.cache`.

**Dashboard & akun**

| Method | URI | Nama | Aksi |
|---|---|---|---|
| GET | `admin` | `admin.dashboard` | `Admin\DashboardController` (invokable) |
| GET | `admin/kata-sandi` | `admin.kata-sandi` | `Admin\Auth\PasswordController::tampilkan` |
| PUT | `admin/kata-sandi` | `admin.kata-sandi.perbarui` | `Admin\Auth\PasswordController::perbarui` |
| POST | `admin/logout` | `admin.logout` | `Admin\Auth\LoginController::keluar` |

**Data penduduk** — urutan pendaftaran penting: `arsip`, `ekspor`, `impor` didaftarkan
**sebelum** `{penduduk}` agar tidak tertangkap sebagai ID.

| Method | URI | Nama | Aksi |
|---|---|---|---|
| GET | `admin/penduduk` | `admin.penduduk.index` | `PendudukController::index` |
| GET | `admin/penduduk/tambah` | `admin.penduduk.create` | `PendudukController::create` |
| POST | `admin/penduduk` | `admin.penduduk.store` | `PendudukController::store` |
| GET | `admin/penduduk/{penduduk}/ubah` | `admin.penduduk.edit` | `PendudukController::edit` — `->withTrashed()` |
| PUT | `admin/penduduk/{penduduk}` | `admin.penduduk.update` | `PendudukController::update` — `->withTrashed()` |
| DELETE | `admin/penduduk/{penduduk}` | `admin.penduduk.arsipkan` | `PendudukController::arsipkan` (soft delete + alasan keluar) |
| GET | `admin/penduduk/ekspor` | `admin.penduduk.ekspor` | `EksporPendudukController` (invokable) |
| GET | `admin/penduduk/impor` | `admin.penduduk.impor` | `ImporPendudukController::tampilkan` |
| GET | `admin/penduduk/impor/contoh` | `admin.penduduk.impor.contoh` | `ImporPendudukController::contoh` (CSV + BOM) |
| POST | `admin/penduduk/impor/pratinjau` | `admin.penduduk.impor.pratinjau` | `ImporPendudukController::pratinjau` |
| POST | `admin/penduduk/impor/proses` | `admin.penduduk.impor.proses` | `ImporPendudukController::proses` |
| POST | `admin/penduduk/impor/batal` | `admin.penduduk.impor.batal` | `ImporPendudukController::batal` |
| GET | `admin/penduduk/arsip` | `admin.penduduk.arsip` | `ArsipPendudukController::index` |
| POST | `admin/penduduk/arsip/{id}/pulihkan` | `admin.penduduk.pulihkan` | `ArsipPendudukController::pulihkan` |
| DELETE | `admin/penduduk/arsip/{id}` | `admin.penduduk.hapus-permanen` | `ArsipPendudukController::hapusPermanen` |

`hapusPermanen` **mewajibkan** field `konfirmasi_nama` yang diverifikasi di server
(dinormalkan: huruf kecil + spasi dirapikan). Kalau warga yang dihapus
`alasan_keluar = meninggal`, pesan sukses menyebutkan bahwa angka kematian tahun
itu ikut berkurang.

**Data grafik dashboard** (JSON internal, bukan API publik) — prefix `admin/statistik`, nama `admin.statistik.`

| URI | Nama | Aksi (`Admin\StatistikController`) |
|---|---|---|
| `sebaran-umur` | `admin.statistik.umur` | `sebaranUmur` |
| `rasio-gender` | `admin.statistik.gender` | `rasioGender` |
| `mata-pencaharian` | `admin.statistik.pekerjaan` | `mataPencaharian` |
| `pendidikan` | `admin.statistik.pendidikan` | `pendidikan` |
| `sebaran-wilayah` | `admin.statistik.wilayah` | `sebaranWilayah` |
| `tren-mutasi` | `admin.statistik.tren` | `trenMutasi` |

**Rekap KK**

| Method | URI | Nama | Aksi |
|---|---|---|---|
| GET | `admin/rekap-kk` | `admin.rekap-kk.index` | `RekapKkController::index` |
| POST | `admin/rekap-kk/{tahun}` | `admin.rekap-kk.simpan` | `RekapKkController::simpan` |
| POST | `admin/rekap-kk/{tahun}/salin` | `admin.rekap-kk.salin` | `RekapKkController::salinTahunSebelumnya` |

**Carousel Gambar Hero**

| Method | URI | Nama | Aksi |
|---|---|---|---|
| GET | `admin/carousel` | `admin.carousel.index` | `CarouselImageController::index` |
| GET | `admin/carousel/tambah` | `admin.carousel.create` | `CarouselImageController::create` |
| POST | `admin/carousel` | `admin.carousel.store` | `CarouselImageController::store` |
| GET | `admin/carousel/{carouselImage}/ubah` | `admin.carousel.edit` | `CarouselImageController::edit` |
| PUT | `admin/carousel/{carouselImage}` | `admin.carousel.update` | `CarouselImageController::update` |
| DELETE | `admin/carousel/{carouselImage}` | `admin.carousel.destroy` | `CarouselImageController::destroy` |
| POST | `admin/carousel/{carouselImage}/geser` | `admin.carousel.geser` | `CarouselImageController::geser` |

**Teks Tampilan**

| Method | URI | Nama | Aksi |
|---|---|---|---|
| GET | `admin/teks-tampilan` | `admin.teks-tampilan.index` | `TeksTampilanController::tampilkan` |
| PUT | `admin/teks-tampilan/{grup}` | `admin.teks-tampilan.perbarui` | `TeksTampilanController::perbarui` |

**Struktur organisasi**

| Method | URI | Nama | Aksi |
|---|---|---|---|
| GET | `admin/pengurus` | `admin.pengurus.index` | `PengurusController::index` |
| GET | `admin/pengurus/tambah` | `admin.pengurus.create` | `PengurusController::create` |
| POST | `admin/pengurus` | `admin.pengurus.store` | `PengurusController::store` |
| GET | `admin/pengurus/{pengurus}/ubah` | `admin.pengurus.edit` | `PengurusController::edit` |
| PUT | `admin/pengurus/{pengurus}` | `admin.pengurus.update` | `PengurusController::update` |
| DELETE | `admin/pengurus/{pengurus}` | `admin.pengurus.destroy` | `PengurusController::destroy` |
| POST | `admin/pengurus/{pengurus}/tampil` | `admin.pengurus.tampil` | `PengurusController::ubahTampil` |
| POST | `admin/pengurus/{pengurus}/geser` | `admin.pengurus.geser` | `PengurusController::geser` |
| GET | `admin/organisasi` | `admin.organisasi.index` | `RefOrganisasiController::index` |
| POST | `admin/organisasi` | `admin.organisasi.store` | `RefOrganisasiController::store` |
| PUT | `admin/organisasi/{organisasi}` | `admin.organisasi.update` | `RefOrganisasiController::update` |
| DELETE | `admin/organisasi/{organisasi}` | `admin.organisasi.destroy` | `RefOrganisasiController::destroy` |
| POST | `admin/organisasi/{organisasi}/geser` | `admin.organisasi.geser` | `RefOrganisasiController::geser` |

**Potensi / Fasilitas / Prestasi** — pola identik. Perhatikan parameter fasilitas
adalah **`{fasilita}`** (bentuk singular hasil Laravel, jangan diubah tanpa
menyesuaikan controller & view).

| Pola | Nama |
|---|---|
| GET `admin/{X}` | `admin.{X}.index` |
| GET `admin/{X}/tambah` | `admin.{X}.create` |
| POST `admin/{X}` | `admin.{X}.store` |
| GET `admin/{X}/{param}/ubah` | `admin.{X}.edit` |
| PUT `admin/{X}/{param}` | `admin.{X}.update` |
| DELETE `admin/{X}/{param}` | `admin.{X}.destroy` |
| POST `admin/{X}/{param}/tampil` | `admin.{X}.tampil` (→ `ubahTampil`) |

`X` ∈ `potensi` (`{potensi}`), `fasilitas` (`{fasilita}`), `prestasi` (`{prestasi}`).

**Berita**

| Method | URI | Nama | Aksi |
|---|---|---|---|
| GET | `admin/berita` | `admin.berita.index` | `BeritaController::index` |
| GET | `admin/berita/tulis` | `admin.berita.create` | `BeritaController::create` |
| POST | `admin/berita` | `admin.berita.store` | `BeritaController::store` |
| GET | `admin/berita/{berita}/ubah` | `admin.berita.edit` | `BeritaController::edit` |
| PUT | `admin/berita/{berita}` | `admin.berita.update` | `BeritaController::update` |
| DELETE | `admin/berita/{berita}` | `admin.berita.destroy` | `BeritaController::destroy` |
| POST | `admin/berita/{berita}/status` | `admin.berita.status` | `BeritaController::ubahStatus` (draf ↔ terbit) |
| POST | `admin/berita/{berita}/utama` | `admin.berita.utama` | `BeritaController::jadikanUtama` |
| POST | `admin/kategori-berita` | `admin.kategori-berita.store` | `KategoriBeritaController::store` |
| DELETE | `admin/kategori-berita/{kategori}` | `admin.kategori-berita.destroy` | `KategoriBeritaController::destroy` |

**Galeri acara**

| Method | URI | Nama | Aksi |
|---|---|---|---|
| GET | `admin/acara` | `admin.acara.index` | `AcaraController::index` |
| GET | `admin/acara/tulis` | `admin.acara.create` | `AcaraController::create` |
| POST | `admin/acara` | `admin.acara.store` | `AcaraController::store` |
| GET | `admin/acara/{acara}/ubah` | `admin.acara.edit` | `AcaraController::edit` |
| PUT | `admin/acara/{acara}` | `admin.acara.update` | `AcaraController::update` |
| DELETE | `admin/acara/{acara}` | `admin.acara.destroy` | `AcaraController::destroy` |
| POST | `admin/acara/{acara}/status` | `admin.acara.status` | `AcaraController::ubahStatus` |
| POST | `admin/acara/{acara}/foto` | `admin.acara.foto.unggah` | `AcaraController::unggahFoto` |
| PUT | `admin/acara/{acara}/foto` | `admin.acara.foto.perbarui` | `AcaraController::perbaruiFoto` |
| DELETE | `admin/acara/{acara}/foto/{foto}` | `admin.acara.foto.hapus` | `AcaraController::hapusFoto` |

**Data referensi & pengaturan**

| Method | URI | Nama | Aksi |
|---|---|---|---|
| GET | `admin/wilayah` | `admin.wilayah.index` | `WilayahController::index` |
| POST | `admin/wilayah` | `admin.wilayah.store` | `WilayahController::store` |
| PUT | `admin/wilayah/{wilayah}` | `admin.wilayah.update` | `WilayahController::update` |
| DELETE | `admin/wilayah/{wilayah}` | `admin.wilayah.destroy` | `WilayahController::destroy` |
| GET | `admin/pekerjaan` | `admin.pekerjaan.index` | `RefPekerjaanController::index` |
| POST | `admin/pekerjaan` | `admin.pekerjaan.store` | `RefPekerjaanController::store` |
| PUT | `admin/pekerjaan/{pekerjaan}` | `admin.pekerjaan.update` | `RefPekerjaanController::update` |
| DELETE | `admin/pekerjaan/{pekerjaan}` | `admin.pekerjaan.destroy` | `RefPekerjaanController::destroy` |
| GET | `admin/pengaturan` | `admin.pengaturan` | `PengaturanDesaController::tampilkan` |
| PUT | `admin/pengaturan/{bagian}` | `admin.pengaturan.perbarui` | `PengaturanDesaController::perbarui` |

Pengaturan = satu halaman, lima bagian dengan tombol Simpan terpisah; `{bagian}`
memilih bagian mana yang divalidasi & disimpan.

Rute health check bawaan: `GET /up`.

---

## 5. Middleware

`bootstrap/app.php`:

| Alias | Kelas | Fungsi |
|---|---|---|
| `tamu.admin` | `RedirectIfAdminAuthenticated` | admin yang sudah login tidak melihat halaman login |
| `sesi.segar` | `EnsureAdminSessionIsFresh` | memaksa login ulang setelah sesi idle |
| `tanpa.cache` | `PreventBackHistory` | mencegah halaman admin tersimpan di riwayat peramban |
| — | `EnsureAdminSessionNotExpired` | check session timeout (1 jam inaktif), auto logout, return 401 untuk AJAX |

`PreventBackHistory` **wajib** memakai `$response->headers->set(...)`, bukan
`$response->withHeaders(...)` — `withHeaders()` tidak ada di `StreamedResponse`,
dan setiap unduhan admin akan 500. Ada tes: `tests/Feature/Admin/UnduhanTest.php`.

Tamu yang membuka halaman admin diarahkan ke `admin.login` lewat
`$middleware->redirectGuestsTo(...)`, bukan ke rute `login` bawaan yang tidak ada.

---

## 6. Skema Database

Migrasi di `database/migrations/`, semua bertanggal `2026_08_04_*` kecuali bawaan.

| Migrasi | Tabel |
|---|---|
| `0001_01_01_000000_create_admins_table` | `admins`, `sessions` |
| `0001_01_01_000001_create_cache_table` | `cache`, `cache_locks` |
| `0001_01_01_000002_create_jobs_table` | `jobs`, `job_batches`, `failed_jobs` |
| `..._000001_create_pengaturan_desa_table` | `pengaturan_desa` |
| `..._000002_create_wilayah_dan_ref_pekerjaan_tables` | `wilayah`, `ref_pekerjaan` |
| `..._000003_create_penduduk_table` | `penduduk` |
| `..._000004_create_rekap_kk_table` | `rekap_kk` |
| `..._000005_create_pengurus_desa_table` | `ref_organisasi`, `pengurus_desa` |
| `..._000005_5_create_ref_kategori_potensi_dan_fasilitas_tables` | `ref_kategori_potensi`, `ref_kategori_fasilitas` |
| `..._000006_create_potensi_fasilitas_prestasi_tables` | `potensi_desa`, `fasilitas_umum`, `prestasi_desa` |
| `..._000007_create_berita_dan_acara_tables` | `kategori_berita`, `berita_desa`, `acara_desa`, `foto_acara` |
| `..._000008_create_teks_tampilan_table` | `teks_tampilan` |
| `..._000226_create_carousel_images_table` | `carousel_images` |
| `2026_08_05_000228_add_is_template_to_ref_pekerjaan` | `ref_pekerjaan` (is_template) |
| `2026_08_05_000301_add_hero_text_color_to_pengaturan_desa` | `pengaturan_desa` (hero_text_color) |

Tabel `password_reset_tokens` **sengaja tidak dibuat** (tidak ada reset sandi publik).

### Kolom penting

**`admins`** — `nama`, `email` (unique), `password`, `last_login_at`, `remember_token`.

**`pengaturan_desa`** — singleton `id = 1`, di-cache. Kelompok kolom: identitas
(`nama_desa`, `logo`, `foto_sampul`, `kecamatan`, `kabupaten`, `provinsi`,
`kode_pos`, `tahun_berdiri`, `luas_desa`), profil (`visi`, `misi`,
`nilai_tujuan`, `sejarah`), geografis (`letak_geografis`, `peta_embed`), kontak
(`alamat_kantor`, `telepon_kantor`, `email`, `website`, `medsos` JSON), SEO
(`meta_description`).
`peta_embed` menyimpan **hanya URL `src`** hasil `App\Support\EmbedPeta::ekstrakUrl()`,
bukan HTML iframe mentah dari admin.

**`pengaturan_desa`** (continued) — `hero_text_color` (default `#ffffff`).
Hex color picker di admin untuk menyesuaikan warna teks hero section dengan foto carousel.

**`wilayah`** — `dusun`, `rw`, `rt`, `urutan`. Unique `(dusun, rw, rt)`.
Wilayah & pekerjaan dibuat sebagai tabel referensi (bukan teks bebas) supaya
"Krajan"/"krajan"/"Dusun Krajan" tidak terhitung tiga wilayah berbeda di statistik.

**`ref_pekerjaan`** — `nama`, `is_template` (boolean, default false).
Default 10 pekerjaan di-mark sebagai template (tidak bisa dihapus). Admin bisa
tambah custom pekerjaan & delete hanya yang custom.

**`penduduk`** — `nama_lengkap`, `tanggal_lahir` (nullable, untuk data impor;
form manual mewajibkan), `jenis_kelamin` (L/P), `wilayah_id` (restrictOnDelete),
`pekerjaan_id` (nullOnDelete), `pendidikan`, `jenis_masuk` + `tanggal_masuk`,
`alasan_keluar` + `tanggal_keluar`, `softDeletes`.
Index: `nama_lengkap`, `jenis_kelamin`, `tanggal_lahir`, `deleted_at`,
`(jenis_masuk, tanggal_masuk)`, `(alasan_keluar, tanggal_keluar)`.

**`rekap_kk`** — `wilayah_id`, `tahun`, `jumlah_kk`, `catatan`, `admin_id`.
Unique `(wilayah_id, tahun)`.

**`ref_organisasi`** — `nama` (unique), `urutan`.
**`pengurus_desa`** — `nama`, `jabatan`, `organisasi_id` (restrict), `wilayah_id`
(nullOnDelete), `nomor_hp`, `foto`, `urutan`, `tampil`.

**`ref_kategori_potensi`** — `nama` (unique), `urutan`.
**`potensi_desa`** — `nama`, `slug` (unique), `kategori_potensi_id` (restrictOnDelete), `deskripsi_singkat`,
`deskripsi`, `foto`, `kontak`, `tampil`, `urutan`.
**`ref_kategori_fasilitas`** — `nama` (unique), `urutan`.
**`fasilitas_umum`** — `nama`, `kategori_fasilitas_id` (restrictOnDelete), `deskripsi`, `alamat`, `wilayah_id`, `foto`, `tampil`.
**`prestasi_desa`** — `judul`, `tahun`, `tingkat`, `penyelenggara`, `deskripsi`, `foto`, `tampil`.

**`kategori_berita`** — `nama`, `slug`.
**`berita_desa`** — `judul`, `slug`, `kategori_id`, `ringkasan`, `isi` (longText),
`gambar_sampul`, `status` (draf|terbit), `utama`, `tanggal_terbit`, `dilihat`, `admin_id`.
**`acara_desa`** — `judul`, `slug`, `tanggal_acara`, `lokasi`, `narasi`, `status`, `admin_id`.
**`foto_acara`** — `acara_id` (cascade), `path`, `keterangan`, `sampul`, `urutan`.

Berita & acara **sengaja dipisah** (PRD §3.1): berita = artikel teks berkategori
dengan satu sampul; acara = kumpulan foto dengan narasi singkat. Nilai `status`
dibuat identik agar logika penyaringan bisa dipakai ulang.

**`carousel_images`** — `path`, `judul` (nullable), `deskripsi` (nullable), 
`urutan`, `tampil`, `admin_id`. Gambar sampul hero section di beranda dengan
auto-scroll 4 detik. Disaring tampil=true dan diurutkan urutan asc di halaman publik.

**`teks_tampilan`** — CMS key-value untuk teks & kuota tampilan publik.
`kunci` (unique), `nilai` (nullable — kosong berarti pakai default dari
`config/teks-tampilan.php`), `grup`, `admin_id`. Fase 1: hanya kunci
`beranda.*`, `footer.*`, `menu.*`, `kuota.beranda.*` — lihat ANALISIS-CMS.md.

---

## 7. Struktur `app/`

### `app/Enums/` — satu-satunya sumber daftar pilihan
| Enum | Case |
|---|---|
| `JenisKelamin` | `L`, `P` |
| `Pendidikan` | `belum_sekolah`, `sd`, `smp`, `sma`, `d1_d3`, `s1`, `s2`, `s3` |
| `JenisMasuk` | `lahir`, `pindah_masuk`, `pendataan_awal` |
| `AlasanKeluar` | `meninggal`, `pindah_keluar`, `duplikat` |
| `KelompokUmur` | `balita_anak`, `anak_sekolah`, `remaja`, `dewasa_muda`, `dewasa`, `lansia`, `tidak_diketahui` |
| `TingkatPrestasi` | `desa`, `kecamatan`, `kabupaten`, `provinsi`, `nasional` |

Semua punya `label()` dan `pilihan()` statis untuk dropdown. Beberapa punya
`warna()` (kelas Tailwind badge) dan `keterangan()`/`contoh()`.

### `app/Queries/` — logika angka, jangan diduplikasi di controller
- **`PencarianPenduduk`** — dipakai bersama oleh daftar warga, arsip, dan ekspor.
  `dariRequest()`, `terapkan(Builder)`, `aktif()`, `adaFilter()`, `parameter()`, `nilai()`.
- **`RingkasanKk`** — sumber tunggal angka KK. `tahunAcuan()`, `total()`,
  `lengkap()`, `keteranganKelengkapan()`, `rataJiwaPerKk()`, `perDusun()`.
- **`StatistikPenduduk`** — angka untuk dashboard admin (data lengkap, tanpa ambang).
- **`StatistikPublik`** — angka untuk halaman publik, **dengan ambang 5** dan
  detail berhenti di dusun. `untukGrafik(string $jenis)`.

### `app/Services/` & `app/Support/`
- **`LayananUnggahGambar`** (singleton) — **satu-satunya** jalur unggah gambar.
  Alur: validasi → nama acak 32 karakter → `scaleDown` 1600px → WebP q82 →
  thumbnail 400px di `{folder}/thumb/`.
  API: `aturanValidasi()`, `simpan()`, `ganti()`, `hapus()`, `url()`,
  `urlThumbnail()`, `urlPlaceholder()`, `pathThumbnail()`, `disk()`.
- **`EmbedPeta`** — `ekstrakUrl()`, `sah()`. Hanya mengambil `src` dari iframe.
- **`MenuPublik::tautan()`** — daftar menu navigasi publik.
- **`Teks`** — `ambil(string $kunci, array $ganti = []): string`,
  `angka(string $kunci): int`. Sumber teks & kuota tampilan dinamis
  (grup `beranda`, `footer`, `menu`, `kuota`). Cache → DB → default
  `config('teks-tampilan')`.

### `app/Imports/` — alur impor 3 langkah
| Kelas | Peran |
|---|---|
| `BarisMentah` | pembaca Excel → array mentah |
| `KolomImporPenduduk` | `judul()`, `normalkan()`, `terlarang()` — peta & whitelist kolom |
| `AnalisisImpor` | `jalankan(array): HasilAnalisisImpor` — **tidak pernah menulis ke DB** |
| `BarisImpor` | value object per baris: `miripDengan` (Penduduk existing) / `gandaDenganBaris` (nomor baris) |
| `HasilAnalisisImpor` | `siap[]`, `ganda[]`, `gagal[]`, `totalBaris`, `adaGanda()`, `nomorBarisGanda()` |
| `ImporPenduduk` | `simpan(HasilAnalisisImpor, array $barisGandaDisetujui): HasilImpor` |
| `HasilImpor` | `ringkasan()` |

**Prinsip: sistem tidak mengambil keputusan sendiri.** Pratinjau melaporkan apa
yang akan ditambahkan, mana yang terdeteksi ganda (beserta data pembandingnya),
dan baris mana yang bermasalah. Admin memilih data ganda mana yang tetap diimpor.
Tidak ada lagi pesan seperti "25 dilewati karena terdeteksi ganda".

Detail teknis: berkas disimpan sementara di `storage/app/impor-sementara/`
dengan token 40 karakter; token diverifikasi terhadap session key
`impor_penduduk_token`. Berkas **dibaca ulang** di langkah proses (bukan memakai
hasil pratinjau) karena data warga bisa berubah di antara dua langkah.

### `app/Exports/EksporPenduduk`
Implementasi `FromQuery`, `WithHeadings`, `WithMapping`, `WithStyles`,
`WithCustomCsvSettings`. CSV wajib `use_bom => true` (tanpa BOM, huruf beraksen
rusak di Excel). `kolomDiizinkan(bool $arsip)` menjaga privasi ekspor.

### `app/Models/Concerns/`
- **`PunyaGambar`** — pada `deleting`, menghapus berkas di kolom yang disebut
  `kolomGambar()`. Menolak dipakai bersama `SoftDeletes`.
- **`PunyaSlug`** — `sumberSlug()`, `slugUnik()`, `getRouteKeyName()` → `slug`.

### `app/Http/Requests/Admin/`
`LoginRequest`, `PendudukRequest`, `ArsipkanPendudukRequest`, `PengurusRequest`,
`SimpanRekapKkRequest`, `UpdatePasswordRequest`, `UpdatePengaturanDesaRequest`,
`UpdateTeksTampilanRequest`.

### `app/Http/Controllers/Admin/Concerns/MengelolaGambar`
Trait pembantu controller untuk simpan/ganti/hapus gambar via `LayananUnggahGambar`.

### `app/Rules/EmbedPetaValid` — validasi URL peta.
### `app/Exceptions/` — `ImporDitolak`, `UnggahanGagal`.

### `app/Providers/AppServiceProvider`
- `singleton(LayananUnggahGambar::class)`
- `View::composer('*')` membagikan **`$desa`** (`PengaturanDesa::ambil()`) ke
  **semua** view. Nama desa/logo/kontak tidak pernah ditulis di layout.
  Ada `try/catch` supaya halaman error & artisan tetap jalan sebelum migrasi.

---

## 8. Frontend

### Entry Vite (`vite.config.js`)
| Entry | Dimuat di |
|---|---|
| `resources/css/app.css` | semua halaman |
| `resources/js/app.js` | semua halaman (Alpine + scroll-fade) |
| `resources/js/scroll-fade.js` | di-import dari app.js (IntersectionObserver) |
| `resources/js/dialog.js` | layout admin |
| `resources/js/grafik.js` | halaman bergrafik saja (Chart.js ~64 kB gzip) |
| `resources/js/editor.js` | halaman tulis berita saja (Trix) |

Layout admin memuat: `app.css`, `app.js`, `dialog.js`. Grafik & editor
di-`@push('skrip')` dari halaman terkait. Publik juga memuat `scroll-fade.js`
untuk animasi fade in/out saat scroll beranda.

### Design Tokens — `resources/css/app.css` (Tailwind 4 `@theme`)
**Tema Terra** (fitur 16): Palet hijau/earth-tone, fonts Literata + Nunito Sans, ikon Material Symbols.

**Warna** (40+ token):
- Primary: `#4a7c59` (forest green) + container/fixed variants
- Secondary: `#6b6358` (warm taupe) + container/fixed variants
- Tertiary: `#705c30` (warm brown) + container/fixed variants
- Error: `#b83230` (red) + container variant
- Surface: 7 tingkatan dari `#faf6f0` (terang) hingga `#e4e0d8` (gelap)
- On-surface / On-surface-variant: untuk text (`#2e3230`, `#4a4e4a`)
- Outline / Outline-variant: untuk border (`#74796e`, `#c4c8bc`)

**Font** (via `@theme --font-*` + Google Fonts links di layout):
- `--font-headline`, `--font-display`: Literata (serif, display/headline)
- `--font-body`, `--font-label`: Nunito Sans (humanist sans, body/label)

**Radius:** Konsisten `0.75rem` (12px) di semua elemen (tokens `rounded-[12px]`).

**Ikon:** Material Symbols Outlined. Migrasi dari inline SVG:
- Old: `<svg class="h-4 w-4"><path d="M..." /></svg>`
- New: `<span class="material-symbols-outlined">nama_ikon</span>`
- Perubahan JS di `dialog.js`: swap `textContent` span daripada path `d` attribute.

**Animasi Scroll** (`resources/css/app.css`):
- `@keyframes fadeIn` — opacity 0→1, translateY 20px→0 (0.6s ease-out)
- `@keyframes fadeOut` — opacity 1→0, translateY 0→20px (0.6s ease-out)
- Elemen dengan `data-fade-in` trigger animasi saat masuk/keluar viewport via IntersectionObserver
- Dipakai di beranda: 7 sections fade-in saat scroll

**Hero Text Color** (`resources/css/app.css`):
- CSS Variable: `--hero-text-color` di hero section (dari `pengaturan_desa.hero_text_color`)
- Applied ke: h1, p, buttons, borders di hero
- Default: `#ffffff` (putih) jika tidak di-set admin
- Support opacity variants: `text-on-primary/90`, `text-on-primary/80`, dst.

**Komponen yang memakai tokens:**
- Admin: `field`, `select`, `textarea`, `gambar`, `simpan`, `kartu-grafik`, `tombol-ekspor`, `notifikasi`, `pusat-dialog`, `dialog-arsip`
- Public: semua halaman inherit tokens otomatis (landing page, profil, struktur, statistik, dst.)

### Pusat dialog — `resources/js/dialog.js` + `<x-admin.pusat-dialog />`
Hanya **satu** modal di DOM. Tombol cukup menandai formulirnya dengan `data-*`.

**Atribut formulir:**
```html
<form method="POST" action="..."
      data-konfirmasi                      {{-- wajib: mengaktifkan intersepsi submit --}}
      data-judul="Hapus potensi desa?"
      data-pesan="..."
      data-catatan="..."                   {{-- peringatan bernada waspada --}}
      data-tombol="Hapus"                  {{-- label tombol utama --}}
      data-bahaya                          {{-- jenis = bahaya (merah) --}}
      data-jenis="bahaya|sukses|tanya"     {{-- alternatif eksplisit --}}
      data-ketik="Budi Santoso"            {{-- admin harus mengetik ulang teks ini --}}
      data-ketik-field="konfirmasi_nama">  {{-- nilai ketikan dikirim di field ini --}}
```
Teks ketikan **ikut dikirim ke server** supaya server bisa memverifikasi sendiri —
pengaman yang hanya hidup di peramban bukan pengaman.

**API JS:** `window.Dialog.buka(pilihan)` → `Promise<boolean>`,
`window.Dialog.beriTahu(pilihan)` (satu tombol). Modul mengekspor
`bukaDialog` dan `beriTahu`.

**ID elemen** (di `pusat-dialog.blade.php`): `pusatDialog`, `dialogPanel`,
`dialogIkon`, `dialogJudul`, `dialogPesan`, `dialogCatatan`, `dialogRincian`,
`dialogBlokKetik`, `dialogLabelKetik`, `dialogInputKetik`, `dialogSetuju`, `dialogBatal`.

`dialogRincian` diisi dengan `replaceChildren()` + `textContent`, **bukan
`innerHTML`** — isinya bisa memuat nama warga hasil ketikan admin.

**Pesan server:** `<x-admin.notifikasi />` mengeluarkan
`<script type="application/json" id="pesanServer">` berisi `{status, galat, kesalahan[]}`
plus fallback `<noscript>`. `dialog.js` membacanya dan menampilkannya sebagai dialog.
Kunci session yang dipakai: `status` (sukses), `galat` (gagal), dan `$errors` (validasi).

**Dirty tracking:** tandai form dengan `data-lacak-perubahan`. Klik tautan internal
saat ada perubahan belum tersimpan → dialog "Perubahan belum disimpan".
`beforeunload` dipasang sebagai jaring pengaman untuk tab yang ditutup (di sini
peramban memaksakan dialognya sendiri, tidak bisa diganti).

### Komponen Blade (`resources/views/components/admin/`)
`pusat-dialog`, `notifikasi`, `dialog-arsip`, `field`, `select`, `textarea`,
`gambar`, `simpan`, `kartu-grafik`, `tombol-ekspor`.

### Layout
- `resources/views/admin/layouts/app.blade.php` — sidebar + `@yield('konten')` +
  `@stack('skrip')`, memuat `<x-admin.notifikasi />` dan `<x-admin.pusat-dialog />`.
- `resources/views/publik/layouts/app.blade.php`.

---

## 9. Konfigurasi

`config/unggahan.php` — **satu-satunya** aturan unggah gambar:
`disk` (`UPLOAD_DISK`, default `public`; produksi `r2`), `maks_kb` 4096,
`maks_berkas_per_kiriman` 10, ekstensi `jpg|jpeg|png|webp`, `lebar_maks` 1600,
`lebar_thumbnail` 400, `kualitas_webp` 82, `folder_thumbnail` `thumb`,
peta `folder` per jenis, dan `placeholder` SVG per jenis.
Pindah ke R2 cukup lewat `.env`, tanpa mengubah kode.

`config/impor.php` — `maks_baris` 2000, `maks_kb` 5120, ekstensi `xlsx|xls|csv|txt`.

`config/app.php` — `'timezone' => env('APP_TIMEZONE', 'UTC')`.
**Jangan kunci ke `'UTC'` literal.** Kalau dikunci, antara 00:00–07:00 WIB "hari
ini" di server masih kemarin dan tanggal dari date-picker ditolak sebagai masa
depan. Ada tes: `tests/Feature/Penduduk/ZonaWaktuTest.php`.

`config/purifier.php` — sanitasi HTML isi berita (Trix).

`config/teks-tampilan.php` — metadata (grup, label, tipe, default, batas
validasi) untuk setiap kunci CMS Teks Tampilan fase 1. Menambah kunci baru
di fase berikutnya cukup menambah entri di sini, tanpa migrasi baru.

`lang/id/` — `validation.php` (ruleset Indonesia lengkap), `pagination.php`, `auth.php`.
`APP_LOCALE=id` **tanpa** berkas ini membuat semua pesan validasi tampil sebagai
`validation.required`. Ada tes: `tests/Feature/BahasaTest.php`.

---

## 10. Perintah Artisan Kustom (`app/Console/Commands/`)

| Perintah | Kelas | Fungsi |
|---|---|---|
| `admin:buat` | `AdminBuat` | membuat akun admin (pengganti registrasi) |
| `admin:reset-sandi` | `AdminResetSandi` | reset kata sandi (pengganti lupa sandi publik) |
| `admin:daftar` | `AdminDaftar` | daftar akun admin |
| `unggahan:uji` | `UnggahanUji` | menguji koneksi & tulis ke disk unggahan |
| `unggahan:berkas-yatim [--hapus]` | `UnggahanBerkasYatim` | mencari berkas tanpa pemilik di DB |

---

## 11. Pengujian

`phpunit.xml` → SQLite `:memory:`. **449 tes, 1243 asersi, semua lulus.**

```
tests/Feature/BahasaTest.php                       lang/id lengkap
tests/Feature/Admin/AutentikasiTest.php            login, guard, sesi
tests/Feature/Admin/DialogKonfirmasiTest.php       tidak ada confirm(), dirty tracking, hapus permanen
tests/Feature/Admin/GantiKataSandiTest.php
tests/Feature/Admin/PengaturanDesaTest.php
tests/Feature/Admin/PerintahAkunTest.php           perintah artisan admin:*
tests/Feature/Admin/UnduhanTest.php                header pada StreamedResponse
tests/Feature/Konten/{Acara,Berita,FasilitasPrestasi,KelolaOrganisasi,Pengurus,Potensi,TeksTampilan}Test.php
tests/Feature/Penduduk/DataReferensiTest.php
tests/Feature/Penduduk/EksporPendudukTest.php
tests/Feature/Penduduk/ImporPendudukTest.php       (butuh Storage::fake('local') di setUp)
tests/Feature/Penduduk/KelolaPendudukTest.php
tests/Feature/Penduduk/PrivasiDataTest.php         ← whitelist kolom, gagal bila ada kolom baru
tests/Feature/Penduduk/RekapKkTest.php
tests/Feature/Penduduk/StatistikTest.php
tests/Feature/Penduduk/UmurDanMutasiTest.php
tests/Feature/Penduduk/ZonaWaktuTest.php
tests/Feature/Publik/HalamanPublikTest.php
tests/Feature/Unggahan/{LayananUnggahGambar,PunyaGambar}Test.php
```

### Perbedaan driver MySQL ↔ SQLite yang sudah pernah menggigit
- `YEAR(kolom)` (MySQL) vs `strftime('%Y', kolom)` (SQLite) — jangan tulis SQL tahun mentah.
- `having()` pada hitungan subquery **ditolak SQLite** ("HAVING clause on a
  non-aggregate query"). Pakai `whereHas()` — lihat `Publik\BeritaController`.
- Header `Cache-Control` dinormalkan urutannya oleh Symfony — uji per-direktif,
  bukan string utuh.

---

## 12. Seeder (`database/seeders/`)

`DatabaseSeeder` memanggil: `AdminSeeder`, `PengaturanDesaSeeder`, `WilayahSeeder`,
`RefPekerjaanSeeder`, `PendudukSeeder`, `RekapKkSeeder`, `PengurusSeeder`,
`PotensiSeeder`, `FasilitasPrestasiSeeder`, `BeritaSeeder`.
Tidak ada factory — data contoh ditulis eksplisit di seeder.

---

## 13. Fitur & Riwayat Commit

Spesifikasi per fitur ada di `features/01-*.md` … `features/15-*.md`
(+ `features/README.md`). PRD lengkap di `PRD.md` (v2.0).

| Commit | Isi |
|---|---|
| `c842ab6` | Fitur 01 — Autentikasi Admin + rancangan proyek |
| `e653157` | Fitur 02 — Layanan unggah gambar terpusat |
| `e585bd0` | Fitur 03 — Pengaturan & Identitas Desa |
| `fc90c1d` | Fitur 04 (1/2) — Data Penduduk: fondasi, CRUD, mutasi, arsip |
| `3aece75` | Fitur 04 (2/2) — Impor data warga dari Excel/CSV |
| `62ec478` | Fitur 05 — Rekap KK per Wilayah |
| `f4d50ca` | Fitur 06 — Statistik Warga: dashboard demografi |
| `e8e5a9f` | Fitur 07 — Ekspor Data Penduduk |
| `ec65ec9` | Fitur 08 — Struktur Organisasi Desa |
| `3a76726` | Fitur 09 — Potensi Desa |
| `8b56d22` | Fitur 10 & 11 — Fasilitas Umum dan Prestasi Desa |
| `98a5668` | Fitur 12 — Berita Desa |
| `115a68d` | Fitur 13 — Galeri & Dokumentasi Acara |
| `3a4e1c7` | Fitur 14 & 15 — Halaman Publik Desa dan Statistik Publik |
| `06ea468` | Impor warga: admin yang memutuskan, bukan sistem |
| `a92d30e` | Kelola Organisasi sebagai halaman tersendiri |
| `2099701` | Hapus permanen warga, pusat dialog, dan dirty tracking |
| `a4a5ad2` | Tambah CLAUDE.md sebagai peta proyek untuk sesi baru |
| `72a5d02` | Perkuat mandat CLAUDE.md dan catat perangkap dot-notation di spec CMS |
| `f66b887` | CMS Teks Tampilan: beranda, footer, menu, dan kuota dari admin panel |
| `1bf5463` | Fitur 17 (1/3) — Kategori Potensi & Fasilitas: Struktur & Model |
| `c628449` | Fitur 17 (2/3) — Pekerjaan: Template Locked + Custom Admin |
| `aeb5a95` | Fitur 17 (3/3) — Session Timeout: 1 Jam Auto Logout + AJAX 401 |
| `142d116` | Fix: Remove invalid orderBy kategori dari BerandaController |
| `c82ae63` | Fix: Add warna() dan label() methods ke kategori models |
| `f7d0bdc` | Add scroll fade in/out animations ke beranda |
| `2f469c0` | Add hero text color picker untuk admin customization |

Seluruh 17 fitur selesai (15 PRD + 2 enhancement).

**Status sekarang:**
- ✅ Fitur 01-15: PRD lengkap
- ✅ Fitur 16: Carousel Hero (auto-scroll 4 detik) + Fade animations
- ✅ Fitur 17: Kategori Potensi/Fasilitas + Pekerjaan Template + Session Timeout (1 jam)
- ✅ Enhancement: Hero Text Color Picker untuk admin

---

## 14. Utang Teknis yang Sudah Diverifikasi (belum dikerjakan)

Temuan berikut **sudah diverifikasi di kode**, bukan dugaan. Audit repositori
lengkap belum pernah dirampungkan.

**Performa**
- `disk()->exists()` memicu satu HTTP request per gambar saat disk = R2.
- `whereDate()` / `whereYear()` mencegah pemakaian index di MySQL.
- `AcaraController::unggahFoto` (~baris 119) menjalankan `COUNT` per berkas yang diunggah.
- `View::composer('*')` menyentuh cache store (database) di **setiap** render view dan komponen.
- `WilayahController::index` tanpa paginasi.
- Dashboard memanggil `RingkasanKk::rataJiwaPerKk()` dua kali.

**Kebersihan**
- `composer.json` masih memakai metadata skeleton dan menyimpan `laravel/sail` yang tidak dipakai.
- `axios` ikut ter-bundle tapi tidak dipakai.

**Aksesibilitas / SEO / Ops**
- Hanya 6 `aria-label` di seluruh lapisan view.
- `robots.txt` menuliskan `http://localhost:8000/sitemap.xml` secara hardcode.
- Belum ada CI/CD, Docker, security header, maupun rate limiting global.
- README belum memuat panduan `storage:link`, `config:cache`, `route:cache`.

---

## 15. Perintah Cepat

```powershell
php artisan test                       # 473 tes (1325+ asersi)
php artisan test --filter=NamaTest
php artisan route:list --except-vendor # CATATAN: opsi --columns TIDAK ADA di Laravel 12
php artisan migrate:fresh --seed
./vendor/bin/pint                      # format PSR-12
npm run dev                            # Vite dev server
npm run build
```

Di lingkungan ini Bash heredoc sering gagal pada tanda kutip/apostrof —
untuk teks multibaris, tulis berkas lewat tool Write atau skrip di scratchpad.
