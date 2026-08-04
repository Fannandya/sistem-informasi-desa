# CMS Teks Tampilan (Beranda & Chrome Bersama) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make every hardcoded text and display-quota on the homepage (and the
footer/menu chrome shown alongside it) editable from the admin panel, backed
by a generic key-value CMS table, without touching any other public page.

**Architecture:** A single `teks_tampilan` table (kunci/nilai/grup) with a
config-driven metadata file (`config/teks-tampilan.php`) supplies label,
input type, and defaults per key. `App\Support\Teks::ambil()`/`::angka()`
resolve a key through cache → DB → config default, with the whole table
cached as one array (mirrors `PengaturanDesa::ambil()`). A single admin page
with 4 tabs (Beranda / Footer / Menu / Kuota Tampilan) edits them, reusing
the existing `admin/pengaturan` tabbed-form pattern. Public views call
`Teks::ambil()`/`Teks::angka()` in place of literal strings/numbers.

**Tech Stack:** Laravel 12, Blade, Eloquent, PHPUnit (SQLite in-memory),
existing `x-admin.field`/`x-admin.textarea`/`x-admin.simpan` components.

## Global Constraints

- Bahasa proyek: seluruh kode (kelas, method, kolom, komentar, pesan) harus
  bahasa Indonesia. Method resource bawaan (`index`, `store`, dst.) tetap
  Inggris.
- **Perangkap Laravel titik/dot-notation** (ditemukan & didokumentasikan di
  spec setelah verifikasi lokal): `.` dalam string argumen `config()` dan
  dalam nama field HTML/`validated()` selalu diperlakukan sebagai pemisah
  array bersarang. Kunci CMS memakai titik (`beranda.hero.sapaan`) hanya di
  dalam PHP (config array literal, kolom DB, pemanggilan `Teks::ambil()`).
  Di HTTP (nama field form, aturan validasi) kunci **wajib** dalam bentuk
  termanggl: titik diganti `__` (`beranda__hero__sapaan`). Akses config
  selalu lewat `config('teks-tampilan')` (tanpa argumen bertitik lanjutan)
  lalu index PHP biasa `[$kunci]` — **jangan pernah**
  `config("teks-tampilan.{$kunci}...")`.
- Nilai CMS selalu dirender lewat `{{ }}` (escaped), tidak pernah `{!! !!}`.
- Tidak menyentuh tabel `penduduk` atau kolom privasi terlarang (CLAUDE.md §3.1).
- **Satu commit untuk seluruh fitur** (CLAUDE.md §3.9: "Satu commit per
  fitur") — jangan commit per task; commit hanya di Task 7 setelah semua
  task selesai dan seluruh test suite proyek lulus.
- CLAUDE.md wajib diperbarui di commit yang sama (§4.3 rute, §6 skema, §7
  support class, §9 config, §13 riwayat commit, jumlah rute/tes di baris status).
- Cakupan hanya §2 (`beranda.*`), §3 (`footer.*`, `menu.*`), dan bagian
  beranda dari §13 (`kuota.beranda.*`) dari `ANALISIS-CMS.md`. **Jangan**
  menyentuh profil/statistik/berita/potensi/fasilitas/prestasi/galeri/
  struktur/404 — itu di luar cakupan fase ini.
- Spec lengkap: `docs/superpowers/specs/2026-08-04-cms-teks-tampilan-beranda-design.md`.

---

## File Structure

**Dibuat:**
- `database/migrations/2026_08_04_000008_create_teks_tampilan_table.php`
- `app/Models/TeksTampilan.php`
- `config/teks-tampilan.php`
- `app/Support/Teks.php`
- `app/Http/Requests/Admin/UpdateTeksTampilanRequest.php`
- `app/Http/Controllers/Admin/TeksTampilanController.php`
- `resources/views/admin/teks-tampilan/index.blade.php`
- `tests/Feature/Konten/TeksTampilanTest.php`

**Diubah:**
- `routes/web.php` — 2 rute admin baru.
- `resources/views/admin/layouts/app.blade.php` — 1 tautan sidebar baru.
- `app/Support/MenuPublik.php` — label menu dari `Teks::ambil()`.
- `resources/views/publik/layouts/app.blade.php` — footer.
- `app/Http/Controllers/Publik/BerandaController.php` — kuota dari `Teks::angka()`.
- `resources/views/publik/beranda.blade.php` — seluruh teks statis.
- `CLAUDE.md` — dokumentasi (Task 7).

---

### Task 1: Migrasi, Model, dan Konfigurasi Dasar

**Files:**
- Create: `database/migrations/2026_08_04_000008_create_teks_tampilan_table.php`
- Create: `app/Models/TeksTampilan.php`
- Create: `config/teks-tampilan.php`
- Test: `tests/Feature/Konten/TeksTampilanTest.php`

**Interfaces:**
- Produces: `App\Models\TeksTampilan::semua(): array<string, string|null>`
  (dipakai Task 2, 3, 4). `TeksTampilan::KUNCI_CACHE = 'teks_tampilan'`.
  Kolom: `kunci` (unique), `nilai` (nullable), `grup`, `admin_id`.
  `config('teks-tampilan')` mengembalikan `array<string, array{grup:string,
  bagian:?string, label:string, tipe:'teks'|'angka', default:string|int,
  maks:?int, min:?int, max:?int, placeholder_tersedia:array<int,string>}>`.

- [ ] **Step 1: Buat berkas test dan tulis test model yang gagal**

```php
<?php

namespace Tests\Feature\Konten;

use App\Models\Admin;
use App\Models\TeksTampilan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeksTampilanTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'nama' => 'Admin Desa',
            'email' => 'admin@desa.test',
            'password' => 'rahasia123',
        ]);
    }

    // ------------------------------------------------------------------
    // Model & cache
    // ------------------------------------------------------------------

    public function test_semua_mengembalikan_peta_kunci_ke_nilai(): void
    {
        TeksTampilan::create(['kunci' => 'beranda.hero.cta_profil', 'nilai' => 'Ayo', 'grup' => 'beranda']);

        $this->assertSame(['beranda.hero.cta_profil' => 'Ayo'], TeksTampilan::semua());
    }

    public function test_cache_terhapus_otomatis_setelah_simpan_dan_hapus(): void
    {
        TeksTampilan::semua(); // isi cache dengan array kosong

        $baris = TeksTampilan::create(['kunci' => 'beranda.hero.cta_profil', 'nilai' => 'Ayo', 'grup' => 'beranda']);
        $this->assertSame(['beranda.hero.cta_profil' => 'Ayo'], TeksTampilan::semua());

        $baris->delete();
        $this->assertSame([], TeksTampilan::semua());
    }
}
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test --filter=TeksTampilanTest`
Expected: FAIL — tabel `teks_tampilan` belum ada / kelas `TeksTampilan` tidak ditemukan.

- [ ] **Step 3: Tulis migrasi**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CMS Teks Tampilan — tabel key-value generik untuk teks & kuota tampilan
 * yang sebelumnya hardcode di kode publik (lihat ANALISIS-CMS.md). Kosong
 * di tabel ini berarti "pakai default dari config/teks-tampilan.php", bukan
 * berarti teksnya kosong — lihat App\Support\Teks::ambil().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teks_tampilan', function (Blueprint $table) {
            $table->id();
            $table->string('kunci', 150)->unique();
            $table->text('nilai')->nullable();
            $table->string('grup', 30);
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teks_tampilan');
    }
};
```

- [ ] **Step 4: Tulis model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * CMS Teks Tampilan — baris kosong (nilai null) berarti admin belum pernah
 * mengubah kunci ini; App\Support\Teks yang menerapkan fallback ke
 * config('teks-tampilan'). Seluruh tabel di-cache sebagai satu array karena
 * jumlah barisnya kecil dan stabil — lebih murah daripada cache per kunci.
 */
class TeksTampilan extends Model
{
    public const KUNCI_CACHE = 'teks_tampilan';

    protected $table = 'teks_tampilan';

    protected $fillable = ['kunci', 'nilai', 'grup', 'admin_id'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::KUNCI_CACHE));
        static::deleted(fn () => Cache::forget(self::KUNCI_CACHE));
    }

    /**
     * @return array<string, string|null>
     */
    public static function semua(): array
    {
        return Cache::rememberForever(
            self::KUNCI_CACHE,
            fn () => static::query()->pluck('nilai', 'kunci')->all(),
        );
    }
}
```

- [ ] **Step 5: Jalankan test, pastikan lulus**

Run: `php artisan test --filter=TeksTampilanTest`
Expected: PASS (2 test).

- [ ] **Step 6: Tulis `config/teks-tampilan.php` lengkap (55 kunci)**

```php
<?php

/**
 * CMS Teks Tampilan — fase 1: beranda + chrome bersama (footer, menu) +
 * kuota cuplikan beranda. Lihat ANALISIS-CMS.md untuk audit lengkap dan
 * docs/superpowers/specs/2026-08-04-cms-teks-tampilan-beranda-design.md
 * untuk rancangannya. Kunci di luar daftar ini TIDAK dikelola CMS.
 *
 * 'bagian' hanya dipakai untuk mengelompokkan field pada tab "Beranda" di
 * halaman admin — tidak berpengaruh pada penyimpanan atau validasi.
 */

return [

    // ---------------------------------------------------------------
    // BERANDA — resources/views/publik/beranda.blade.php
    // ---------------------------------------------------------------

    'beranda.hero.sapaan' => ['grup' => 'beranda', 'bagian' => 'Hero', 'label' => 'Sapaan (sebelum nama desa)', 'tipe' => 'teks', 'default' => 'Selamat Datang di', 'maks' => 60],
    'beranda.hero.cta_profil' => ['grup' => 'beranda', 'bagian' => 'Hero', 'label' => 'Tombol: kenali desa', 'tipe' => 'teks', 'default' => 'Kenali Desa Kami', 'maks' => 40],
    'beranda.hero.cta_potensi' => ['grup' => 'beranda', 'bagian' => 'Hero', 'label' => 'Tombol: lihat potensi', 'tipe' => 'teks', 'default' => 'Lihat Potensi Desa', 'maks' => 40],

    'beranda.statistik.label_penduduk' => ['grup' => 'beranda', 'bagian' => 'Statistik', 'label' => 'Label: jumlah penduduk', 'tipe' => 'teks', 'default' => 'Jumlah Penduduk', 'maks' => 40],
    'beranda.statistik.satuan_jiwa' => ['grup' => 'beranda', 'bagian' => 'Statistik', 'label' => 'Satuan: jiwa', 'tipe' => 'teks', 'default' => 'jiwa', 'maks' => 20],
    'beranda.statistik.label_kk' => ['grup' => 'beranda', 'bagian' => 'Statistik', 'label' => 'Label: kepala keluarga', 'tipe' => 'teks', 'default' => 'Kepala Keluarga', 'maks' => 40],
    'beranda.statistik.satuan_keluarga' => ['grup' => 'beranda', 'bagian' => 'Statistik', 'label' => 'Satuan: keluarga', 'tipe' => 'teks', 'default' => 'keluarga', 'maks' => 20],
    'beranda.statistik.label_laki' => ['grup' => 'beranda', 'bagian' => 'Statistik', 'label' => 'Label: laki-laki', 'tipe' => 'teks', 'default' => 'Laki-laki', 'maks' => 40],
    'beranda.statistik.label_perempuan' => ['grup' => 'beranda', 'bagian' => 'Statistik', 'label' => 'Label: perempuan', 'tipe' => 'teks', 'default' => 'Perempuan', 'maks' => 40],
    'beranda.statistik.tautan' => ['grup' => 'beranda', 'bagian' => 'Statistik', 'label' => 'Tautan: lihat statistik lengkap', 'tipe' => 'teks', 'default' => 'Lihat Statistik Lengkap →', 'maks' => 60],

    'beranda.sekilas.prefix' => ['grup' => 'beranda', 'bagian' => 'Sekilas Desa', 'label' => 'Judul (sebelum nama desa)', 'tipe' => 'teks', 'default' => 'Sekilas', 'maks' => 40],
    'beranda.sekilas.cta' => ['grup' => 'beranda', 'bagian' => 'Sekilas Desa', 'label' => 'Tombol: lihat profil lengkap', 'tipe' => 'teks', 'default' => 'Lihat Profil Lengkap', 'maks' => 40],
    'beranda.sekilas.judul_misi' => ['grup' => 'beranda', 'bagian' => 'Sekilas Desa', 'label' => 'Judul kotak misi', 'tipe' => 'teks', 'default' => 'Misi Desa', 'maks' => 40],

    'beranda.potensi.judul' => ['grup' => 'beranda', 'bagian' => 'Potensi', 'label' => 'Judul seksi', 'tipe' => 'teks', 'default' => 'Potensi Unggulan', 'maks' => 60],
    'beranda.potensi.subjudul' => ['grup' => 'beranda', 'bagian' => 'Potensi', 'label' => 'Subjudul', 'tipe' => 'teks', 'default' => 'UMKM, hasil bumi, wisata, dan kerajinan desa.', 'maks' => 150],
    'beranda.potensi.tautan' => ['grup' => 'beranda', 'bagian' => 'Potensi', 'label' => 'Tautan: lihat semua potensi', 'tipe' => 'teks', 'default' => 'Lihat Semua Potensi →', 'maks' => 60],

    'beranda.berita.judul' => ['grup' => 'beranda', 'bagian' => 'Berita', 'label' => 'Judul seksi', 'tipe' => 'teks', 'default' => 'Berita Terbaru', 'maks' => 60],
    'beranda.berita.subjudul' => ['grup' => 'beranda', 'bagian' => 'Berita', 'label' => 'Subjudul', 'tipe' => 'teks', 'default' => 'Kabar dan pengumuman dari pemerintah desa.', 'maks' => 150],
    'beranda.berita.tautan' => ['grup' => 'beranda', 'bagian' => 'Berita', 'label' => 'Tautan: lihat berita lebih lengkap', 'tipe' => 'teks', 'default' => 'Lihat Berita Lebih Lengkap →', 'maks' => 60],

    'beranda.galeri.judul' => ['grup' => 'beranda', 'bagian' => 'Galeri', 'label' => 'Judul seksi', 'tipe' => 'teks', 'default' => 'Galeri Kegiatan', 'maks' => 60],
    'beranda.galeri.subjudul' => ['grup' => 'beranda', 'bagian' => 'Galeri', 'label' => 'Subjudul', 'tipe' => 'teks', 'default' => 'Dokumentasi acara dan kegiatan warga.', 'maks' => 150],
    'beranda.galeri.tautan' => ['grup' => 'beranda', 'bagian' => 'Galeri', 'label' => 'Tautan: lihat galeri lengkap', 'tipe' => 'teks', 'default' => 'Lihat Galeri Lengkap →', 'maks' => 60],
    'beranda.galeri.suffix_foto' => ['grup' => 'beranda', 'bagian' => 'Galeri', 'label' => 'Akhiran jumlah foto (mis. "3 foto")', 'tipe' => 'teks', 'default' => 'foto', 'maks' => 20],

    'beranda.fasilitas.judul' => ['grup' => 'beranda', 'bagian' => 'Fasilitas & Prestasi', 'label' => 'Judul seksi fasilitas', 'tipe' => 'teks', 'default' => 'Fasilitas Desa', 'maks' => 60],
    'beranda.tautan_lihat_semua' => ['grup' => 'beranda', 'bagian' => 'Fasilitas & Prestasi', 'label' => 'Tautan "Lihat Semua" (fasilitas & prestasi)', 'tipe' => 'teks', 'default' => 'Lihat Semua →', 'maks' => 40],
    'beranda.prestasi.judul' => ['grup' => 'beranda', 'bagian' => 'Fasilitas & Prestasi', 'label' => 'Judul seksi prestasi', 'tipe' => 'teks', 'default' => 'Prestasi Desa', 'maks' => 60],

    'beranda.pengurus.judul' => ['grup' => 'beranda', 'bagian' => 'Struktur Organisasi', 'label' => 'Judul seksi', 'tipe' => 'teks', 'default' => 'Struktur Organisasi', 'maks' => 60],
    'beranda.pengurus.subjudul' => ['grup' => 'beranda', 'bagian' => 'Struktur Organisasi', 'label' => 'Subjudul', 'tipe' => 'teks', 'default' => 'Aparat desa yang melayani warga.', 'maks' => 150],
    'beranda.pengurus.tautan' => ['grup' => 'beranda', 'bagian' => 'Struktur Organisasi', 'label' => 'Tautan: lihat struktur lengkap', 'tipe' => 'teks', 'default' => 'Lihat Struktur Lengkap →', 'maks' => 60],

    'beranda.kontak.judul' => ['grup' => 'beranda', 'bagian' => 'Kontak', 'label' => 'Judul seksi', 'tipe' => 'teks', 'default' => 'Hubungi Kami', 'maks' => 60],
    'beranda.kontak.kalimat' => ['grup' => 'beranda', 'bagian' => 'Kontak', 'label' => 'Kalimat pembuka', 'tipe' => 'teks', 'default' => 'Kantor {nama} melayani warga pada hari kerja.', 'maks' => 150, 'placeholder_tersedia' => ['{nama}']],
    'beranda.kontak.label_alamat' => ['grup' => 'beranda', 'bagian' => 'Kontak', 'label' => 'Label: alamat', 'tipe' => 'teks', 'default' => 'Alamat', 'maks' => 20],
    'beranda.kontak.label_telepon' => ['grup' => 'beranda', 'bagian' => 'Kontak', 'label' => 'Label: telepon', 'tipe' => 'teks', 'default' => 'Telepon', 'maks' => 20],
    'beranda.kontak.label_email' => ['grup' => 'beranda', 'bagian' => 'Kontak', 'label' => 'Label: email', 'tipe' => 'teks', 'default' => 'Email', 'maks' => 20],

    // ---------------------------------------------------------------
    // FOOTER — resources/views/publik/layouts/app.blade.php
    // ---------------------------------------------------------------

    'footer.kolom_kontak' => ['grup' => 'footer', 'label' => 'Judul kolom "Kontak"', 'tipe' => 'teks', 'default' => 'Kontak', 'maks' => 30],
    'footer.kolom_halaman' => ['grup' => 'footer', 'label' => 'Judul kolom "Halaman"', 'tipe' => 'teks', 'default' => 'Halaman', 'maks' => 30],
    'footer.pengelola' => ['grup' => 'footer', 'label' => 'Baris hak cipta (setelah tahun & nama desa)', 'tipe' => 'teks', 'default' => 'Dikelola oleh Pemerintah Desa.', 'maks' => 80],

    // ---------------------------------------------------------------
    // MENU — app/Support/MenuPublik.php
    // ---------------------------------------------------------------

    'menu.beranda' => ['grup' => 'menu', 'label' => 'Beranda', 'tipe' => 'teks', 'default' => 'Beranda', 'maks' => 30],
    'menu.profil' => ['grup' => 'menu', 'label' => 'Profil', 'tipe' => 'teks', 'default' => 'Profil', 'maks' => 30],
    'menu.potensi' => ['grup' => 'menu', 'label' => 'Potensi', 'tipe' => 'teks', 'default' => 'Potensi', 'maks' => 30],
    'menu.fasilitas' => ['grup' => 'menu', 'label' => 'Fasilitas', 'tipe' => 'teks', 'default' => 'Fasilitas', 'maks' => 30],
    'menu.prestasi' => ['grup' => 'menu', 'label' => 'Prestasi', 'tipe' => 'teks', 'default' => 'Prestasi', 'maks' => 30],
    'menu.berita' => ['grup' => 'menu', 'label' => 'Berita', 'tipe' => 'teks', 'default' => 'Berita', 'maks' => 30],
    'menu.galeri' => ['grup' => 'menu', 'label' => 'Galeri', 'tipe' => 'teks', 'default' => 'Galeri', 'maks' => 30],
    'menu.struktur' => ['grup' => 'menu', 'label' => 'Struktur Organisasi', 'tipe' => 'teks', 'default' => 'Struktur Organisasi', 'maks' => 30],
    'menu.statistik' => ['grup' => 'menu', 'label' => 'Statistik', 'tipe' => 'teks', 'default' => 'Statistik', 'maks' => 30],

    // ---------------------------------------------------------------
    // KUOTA — app/Http/Controllers/Publik/BerandaController.php +
    // resources/views/publik/beranda.blade.php
    // ---------------------------------------------------------------

    'kuota.beranda.potensi' => ['grup' => 'kuota', 'label' => 'Jumlah potensi di beranda', 'tipe' => 'angka', 'default' => 6, 'min' => 1, 'max' => 12],
    'kuota.beranda.berita' => ['grup' => 'kuota', 'label' => 'Jumlah berita di beranda', 'tipe' => 'angka', 'default' => 4, 'min' => 1, 'max' => 12],
    'kuota.beranda.berita_samping' => ['grup' => 'kuota', 'label' => 'Jumlah berita samping (di kartu berita utama)', 'tipe' => 'angka', 'default' => 3, 'min' => 1, 'max' => 6],
    'kuota.beranda.acara' => ['grup' => 'kuota', 'label' => 'Jumlah acara/galeri di beranda', 'tipe' => 'angka', 'default' => 6, 'min' => 1, 'max' => 12],
    'kuota.beranda.fasilitas' => ['grup' => 'kuota', 'label' => 'Jumlah fasilitas di beranda', 'tipe' => 'angka', 'default' => 4, 'min' => 1, 'max' => 12],
    'kuota.beranda.prestasi' => ['grup' => 'kuota', 'label' => 'Jumlah prestasi di beranda', 'tipe' => 'angka', 'default' => 3, 'min' => 1, 'max' => 12],
    'kuota.beranda.pengurus' => ['grup' => 'kuota', 'label' => 'Jumlah pengurus di beranda', 'tipe' => 'angka', 'default' => 4, 'min' => 1, 'max' => 12],
    'kuota.beranda.limit_sapaan' => ['grup' => 'kuota', 'label' => 'Panjang potongan kalimat hero (karakter)', 'tipe' => 'angka', 'default' => 200, 'min' => 50, 'max' => 500],
    'kuota.beranda.limit_sekilas' => ['grup' => 'kuota', 'label' => 'Panjang potongan "Sekilas Desa" (karakter)', 'tipe' => 'angka', 'default' => 480, 'min' => 100, 'max' => 1000],

];
```

- [ ] **Step 7: Jalankan seluruh test file, pastikan lulus**

Run: `php artisan test --filter=TeksTampilanTest`
Expected: PASS (2 test, tidak berubah — config belum dipakai kode manapun di task ini).

Tidak ada commit di sini — lihat Global Constraints (satu commit di Task 7).

---

### Task 2: Kelas Pendukung `App\Support\Teks`

**Files:**
- Create: `app/Support/Teks.php`
- Test: `tests/Feature/Konten/TeksTampilanTest.php` (tambah section)

**Interfaces:**
- Consumes: `TeksTampilan::semua(): array<string, string|null>` (Task 1),
  `config('teks-tampilan')` (Task 1).
- Produces: `Teks::ambil(string $kunci, array $ganti = []): string`,
  `Teks::angka(string $kunci): int` — dipakai Task 3 (validasi min/max),
  Task 5 & 6 (integrasi publik).

- [ ] **Step 1: Tambahkan test yang gagal**

Tambahkan ke `tests/Feature/Konten/TeksTampilanTest.php`, di bawah section model:

```php
    // ------------------------------------------------------------------
    // Kelas pendukung Teks
    // ------------------------------------------------------------------

    public function test_teks_ambil_memakai_default_config_saat_db_kosong(): void
    {
        $this->assertSame('Kenali Desa Kami', \App\Support\Teks::ambil('beranda.hero.cta_profil'));
    }

    public function test_teks_ambil_memakai_nilai_db_bila_terisi(): void
    {
        TeksTampilan::create(['kunci' => 'beranda.hero.cta_profil', 'nilai' => 'Ayo Kenalan', 'grup' => 'beranda']);

        $this->assertSame('Ayo Kenalan', \App\Support\Teks::ambil('beranda.hero.cta_profil'));
    }

    public function test_teks_ambil_mengganti_placeholder(): void
    {
        $hasil = \App\Support\Teks::ambil('beranda.kontak.kalimat', ['{nama}' => 'Sukamaju']);

        $this->assertSame('Kantor Sukamaju melayani warga pada hari kerja.', $hasil);
    }

    public function test_teks_angka_mengembalikan_bilangan_bulat_dan_ikut_cache(): void
    {
        $this->assertSame(6, \App\Support\Teks::angka('kuota.beranda.potensi'));

        TeksTampilan::create(['kunci' => 'kuota.beranda.potensi', 'nilai' => '3', 'grup' => 'kuota']);

        $this->assertSame(3, \App\Support\Teks::angka('kuota.beranda.potensi'));
    }
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test --filter=TeksTampilanTest`
Expected: FAIL — `Class "App\Support\Teks" not found`.

- [ ] **Step 3: Tulis `app/Support/Teks.php`**

```php
<?php

namespace App\Support;

use App\Models\TeksTampilan;

/**
 * Static, dipanggil langsung dari Blade tanpa facade — pola yang sama
 * dengan MenuPublik dan EmbedPeta di kelas ini.
 *
 * config('teks-tampilan') WAJIB dipanggil tanpa argumen bertitik lanjutan:
 * kunci CMS ('beranda.hero.sapaan') memakai titik sebagai bagian dari
 * namanya sendiri, bukan sebagai pemisah bersarang Laravel. Ambil seluruh
 * array config lalu index dengan PHP biasa.
 */
class Teks
{
    public static function ambil(string $kunci, array $ganti = []): string
    {
        $meta = config('teks-tampilan')[$kunci] ?? null;
        $default = $meta['default'] ?? $kunci;

        $nilai = TeksTampilan::semua()[$kunci] ?? null;
        $teks = blank($nilai) ? (string) $default : $nilai;

        return $ganti === [] ? $teks : strtr($teks, $ganti);
    }

    public static function angka(string $kunci): int
    {
        return (int) self::ambil($kunci);
    }
}
```

- [ ] **Step 4: Jalankan test, pastikan lulus**

Run: `php artisan test --filter=TeksTampilanTest`
Expected: PASS (6 test).

---

### Task 3: Rute, Request, dan Controller Admin

**Files:**
- Modify: `routes/web.php`
- Create: `app/Http/Requests/Admin/UpdateTeksTampilanRequest.php`
- Create: `app/Http/Controllers/Admin/TeksTampilanController.php`
- Test: `tests/Feature/Konten/TeksTampilanTest.php` (tambah section)

**Interfaces:**
- Consumes: `TeksTampilan::semua()`, `config('teks-tampilan')` (Task 1).
- Produces: rute bernama `admin.teks-tampilan.index` (GET, query opsional
  `grup`) dan `admin.teks-tampilan.perbarui` (PUT `{grup}`), dipakai Task 4
  (view) dan Task 4 (sidebar).
  `UpdateTeksTampilanRequest::nilaiUntukDisimpan(): array<string, string|null>`
  (kunci asli => nilai tervalidasi) dipakai controller di task ini.

- [ ] **Step 1: Tambahkan test akses & penyimpanan yang gagal**

Tambahkan ke `tests/Feature/Konten/TeksTampilanTest.php`:

```php
    // ------------------------------------------------------------------
    // Akses & penyimpanan halaman admin
    // ------------------------------------------------------------------

    public function test_tamu_tidak_bisa_membuka_teks_tampilan(): void
    {
        $this->get(route('admin.teks-tampilan.index'))->assertRedirect(route('admin.login'));
        $this->put(route('admin.teks-tampilan.perbarui', 'beranda'), [])
            ->assertRedirect(route('admin.login'));
    }

    public function test_grup_yang_tidak_dikenal_menghasilkan_404(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.teks-tampilan.perbarui', 'grup-ngawur'), [])
            ->assertNotFound();
    }

    public function test_admin_dapat_menyimpan_teks_beranda(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.teks-tampilan.perbarui', 'beranda'), [
                'nilai' => ['beranda__hero__cta_profil' => 'Ayo Kenalan'],
            ])
            ->assertRedirect(route('admin.teks-tampilan.index', ['grup' => 'beranda']));

        $this->assertDatabaseHas('teks_tampilan', [
            'kunci' => 'beranda.hero.cta_profil',
            'nilai' => 'Ayo Kenalan',
            'grup' => 'beranda',
            'admin_id' => $this->admin->id,
        ]);
    }

    public function test_mengosongkan_field_mengembalikan_ke_default(): void
    {
        TeksTampilan::create(['kunci' => 'beranda.hero.cta_profil', 'nilai' => 'Ayo Kenalan', 'grup' => 'beranda']);

        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.teks-tampilan.perbarui', 'beranda'), [
                'nilai' => ['beranda__hero__cta_profil' => ''],
            ]);

        $this->assertDatabaseHas('teks_tampilan', [
            'kunci' => 'beranda.hero.cta_profil',
            'nilai' => null,
        ]);
        $this->assertSame('Kenali Desa Kami', \App\Support\Teks::ambil('beranda.hero.cta_profil'));
    }

    public function test_kunci_di_luar_grup_tidak_ikut_tersimpan(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.teks-tampilan.perbarui', 'beranda'), [
                'nilai' => ['kuota__beranda__potensi' => '99'],
            ]);

        $this->assertDatabaseMissing('teks_tampilan', ['kunci' => 'kuota.beranda.potensi']);
    }

    public function test_angka_kuota_ditolak_di_luar_rentang(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.teks-tampilan.perbarui', 'kuota'), [
                'nilai' => ['kuota__beranda__potensi' => '99'],
            ])
            ->assertSessionHasErrors('nilai.kuota__beranda__potensi');

        $this->assertDatabaseMissing('teks_tampilan', ['kunci' => 'kuota.beranda.potensi']);
    }
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test --filter=TeksTampilanTest`
Expected: FAIL — rute `admin.teks-tampilan.index`/`.perbarui` belum terdaftar.

- [ ] **Step 3: Tambahkan rute**

Di `routes/web.php`, di dalam grup admin terautentikasi, tepat sebelum blok
rute `pengaturan` (dekat baris 212 pada isi saat ini):

```php
        Route::get('teks-tampilan', [TeksTampilanController::class, 'tampilkan'])->name('teks-tampilan.index');
        Route::put('teks-tampilan/{grup}', [TeksTampilanController::class, 'perbarui'])
            ->name('teks-tampilan.perbarui');

        Route::get('pengaturan', [PengaturanDesaController::class, 'tampilkan'])->name('pengaturan');
```

Tambahkan juga `use App\Http\Controllers\Admin\TeksTampilanController;` ke
daftar `use` di bagian atas `routes/web.php`, mengikuti urutan alfabetis
`use` controller Admin yang sudah ada.

- [ ] **Step 4: Tulis `UpdateTeksTampilanRequest`**

```php
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi dinamis: aturan dibangun dari config('teks-tampilan') sesuai
 * grup yang dikirim di URL. Nama field HTTP memakai kunci termanggl
 * (titik -> __) karena Laravel memperlakukan titik pada nama field sebagai
 * pemisah array bersarang — lihat Global Constraints di plan ini.
 */
class UpdateTeksTampilanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    public function grup(): string
    {
        return (string) $this->route('grup');
    }

    protected function prepareForValidation(): void
    {
        $nilai = collect($this->input('nilai', []))
            ->map(fn ($v) => $v === '' ? null : $v)
            ->all();

        $this->merge(['nilai' => $nilai]);
    }

    public function rules(): array
    {
        $aturan = [];

        foreach ($this->kunciGrupIni() as $kunci => $meta) {
            $termanggl = str_replace('.', '__', $kunci);

            $aturan["nilai.{$termanggl}"] = $meta['tipe'] === 'angka'
                ? ['nullable', 'integer', 'min:'.$meta['min'], 'max:'.$meta['max']]
                : ['nullable', 'string', 'max:'.$meta['maks']];
        }

        return $aturan;
    }

    public function attributes(): array
    {
        $atribut = [];

        foreach ($this->kunciGrupIni() as $kunci => $meta) {
            $atribut['nilai.'.str_replace('.', '__', $kunci)] = $meta['label'];
        }

        return $atribut;
    }

    /**
     * Nilai tervalidasi, dipetakan kembali dari kunci termanggl ke kunci
     * asli bertitik supaya controller bisa langsung menyimpannya.
     *
     * @return array<string, string|null>
     */
    public function nilaiUntukDisimpan(): array
    {
        $termanggl = collect($this->validated('nilai', []));

        return collect($this->kunciGrupIni())
            ->keys()
            ->mapWithKeys(fn (string $kunci) => [
                $kunci => $termanggl->get(str_replace('.', '__', $kunci)),
            ])
            ->all();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function kunciGrupIni(): array
    {
        return collect(config('teks-tampilan'))
            ->filter(fn (array $meta) => $meta['grup'] === $this->grup())
            ->all();
    }
}
```

- [ ] **Step 5: Tulis `TeksTampilanController`**

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateTeksTampilanRequest;
use App\Models\TeksTampilan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TeksTampilanController extends Controller
{
    public function tampilkan(): View
    {
        return view('admin.teks-tampilan.index', [
            'nilai' => TeksTampilan::semua(),
        ]);
    }

    public function perbarui(UpdateTeksTampilanRequest $request, string $grup): RedirectResponse
    {
        abort_unless(
            collect(config('teks-tampilan'))->pluck('grup')->unique()->contains($grup),
            404,
        );

        foreach ($request->nilaiUntukDisimpan() as $kunci => $nilai) {
            TeksTampilan::updateOrCreate(
                ['kunci' => $kunci],
                ['nilai' => $nilai, 'grup' => $grup, 'admin_id' => auth('admin')->id()],
            );
        }

        return redirect()
            ->route('admin.teks-tampilan.index', ['grup' => $grup])
            ->with('status', 'Teks tampilan berhasil disimpan.');
    }
}
```

- [ ] **Step 6: Jalankan test, pastikan lulus**

Run: `php artisan test --filter=TeksTampilanTest`
Expected: PASS (11 test). Test view (`test_admin_dapat_menyimpan_teks_beranda`
dkk.) akan gagal render bila view `admin.teks-tampilan.index` belum ada —
buat placeholder minimal dulu agar test ini bisa lulus:

```blade
{{-- resources/views/admin/teks-tampilan/index.blade.php (sementara, dilengkapi Task 4) --}}
@extends('admin.layouts.app')
@section('konten')
<p>Teks Tampilan</p>
@endsection
```

Placeholder ini akan ditimpa penuh di Task 4, Step 3.

---

### Task 4: Halaman Admin — View 4 Tab & Tautan Sidebar

**Files:**
- Create (menimpa placeholder Task 3): `resources/views/admin/teks-tampilan/index.blade.php`
- Modify: `resources/views/admin/layouts/app.blade.php`
- Test: `tests/Feature/Konten/TeksTampilanTest.php` (tambah section)

**Interfaces:**
- Consumes: `$nilai` (array kunci=>nilai, dari `TeksTampilanController::tampilkan()`),
  `config('teks-tampilan')`, `x-admin.field`, `x-admin.textarea`, `x-admin.simpan`,
  `route('admin.teks-tampilan.perbarui', $grup)`.

- [ ] **Step 1: Tambahkan test tampilan yang gagal**

```php
    public function test_halaman_teks_tampilan_menampilkan_field_setiap_kunci(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.teks-tampilan.index'));

        $response->assertOk();
        $response->assertSee('name="nilai[beranda__hero__cta_profil]"', false);
        $response->assertSee('name="nilai[footer__pengelola]"', false);
        $response->assertSee('name="nilai[menu__beranda]"', false);
        $response->assertSee('name="nilai[kuota__beranda__potensi]"', false);
    }

    public function test_field_terisi_dengan_nilai_tersimpan(): void
    {
        TeksTampilan::create(['kunci' => 'beranda.hero.cta_profil', 'nilai' => 'Ayo Kenalan', 'grup' => 'beranda']);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.teks-tampilan.index'))
            ->assertSee('value="Ayo Kenalan"', false);
    }
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test --filter=TeksTampilanTest`
Expected: FAIL — placeholder view belum menampilkan field apa pun.

- [ ] **Step 3: Tulis view penuh, timpa placeholder**

```blade
@extends('admin.layouts.app')

@section('judul', 'Teks Tampilan')

@php
    $grupAktif = request('grup', 'beranda');

    $tab = [
        'beranda' => 'Beranda',
        'footer' => 'Footer',
        'menu' => 'Menu',
        'kuota' => 'Kuota Tampilan',
    ];

    $semuaKunci = collect(config('teks-tampilan'));
@endphp

@section('konten')
<div x-data="{ grup: @js($grupAktif) }" class="max-w-3xl">

    <div class="mb-6 overflow-x-auto">
        <nav class="flex gap-1 border-b border-slate-200" role="tablist">
            @foreach ($tab as $kunciTab => $label)
                <button type="button" role="tab" @click="grup = @js($kunciTab)"
                        class="shrink-0 border-b-2 px-4 py-2.5 text-sm font-medium transition"
                        :class="grup === @js($kunciTab)
                            ? 'border-emerald-600 text-emerald-700'
                            : 'border-transparent text-slate-500 hover:text-slate-800'">
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    @foreach ($tab as $kunciTab => $labelTab)
        <section x-show="grup === @js($kunciTab)" x-cloak
                 class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <form method="POST" action="{{ route('admin.teks-tampilan.perbarui', $kunciTab) }}"
                  data-lacak-perubahan class="space-y-6">
                @csrf @method('PUT')

                @php
                    $kunciGrupIni = $semuaKunci->filter(fn ($meta) => $meta['grup'] === $kunciTab);
                    $perBagian = $kunciGrupIni->groupBy(fn ($meta) => $meta['bagian'] ?? '');
                @endphp

                @foreach ($perBagian as $namaBagian => $kunciBagian)
                    <div class="space-y-4">
                        @if ($namaBagian !== '')
                            <h3 class="text-sm font-semibold tracking-wider text-slate-500 uppercase">{{ $namaBagian }}</h3>
                        @endif

                        <div class="grid gap-5 sm:grid-cols-2">
                            @foreach ($kunciBagian as $kunci => $meta)
                                @php
                                    $namaField = 'nilai['.str_replace('.', '__', $kunci).']';
                                    $keterangan = ($meta['placeholder_tersedia'] ?? []) !== []
                                        ? 'Placeholder yang tersedia: '.implode(', ', $meta['placeholder_tersedia'])
                                        : null;
                                @endphp

                                @if ($meta['tipe'] === 'angka')
                                    <x-admin.field :name="$namaField" :label="$meta['label']" type="number"
                                                   :value="$nilai[$kunci] ?? null" :placeholder="(string) $meta['default']"
                                                   :min="$meta['min']" :max="$meta['max']" :keterangan="$keterangan" />
                                @else
                                    <x-admin.field :name="$namaField" :label="$meta['label']"
                                                   :value="$nilai[$kunci] ?? null" :placeholder="$meta['default']"
                                                   :keterangan="$keterangan" />
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <x-admin.simpan />
            </form>
        </section>
    @endforeach
</div>
@endsection
```

- [ ] **Step 4: Tambahkan tautan sidebar**

Di `resources/views/admin/layouts/app.blade.php`, tepat sebelum tautan
"Pengaturan Desa" (baris 107 pada isi saat ini):

```blade
            <a href="{{ route('admin.teks-tampilan.index') }}"
               class="{{ request()->routeIs('admin.teks-tampilan.*') ? $tautanAktif : $tautanBiasa }}">
                Teks Tampilan
            </a>

            <a href="{{ route('admin.pengaturan') }}"
               class="{{ request()->routeIs('admin.pengaturan*') ? $tautanAktif : $tautanBiasa }}">
                Pengaturan Desa
            </a>
```

(baris "Pengaturan Desa" sudah ada — hanya menambahkan blok "Teks Tampilan"
sebelumnya, jangan menduplikasi baris Pengaturan Desa.)

- [ ] **Step 5: Jalankan seluruh file test, pastikan lulus**

Run: `php artisan test --filter=TeksTampilanTest`
Expected: PASS (13 test).

---

### Task 5: Integrasi Publik — Menu & Footer

**Files:**
- Modify: `app/Support/MenuPublik.php`
- Modify: `resources/views/publik/layouts/app.blade.php`
- Test: `tests/Feature/Konten/TeksTampilanTest.php` (tambah section)

**Interfaces:**
- Consumes: `Teks::ambil()` (Task 2).

- [ ] **Step 1: Tambahkan test yang gagal**

```php
    // ------------------------------------------------------------------
    // Integrasi publik — menu & footer
    // ------------------------------------------------------------------

    public function test_menu_dan_footer_memakai_teks_tersimpan(): void
    {
        TeksTampilan::create(['kunci' => 'menu.beranda', 'nilai' => 'Home', 'grup' => 'menu']);
        TeksTampilan::create(['kunci' => 'footer.pengelola', 'nilai' => 'Dikelola oleh Kelurahan.', 'grup' => 'footer']);
        TeksTampilan::create(['kunci' => 'footer.kolom_kontak', 'nilai' => 'Hubungi', 'grup' => 'footer']);

        $response = $this->get(route('beranda'));

        $response->assertSee('Home');
        $response->assertSee('Dikelola oleh Kelurahan.');
        $response->assertSee('Hubungi');
    }

    public function test_menu_dan_footer_memakai_default_saat_belum_diubah(): void
    {
        $response = $this->get(route('beranda'));

        $response->assertSee('Struktur Organisasi'); // menu.struktur default
        $response->assertSee('Dikelola oleh Pemerintah Desa.'); // footer.pengelola default
    }
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test --filter=TeksTampilanTest`
Expected: FAIL — `Home`/`Dikelola oleh Kelurahan.`/`Hubungi` belum muncul
(menu & footer masih memakai teks hardcode).

- [ ] **Step 3: Ubah `MenuPublik::TAUTAN` dan `tautan()`**

```php
<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * Navigasi halaman publik.
 *
 * Daftar tautan (rute & urutannya) tetap dikode — hanya label yang dinamis
 * lewat CMS Teks Tampilan (grup 'menu'). Tautan yang rutenya belum
 * terdaftar otomatis dilewati.
 *
 * @return array<int, array{label: string, url: string, aktif: bool}>
 */
class MenuPublik
{
    /** @var array<string, string> nama rute => kunci Teks Tampilan */
    private const TAUTAN = [
        'beranda' => 'menu.beranda',
        'publik.profil' => 'menu.profil',
        'publik.potensi.index' => 'menu.potensi',
        'publik.fasilitas' => 'menu.fasilitas',
        'publik.prestasi' => 'menu.prestasi',
        'publik.berita.index' => 'menu.berita',
        'publik.galeri.index' => 'menu.galeri',
        'publik.struktur' => 'menu.struktur',
        'publik.statistik' => 'menu.statistik',
    ];

    /**
     * @return array<int, array{label: string, url: string, aktif: bool}>
     */
    public static function tautan(): array
    {
        $hasil = [];

        foreach (self::TAUTAN as $rute => $kunci) {
            if (! Route::has($rute)) {
                continue;
            }

            $hasil[] = [
                'label' => Teks::ambil($kunci),
                'url' => route($rute),
                'aktif' => request()->routeIs($rute) || request()->routeIs(str_replace('.index', '.*', $rute)),
            ];
        }

        return $hasil;
    }
}
```

- [ ] **Step 4: Ubah footer di `resources/views/publik/layouts/app.blade.php`**

Ganti baris 103:
```blade
                    <p class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Kontak</p>
```
menjadi:
```blade
                    <p class="text-xs font-semibold tracking-wider text-slate-500 uppercase">{{ \App\Support\Teks::ambil('footer.kolom_kontak') }}</p>
```

Ganti baris 129:
```blade
                <p class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Halaman</p>
```
menjadi:
```blade
                <p class="text-xs font-semibold tracking-wider text-slate-500 uppercase">{{ \App\Support\Teks::ambil('footer.kolom_halaman') }}</p>
```

Ganti baris 139:
```blade
            &copy; {{ now()->year }} {{ $desa->nama_desa }}. Dikelola oleh Pemerintah Desa.
```
menjadi:
```blade
            &copy; {{ now()->year }} {{ $desa->nama_desa }}. {{ \App\Support\Teks::ambil('footer.pengelola') }}
```

- [ ] **Step 5: Jalankan test, pastikan lulus**

Run: `php artisan test --filter=TeksTampilanTest`
Expected: PASS (15 test).

- [ ] **Step 6: Jalankan test lain yang menyentuh menu/footer, pastikan tidak rusak**

Run: `php artisan test --filter=HalamanPublikTest`
Expected: PASS — semua default teks identik dengan string lama, tidak ada
regresi.

---

### Task 6: Integrasi Publik — Beranda (Kuota Controller & Teks Blade)

**Files:**
- Modify: `app/Http/Controllers/Publik/BerandaController.php`
- Modify: `resources/views/publik/beranda.blade.php`
- Test: `tests/Feature/Konten/TeksTampilanTest.php` (tambah section)

**Interfaces:**
- Consumes: `Teks::ambil()`, `Teks::angka()` (Task 2).

- [ ] **Step 1: Tambahkan test yang gagal**

```php
    // ------------------------------------------------------------------
    // Integrasi publik — beranda
    // ------------------------------------------------------------------

    public function test_beranda_menampilkan_teks_default(): void
    {
        $this->get(route('beranda'))->assertSee('Kenali Desa Kami');
    }

    public function test_beranda_menampilkan_teks_hasil_ubahan_admin(): void
    {
        TeksTampilan::create(['kunci' => 'beranda.hero.cta_profil', 'nilai' => 'Ayo Kenalan', 'grup' => 'beranda']);

        $this->get(route('beranda'))->assertSee('Ayo Kenalan');
    }

    public function test_nilai_html_dirender_ter_escape(): void
    {
        TeksTampilan::create([
            'kunci' => 'beranda.hero.cta_profil',
            'nilai' => '<script>alert(1)</script>',
            'grup' => 'beranda',
        ]);

        $html = $this->get(route('beranda'))->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    public function test_kuota_potensi_mengubah_jumlah_kartu_di_beranda(): void
    {
        foreach (range(1, 5) as $i) {
            \App\Models\PotensiDesa::create(['nama' => "Potensi {$i}", 'kategori' => 'umkm', 'tampil' => true]);
        }

        TeksTampilan::create(['kunci' => 'kuota.beranda.potensi', 'nilai' => '2', 'grup' => 'kuota']);

        $response = $this->get(route('beranda'))->assertOk();

        $this->assertCount(2, $response->viewData('potensi'));
    }

    public function test_kuota_default_potensi_tetap_enam(): void
    {
        foreach (range(1, 20) as $i) {
            \App\Models\PotensiDesa::create(['nama' => "Potensi {$i}", 'kategori' => 'umkm', 'tampil' => true]);
        }

        $response = $this->get(route('beranda'))->assertOk();

        $this->assertCount(6, $response->viewData('potensi'));
    }
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test --filter=TeksTampilanTest`
Expected: FAIL — `test_beranda_menampilkan_teks_hasil_ubahan_admin` dan
`test_nilai_html_dirender_ter_escape` gagal karena blade masih memakai teks
statis; dua test kuota kemungkinan sudah PASS kebetulan (nilai default sama
dengan `take()` lama) — itu wajar, lanjutkan ke implementasi.

- [ ] **Step 3: Ubah `BerandaController`**

```php
<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\AcaraDesa;
use App\Models\BeritaDesa;
use App\Models\FasilitasUmum;
use App\Models\Pengurus;
use App\Models\PotensiDesa;
use App\Models\PrestasiDesa;
use App\Queries\StatistikPublik;
use App\Support\Teks;
use Illuminate\View\View;

/**
 * Fitur 14 — halaman muka.
 *
 * Setiap bagian hanya memuat cuplikan, bukan seluruh data. Halaman muka desa
 * sering dibuka lewat koneksi seluler; memuat ratusan kartu di satu halaman
 * membuatnya lambat tanpa memberi manfaat apa pun. Jumlah cuplikan diatur
 * admin lewat CMS Teks Tampilan (grup 'kuota'), bukan lagi hardcode.
 */
class BerandaController extends Controller
{
    public function __invoke(StatistikPublik $statistik): View
    {
        return view('publik.beranda', [
            'potensi' => PotensiDesa::query()->tampil()->urut()->take(Teks::angka('kuota.beranda.potensi'))->get(),

            'beritaUtama' => BeritaDesa::query()->terbit()->where('utama', true)->with('kategori')->first()
                ?? BeritaDesa::query()->terbit()->with('kategori')->terbaru()->first(),

            'berita' => BeritaDesa::query()->terbit()->with('kategori')->terbaru()->take(Teks::angka('kuota.beranda.berita'))->get(),

            'acara' => AcaraDesa::query()->terbit()->withCount('foto')->with('foto')->terbaru()->take(Teks::angka('kuota.beranda.acara'))->get(),

            'fasilitas' => FasilitasUmum::query()->tampil()->orderBy('kategori')->take(Teks::angka('kuota.beranda.fasilitas'))->get(),

            'prestasi' => PrestasiDesa::query()->tampil()->terbaru()->take(Teks::angka('kuota.beranda.prestasi'))->get(),

            // Hanya pengurus inti; daftar lengkap ada di halaman khususnya.
            'pengurus' => Pengurus::query()
                ->tampil()
                ->with(['organisasi', 'wilayah'])
                ->join('ref_organisasi', 'pengurus_desa.organisasi_id', '=', 'ref_organisasi.id')
                ->orderBy('ref_organisasi.urutan')
                ->orderBy('pengurus_desa.urutan')
                ->select('pengurus_desa.*')
                ->take(Teks::angka('kuota.beranda.pengurus'))
                ->get(),

            'statistik' => $statistik->ringkasan(),
        ]);
    }
}
```

- [ ] **Step 4: Ubah `resources/views/publik/beranda.blade.php`**

Ganti seluruh isi berkas dengan versi berikut (struktur/kelas Tailwind tidak
berubah — hanya string statis yang diganti pemanggilan `Teks::ambil()`/`::angka()`):

```blade
@extends('publik.layouts.app')

@section('judul', 'Beranda')
@section('deskripsi', $desa->deskripsiMeta())

@php
    use App\Support\Teks;

    // Setiap bagian hanya dirender bila datanya ada, supaya tidak ada judul
    // bagian yang menggantung tanpa isi (PRD fitur 14).
    $seksi = fn ($kondisi) => $kondisi;
@endphp

@section('konten')

{{-- ============================== HERO ============================== --}}
<section class="relative overflow-hidden bg-slate-900">
    @if (filled($desa->foto_sampul))
        <img src="{{ $desa->urlSampul() }}" alt=""
             class="absolute inset-0 h-full w-full object-cover opacity-45">
    @endif

    <div class="relative mx-auto max-w-6xl px-4 py-24 sm:px-6 sm:py-32">
        <div class="max-w-2xl">
            @if ($desa->wilayahLengkap())
                <p class="text-sm font-medium tracking-wider text-emerald-300 uppercase">
                    {{ $desa->wilayahLengkap() }}
                </p>
            @endif

            <h1 class="mt-3 text-4xl leading-tight font-semibold text-white sm:text-5xl">
                {{ Teks::ambil('beranda.hero.sapaan') }}<br>{{ $desa->nama_desa }}
            </h1>

            @if (filled($desa->meta_description) || filled($desa->visi))
                <p class="mt-5 text-lg leading-relaxed text-slate-200">
                    {{ Str::limit($desa->meta_description ?: $desa->visi, Teks::angka('kuota.beranda.limit_sapaan')) }}
                </p>
            @endif

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('publik.profil') }}"
                   class="rounded-lg bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-500">
                    {{ Teks::ambil('beranda.hero.cta_profil') }}
                </a>
                <a href="#potensi"
                   class="rounded-lg border border-white/30 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                    {{ Teks::ambil('beranda.hero.cta_potensi') }}
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ============================ STATISTIK ============================ --}}
<section id="statistik" class="border-b border-slate-200 bg-slate-50 py-14">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                [Teks::ambil('beranda.statistik.label_penduduk'), number_format($statistik['jumlah_penduduk'], 0, ',', '.'), Teks::ambil('beranda.statistik.satuan_jiwa')],
                [Teks::ambil('beranda.statistik.label_kk'), $statistik['jumlah_kk'] !== null ? number_format($statistik['jumlah_kk'], 0, ',', '.') : null, Teks::ambil('beranda.statistik.satuan_keluarga')],
                [Teks::ambil('beranda.statistik.label_laki'), number_format($statistik['laki'], 0, ',', '.'), Teks::ambil('beranda.statistik.satuan_jiwa')],
                [Teks::ambil('beranda.statistik.label_perempuan'), number_format($statistik['perempuan'], 0, ',', '.'), Teks::ambil('beranda.statistik.satuan_jiwa')],
            ] as [$label, $nilai, $satuan])
                @if ($nilai !== null)
                    <div class="text-center">
                        <p class="text-3xl font-semibold text-slate-900 sm:text-4xl">{{ $nilai }}</p>
                        <p class="mt-1 text-sm font-medium text-slate-700">{{ $label }}</p>
                        <p class="text-xs text-slate-400">{{ $satuan }}</p>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('publik.statistik') }}"
               class="text-sm font-medium text-emerald-700 hover:text-emerald-800">
                {{ Teks::ambil('beranda.statistik.tautan') }}
            </a>
        </div>
    </div>
</section>

{{-- ========================== SEKILAS DESA ========================== --}}
@if (filled($desa->visi) || filled($desa->sejarah))
    <section id="profil" class="py-16">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-2">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">{{ Teks::ambil('beranda.sekilas.prefix') }} {{ $desa->nama_desa }}</h2>
                <p class="mt-4 leading-relaxed text-slate-600">
                    {{ Str::limit(strip_tags($desa->sejarah ?: $desa->visi), Teks::angka('kuota.beranda.limit_sekilas')) }}
                </p>
                <a href="{{ route('publik.profil') }}"
                   class="mt-6 inline-block rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                    {{ Teks::ambil('beranda.sekilas.cta') }}
                </a>
            </div>

            @if ($desa->misiSebagaiDaftar())
                <div class="rounded-2xl bg-slate-50 p-6">
                    <h3 class="mb-4 text-sm font-semibold tracking-wider text-slate-500 uppercase">{{ Teks::ambil('beranda.sekilas.judul_misi') }}</h3>
                    <ol class="list-decimal space-y-2 pl-5 text-sm text-slate-700">
                        @foreach (array_slice($desa->misiSebagaiDaftar(), 0, 4) as $misi)
                            <li>{{ $misi }}</li>
                        @endforeach
                    </ol>
                </div>
            @endif
        </div>
    </section>
@endif

{{-- ============================ POTENSI ============================ --}}
@if ($potensi->isNotEmpty())
    <section id="potensi" class="border-t border-slate-200 py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">{{ Teks::ambil('beranda.potensi.judul') }}</h2>
                    <p class="mt-1.5 text-slate-600">{{ Teks::ambil('beranda.potensi.subjudul') }}</p>
                </div>
                <a href="{{ route('publik.potensi.index') }}"
                   class="text-sm font-medium text-emerald-700 hover:text-emerald-800">{{ Teks::ambil('beranda.potensi.tautan') }}</a>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($potensi as $item)
                    <a href="{{ route('publik.potensi.show', $item) }}"
                       class="group overflow-hidden rounded-2xl border border-slate-200 transition hover:border-slate-300 hover:shadow-md">
                        <div class="aspect-[4/3] overflow-hidden bg-slate-100">
                            <img src="{{ $item->urlFotoKecil() }}" alt="{{ $item->nama }}" loading="lazy"
                                 class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        </div>
                        <div class="p-5">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $item->kategori->warna() }}">
                                {{ $item->kategori->label() }}
                            </span>
                            <h3 class="mt-3 font-semibold text-slate-900 group-hover:text-emerald-700">{{ $item->nama }}</h3>
                            <p class="mt-1.5 line-clamp-2 text-sm text-slate-600">{{ $item->ringkasan() }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- ============================= BERITA ============================= --}}
@if ($beritaUtama)
    <section id="berita" class="border-t border-slate-200 bg-slate-50 py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">{{ Teks::ambil('beranda.berita.judul') }}</h2>
                    <p class="mt-1.5 text-slate-600">{{ Teks::ambil('beranda.berita.subjudul') }}</p>
                </div>
                <a href="{{ route('publik.berita.index') }}"
                   class="text-sm font-medium text-emerald-700 hover:text-emerald-800">{{ Teks::ambil('beranda.berita.tautan') }}</a>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <a href="{{ route('publik.berita.show', $beritaUtama) }}"
                   class="group overflow-hidden rounded-2xl bg-white transition hover:shadow-md">
                    <div class="aspect-[16/9] overflow-hidden bg-slate-100">
                        <img src="{{ $beritaUtama->urlSampul() }}" alt="{{ $beritaUtama->judul }}" loading="lazy"
                             class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    </div>
                    <div class="p-6">
                        @if ($beritaUtama->kategori)
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                {{ $beritaUtama->kategori->nama }}
                            </span>
                        @endif
                        <h3 class="mt-3 text-xl font-semibold text-slate-900 group-hover:text-emerald-700">
                            {{ $beritaUtama->judul }}
                        </h3>
                        <p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ $beritaUtama->ringkasanTampil() }}</p>
                        <p class="mt-3 text-xs text-slate-400">
                            {{ $beritaUtama->tanggal_terbit?->translatedFormat('d F Y') }}
                        </p>
                    </div>
                </a>

                <div class="space-y-4">
                    @foreach ($berita->where('id', '!=', $beritaUtama->id)->take(Teks::angka('kuota.beranda.berita_samping')) as $item)
                        <a href="{{ route('publik.berita.show', $item) }}"
                           class="group flex gap-4 rounded-2xl bg-white p-4 transition hover:shadow-md">
                            <img src="{{ $item->urlSampulKecil() }}" alt="" loading="lazy"
                                 class="h-20 w-28 shrink-0 rounded-xl bg-slate-100 object-cover">
                            <div class="min-w-0">
                                <h3 class="line-clamp-2 font-medium text-slate-900 group-hover:text-emerald-700">
                                    {{ $item->judul }}
                                </h3>
                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $item->tanggal_terbit?->translatedFormat('d F Y') }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif

{{-- ============================= GALERI ============================= --}}
@if ($acara->isNotEmpty())
    <section id="galeri" class="border-t border-slate-200 py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">{{ Teks::ambil('beranda.galeri.judul') }}</h2>
                    <p class="mt-1.5 text-slate-600">{{ Teks::ambil('beranda.galeri.subjudul') }}</p>
                </div>
                <a href="{{ route('publik.galeri.index') }}"
                   class="text-sm font-medium text-emerald-700 hover:text-emerald-800">{{ Teks::ambil('beranda.galeri.tautan') }}</a>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                @foreach ($acara as $item)
                    <a href="{{ route('publik.galeri.show', $item) }}"
                       class="group relative aspect-[4/3] overflow-hidden rounded-2xl bg-slate-100">
                        <img src="{{ $item->urlSampulKecil() }}" alt="{{ $item->judul }}" loading="lazy"
                             class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-900/85 to-transparent p-4">
                            <p class="line-clamp-2 text-sm font-medium text-white">{{ $item->judul }}</p>
                            <p class="mt-0.5 text-xs text-white/70">
                                {{ $item->tanggal_acara->translatedFormat('d M Y') }}
                                @if ($item->foto_count > 1) · {{ $item->foto_count }} {{ Teks::ambil('beranda.galeri.suffix_foto') }} @endif
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- ===================== FASILITAS & PRESTASI ===================== --}}
@if ($fasilitas->isNotEmpty() || $prestasi->isNotEmpty())
    <section class="border-t border-slate-200 bg-slate-50 py-16">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-2">

            @if ($fasilitas->isNotEmpty())
                <div>
                    <div class="mb-5 flex items-end justify-between gap-3">
                        <h2 class="text-2xl font-semibold text-slate-900">{{ Teks::ambil('beranda.fasilitas.judul') }}</h2>
                        <a href="{{ route('publik.fasilitas') }}"
                           class="text-sm font-medium text-emerald-700 hover:text-emerald-800">{{ Teks::ambil('beranda.tautan_lihat_semua') }}</a>
                    </div>
                    <div class="space-y-3">
                        @foreach ($fasilitas as $item)
                            <div class="flex items-center gap-4 rounded-xl bg-white p-4">
                                <img src="{{ $item->urlFotoKecil() }}" alt="" loading="lazy"
                                     class="h-14 w-14 shrink-0 rounded-lg bg-slate-100 object-cover">
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-slate-900">{{ $item->nama }}</p>
                                    <span class="rounded-full px-2 py-0.5 text-xs {{ $item->kategori->warna() }}">
                                        {{ $item->kategori->label() }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($prestasi->isNotEmpty())
                <div>
                    <div class="mb-5 flex items-end justify-between gap-3">
                        <h2 class="text-2xl font-semibold text-slate-900">{{ Teks::ambil('beranda.prestasi.judul') }}</h2>
                        <a href="{{ route('publik.prestasi') }}"
                           class="text-sm font-medium text-emerald-700 hover:text-emerald-800">{{ Teks::ambil('beranda.tautan_lihat_semua') }}</a>
                    </div>
                    <div class="space-y-3">
                        @foreach ($prestasi as $item)
                            <div class="rounded-xl bg-white p-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-slate-900 tabular-nums">{{ $item->tahun }}</span>
                                    @if ($item->tingkat)
                                        <span class="rounded-full px-2 py-0.5 text-xs {{ $item->tingkat->warna() }}">
                                            {{ $item->tingkat->label() }}
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-1 font-medium text-slate-900">{{ $item->judul }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endif

{{-- ============================ PENGURUS ============================ --}}
@if ($pengurus->isNotEmpty())
    <section id="pengurus" class="border-t border-slate-200 py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">{{ Teks::ambil('beranda.pengurus.judul') }}</h2>
                    <p class="mt-1.5 text-slate-600">{{ Teks::ambil('beranda.pengurus.subjudul') }}</p>
                </div>
                <a href="{{ route('publik.struktur') }}"
                   class="text-sm font-medium text-emerald-700 hover:text-emerald-800">{{ Teks::ambil('beranda.pengurus.tautan') }}</a>
            </div>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($pengurus as $orang)
                    <div class="rounded-2xl border border-slate-200 p-5 text-center">
                        <img src="{{ $orang->urlFotoKecil() }}" alt="Foto {{ $orang->nama }}" loading="lazy"
                             class="mx-auto h-24 w-24 rounded-full bg-slate-100 object-cover">
                        <p class="mt-3 font-semibold text-slate-900">{{ $orang->nama }}</p>
                        <p class="text-sm text-emerald-700">{{ $orang->jabatan }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- ============================= KONTAK ============================= --}}
@if ($desa->punyaKontak() || filled($desa->peta_embed))
    <section id="kontak" class="border-t border-slate-200 bg-slate-50 py-16">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-2">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">{{ Teks::ambil('beranda.kontak.judul') }}</h2>
                <p class="mt-2 text-slate-600">
                    {{ Teks::ambil('beranda.kontak.kalimat', ['{nama}' => $desa->nama_desa]) }}
                </p>

                <dl class="mt-6 space-y-3 text-sm">
                    @foreach ([
                        Teks::ambil('beranda.kontak.label_alamat') => $desa->alamat_kantor,
                        Teks::ambil('beranda.kontak.label_telepon') => $desa->telepon_kantor,
                        Teks::ambil('beranda.kontak.label_email') => $desa->email,
                    ] as $label => $nilai)
                        @if (filled($nilai))
                            <div class="flex gap-3">
                                <dt class="w-20 shrink-0 text-slate-500">{{ $label }}</dt>
                                <dd class="text-slate-800">{{ $nilai }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </div>

            @if (filled($desa->peta_embed))
                <div class="overflow-hidden rounded-2xl ring-1 ring-slate-200">
                    <iframe src="{{ $desa->peta_embed }}" class="h-72 w-full" style="border:0"
                            loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                            title="Peta lokasi {{ $desa->nama_desa }}"></iframe>
                </div>
            @endif
        </div>
    </section>
@endif

@endsection
```

> Catatan: `@php use App\Support\Teks; @endphp` di atas berlaku untuk sisa
> berkas blade ini (Blade meng-compile `@php` blok menjadi PHP biasa, dan
> `use` di scope file PHP hasil kompilasi berlaku untuk seluruh file). Bila
> ternyata tidak berfungsi seperti PHP `use` biasa saat dijalankan (periksa
> di Step 5), ganti seluruh pemanggilan `Teks::ambil(...)` /
> `Teks::angka(...)` menjadi `\App\Support\Teks::ambil(...)` /
> `\App\Support\Teks::angka(...)` sepenuhnya, mengikuti pola
> `\App\Support\MenuPublik::tautan()` yang sudah dipakai di layout publik.

- [ ] **Step 5: Jalankan test, pastikan lulus**

Run: `php artisan test --filter=TeksTampilanTest`
Expected: PASS (semua test di file ini, sekitar 19-20 test). Jika muncul
error `Class "Teks" not found`, terapkan catatan di Step 4 (ganti ke FQCN
`\App\Support\Teks::`) dan jalankan ulang.

- [ ] **Step 6: Jalankan seluruh test suite proyek**

Run: `php artisan test`
Expected: PASS — seluruh test lama (termasuk `HalamanPublikTest`,
`DialogKonfirmasiTest`, dll. yang menyentuh beranda) tetap lulus karena nilai
default identik dengan string lama.

---

### Task 7: Dokumentasi, Verifikasi Akhir, dan Commit

**Files:**
- Modify: `CLAUDE.md`

**Interfaces:** (tidak ada — task dokumentasi & commit)

- [ ] **Step 1: Perbarui `CLAUDE.md` §4.3 (rute admin)**

Di tabel "Data referensi & pengaturan" (atau tabel baru sebelum "Struktur
organisasi"), tambahkan baris:

```markdown
**Teks Tampilan**

| Method | URI | Nama | Aksi |
|---|---|---|---|
| GET | `admin/teks-tampilan` | `admin.teks-tampilan.index` | `TeksTampilanController::tampilkan` |
| PUT | `admin/teks-tampilan/{grup}` | `admin.teks-tampilan.perbarui` | `TeksTampilanController::perbarui` |
```

- [ ] **Step 2: Perbarui `CLAUDE.md` §6 (skema database)**

Tambahkan baris migrasi ke tabel migrasi:
```markdown
| `..._000008_create_teks_tampilan_table` | `teks_tampilan` |
```

Tambahkan deskripsi kolom:
```markdown
**`teks_tampilan`** — CMS key-value untuk teks & kuota tampilan publik.
`kunci` (unique), `nilai` (nullable — kosong berarti pakai default dari
`config/teks-tampilan.php`), `grup`, `admin_id`. Fase 1: hanya kunci
`beranda.*`, `footer.*`, `menu.*`, `kuota.beranda.*` — lihat ANALISIS-CMS.md.
```

- [ ] **Step 3: Perbarui `CLAUDE.md` §7 (app/Support/) dan §9 (config)**

Di daftar `app/Support/` tambahkan:
```markdown
- **`Teks`** — `ambil(string $kunci, array $ganti = []): string`,
  `angka(string $kunci): int`. Sumber teks & kuota tampilan dinamis
  (grup `beranda`, `footer`, `menu`, `kuota`). Cache → DB → default
  `config('teks-tampilan')`.
```

Di §9 tambahkan:
```markdown
`config/teks-tampilan.php` — metadata (grup, label, tipe, default, batas
validasi) untuk setiap kunci CMS Teks Tampilan fase 1. Menambah kunci baru
di fase berikutnya cukup menambah entri di sini, tanpa migrasi baru.
```

- [ ] **Step 4: Perbarui `CLAUDE.md` §13 (riwayat commit) dan baris status**

Tambahkan baris ke tabel riwayat commit (isi hash sebenarnya setelah commit
dibuat di Step 6 — gunakan placeholder lalu jalankan `git log -1
--format=%h` setelah commit dan edit hash-nya jika perlu):
```markdown
| `<hash>` | CMS Teks Tampilan: beranda, footer, menu, dan kuota dari admin panel |
```

Perbarui baris status paling atas berkas (jumlah rute +2, jumlah tes sesuai
hasil `php artisan test` di Step 5).

- [ ] **Step 5: Jalankan seluruh test suite sekali lagi**

Run: `php artisan test`
Expected: PASS — seluruh test (lama + `TeksTampilanTest`) lulus. Catat
jumlah test & asersi untuk mengisi baris status CLAUDE.md di Step 4.

- [ ] **Step 6: Commit satu kali untuk seluruh fitur**

```bash
git add database/migrations/2026_08_04_000008_create_teks_tampilan_table.php \
        app/Models/TeksTampilan.php \
        config/teks-tampilan.php \
        app/Support/Teks.php \
        app/Http/Requests/Admin/UpdateTeksTampilanRequest.php \
        app/Http/Controllers/Admin/TeksTampilanController.php \
        resources/views/admin/teks-tampilan/index.blade.php \
        resources/views/admin/layouts/app.blade.php \
        app/Support/MenuPublik.php \
        resources/views/publik/layouts/app.blade.php \
        app/Http/Controllers/Publik/BerandaController.php \
        resources/views/publik/beranda.blade.php \
        routes/web.php \
        tests/Feature/Konten/TeksTampilanTest.php \
        CLAUDE.md

git commit -m "$(cat <<'EOF'
CMS Teks Tampilan: beranda, footer, menu, dan kuota dari admin panel

Seluruh teks statis dan kuota cuplikan di halaman muka (beranda, footer,
menu navigasi) kini diatur admin lewat tabel key-value teks_tampilan,
bukan lagi hardcode di kode. Halaman publik lain menyusul di fase
berikutnya — lihat ANALISIS-CMS.md.
EOF
)"
```

---

## Self-Review

**Cakupan spec:** §3 Model Data → Task 1 & 2. §4 Halaman Admin → Task 3 & 4.
§5 Integrasi Publik → Task 5 (menu/footer) & Task 6 (beranda). §6 Pengujian
→ tersebar di setiap task (fallback default, simpan+cache, kosongkan=default,
kuota, whitelist, escaping, guard admin) plus regresi `HalamanPublikTest` di
Task 5/6. §7 Dokumentasi → Task 7. §8 Di luar cakupan → tidak ada task yang
menyentuhnya (diverifikasi: tidak ada perubahan ke profil/statistik/berita/
potensi/fasilitas/prestasi/galeri/struktur/404 di file manapun pada plan ini).

**Placeholder scan:** tidak ditemukan TBD/TODO; satu catatan eksplisit di
Task 6 Step 4 tentang risiko `@php use ...; @endphp` bukan penyembunyian
detail — sudah disertai instruksi konkret (ganti ke FQCN) dan langkah
verifikasi (Step 5) bila terjadi.

**Konsistensi tipe:** `Teks::ambil(string, array): string` dan
`Teks::angka(string): int` dipakai identik dari Task 2 sampai Task 6.
`TeksTampilan::semua(): array<string, string|null>` dipakai identik di
Task 2 (Teks) dan Task 3 (Controller::tampilkan). Nama field HTTP termanggl
(`nilai[kunci__termanggl]`) konsisten antara Task 3 (rules/attributes),
Task 4 (view), dan test di Task 3/4.
