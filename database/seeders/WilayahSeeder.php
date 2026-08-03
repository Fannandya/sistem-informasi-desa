<?php

namespace Database\Seeders;

use App\Models\Wilayah;
use Illuminate\Database\Seeder;

class WilayahSeeder extends Seeder
{
    /**
     * Tiga dusun dengan jumlah RT berbeda-beda, meniru bentuk desa nyata —
     * berguna untuk menguji sebaran wilayah dan kelengkapan rekap KK.
     *
     * @var array<string, array<string, int>>  dusun => [rw => jumlah RT]
     */
    private const STRUKTUR = [
        'Dusun Krajan' => ['001' => 4, '002' => 3],
        'Dusun Sukasari' => ['003' => 3, '004' => 2],
        'Dusun Cimekar' => ['005' => 2],
    ];

    public function run(): void
    {
        $urutan = 0;

        foreach (self::STRUKTUR as $dusun => $rwList) {
            foreach ($rwList as $rw => $jumlahRt) {
                for ($i = 1; $i <= $jumlahRt; $i++) {
                    Wilayah::firstOrCreate(
                        [
                            'dusun' => $dusun,
                            'rw' => $rw,
                            'rt' => str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                        ],
                        ['urutan' => $urutan++],
                    );
                }
            }
        }
    }
}
