<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class AdminBuat extends Command
{
    protected $signature = 'admin:buat
                            {--nama= : Nama tampilan admin}
                            {--email= : Email untuk masuk}';

    protected $description = 'Membuat akun admin baru. Tidak ada pendaftaran mandiri lewat web.';

    public function handle(): int
    {
        $nama = $this->option('nama') ?: text('Nama admin', required: true);
        $email = $this->option('email') ?: text('Email', required: true);
        $sandi = password('Kata sandi (minimal 8 karakter)', required: true);

        $validator = Validator::make(
            ['nama' => $nama, 'email' => $email, 'password' => $sandi],
            [
                'nama' => ['required', 'string', 'max:80'],
                'email' => ['required', 'email', 'max:120', 'unique:admins,email'],
                'password' => ['required', 'string', 'min:8'],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $pesan) {
                $this->error($pesan);
            }

            return self::FAILURE;
        }

        $admin = Admin::create([
            'nama' => $nama,
            'email' => $email,
            'password' => $sandi,
        ]);

        $this->info("Akun admin dibuat: {$admin->email} (ID {$admin->id})");

        return self::SUCCESS;
    }
}
