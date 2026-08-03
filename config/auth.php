<?php

use App\Models\Admin;

return [

    /*
    |--------------------------------------------------------------------------
    | Default
    |--------------------------------------------------------------------------
    |
    | Aplikasi ini hanya punya satu jenis pengguna terautentikasi: Admin Desa.
    | Pengunjung publik tidak pernah login (PRD 2.1), sehingga tidak ada guard
    | "web" untuk warga.
    |
    */

    'defaults' => [
        'guard' => 'admin',
        'passwords' => 'admins',
    ],

    /*
    |--------------------------------------------------------------------------
    | Guards
    |--------------------------------------------------------------------------
    */

    'guards' => [
        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        'admins' => [
            'driver' => 'eloquent',
            'model' => Admin::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reset Kata Sandi
    |--------------------------------------------------------------------------
    |
    | Sengaja dikosongkan. Fitur 01 melarang halaman lupa kata sandi publik;
    | pemulihan akun dilakukan lewat `php artisan admin:reset-password`.
    |
    */

    'passwords' => [],

    'password_timeout' => 10800,

];
