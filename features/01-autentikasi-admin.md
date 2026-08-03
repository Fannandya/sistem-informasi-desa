# Autentikasi Admin

Memberikan akses masuk khusus aparat desa agar data desa aman.

> **Fase 1 — Fondasi.** Tidak ada dependensi. Fitur ini harus selesai lebih dulu karena seluruh panel admin bergantung padanya.

## Spesifikasi

### Tujuan
Memberikan akses masuk khusus aparat desa agar data desa aman dari orang yang tidak berwenang.

### Selesai bila
- Seluruh halaman panel admin hanya bisa diakses setelah login; membuka URL admin tanpa sesi akan dialihkan ke halaman login.
- Admin dapat masuk dengan email dan kata sandi yang benar.
- Percobaan login yang salah berulang kali dibatasi untuk mencegah pembobolan.
- Kata sandi admin disimpan sebagai hash bcrypt, tidak pernah tersimpan sebagai teks biasa dan tidak bisa dibaca balik.
- Admin dapat keluar dari panel kapan saja, dan sesi berakhir sendiri setelah tidak ada aktivitas.

## Sub-fitur: Login Admin

Masuk ke panel admin dengan email dan kata sandi.

### Tujuan
Memberi aparat desa akses masuk ke panel admin dengan email dan kata sandi.

### Selesai bila
- Form login menampilkan kolom email dan kata sandi, serta pilihan "ingat saya".
- Admin dengan email dan kata sandi yang benar masuk ke dashboard admin.
- Email atau kata sandi salah menampilkan satu pesan error yang sama ("Email atau kata sandi salah"), tanpa membocorkan mana yang keliru.
- Tidak ada data desa yang bisa dilihat sebelum login berhasil.
- Waktu login terakhir tercatat pada akun admin.

## Sub-fitur: Batas Percobaan Login

Membatasi percobaan login gagal agar akun tidak bisa ditebak paksa.

### Tujuan
Mencegah percobaan pembobolan dengan menebak kata sandi berulang kali.

### Selesai bila
- Setelah 5 kali percobaan gagal dari kombinasi email + alamat IP yang sama, login diblokir sementara selama 1 menit.
- Selama diblokir, pesan menjelaskan sisa waktu tunggu dalam detik.
- Percobaan yang berhasil mengatur ulang hitungan kegagalan ke nol.

## Sub-fitur: Logout & Timeout Sesi

Keluar dari panel admin dan mengakhiri sesi secara otomatis bila ditinggalkan.

### Tujuan
Mengakhiri sesi admin dengan aman agar orang lain tidak bisa mengakses panel dari perangkat yang ditinggal terbuka.

### Selesai bila
- Tombol logout tersedia di dalam panel admin pada posisi yang konsisten di setiap halaman.
- Setelah logout, admin dialihkan ke halaman login dan sesi dihapus.
- Sesi berakhir otomatis setelah 120 menit tanpa aktivitas; admin yang kembali diminta login ulang dengan pesan "Sesi Anda telah berakhir".
- Menekan tombol kembali pada peramban setelah logout tidak menampilkan halaman admin dari cache.

## Sub-fitur: Ganti Kata Sandi

Memperbarui kata sandi admin secara berkala demi keamanan.

### Tujuan
Memungkinkan admin memperbarui kata sandi sendiri agar akun tetap aman.

### Selesai bila
- Form ganti kata sandi menampilkan kolom kata sandi lama, kata sandi baru, dan konfirmasi kata sandi baru.
- Kata sandi baru minimal 8 karakter dan berbeda dari kata sandi lama.
- Admin dapat mengganti kata sandi dan kata sandi baru berlaku untuk login berikutnya.
- Kata sandi lama yang salah atau konfirmasi yang tidak cocok menampilkan pesan error yang jelas.
- Setelah kata sandi diganti, sesi di perangkat lain ikut berakhir.

## Sub-fitur: Pemulihan Akun Terkunci

Menyediakan jalan keluar bila admin lupa kata sandi.

### Tujuan
Mencegah sistem terkunci permanen ketika admin lupa kata sandi, tanpa membuka celah reset lewat halaman publik.

### Selesai bila
- Tersedia perintah artisan untuk membuat akun admin baru dan untuk mengatur ulang kata sandi akun yang ada, dijalankan dari terminal/SSH.
- Tersedia perintah artisan untuk mendaftar seluruh akun admin yang terdaftar.
- Tidak ada halaman "lupa kata sandi" yang dapat diakses publik.
- Cara pemulihan ini terdokumentasi di README proyek.

## Task

### 1. Siapkan skema dan migrasi tabel admins

### 2. Buat seeder akun admin awal

### 3. Buat halaman login admin dengan validasi

### 4. Buat endpoint login admin dengan rate limiting

### 5. Buat middleware proteksi seluruh route admin

### 6. Buat layout panel admin dengan sidebar dan tombol logout

### 7. Buat endpoint logout dan pencegahan cache halaman admin

### 8. Terapkan timeout sesi 120 menit tanpa aktivitas

### 9. Buat form dan endpoint ganti kata sandi

### 10. Buat perintah artisan buat admin, reset sandi, dan daftar admin

### 11. Tulis dokumentasi pemulihan akun di README
