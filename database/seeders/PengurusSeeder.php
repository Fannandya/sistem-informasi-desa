<?php

namespace Database\Seeders;

use App\Models\Pengurus;
use App\Models\RefOrganisasi;
use App\Models\Wilayah;
use Illuminate\Database\Seeder;

class PengurusSeeder extends Seeder
{
    /** @var array<string, int> nama organisasi => urutan tampil */
    private const ORGANISASI = [
        'Pemerintah Desa' => 1,
        'BPD' => 2,
        'RW' => 3,
        'RT' => 4,
        'Karang Taruna' => 5,
        'PKK' => 6,
    ];

    public function run(): void
    {
        foreach (self::ORGANISASI as $nama => $urutan) {
            RefOrganisasi::firstOrCreate(['nama' => $nama], ['urutan' => $urutan]);
        }

        if (Pengurus::exists()) {
            return;
        }

        $organisasi = RefOrganisasi::pluck('id', 'nama');

        $perangkat = [
            ['Sutrisno Hadi', 'Kepala Desa'],
            ['Endang Suryani', 'Sekretaris Desa'],
            ['Wahyu Nugraha', 'Kaur Keuangan'],
            ['Siti Rahayu', 'Kaur Umum'],
            ['Firman Maulana', 'Kasi Pemerintahan'],
            ['Dewi Puspitasari', 'Kasi Kesejahteraan'],
        ];

        foreach ($perangkat as $i => [$nama, $jabatan]) {
            Pengurus::create([
                'nama' => $nama,
                'jabatan' => $jabatan,
                'organisasi_id' => $organisasi['Pemerintah Desa'],
                'nomor_hp' => '08'.mt_rand(1000000000, 1999999999),
                'urutan' => $i,
            ]);
        }

        foreach ([['Joko Santoso', 'Ketua BPD'], ['Nurhayati', 'Sekretaris BPD']] as $i => [$nama, $jabatan]) {
            Pengurus::create([
                'nama' => $nama,
                'jabatan' => $jabatan,
                'organisasi_id' => $organisasi['BPD'],
                'nomor_hp' => '08'.mt_rand(1000000000, 1999999999),
                'urutan' => $i,
            ]);
        }

        // Ketua RT untuk setiap wilayah terdaftar, supaya halaman publik
        // menampilkan daftar panjang yang realistis.
        $namaDepan = ['Agus', 'Bambang', 'Cahyo', 'Darmawan', 'Eko', 'Fajar', 'Gatot', 'Hendra', 'Imam', 'Jaka', 'Kusno', 'Lilik', 'Mulyono', 'Nanang'];

        foreach (Wilayah::urut()->get() as $i => $wilayah) {
            Pengurus::create([
                'nama' => ($namaDepan[$i % count($namaDepan)]).' Wijaya',
                'jabatan' => 'Ketua RT '.$wilayah->rt,
                'organisasi_id' => $organisasi['RT'],
                'wilayah_id' => $wilayah->id,
                'nomor_hp' => '08'.mt_rand(1000000000, 1999999999),
                'urutan' => $i,
            ]);
        }

        foreach ([['Rudi Hartono', 'Ketua Karang Taruna'], ['Mira Lestari', 'Sekretaris']] as $i => [$nama, $jabatan]) {
            Pengurus::create([
                'nama' => $nama,
                'jabatan' => $jabatan,
                'organisasi_id' => $organisasi['Karang Taruna'],
                'nomor_hp' => '08'.mt_rand(1000000000, 1999999999),
                'urutan' => $i,
            ]);
        }

        Pengurus::create([
            'nama' => 'Tuti Handayani',
            'jabatan' => 'Ketua PKK',
            'organisasi_id' => $organisasi['PKK'],
            'nomor_hp' => '08'.mt_rand(1000000000, 1999999999),
            'urutan' => 0,
        ]);
    }
}
