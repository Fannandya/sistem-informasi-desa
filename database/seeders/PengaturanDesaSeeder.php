<?php

namespace Database\Seeders;

use App\Models\PengaturanDesa;
use Illuminate\Database\Seeder;

class PengaturanDesaSeeder extends Seeder
{
    public function run(): void
    {
        $pengaturan = PengaturanDesa::ambil();

        // Tidak menimpa data yang sudah diisi admin.
        if (filled($pengaturan->kecamatan)) {
            return;
        }

        $pengaturan->fill([
            'nama_desa' => 'Desa Sukamaju',
            'kecamatan' => 'Cibeureum',
            'kabupaten' => 'Bandung Barat',
            'provinsi' => 'Jawa Barat',
            'kode_pos' => '40553',
            'tahun_berdiri' => 1952,
            'luas_desa' => 428.75,

            'visi' => 'Terwujudnya Desa Sukamaju yang mandiri, sejahtera, dan berbudaya '
                .'melalui penguatan ekonomi warga dan pelayanan publik yang terbuka.',

            'misi' => "Meningkatkan kualitas pelayanan administrasi desa yang cepat dan transparan\n"
                ."Mengembangkan potensi UMKM dan pertanian sebagai penopang ekonomi warga\n"
                ."Membangun infrastruktur desa yang merata hingga ke setiap dusun\n"
                ."Melestarikan seni dan budaya lokal sebagai identitas desa\n"
                .'Mendorong keterlibatan pemuda dalam pembangunan desa',

            'nilai_tujuan' => 'Gotong royong, keterbukaan informasi, dan pelayanan yang berpihak pada warga.',

            'sejarah' => 'Desa Sukamaju terbentuk pada tahun 1952 sebagai hasil pemekaran dari desa '
                .'induk di wilayah Cibeureum. Nama Sukamaju diambil dari harapan para sesepuh agar '
                .'desa ini terus bergerak maju tanpa meninggalkan tradisi gotong royong yang sudah '
                .'mengakar sejak lama.',

            'letak_geografis' => 'Desa Sukamaju berada di ketinggian sekitar 750 meter di atas '
                .'permukaan laut. Sebelah utara berbatasan dengan Desa Cimanggu, sebelah timur '
                .'dengan Desa Margaluyu, sebelah selatan dengan kawasan hutan lindung, dan sebelah '
                .'barat dengan Desa Sindangsari.',

            'alamat_kantor' => 'Jalan Raya Sukamaju No. 12, Kecamatan Cibeureum',
            'telepon_kantor' => '(022) 5550123',
            'email' => 'kontak@sukamaju.desa.id',
            'website' => 'https://sukamaju.desa.id',

            'medsos' => [
                'facebook' => 'https://facebook.com/desasukamaju',
                'instagram' => 'https://instagram.com/desasukamaju',
            ],

            'meta_description' => 'Website resmi Desa Sukamaju, Kecamatan Cibeureum, Bandung Barat. '
                .'Profil desa, potensi UMKM dan wisata, berita, serta dokumentasi kegiatan warga.',
        ])->save();
    }
}
