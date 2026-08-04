<?php

namespace Database\Seeders;

use App\Enums\KategoriFasilitas;
use App\Enums\TingkatPrestasi;
use App\Models\FasilitasUmum;
use App\Models\PrestasiDesa;
use App\Models\Wilayah;
use Illuminate\Database\Seeder;

class FasilitasPrestasiSeeder extends Seeder
{
    public function run(): void
    {
        $wilayah = Wilayah::urut()->get();

        if (! FasilitasUmum::exists()) {
            $fasilitas = [
                ['SDN Sukamaju 01', KategoriFasilitas::Pendidikan, 'Sekolah dasar negeri dengan 6 ruang kelas dan perpustakaan.'],
                ['SDN Sukamaju 02', KategoriFasilitas::Pendidikan, 'Sekolah dasar negeri di Dusun Sukasari.'],
                ['PAUD Melati', KategoriFasilitas::Pendidikan, 'Pendidikan anak usia dini yang dikelola PKK desa.'],
                ['Puskesmas Pembantu Sukamaju', KategoriFasilitas::Kesehatan, 'Melayani pemeriksaan umum setiap Senin sampai Jumat.'],
                ['Posyandu Anggrek', KategoriFasilitas::Kesehatan, 'Posyandu balita dan lansia, buka setiap tanggal 10.'],
                ['Masjid Al-Ikhlas', KategoriFasilitas::Ibadah, 'Masjid jami desa dengan kapasitas sekitar 400 jemaah.'],
                ['Musala Nurul Huda', KategoriFasilitas::Ibadah, 'Musala di Dusun Cimekar.'],
                ['Balai Desa Sukamaju', KategoriFasilitas::Pemerintahan, 'Tempat musyawarah desa dan pelayanan administrasi warga.'],
                ['Lapangan Bola Desa', KategoriFasilitas::Olahraga, 'Lapangan sepak bola yang juga dipakai untuk upacara dan senam warga.'],
                ['Lapangan Voli Krajan', KategoriFasilitas::Olahraga, 'Lapangan voli di samping balai dusun.'],
                ['Pasar Desa Sukamaju', KategoriFasilitas::Umum, 'Pasar tradisional yang ramai setiap Selasa dan Sabtu.'],
            ];

            foreach ($fasilitas as $i => [$nama, $kategori, $deskripsi]) {
                FasilitasUmum::create([
                    'nama' => $nama,
                    'kategori' => $kategori,
                    'deskripsi' => $deskripsi,
                    'wilayah_id' => $wilayah->isNotEmpty() ? $wilayah[$i % $wilayah->count()]->id : null,
                    'tampil' => true,
                ]);
            }
        }

        if (! PrestasiDesa::exists()) {
            $prestasi = [
                ['Juara 1 Lomba Desa Bersih', now()->year, TingkatPrestasi::Kabupaten, 'Pemerintah Kabupaten Bandung Barat', 'Penilaian meliputi kebersihan lingkungan, pengelolaan sampah, dan partisipasi warga.'],
                ['Desa Sadar Hukum', now()->year - 1, TingkatPrestasi::Provinsi, 'Kanwil Kemenkumham Jawa Barat', 'Diberikan atas rendahnya angka pelanggaran hukum dan aktifnya penyuluhan hukum desa.'],
                ['Juara 2 Lomba Posyandu', now()->year - 1, TingkatPrestasi::Kecamatan, 'Puskesmas Kecamatan Cibeureum', 'Posyandu Anggrek dinilai dari kelengkapan administrasi dan cakupan imunisasi.'],
                ['Penghargaan Inovasi Bank Sampah', now()->year - 2, TingkatPrestasi::Kabupaten, 'Dinas Lingkungan Hidup', 'Bank sampah desa berhasil mengurangi sampah rumah tangga hingga 30 persen.'],
                ['Juara Harapan 1 Lomba Kampung KB', now()->year - 3, TingkatPrestasi::Provinsi, 'BKKBN Jawa Barat', null],
                ['Desa Mandiri Pangan', now()->year - 4, TingkatPrestasi::Nasional, 'Kementerian Desa PDTT', 'Diraih atas keberhasilan program lumbung pangan desa dan diversifikasi tanaman.'],
            ];

            foreach ($prestasi as [$judul, $tahun, $tingkat, $penyelenggara, $deskripsi]) {
                PrestasiDesa::create([
                    'judul' => $judul,
                    'tahun' => $tahun,
                    'tingkat' => $tingkat,
                    'penyelenggara' => $penyelenggara,
                    'deskripsi' => $deskripsi,
                    'tampil' => true,
                ]);
            }
        }
    }
}
