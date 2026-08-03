<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\PasswordController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PengaturanDesaController;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Publik
|--------------------------------------------------------------------------
| Diisi pada fitur 14 (Halaman Publik Desa).
*/

Route::view('/', 'welcome')->name('beranda');

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

        // Pengaturan desa — satu halaman, lima bagian dengan simpan terpisah.
        Route::get('pengaturan', [PengaturanDesaController::class, 'tampilkan'])->name('pengaturan');
        Route::put('pengaturan/{bagian}', [PengaturanDesaController::class, 'perbarui'])
            ->name('pengaturan.perbarui');

        Route::get('kata-sandi', [PasswordController::class, 'tampilkan'])->name('kata-sandi');
        Route::put('kata-sandi', [PasswordController::class, 'perbarui'])->name('kata-sandi.perbarui');

        Route::post('logout', [LoginController::class, 'keluar'])->name('logout');
    });
});
