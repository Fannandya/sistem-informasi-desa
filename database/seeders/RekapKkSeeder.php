<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Penduduk;
use App\Models\RekapKk;
use App\Models\Wilayah;
use Illuminate\Database\Seeder;

/**
 * Rekap KK contoh untuk dua tahun.
 *
 * Tahun lalu sengaja dibuat lengkap, tahun ini sengaja disisakan beberapa RT
 * kosong. Dengan begitu penanda "belum lengkap" pada dashboard ikut teruji,
 * bukan hanya keadaan ideal saat semua data sudah terisi rapi.
 */
class RekapKkSeeder extends Seeder
{
    public function run(): void
    {
        if (RekapKk::exists()) {
            return;
        }

        $wilayah = Wilayah::urut()->get();

        if ($wilayah->isEmpty()) {
            $this->command?->warn('WilayahSeeder harus dijalankan lebih dulu.');

            return;
        }

        $adminId = Admin::value('id');
        mt_srand(2026);

        // Jumlah KK diperkirakan dari warga yang benar-benar ada di RT itu,
        // dengan rata-rata 3–4 jiwa per keluarga, agar angka rata-rata
        // jiwa/KK di dashboard masuk akal.
        $jumlahWarga = Penduduk::query()->aktif()
            ->selectRaw('wilayah_id, COUNT(*) as jumlah')
            ->groupBy('wilayah_id')
            ->pluck('jumlah', 'wilayah_id');

        $tahunLalu = now()->year - 1;
        $tahunIni = now()->year;

        // RT yang sengaja dibiarkan kosong pada tahun berjalan.
        $belumDiisi = $wilayah->take(3)->pluck('id')->all();

        foreach ($wilayah as $w) {
            $warga = (int) ($jumlahWarga[$w->id] ?? 0);
            $kkTahunLalu = max(1, (int) round($warga / mt_rand(30, 40) * 10));

            RekapKk::create([
                'wilayah_id' => $w->id,
                'tahun' => $tahunLalu,
                'jumlah_kk' => $kkTahunLalu,
                'admin_id' => $adminId,
            ]);

            if (in_array($w->id, $belumDiisi, true)) {
                continue;
            }

            RekapKk::create([
                'wilayah_id' => $w->id,
                'tahun' => $tahunIni,
                'jumlah_kk' => $kkTahunLalu + mt_rand(-1, 3),
                'admin_id' => $adminId,
            ]);
        }
    }
}
