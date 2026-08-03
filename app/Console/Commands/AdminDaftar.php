<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;

class AdminDaftar extends Command
{
    protected $signature = 'admin:daftar';

    protected $description = 'Menampilkan seluruh akun admin yang terdaftar.';

    public function handle(): int
    {
        $admins = Admin::orderBy('id')->get();

        if ($admins->isEmpty()) {
            $this->warn('Belum ada akun admin. Jalankan `php artisan admin:buat`.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Nama', 'Email', 'Login Terakhir'],
            $admins->map(fn (Admin $a) => [
                $a->id,
                $a->nama,
                $a->email,
                $a->last_login_at?->format('d M Y H:i') ?? 'belum pernah',
            ])->all(),
        );

        return self::SUCCESS;
    }
}
