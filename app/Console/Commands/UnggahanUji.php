<?php

namespace App\Console\Commands;

use App\Services\LayananUnggahGambar;
use Illuminate\Console\Command;
use Throwable;

class UnggahanUji extends Command
{
    protected $signature = 'unggahan:uji';

    protected $description = 'Menguji koneksi ke disk penyimpanan unggahan dengan menulis lalu menghapus satu berkas uji.';

    public function handle(LayananUnggahGambar $layanan): int
    {
        $disk = config('unggahan.disk');
        $path = 'uji-koneksi-'.now()->format('YmdHis').'.txt';
        $isi = 'Uji koneksi penyimpanan '.now()->toDateTimeString();

        $this->line("Disk aktif: <options=bold>{$disk}</>");

        if ($disk === 'r2') {
            foreach (['R2_ACCESS_KEY_ID', 'R2_SECRET_ACCESS_KEY', 'R2_BUCKET', 'R2_ENDPOINT'] as $variabel) {
                if (blank(env($variabel))) {
                    $this->error("Variabel {$variabel} belum diisi di .env.");

                    return self::FAILURE;
                }
            }
        }

        try {
            $this->line('Menulis berkas uji...');
            $layanan->disk()->put($path, $isi);

            $terbaca = $layanan->disk()->get($path);

            if ($terbaca !== $isi) {
                $this->error('Berkas tertulis, tetapi isinya tidak sama saat dibaca kembali.');
                $layanan->disk()->delete($path);

                return self::FAILURE;
            }

            $this->line('Membaca kembali... isi cocok.');

            $url = $layanan->disk()->url($path);
            $this->line("URL publik: {$url}");

            $layanan->disk()->delete($path);
            $this->line('Berkas uji dihapus.');

        } catch (Throwable $e) {
            $this->error('Gagal terhubung ke penyimpanan.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info("Koneksi ke disk \"{$disk}\" berfungsi.");

        if ($disk !== 'r2') {
            $this->newLine();
            $this->warn('Catatan: aplikasi masih memakai penyimpanan lokal.');
            $this->line('Untuk pindah ke Cloudflare R2, isi variabel R2_* di .env lalu set UPLOAD_DISK=r2.');
        }

        return self::SUCCESS;
    }
}
