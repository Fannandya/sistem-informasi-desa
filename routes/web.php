<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\PasswordController;
use App\Http\Controllers\Admin\ArsipPendudukController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EksporPendudukController;
use App\Http\Controllers\Admin\ImporPendudukController;
use App\Http\Controllers\Admin\PendudukController;
use App\Http\Controllers\Admin\PengaturanDesaController;
use App\Http\Controllers\Admin\PengurusController;
use App\Http\Controllers\Admin\RefOrganisasiController;
use App\Http\Controllers\Admin\RefPekerjaanController;
use App\Http\Controllers\Admin\RekapKkController;
use App\Http\Controllers\Admin\StatistikController;
use App\Http\Controllers\Admin\WilayahController;
use App\Http\Controllers\Publik\StrukturOrganisasiController;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Publik
|--------------------------------------------------------------------------
| Diisi pada fitur 14 (Halaman Publik Desa).
*/

Route::view('/', 'welcome')->name('beranda');

Route::name('publik.')->group(function () {
    Route::get('struktur-organisasi', StrukturOrganisasiController::class)->name('struktur');
});

/*
|--------------------------------------------------------------------------
| Rute Admin
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    // --- Tamu (belum masuk) ---
    Route::middleware('tamu.admin')->group(function () {
        Route::get('login', [LoginController::class, 'tampilkan'])->name('login');
        Route::post('login', [LoginController::class, 'masuk'])->name('login.proses');
    });

    // --- Sudah masuk ---
    // AuthenticateSession dibutuhkan agar logoutOtherDevices() bekerja saat
    // admin mengganti kata sandi (fitur 01).
    Route::middleware(['auth:admin', AuthenticateSession::class, 'sesi.segar', 'tanpa.cache'])->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        // --- Data penduduk ---
        Route::get('penduduk', [PendudukController::class, 'index'])->name('penduduk.index');
        Route::get('penduduk/tambah', [PendudukController::class, 'create'])->name('penduduk.create');
        Route::post('penduduk', [PendudukController::class, 'store'])->name('penduduk.store');

        Route::get('penduduk/ekspor', EksporPendudukController::class)->name('penduduk.ekspor');

        Route::get('penduduk/impor', [ImporPendudukController::class, 'tampilkan'])->name('penduduk.impor');
        Route::get('penduduk/impor/contoh', [ImporPendudukController::class, 'contoh'])->name('penduduk.impor.contoh');
        Route::post('penduduk/impor', [ImporPendudukController::class, 'proses'])->name('penduduk.impor.proses');

        // Arsip didaftarkan sebelum rute {penduduk} agar "arsip" tidak
        // ditangkap sebagai ID warga.
        Route::get('penduduk/arsip', [ArsipPendudukController::class, 'index'])->name('penduduk.arsip');
        Route::post('penduduk/arsip/{id}/pulihkan', [ArsipPendudukController::class, 'pulihkan'])
            ->name('penduduk.pulihkan');

        // withTrashed() disengaja: tanpa itu membuka halaman ubah milik warga
        // terarsip menghasilkan 404 polos. Dengan ini controller bisa
        // menjelaskan bahwa datanya harus dipulihkan lebih dulu.
        Route::get('penduduk/{penduduk}/ubah', [PendudukController::class, 'edit'])
            ->withTrashed()->name('penduduk.edit');
        Route::put('penduduk/{penduduk}', [PendudukController::class, 'update'])
            ->withTrashed()->name('penduduk.update');
        Route::delete('penduduk/{penduduk}', [PendudukController::class, 'arsipkan'])->name('penduduk.arsipkan');

        // --- Data grafik dashboard (JSON internal, bukan API publik) ---
        Route::prefix('statistik')->name('statistik.')->group(function () {
            Route::get('sebaran-umur', [StatistikController::class, 'sebaranUmur'])->name('umur');
            Route::get('rasio-gender', [StatistikController::class, 'rasioGender'])->name('gender');
            Route::get('mata-pencaharian', [StatistikController::class, 'mataPencaharian'])->name('pekerjaan');
            Route::get('pendidikan', [StatistikController::class, 'pendidikan'])->name('pendidikan');
            Route::get('sebaran-wilayah', [StatistikController::class, 'sebaranWilayah'])->name('wilayah');
            Route::get('tren-mutasi', [StatistikController::class, 'trenMutasi'])->name('tren');
        });

        // --- Rekap KK ---
        Route::get('rekap-kk', [RekapKkController::class, 'index'])->name('rekap-kk.index');
        Route::post('rekap-kk/{tahun}', [RekapKkController::class, 'simpan'])->name('rekap-kk.simpan');
        Route::post('rekap-kk/{tahun}/salin', [RekapKkController::class, 'salinTahunSebelumnya'])
            ->name('rekap-kk.salin');

        // --- Struktur organisasi ---
        Route::get('pengurus', [PengurusController::class, 'index'])->name('pengurus.index');
        Route::get('pengurus/tambah', [PengurusController::class, 'create'])->name('pengurus.create');
        Route::post('pengurus', [PengurusController::class, 'store'])->name('pengurus.store');
        Route::get('pengurus/{pengurus}/ubah', [PengurusController::class, 'edit'])->name('pengurus.edit');
        Route::put('pengurus/{pengurus}', [PengurusController::class, 'update'])->name('pengurus.update');
        Route::delete('pengurus/{pengurus}', [PengurusController::class, 'destroy'])->name('pengurus.destroy');
        Route::post('pengurus/{pengurus}/tampil', [PengurusController::class, 'ubahTampil'])->name('pengurus.tampil');
        Route::post('pengurus/{pengurus}/geser', [PengurusController::class, 'geser'])->name('pengurus.geser');

        Route::post('organisasi', [RefOrganisasiController::class, 'store'])->name('organisasi.store');
        Route::put('organisasi/{organisasi}', [RefOrganisasiController::class, 'update'])->name('organisasi.update');
        Route::delete('organisasi/{organisasi}', [RefOrganisasiController::class, 'destroy'])->name('organisasi.destroy');

        // --- Data referensi ---
        Route::get('wilayah', [WilayahController::class, 'index'])->name('wilayah.index');
        Route::post('wilayah', [WilayahController::class, 'store'])->name('wilayah.store');
        Route::put('wilayah/{wilayah}', [WilayahController::class, 'update'])->name('wilayah.update');
        Route::delete('wilayah/{wilayah}', [WilayahController::class, 'destroy'])->name('wilayah.destroy');

        Route::get('pekerjaan', [RefPekerjaanController::class, 'index'])->name('pekerjaan.index');
        Route::post('pekerjaan', [RefPekerjaanController::class, 'store'])->name('pekerjaan.store');
        Route::put('pekerjaan/{pekerjaan}', [RefPekerjaanController::class, 'update'])->name('pekerjaan.update');
        Route::delete('pekerjaan/{pekerjaan}', [RefPekerjaanController::class, 'destroy'])->name('pekerjaan.destroy');

        // Pengaturan desa — satu halaman, lima bagian dengan simpan terpisah.
        Route::get('pengaturan', [PengaturanDesaController::class, 'tampilkan'])->name('pengaturan');
        Route::put('pengaturan/{bagian}', [PengaturanDesaController::class, 'perbarui'])
            ->name('pengaturan.perbarui');

        Route::get('kata-sandi', [PasswordController::class, 'tampilkan'])->name('kata-sandi');
        Route::put('kata-sandi', [PasswordController::class, 'perbarui'])->name('kata-sandi.perbarui');

        Route::post('logout', [LoginController::class, 'keluar'])->name('logout');
    });
});
