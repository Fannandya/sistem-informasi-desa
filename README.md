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

## Penyimpanan Gambar

Seluruh unggahan gambar melewati satu layanan, `App\Services\LayananUnggahGambar`. Tidak ada fitur yang boleh memanggil `Storage::` langsung untuk gambar — aturan format, ukuran, kompresi, penamaan, dan folder ditetapkan sekali di [`config/unggahan.php`](config/unggahan.php).

Yang dilakukan layanan ini pada setiap gambar masuk:

| Langkah | Perilaku |
|---|---|
| Validasi | jpg / jpeg / png / webp, maksimal 4 MB |
| Penamaan | diacak 32 karakter — nama berkas asli dari perangkat admin tidak pernah bocor ke URL |
| Ukuran | dikecilkan ke lebar maks 1600 px; gambar yang sudah kecil tidak diperbesar |
| Format | dikonversi ke WebP kualitas 82 |
| Thumbnail | dibuat otomatis di `{folder}/thumb/`, lebar 400 px, untuk daftar dan kartu |
| Penggantian | gambar baru disimpan **lebih dulu**, gambar lama dihapus setelah berhasil |

Data tanpa foto tidak error — layanan mengembalikan gambar pengganti dari `public/images/placeholder/`.

### Beralih ke Cloudflare R2

Aplikasi saat ini memakai disk lokal (`storage/app/public`). Berpindah ke R2 **tidak memerlukan perubahan kode sama sekali** — cukup `.env`:

```dotenv
UPLOAD_DISK=r2

R2_ACCESS_KEY_ID=<access key dari R2 API Token>
R2_SECRET_ACCESS_KEY=<secret key dari R2 API Token>
R2_BUCKET=<nama bucket>
R2_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com
R2_URL=https://<domain-publik-bucket>
```

Langkah menyiapkan bucket di dashboard Cloudflare:

1. **R2 → Create bucket**, beri nama, pilih lokasi terdekat (mis. APAC).
2. Buka bucket → **Settings → Public access** → aktifkan akses publik, atau sambungkan domain kustom. Alamat yang muncul itulah `R2_URL`.
3. **R2 → Manage API Tokens → Create API Token**, beri izin **Object Read & Write** terbatas pada bucket tersebut. Salin Access Key dan Secret Key — keduanya hanya ditampilkan sekali.
4. `R2_ENDPOINT` memakai Account ID Anda, bukan nama bucket.

Setelah `.env` diisi, pastikan koneksinya benar sebelum mengunggah apa pun:

```bash
php artisan config:clear
php artisan unggahan:uji
```

Perintah itu menulis satu berkas uji, membacanya kembali, menampilkan URL publiknya, lalu menghapusnya. Bila ada variabel yang belum diisi, perintah berhenti dan menyebut variabel mana yang kurang.

> Konfigurasi R2 sudah diatur benar di `config/filesystems.php`: `region` wajib `auto` dan `use_path_style_endpoint` wajib `true`. Keduanya adalah sumber galat tersering saat memakai R2 lewat driver S3.

### Berkas yatim

Gambar ikut terhapus otomatis saat datanya dihapus. Untuk memeriksa berkas yang tertinggal — misalnya akibat gangguan jaringan saat penghapusan:

```bash
php artisan unggahan:berkas-yatim            # hanya melaporkan
php artisan unggahan:berkas-yatim --hapus    # menghapus setelah konfirmasi
```

Perintah ini sengaja tidak menghapus apa pun secara otomatis. Menghapus berdasarkan selisih database–penyimpanan berisiko: satu bug pembacaan bisa memusnahkan seluruh foto desa.

---

## Identitas Desa

Nama desa, logo, visi-misi, kontak, dan peta **tidak pernah ditulis di dalam kode**. Semuanya berasal dari satu baris pada tabel `pengaturan_desa`, diatur admin lewat **Panel Admin → Pengaturan Desa**, dan tersedia di seluruh tampilan sebagai variabel `$desa`.

Halaman pengaturan terbagi lima bagian dengan tombol simpan masing-masing, sehingga menyunting kontak tidak berisiko menimpa profil.

### Kode peta Google Maps

Admin cukup menempelkan kode embed dari Google Maps (Bagikan → Sematkan peta → Salin HTML). Yang **disimpan hanya alamat `src`-nya**, setelah dipastikan berasal dari domain Google Maps dan memakai `https`. Peta kemudian dirender ulang oleh aplikasi di dalam iframe yang atributnya ditentukan sistem.

Ini disengaja: menyimpan lalu menampilkan ulang HTML mentah dari admin akan menjadikan kolom itu celah XSS di setiap halaman publik. Ada 7 kasus uji yang memastikan skrip, iframe dari domain lain, `javascript:` URI, dan URL non-https ditolak.

---

## Data Penduduk

Yang **tidak** disimpan, dan tidak boleh ditambahkan: NIK, nomor Kartu Keluarga, nomor telepon warga, dan alamat rumah detail. Larangan ini dijaga pengujian otomatis dengan **daftar putih** — kolom baru apa pun pada tabel `penduduk` akan menggagalkan tes, sehingga penambahannya harus disengaja dan ditinjau ulang terhadap PRD §2.4.

### Umur tidak disimpan

Yang disimpan adalah tanggal lahir; umur dihitung terhadap hari ini setiap kali dibutuhkan. Angka umur yang disimpan akan basi tanpa ada yang menyadarinya — ada tes yang memajukan waktu lima tahun dan memastikan umurnya ikut bergeser.

Penyaringan kelompok umur memakai rentang tanggal lahir, bukan menghitung umur di dalam SQL: fungsi penghitung umur berbeda antara MySQL dan SQLite. Ada tes yang membandingkan hasil saringan SQL dengan perhitungan PHP untuk setiap kelompok agar keduanya tidak menyimpang diam-diam.

### Kelahiran bukan "baris bertambah"

Menambah warga bukan berarti kelahiran, dan mengarsipkan bukan berarti kematian. Setiap warga punya penanda mutasi:

| Metrik | Sumber |
|---|---|
| Kelahiran | `jenis_masuk = lahir` — bukan pendatang, bukan pendataan awal |
| Kematian | `alasan_keluar = meninggal` — bukan pindah keluar, bukan data duplikat |

Tanpa pembeda ini, satu kali impor 2.000 warga akan terbaca sebagai ledakan kelahiran, dan setiap pembersihan data ganda akan terbaca sebagai kematian.

### Arsip, bukan hapus

Warga yang keluar dari daftar aktif diarsipkan beserta sebabnya, dan dapat dipulihkan dari **Data Warga → Arsip Warga**. Pemulihan mengosongkan alasan dan tanggal keluar, supaya warga yang dipulihkan tidak tetap terhitung pada statistik kematian.

### Impor Excel/CSV

**Data Warga → Impor.** Unduh berkas contoh lebih dulu untuk melihat susunan kolomnya.

- Batas 2.000 baris dan 5 MB per berkas (tidak ada queue worker di shared hosting — lihat PRD §8).
- Berkas yang memuat kolom NIK, nomor KK, atau nomor telepon **ditolak seluruhnya** sebelum satu baris pun diproses.
- Wilayah dan pekerjaan yang belum terdaftar **tidak dibuat otomatis**. Salah ketik nama dusun akan diam-diam menambah wilayah palsu dan memecah statistik sebaran, jadi barisnya dilaporkan gagal.
- Baris yang gagal dilaporkan beserta nomor barisnya di berkas asli dan alasannya, sehingga bisa diperbaiki lalu diunggah ulang.
- Seluruh proses berjalan dalam satu transaksi: impor yang gagal di tengah jalan tidak meninggalkan separuh data.

**Deteksi ganda.** Tanpa NIK atau nomor KK tidak ada kunci unik yang benar-benar pasti. Sistem memakai kombinasi **nama + tanggal lahir + wilayah** — petunjuk terkuat yang tersedia, tetapi bukan kepastian. Baris yang dilewati ditampilkan agar admin bisa memeriksanya, dan deteksi ini bisa dimatikan bila desa memang punya dua warga dengan ketiga data itu sama.

---

## Jumlah KK

Nomor Kartu Keluarga sengaja tidak disimpan, sehingga jumlah keluarga **tidak bisa dihitung otomatis** dari data warga. Angkanya diinput admin per RT lewat **Panel Admin → Rekap KK**, lalu dijumlahkan sistem.

Satu hal yang membedakan halaman ini dari form biasa: **kosong dan nol bukan hal yang sama**. Isian kosong berarti "RT ini belum direkap"; angka `0` berarti "sudah diperiksa, memang tidak ada KK". Tanpa pembedaan itu, sistem tidak bisa tahu apakah total yang ditampilkan sudah lengkap atau masih kurang.

Karena itu setiap tampilan Jumlah KK mencantumkan tahun datanya, dan dashboard admin menandai bila masih ada RT yang kosong:

> Jumlah KK **94** · data tahun 2026 · ⚠ 3 RT belum diisi

**Periode data.** Angka selalu diambil dari tahun rekap **terakhir yang memiliki data**, bukan otomatis tahun berjalan. Awal tahun, sebelum admin sempat merekap, memakai tahun berjalan akan menampilkan "0 KK" seolah seluruh keluarga desa lenyap.

Aturan ini dipusatkan di `App\Queries\RingkasanKk` dan dipakai bersama oleh dashboard admin dan halaman statistik publik, sehingga keduanya tidak mungkin menampilkan angka berbeda. Bedanya hanya penanda "belum lengkap", yang muncul di panel admin saja.

**Salin tahun sebelumnya.** Tombol ini memuat angka tahun lalu ke formulir sebagai titik awal, tetapi **tidak langsung menyimpannya** — admin masih harus memeriksa lalu menekan Simpan. Menyimpan langsung akan membuat angka tahun lalu tercatat sebagai angka tahun ini meski belum diverifikasi siapa pun.

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
| 1 | 02 Integrasi Penyimpanan R2 | Selesai — berjalan di disk lokal, tinggal isi kredensial R2 |
| 1 | 03 Pengaturan & Identitas Desa | Selesai |
| 2 | 04 Data Penduduk | Selesai |
| 2 | 05 Rekap KK per Wilayah | Selesai |
| 2 | 06 Statistik Warga (dashboard) | Selesai |
| 2 | 07 Ekspor Data Penduduk | Belum |
| 3 | 08–13 Konten desa | Belum |
| 4 | 14–15 Etalase publik | Belum |
