# CMS Teks Tampilan — Beranda & Chrome Bersama

> Status: disetujui pengguna, siap masuk tahap perencanaan implementasi.
> Sumber temuan: `ANALISIS-CMS.md` (audit teks statis seluruh situs publik).
> Dokumen ini mencakup **fase 1** dari audit tersebut: beranda, footer, menu,
> dan kuota cuplikan beranda. Halaman publik lain (profil, statistik, berita,
> potensi, fasilitas, prestasi, galeri, struktur, 404) **sengaja tidak**
> dikerjakan di fase ini — infrastrukturnya generik sehingga menambah grup
> baru nanti tinggal menambah entri konfigurasi, bukan mengubah skema.

## 1. Masalah

Beberapa konten di halaman muka (beranda) desa masih hardcode di kode Blade
dan controller: judul seksi, subtitle, label kartu statistik, teks tombol
CTA, teks footer, label menu navigasi, dan kuota cuplikan (`take(6)` dst).
Admin desa tidak bisa mengubah kalimat-kalimat ini tanpa mengedit kode.
`ANALISIS-CMS.md` sudah mengaudit seluruh situs publik dan menyarankan skema
CMS key-value generik. Dokumen ini mempersempit audit itu menjadi rencana
implementasi fase 1: beranda + chrome yang tampil bersamanya (footer, menu)
+ kuota beranda.

## 2. Cakupan

**Masuk fase 1** (~50 kunci):
- `beranda.*` — §2 `ANALISIS-CMS.md`: hero, statistik, sekilas desa, potensi,
  berita, galeri, fasilitas, prestasi, pengurus, kontak (semua judul seksi,
  subtitle, CTA, label kartu, satuan).
- `footer.*` — §3: judul kolom footer, kalimat pengelola hak cipta.
- `menu.*` — §3: 9 label menu navigasi (`MenuPublik::TAUTAN`).
- `kuota.beranda.*` — §13: 6 `take(n)` di `BerandaController` + 2
  `Str::limit(...)` di `beranda.blade.php`.

**Di luar fase 1** (didaftar di `ANALISIS-CMS.md`, dikerjakan nanti sebagai
fase terpisah dengan menambah entri config, bukan mengubah skema): profil,
statistik, berita, potensi, fasilitas, prestasi, galeri, struktur organisasi,
404, meta deskripsi non-beranda, kuota non-beranda.

**Sengaja tidak pernah di-CMS** (lihat `ANALISIS-CMS.md` §17): ambang privasi
statistik (`StatistikPublik::AMBANG`), label enum (`App\Enums\*`), teks
status grafik, pesan validasi, daftar & urutan menu (hanya labelnya yang
dinamis).

## 3. Model Data

### Migrasi & tabel

```
teks_tampilan
  id           bigint unsigned primary key auto_increment
  kunci        varchar(150)  not null unique   -- 'beranda.hero.cta_profil'
  nilai        text          null              -- null = pakai fallback config
  grup         varchar(30)   not null           -- 'beranda' | 'footer' | 'menu' | 'kuota'
  admin_id     bigint unsigned null  -- FK admins, nullOnDelete, pencatat perubahan terakhir
  timestamps
```

File migrasi mengikuti konvensi tanggal proyek:
`database/migrations/2026_08_04_000008_create_teks_tampilan_table.php`.

Tidak menyentuh tabel `penduduk` — tidak berdampak pada whitelist privasi
`PrivasiDataTest`.

### Model `App\Models\TeksTampilan`

- `$fillable = ['kunci', 'nilai', 'grup', 'admin_id']`.
- Konstanta cache: `KUNCI_CACHE = 'teks_tampilan'`.
- `static::saved()` dan `static::deleted()` memanggil `Cache::forget(self::KUNCI_CACHE)`
  — pola identik `PengaturanDesa`.
- Method statis `semua(): array<string,string>` — `Cache::rememberForever(self::KUNCI_CACHE, fn () => static::query()->pluck('nilai', 'kunci')->all())`.
  Satu entri cache untuk seluruh tabel, bukan per-kunci (beda dari saran awal
  `ANALISIS-CMS.md` §16 "cache di-forget saat saved/deleted" per kunci —
  di sini satu array untuk semua kunci sekaligus, lebih murah karena jumlah
  kunci fase 1 kecil dan stabil).

### Konfigurasi `config/teks-tampilan.php`

Bukan `kunci => nilai` datar seperti saran awal di `ANALISIS-CMS.md` §16,
karena form admin butuh tahu tipe input & aturan validasi per kunci. Setiap
entri:

```php
'beranda.hero.cta_profil' => [
    'grup' => 'beranda',
    'label' => 'Tombol: kenali desa',
    'tipe' => 'teks',        // teks | textarea | angka
    'default' => 'Kenali Desa Kami',
    'maks' => 60,             // untuk tipe teks/textarea
    'placeholder_tersedia' => [],  // mis. ['{nama}'] — ditampilkan sebagai bantuan di form admin
],
'kuota.beranda.potensi' => [
    'grup' => 'kuota',
    'label' => 'Jumlah potensi di beranda',
    'tipe' => 'angka',
    'default' => 6,
    'min' => 1,
    'max' => 12,
],
```

Hanya berisi ~50 entri fase 1 yang didaftar di §2 dokumen ini. Kunci yang
memuat placeholder (`{nama}`, `{n}`, `{tahun}`) mencantumkan
`placeholder_tersedia` supaya form admin bisa menampilkan keterangan
"placeholder yang tersedia: {nama}" di bawah field — mencegah admin tidak
sengaja menghapus placeholder saat mengedit.

### Kelas pendukung `App\Support\Teks`

Static class, dipanggil langsung dari Blade tanpa facade — mengikuti pola
`App\Support\MenuPublik` dan `App\Support\EmbedPeta` yang sudah ada di
proyek ini.

```php
Teks::ambil(string $kunci, array $ganti = []): string
Teks::angka(string $kunci): int
```

- `ambil()`: `TeksTampilan::semua()[$kunci] ?? null`, jika null/kosong pakai
  `config("teks-tampilan.{$kunci}.default")`, lalu `strtr($nilai, $ganti)`
  bila `$ganti` diisi. Jika kunci tidak terdaftar di config sama sekali,
  kembalikan kunci itu sendiri (memudahkan mendeteksi kunci yang lupa
  didaftarkan, sama seperti pola fallback yang disarankan `ANALISIS-CMS.md`).
- `angka()`: sama, tapi `(int)` hasilnya.

Nilai kosong (`''`) yang tersimpan di DB diperlakukan sama seperti `null`
(fallback ke default) — lihat §5 soal normalisasi saat simpan.

## 4. Halaman Admin

### Rute

Menyisip di `routes/web.php`, grup admin terautentikasi, dekat rute
`admin.pengaturan.*` (pola sama):

| Method | URI | Nama | Aksi |
|---|---|---|---|
| GET | `admin/teks-tampilan` | `admin.teks-tampilan.index` | `TeksTampilanController::tampilkan` |
| PUT | `admin/teks-tampilan/{grup}` | `admin.teks-tampilan.perbarui` | `TeksTampilanController::perbarui` |

### Controller `App\Http\Controllers\Admin\TeksTampilanController`

- `tampilkan()`: kirim `$nilai = TeksTampilan::semua()` dan `config('teks-tampilan')`
  ke view `admin.teks-tampilan.index`.
- `perbarui(UpdateTeksTampilanRequest $request, string $grup)`: `abort_unless`
  grup valid (salah satu dari 4 grup yang ada di config). Untuk setiap kunci
  tervalidasi milik grup ini, `updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai_dinormalkan, 'grup' => $grup, 'admin_id' => auth('admin')->id()])`.
  Redirect kembali ke tab yang sama dengan pesan sukses.

### Request `App\Http\Requests\Admin\UpdateTeksTampilanRequest`

- `rules()`: baca `config("teks-tampilan")`, filter berdasarkan `grup` dari
  route param, bangun aturan per kunci berdasarkan `tipe`:
  - `teks`/`textarea` → `['nullable', 'string', 'max:{maks}']`
  - `angka` → `['nullable', 'integer', 'min:{min}', 'max:{max}']`
- Whitelist otomatis: hanya kunci yang terdaftar di config untuk grup ini
  yang punya aturan, sehingga hanya kunci itu yang muncul di `validated()` —
  input tambahan/tidak dikenal diabaikan tanpa perlu pengecekan eksplisit.
- `prepareForValidation()`: normalisasi string kosong (`''`) menjadi `null`
  untuk seluruh kunci bertipe teks/textarea, supaya "mengosongkan field"
  di form == "kembali ke default", bukan tersimpan sebagai string kosong.

### View `admin/teks-tampilan/index.blade.php`

Empat tab (pola identik `admin/pengaturan/index.blade.php`, memakai Alpine
`x-data="{ bagian: ... }"`), satu form per tab dengan tombol Simpan sendiri:

1. **Beranda** — field teks untuk tiap kunci `beranda.*`, dikelompokkan
   per bagian beranda (Hero, Statistik, Sekilas Desa, Potensi, Berita,
   Galeri, Fasilitas & Prestasi, Struktur Organisasi, Kontak) sesuai urutan
   kemunculannya di halaman.
2. **Footer** — kunci `footer.*`.
3. **Menu** — 9 kunci `menu.*`.
4. **Kuota Tampilan** — kunci `kuota.beranda.*`, field bertipe number dengan
   `min`/`max` dari config.

Setiap field pakai `<x-admin.field>` (teks/angka) atau `<x-admin.textarea>`
sesuai `tipe`, label dari config, dan keterangan placeholder bila
`placeholder_tersedia` tidak kosong. Form pakai `data-lacak-perubahan` untuk
dirty tracking (§8 CLAUDE.md), notifikasi sukses/gagal lewat
`<x-admin.notifikasi />` yang sudah ada di layout.

## 5. Integrasi Halaman Publik

Perubahan di file yang sudah ada, menggantikan string statis dengan
pemanggilan `\App\Support\Teks::ambil()` / `::angka()`:

- **`resources/views/publik/beranda.blade.php`** — seluruh baris di tabel
  §2 `ANALISIS-CMS.md`. Empat kunci berplaceholder (`hero.sapaan`,
  `sekilas.prefix`, `kontak.kalimat`, `galeri.suffix_foto`) memanggil
  `Teks::ambil($kunci, ['{nama}' => $desa->nama_desa])` atau `['{n}' => $n]`
  sesuai konteksnya. Dua `Str::limit(...)` (baris 35 dan 89) memakai panjang
  dari `Teks::angka('kuota.beranda.limit_sapaan')` /
  `Teks::angka('kuota.beranda.limit_sekilas')`.
- **`app/Http/Controllers/Publik/BerandaController.php`** — 6 pemanggilan
  `take(n)` di controller (potensi, berita, acara, fasilitas, prestasi,
  pengurus) diganti `Teks::angka('kuota.beranda.{nama}')`. Ditambah 1
  `take(3)` "berita_samping" di `beranda.blade.php` (baris 183, di luar
  controller) yang memakai kunci sama `kuota.beranda.*` — total 7 kunci
  kuota bertipe `take()`, sesuai daftar `ANALISIS-CMS.md` §13.
- **`app/Support/MenuPublik.php`** — `TAUTAN` const diubah dari
  `'nama-rute' => 'Label'` menjadi `'nama-rute' => 'menu.kunci'`; method
  `tautan()` memanggil `Teks::ambil($kunci)` untuk label.
- **`resources/views/publik/layouts/app.blade.php`** — judul kolom footer
  (`Kontak`, `Halaman`) dan fragmen `Dikelola oleh Pemerintah Desa.` di baris
  hak cipta.

Nilai CMS selalu dirender lewat `{{ }}` (escaped) — tidak pernah `{!! !!}`,
sesuai catatan keamanan `ANALISIS-CMS.md` §16.

## 6. Pengujian

`tests/Feature/Konten/TeksTampilanTest.php`, mengikuti pola
`tests/Feature/Admin/PengaturanDesaTest.php` yang sudah ada:

1. Tanpa data di `teks_tampilan`, beranda menampilkan teks default dari
   config (fallback bekerja saat tabel kosong).
2. Admin login dapat menyimpan satu grup (mis. `beranda`) dan nilainya
   langsung tampil di halaman beranda pada request berikutnya (cache
   ter-invalidasi, tidak perlu `cache:clear`).
3. Mengosongkan field lalu simpan → nilai tersimpan `null` di DB → halaman
   publik kembali menampilkan default, bukan string kosong.
4. Mengubah `kuota.beranda.potensi` dari 6 ke 2 → jumlah kartu potensi yang
   dirender di beranda ikut berubah jadi 2.
5. Mengirim kunci yang tidak terdaftar di config untuk grup tersebut →
   tidak tersimpan ke DB (whitelist via `rules()` dinamis bekerja).
6. Nilai yang memuat karakter HTML (`<script>`) dirender ter-escape di
   halaman publik, bukan dieksekusi.
7. Tamu (belum login) diarahkan ke `admin.login` saat membuka
   `admin/teks-tampilan` (guard `auth:admin` konsisten dengan rute admin
   lain).

## 7. Dokumentasi

`CLAUDE.md` diperbarui di commit yang sama (mandat §"MANDAT" di berkas itu
sendiri):
- §4.3 — tambah baris rute `admin.teks-tampilan.index` /
  `admin.teks-tampilan.perbarui`.
- §6 — tambah migrasi & tabel `teks_tampilan` ke daftar skema.
- §7 — tambah `App\Support\Teks` ke daftar `app/Support/`.
- §9 — tambah `config/teks-tampilan.php`.
- §13 — tambah baris riwayat commit (mengikuti pola commit tak-bernomor
  seperti "Kelola Organisasi sebagai halaman tersendiri" — pekerjaan ini di
  luar 15 fitur PRD asli).
- Perbarui jumlah rute & jumlah tes di baris status paling atas berkas.

## 8. Yang Sengaja Tidak Dikerjakan di Fase Ini

- 11 grup lain dari `ANALISIS-CMS.md` (profil, statistik, berita, potensi,
  fasilitas, prestasi, galeri, struktur, 404, meta non-beranda, kuota
  non-beranda) — infrastrukturnya sudah generik, tinggal menambah entri
  `config/teks-tampilan.php` per grup di fase terpisah.
- `robots.txt` hardcode (§15 `ANALISIS-CMS.md`) — sudah tercatat sebagai
  utang teknis terpisah di CLAUDE.md §14, tidak terkait CMS teks.
- Label enum, pesan validasi, teks status grafik — sengaja tidak di-CMS
  (§17 `ANALISIS-CMS.md`), tidak berubah di fase ini maupun fase berikutnya.
