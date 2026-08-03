<?php

namespace Database\Seeders;

use App\Enums\AlasanKeluar;
use App\Enums\JenisKelamin;
use App\Enums\JenisMasuk;
use App\Enums\Pendidikan;
use App\Models\Penduduk;
use App\Models\RefPekerjaan;
use App\Models\Wilayah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Data warga contoh untuk pengembangan.
 *
 * Sengaja dibuat beragam agar dashboard (fitur 06) tidak terlihat "bagus"
 * hanya karena datanya rapi: ada warga tanpa tanggal lahir, tanpa pekerjaan,
 * ada kelahiran dan kematian lintas tahun, serta ada arsip dengan ketiga
 * alasan keluar.
 */
class PendudukSeeder extends Seeder
{
    private const DEPAN_PRIA = ['Ahmad', 'Budi', 'Cecep', 'Dedi', 'Endang', 'Firman', 'Gunawan', 'Hendra', 'Iwan', 'Joko', 'Kurnia', 'Lukman', 'Maman', 'Nanang', 'Oman', 'Pujiono', 'Rahmat', 'Sutrisno', 'Taufik', 'Usman', 'Wahyu', 'Yusuf'];

    private const DEPAN_WANITA = ['Ani', 'Bunga', 'Citra', 'Dewi', 'Eka', 'Fitri', 'Gita', 'Hesti', 'Indah', 'Juwita', 'Kartika', 'Lestari', 'Mira', 'Nurhayati', 'Oktavia', 'Puspita', 'Ratna', 'Siti', 'Tuti', 'Umi', 'Wulan', 'Yanti'];

    private const BELAKANG = ['Setiawan', 'Nugraha', 'Permana', 'Wijaya', 'Saputra', 'Hidayat', 'Kurniawan', 'Ramadhan', 'Santoso', 'Firdaus', 'Maulana', 'Suryani', 'Handayani', 'Rahayu', 'Puspitasari', 'Anggraini'];

    public function run(): void
    {
        if (Penduduk::withTrashed()->exists()) {
            return;
        }

        $wilayah = Wilayah::pluck('id')->all();
        $pekerjaan = RefPekerjaan::pluck('id')->all();

        if ($wilayah === []) {
            $this->command?->warn('WilayahSeeder harus dijalankan lebih dulu.');

            return;
        }

        mt_srand(2026); // hasil seeder konsisten antar-jalan

        $this->warga(320, $wilayah, $pekerjaan);
        $this->kelahiran($wilayah);
        $this->arsip($wilayah, $pekerjaan);
    }

    /** Warga aktif hasil pendataan awal, tersebar di seluruh kelompok umur. */
    private function warga(int $jumlah, array $wilayah, array $pekerjaan): void
    {
        $baris = [];

        for ($i = 0; $i < $jumlah; $i++) {
            $pria = mt_rand(0, 1) === 1;
            $umur = $this->umurAcak();

            // ~2% warga sengaja tanpa tanggal lahir, meniru data lama yang
            // tidak lengkap, agar kelompok "Tidak Diketahui" ikut teruji.
            $tanggalLahir = mt_rand(1, 100) <= 2
                ? null
                : Carbon::today()->subYears($umur)->subDays(mt_rand(0, 364));

            $baris[] = [
                'nama_lengkap' => $this->nama($pria),
                'tanggal_lahir' => $tanggalLahir?->toDateString(),
                'jenis_kelamin' => $pria ? JenisKelamin::LakiLaki->value : JenisKelamin::Perempuan->value,
                'wilayah_id' => $wilayah[array_rand($wilayah)],
                'pekerjaan_id' => $umur >= 17 && mt_rand(1, 100) <= 88
                    ? $pekerjaan[array_rand($pekerjaan)]
                    : null,
                'pendidikan' => $this->pendidikan($umur)?->value,
                'jenis_masuk' => JenisMasuk::PendataanAwal->value,
                'tanggal_masuk' => Carbon::today()->subYears(3)->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($baris, 100) as $bagian) {
            Penduduk::insert($bagian);
        }
    }

    /** Kelahiran tersebar pada empat tahun terakhir, untuk grafik tren. */
    private function kelahiran(array $wilayah): void
    {
        foreach (range(now()->year - 3, now()->year) as $tahun) {
            foreach (range(1, mt_rand(4, 9)) as $ignored) {
                $pria = mt_rand(0, 1) === 1;
                $lahir = Carbon::create($tahun, mt_rand(1, 12), mt_rand(1, 28));

                if ($lahir->isFuture()) {
                    continue;
                }

                Penduduk::create([
                    'nama_lengkap' => $this->nama($pria),
                    'tanggal_lahir' => $lahir,
                    'jenis_kelamin' => $pria ? JenisKelamin::LakiLaki : JenisKelamin::Perempuan,
                    'wilayah_id' => $wilayah[array_rand($wilayah)],
                    'pendidikan' => Pendidikan::BelumSekolah,
                    'jenis_masuk' => JenisMasuk::Lahir,
                    'tanggal_masuk' => $lahir,
                ]);
            }
        }
    }

    /** Warga terarsip dengan ketiga alasan keluar. */
    private function arsip(array $wilayah, array $pekerjaan): void
    {
        $rencana = [
            [AlasanKeluar::Meninggal, 11, 68, 92],
            [AlasanKeluar::PindahKeluar, 7, 20, 45],
            [AlasanKeluar::Duplikat, 3, 25, 60],
        ];

        foreach ($rencana as [$alasan, $jumlah, $umurMin, $umurMaks]) {
            foreach (range(1, $jumlah) as $ignored) {
                $pria = mt_rand(0, 1) === 1;
                $umur = mt_rand($umurMin, $umurMaks);

                $penduduk = Penduduk::create([
                    'nama_lengkap' => $this->nama($pria),
                    'tanggal_lahir' => Carbon::today()->subYears($umur)->subDays(mt_rand(0, 364)),
                    'jenis_kelamin' => $pria ? JenisKelamin::LakiLaki : JenisKelamin::Perempuan,
                    'wilayah_id' => $wilayah[array_rand($wilayah)],
                    'pekerjaan_id' => $pekerjaan[array_rand($pekerjaan)],
                    'pendidikan' => $this->pendidikan($umur)?->value,
                    'jenis_masuk' => JenisMasuk::PendataanAwal,
                    'tanggal_masuk' => Carbon::today()->subYears(3),
                    'alasan_keluar' => $alasan,
                    'tanggal_keluar' => Carbon::today()->subDays(mt_rand(1, 730)),
                ]);

                $penduduk->delete();
            }
        }
    }

    private function nama(bool $pria): string
    {
        $depan = $pria
            ? self::DEPAN_PRIA[array_rand(self::DEPAN_PRIA)]
            : self::DEPAN_WANITA[array_rand(self::DEPAN_WANITA)];

        return $depan.' '.self::BELAKANG[array_rand(self::BELAKANG)];
    }

    /**
     * Sebaran umur menyerupai piramida penduduk desa.
     *
     * Undian dilakukan sekali lalu dibandingkan dengan ambang kumulatif.
     * Mengundi ulang di tiap cabang `match` akan membuat peluangnya menumpuk
     * salah — kelompok pertama kebagian jauh lebih besar dari yang diniatkan.
     */
    private function umurAcak(): int
    {
        $undi = mt_rand(1, 100);

        return match (true) {
            $undi <= 10 => mt_rand(0, 5),    // 10%
            $undi <= 24 => mt_rand(6, 12),   // 14%
            $undi <= 34 => mt_rand(13, 18),  // 10%
            $undi <= 62 => mt_rand(19, 35),  // 28%
            $undi <= 87 => mt_rand(36, 60),  // 25%
            default => mt_rand(61, 88),      // 13%
        };
    }

    private function pendidikan(int $umur): ?Pendidikan
    {
        return match (true) {
            $umur < 6 => Pendidikan::BelumSekolah,
            $umur < 13 => Pendidikan::SD,
            $umur < 16 => Pendidikan::SMP,
            $umur < 19 => Pendidikan::SMA,
            default => collect([
                Pendidikan::SD, Pendidikan::SD, Pendidikan::SMP, Pendidikan::SMP,
                Pendidikan::SMA, Pendidikan::SMA, Pendidikan::SMA,
                Pendidikan::D1D3, Pendidikan::S1, Pendidikan::S1, Pendidikan::S2,
            ])->random(),
        };
    }
}
