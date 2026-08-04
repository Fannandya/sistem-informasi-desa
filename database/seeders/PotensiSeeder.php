<?php

namespace Database\Seeders;

use App\Models\PotensiDesa;
use App\Models\RefKategoriPotensi;
use Illuminate\Database\Seeder;

class PotensiSeeder extends Seeder
{
    public function run(): void
    {
        if (PotensiDesa::exists()) {
            return;
        }

        $umkm = RefKategoriPotensi::where('nama', 'UMKM')->first()?->id;
        $hasilBumi = RefKategoriPotensi::where('nama', 'Hasil Bumi')->first()?->id;
        $wisata = RefKategoriPotensi::where('nama', 'Wisata')->first()?->id;
        $kerajinan = RefKategoriPotensi::where('nama', 'Kerajinan')->first()?->id;

        $data = [
            ['Keripik Singkong Bu Sari', $umkm, 'Keripik singkong renyah dengan bumbu balado dan original, diproduksi rumahan sejak 2015.', '0812-3456-7890 (Bu Sari)'],
            ['Kopi Robusta Lereng Cimekar', $hasilBumi, 'Kopi robusta yang ditanam di lereng pada ketinggian 800 mdpl, dipanen dan disangrai warga sendiri.', '0813-2222-1111 (Kelompok Tani)'],
            ['Curug Sukamaju', $wisata, 'Air terjun setinggi 18 meter, sekitar 20 menit berjalan kaki dari balai desa.', null],
            ['Anyaman Bambu Dusun Krajan', $kerajinan, 'Besek, tampah, dan tas anyaman bambu buatan ibu-ibu PKK Dusun Krajan.', '0857-9999-0000'],
            ['Gula Aren Cetak', $hasilBumi, 'Gula aren cetak tanpa campuran, diproduksi dari nira pohon aren di kebun warga.', null],
            ['Bakso Pak Trisno', $umkm, 'Bakso urat dan bakso halus, buka setiap hari mulai pukul 10.00 di depan pasar desa.', '0821-3333-4444'],
            ['Bukit Panorama Sukasari', $wisata, 'Titik pandang menghadap lembah, ramai saat matahari terbit.', null],
            ['Batik Tulis Motif Aren', $kerajinan, 'Batik tulis dengan motif pohon aren dan padi khas desa.', '0878-5555-6666'],
        ];

        foreach ($data as $i => [$nama, $kategoriId, $deskripsi, $kontak]) {
            PotensiDesa::create([
                'nama' => $nama,
                'kategori_potensi_id' => $kategoriId,
                'deskripsi_singkat' => \Illuminate\Support\Str::limit($deskripsi, 120),
                'deskripsi' => $deskripsi."\n\n".'Silakan hubungi kontak yang tertera untuk pemesanan atau informasi lebih lanjut.',
                'kontak' => $kontak,
                'urutan' => $i,
                'tampil' => $nama !== 'Batik Tulis Motif Aren',
            ]);
        }
    }
}
