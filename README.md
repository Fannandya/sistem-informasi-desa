# Sistem Informasi Desa

Website profil dan pendataan demografi desa. Dua sisi: halaman publik tanpa login untuk promosi desa, dan panel admin untuk aparat desa mengelola data penduduk serta konten.

Acuan lengkap: [PRD.md](PRD.md) · Daftar fitur & urutan pengerjaan: [features/README.md](features/README.md)

---

## Kebutuhan Sistem

| Komponen | Versi |
|---|---|
| PHP | 8.2+ (ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`, `zip`, `curl`, `intl`) |
| MySQL / MariaDB | 8.0 / 10.6+ |
| Node.js | 20+ (hanya untuk build aset, tidak dibutuhkan di server) |
| Composer | 2.x |

Laravel 12 dipakai karena merupakan rilis tertinggi yang masih mendukung PHP 8.2.

---

## Menjalankan di Komputer Lokal

```bash
# 1. Dependensi
php composer.phar install
npm install

# 2. Konfigurasi
cp .env.example .env          # lalu sesuaikan bagian DB_* dan R2_*
php artisan key:generate

# 3. Database
php artisan migrate --seed

# 4. Aset
npm run build                 # atau `npm run dev` selama pengembangan

# 5. Jalankan
php artisan serve
```

Buka `http://localhost:8000` untuk halaman publik dan `http://localhost:8000/admin` untuk panel admin.

### Akun awal dari seeder

| Email | Kata sandi |
|---|---|
| `admin@desa.test` | `rahasia123` |

**Ganti kata sandi ini sebelum dipakai sungguhan.** Akun seeder hanya untuk pengembangan lokal.

---

## Mengelola Akun Admin

Sistem ini **tidak punya halaman pendaftaran mandiri dan tidak punya halaman "lupa kata sandi" yang dapat diakses publik**. Keputusan ini diambil di [fitur 01](features/01-autentikasi-admin.md): halaman reset publik adalah permukaan serangan yang tidak sebanding manfaatnya untuk sistem dengan segelintir akun.

Karena itu seluruh pengelolaan akun dilakukan dari terminal (SSH pada server, atau terminal lokal):

```bash
# Melihat akun yang terdaftar
php artisan admin:daftar

# Membuat akun admin baru
php artisan admin:buat
php artisan admin:buat --nama="Sekretaris Desa" --email=sekdes@desa.test

# Mengatur ulang kata sandi yang terlupa
php artisan admin:reset-sandi admin@desa.test
```

`admin:reset-sandi` juga mengosongkan token "ingat saya", sehingga sesi lama di perangkat lain langsung tidak berlaku.

> **Penting:** bila seluruh akun admin kehilangan kata sandinya dan Anda tidak punya akses terminal ke server, sistem tidak dapat dipulihkan lewat web. Pastikan akses SSH atau Terminal cPanel tersedia sebelum dipakai produksi.

---

## Keamanan Autentikasi

| Perlindungan | Perilaku |
|---|---|
| Kata sandi | Hash bcrypt, tidak pernah tersimpan sebagai teks biasa |
| Percobaan login | Maksimal 5 kali gagal per kombinasi email + IP, lalu diblokir 60 detik |
| Pesan kegagalan | Sama untuk email salah maupun kata sandi salah, agar tidak membocorkan email terdaftar |
| Timeout sesi | 120 menit tanpa aktivitas, lalu logout dengan pesan yang jelas |
| Tombol kembali | Halaman admin dikirim dengan `no-store`, sehingga tidak muncul dari cache setelah logout |
| Ganti kata sandi | Sesi di perangkat lain ikut berakhir |

---

## Pengujian

```bash
php artisan test
```

Tes berjalan di atas SQLite in-memory, jadi tidak menyentuh database MySQL Anda.

---

## Cloudflare R2

Belum aktif — dikerjakan pada [fitur 02](features/02-integrasi-penyimpanan-r2.md). Variabel yang perlu diisi di `.env`:

```
R2_ACCESS_KEY_ID=
R2_SECRET_ACCESS_KEY=
R2_BUCKET=
R2_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com
R2_URL=https://<domain-publik-bucket>
```

Seluruh unggahan gambar wajib masuk ke R2, bukan ke penyimpanan lokal server.

---

## Deployment ke Shared Hosting cPanel

Batasan yang mengikat desain aplikasi ada di [PRD §8](PRD.md). Ringkasnya:

1. **Build aset di komputer lokal** (`npm run build`), lalu unggah folder `public/build`. Server cPanel umumnya tidak punya Node.js.
2. Arahkan `public_html` ke folder `public`, letakkan folder aplikasi di luar `public_html`.
3. Tidak ada queue worker: impor Excel dibatasi 2.000 baris / 5 MB, unggah foto 4 MB per berkas.
4. `SESSION_DRIVER=database` — jangan diubah ke `file`, karena timeout sesi dan logout perangkat lain bergantung padanya.
5. Set `APP_DEBUG=false` dan `APP_ENV=production`.

---

## Status Pengerjaan

| Fase | Fitur | Status |
|---|---|---|
| 1 | 01 Autentikasi Admin | Selesai |
| 1 | 02 Integrasi Penyimpanan R2 | Belum |
| 1 | 03 Pengaturan & Identitas Desa | Belum |
| 2 | 04–07 Data penduduk, rekap KK, statistik, ekspor | Belum |
| 3 | 08–13 Konten desa | Belum |
| 4 | 14–15 Etalase publik | Belum |
