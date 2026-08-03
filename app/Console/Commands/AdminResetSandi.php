<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;

class AdminResetSandi extends Command
{
    protected $signature = 'admin:reset-sandi {email : Email akun yang akan diatur ulang}';

    protected $description = 'Mengatur ulang kata sandi admin dari terminal. Satu-satunya jalur pemulihan akun.';

    public function handle(): int
    {
        $admin = Admin::where('email', $this->argument('email'))->first();

        if (! $admin) {
            $this->error("Tidak ada akun admin dengan email {$this->argument('email')}.");
            $this->line('Jalankan `php artisan admin:daftar` untuk melihat akun yang terdaftar.');

            return self::FAILURE;
        }

        $sandi = password('Kata sandi baru (minimal 8 karakter)', required: true);

        $validator = Validator::make(['password' => $sandi], [
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $pesan) {
                $this->error($pesan);
            }

            return self::FAILURE;
        }

        // setRememberToken mengosongkan token "ingat saya" agar sesi lama
        // di perangkat lain tidak bisa dipakai lagi setelah reset.
        $admin->forceFill(['password' => $sandi])->setRememberToken(null);
        $admin->save();

        $this->info("Kata sandi untuk {$admin->email} berhasil diatur ulang.");

        return self::SUCCESS;
    }
}
