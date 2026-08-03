<?php

namespace App\Console\Commands;

use App\Services\LayananUnggahGambar;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fitur 02 — memeriksa berkas yatim.
 *
 * Perintah ini sengaja hanya melaporkan, tidak menghapus. Menghapus otomatis
 * berdasarkan selisih berisiko: satu bug pada pembacaan database bisa
 * memusnahkan seluruh foto desa.
 */
class UnggahanBerkasYatim extends Command
{
    protected $signature = 'unggahan:berkas-yatim {--hapus : Hapus berkas yatim setelah konfirmasi}';

    protected $description = 'Membandingkan berkas di penyimpanan dengan path yang tercatat di database, lalu melaporkan selisihnya.';

    /**
     * Kolom gambar per tabel. Ditambah seiring fitur 03–13 dikerjakan.
     *
     * @var array<string, array<int, string>>
     */
    private const PETA_KOLOM = [
        'pengaturan_desa' => ['logo', 'foto_sampul'],
        'pengurus_desa' => ['foto'],
        'potensi_desa' => ['foto'],
        'fasilitas_umum' => ['foto'],
        'prestasi_desa' => ['foto'],
        'berita_desa' => ['gambar_sampul'],
        'foto_acara' => ['path'],
    ];

    public function handle(LayananUnggahGambar $layanan): int
    {
        $disk = $layanan->disk();
        $subThumbnail = config('unggahan.folder_thumbnail');

        $terpakai = $this->pathTerpakai($layanan);
        $this->line('Path tercatat di database: '.$terpakai->count());

        $adaDiPenyimpanan = collect();
        foreach (array_values(config('unggahan.folder')) as $folder) {
            $adaDiPenyimpanan = $adaDiPenyimpanan->merge($disk->allFiles($folder));
        }

        $this->line('Berkas di penyimpanan   : '.$adaDiPenyimpanan->count());

        $yatim = $adaDiPenyimpanan->reject(
            fn (string $path) => $terpakai->contains($path),
        )->values();

        $this->newLine();

        if ($yatim->isEmpty()) {
            $this->info('Tidak ada berkas yatim. Penyimpanan bersih.');

            return self::SUCCESS;
        }

        $this->warn("Ditemukan {$yatim->count()} berkas yatim:");
        foreach ($yatim as $path) {
            $this->line('  '.$path);
        }

        if (! $this->option('hapus')) {
            $this->newLine();
            $this->line('Jalankan ulang dengan --hapus untuk menghapusnya.');

            return self::SUCCESS;
        }

        if (! $this->confirm("Hapus {$yatim->count()} berkas ini secara permanen?", false)) {
            $this->line('Dibatalkan. Tidak ada berkas yang dihapus.');

            return self::SUCCESS;
        }

        foreach ($yatim as $path) {
            $disk->delete($path);
        }

        $this->info("{$yatim->count()} berkas yatim dihapus.");

        return self::SUCCESS;
    }

    /**
     * Seluruh path yang masih dirujuk database, termasuk thumbnailnya.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function pathTerpakai(LayananUnggahGambar $layanan): \Illuminate\Support\Collection
    {
        $path = collect();

        foreach (self::PETA_KOLOM as $tabel => $kolomList) {
            if (! Schema::hasTable($tabel)) {
                continue;
            }

            foreach ($kolomList as $kolom) {
                if (! Schema::hasColumn($tabel, $kolom)) {
                    continue;
                }

                DB::table($tabel)
                    ->whereNotNull($kolom)
                    ->where($kolom, '!=', '')
                    ->pluck($kolom)
                    ->each(function (string $nilai) use ($path, $layanan): void {
                        $path->push($nilai);
                        $path->push($layanan->pathThumbnail($nilai));
                    });
            }
        }

        return $path->unique();
    }
}
